<?php

namespace Drupal\lms_ai\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\Permission;
use Google\Service\Slides;
use Google\Service\Slides\Request;
use Google\Service\Slides\BatchUpdatePresentationRequest;

/**
 * Service to generate Google Slides from templates and JSON data.
 */
class GoogleSlideBuilderService {

  protected $logger;
  protected $configFactory;
  protected $entityTypeManager;
  protected $client;
  protected $driveService;
  protected $slidesService;

  public function __construct(LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->logger = $logger_factory->get('lms_ai');
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Main orchestrator function to build a slide from a template.
   *
   * @param string $templatePresentationId The Google Slides Template ID.
   * @param array $generated_json The Gemini parsed JSON mapping.
   * @param string $lesson_title Title for the new duplicated file.
   * @param string $google_api_client_id The configured entity ID from google_api_client module.
   * @return string|bool The URL or ID of the new file, or FALSE on failure.
   */
  public function buildSlideFromTemplate(string $templatePresentationId, array $generated_json, string $lesson_title, string $google_api_client_id) {
    try {
      // Load Google Client from the google_api_client contributed module (Reverting to standard OAuth due to Google 0-byte Service Account Quota)
      $googleApiClientEntity = $this->entityTypeManager->getStorage('google_api_client')->load($google_api_client_id);
      if (!$googleApiClientEntity) {
         $this->logger->error('Google API Client entity not found for ID: @id', ['@id' => $google_api_client_id]);
         return FALSE;
      }
      
      $google_api_service = \Drupal::service('google_api_client.client');
      $google_api_service->setGoogleApiClient($googleApiClientEntity);
      $this->client = $google_api_service->googleClient;
      
      if (!$this->client) {
         $this->logger->error('Failed to get Google Client instance from google_api_client module.');
         return FALSE;
      }

      $this->driveService = new Drive($this->client);
      $this->slidesService = new Slides($this->client);

      // 1. Duplicate the template
      $newPresentationId = $this->duplicateTemplateSlide($templatePresentationId, $lesson_title);
      if (!$newPresentationId) {
        return FALSE;
      }

      // KIỂM TRA CHẾ ĐỘ: Nếu Gemini trả về mảng slide_content (Prompt mới), chạy cơ chế Nhân bản Slide Động
      if (isset($generated_json['slide_content']) && is_array($generated_json['slide_content'])) {
         $this->buildDynamicSlides($newPresentationId, $generated_json['slide_content']);
      } else {
         // Chế độ Cũ: Dùng cho template Fix cứng 6 slide
         $requests = [];
         $this->flattenJsonToReplaceRequests($generated_json, $requests);
         if (!empty($requests)) {
           $batchUpdateRequest = new BatchUpdatePresentationRequest([
             'requests' => $requests
           ]);
           $this->slidesService->presentations->batchUpdate($newPresentationId, $batchUpdateRequest);
         }
      }

      // 4. Make the file public so it can be embedded via iframe
      $this->makeItPublic($newPresentationId);
      
      return $newPresentationId;

    } catch (\Exception $e) {
      $this->logger->error('Error building slide from template: @message', ['@message' => $e->getMessage()]);
      return FALSE;
    }
  }

  /**
   * Duplicates a Google Drive file and returns the new file's ID.
   */
  protected function duplicateTemplateSlide(string $templatePresentationId, string $newTitle) {
    try {
      $newFile = new \Google\Service\Drive\DriveFile();
      $newFile->setName($newTitle);
      $copiedFile = $this->driveService->files->copy($templatePresentationId, $newFile);
      return $copiedFile->getId();
    } catch (\Exception $e) {
      $this->logger->error('Error cloning template @id: @msg', ['@id' => $templatePresentationId, '@msg' => $e->getMessage()]);
      return FALSE;
    }
  }

  /**
   * Flattens the nested JSON structure into ReplaceAllTextRequests.
   */
  protected function flattenJsonToReplaceRequests(array $data, array &$requests) {
    foreach ($data as $page) {
      if (isset($page['values']) && is_array($page['values'])) {
        $this->extractKeyValuePairs($page['values'], $requests);
      }
    }
  }

  /**
   * Recursive extraction of key-value pairs to build Slide Replace Requests.
   */
  protected function extractKeyValuePairs($array, &$requests) {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $this->extractKeyValuePairs($value, $requests);
      } else {
        // Build ReplaceAllTextRequest
        if (is_string($value) || is_numeric($value)) {
           $requests[] = new Request([
             'replaceAllText' => [
               'containsText' => [
                 'text' => '{{' . $key . '}}',
                 'matchCase' => true
               ],
               'replaceText' => (string) $value
             ]
           ]);
        }
      }
    }
  }

  /**
   * Makes the file readable by anyone with the link.
   */
  protected function makeItPublic(string $presentationId) {
    try {
      $permission = new Permission([
        'type' => 'anyone',
        'role' => 'reader'
      ]);
      $this->driveService->permissions->create($presentationId, $permission);
    } catch (\Exception $e) {
      $this->logger->error('Error setting public permissions: @msg', ['@msg' => $e->getMessage()]);
    }
  }

  /**
   * Sinh các slide động dựa trên mảng slide_content từ Gemini.
   */
  protected function buildDynamicSlides(string $presentationId, array $slideContents) {
    if (empty($slideContents)) {
      return;
    }

    $presentation = $this->slidesService->presentations->get($presentationId);
    $slides = $presentation->getSlides();
    if (empty($slides)) {
      return;
    }

    // Giả định: Slide đầu tiên (index 0) là ảnh bìa, Slide thứ 2 (index 1) là layout thân bài.
    // Nếu Template chỉ có 1 Slide, ta lấy luôn Slide 0 làm Layout mẫu.
    $templateSlide = isset($slides[1]) ? $slides[1] : $slides[0];
    $templateSlideId = $templateSlide->getObjectId();

    $requests = [];
    $newSlideIds = [];

    // BƯỚC 1: Nhân bản (Duplicate) cái Layout mẫu lên N lần tương ứng với N nội dung
    foreach ($slideContents as $index => $slideData) {
      $newSlideId = 'gen_slide_' . uniqid() . '_' . $index;
      $newSlideIds[] = $newSlideId;

      $requests[] = new Request([
        'duplicateObject' => [
          'objectId' => $templateSlideId,
          'objectIds' => [
             $templateSlideId => $newSlideId,
          ],
        ]
      ]);
    }

    if (!empty($requests)) {
      $batchUpdateRequest = new BatchUpdatePresentationRequest([
        'requests' => $requests
      ]);
      $this->slidesService->presentations->batchUpdate($presentationId, $batchUpdateRequest);
    }

    // BƯỚC 2: Điền nội dung vào từng thẻ {{title}} và {{content}} trên CÁC SLIDE MỚI VỪA NHÂN BẢN
    $textRequests = [];
    foreach ($slideContents as $index => $slideData) {
      $targetSlideId = $newSlideIds[$index];
      $title = $slideData['title'] ?? '';
      $htmlHtml = $slideData['html'] ?? '';

      $plainText = str_replace(['<li>', '</li>', '<ul>', '</ul>', '<p>', '</p>', '<br>', '<br/>', '<strong>', '</strong>'], ['- ', "\n", "", "", "", "\n", "\n", "\n", "", ""], $htmlHtml);
      $plainText = trim(strip_tags(html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
      $plainText = preg_replace("/[\r\n]+/", "\n", $plainText);

      $textRequests[] = new Request([
        'replaceAllText' => [
          'containsText' => ['text' => '{{title}}', 'matchCase' => false],
          'replaceText' => $title,
          'pageObjectIds' => [$targetSlideId]
        ]
      ]);

      $textRequests[] = new Request([
        'replaceAllText' => [
          'containsText' => ['text' => '{{content}}', 'matchCase' => false],
          'replaceText' => $plainText,
          'pageObjectIds' => [$targetSlideId]
        ]
      ]);
    }

    // BƯỚC 3: Xoá bỏ cái Template Slide Gốc dư thừa (nếu template gốc là slide 2)
    if (isset($slides[1])) {
       $textRequests[] = new Request([
          'deleteObject' => [
              'objectId' => $templateSlideId
          ]
       ]);
    }

    // BƯỚC 4: Submit lệnh ghi văn bản và xoá trang
    if (!empty($textRequests)) {
      $batchTextRequest = new BatchUpdatePresentationRequest([
        'requests' => $textRequests
      ]);
      $this->slidesService->presentations->batchUpdate($presentationId, $batchTextRequest);
    }
  }

}

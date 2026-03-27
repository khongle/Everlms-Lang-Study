<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_ai\Plugin\AiProviderManager;
use Drupal\lms_lesson\Entity\Lesson;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles AI integrations specifically for Zoom (Auto-Agenda, VTT Summary).
 */
class ZoomAiService {

  public function __construct(
    private readonly ZoomService $zoomApi,
    private readonly AiProviderManager $aiProviderManager,
    private readonly ClientInterface $httpClient,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly LoggerInterface $logger
  ) {}

  /**
   * Generates a Lesson description/agenda based on its name.
   */
  public function generateAgenda(string $topic): ?string {
    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $ai */
      $ai = $this->aiProviderManager->createInstance('openai');
      
      $prompt = sprintf("Write a structured, engaging agenda and description for a live Zoom class titled: '%s'. Format using HTML lists.", $topic);
      $result = $ai->generateText($prompt);
      
      return $result;
    } catch (\Exception $e) {
      $this->logger->error('Failed to generate Zoom agenda: @m', ['@m' => $e->getMessage()]);
    }
    return NULL;
  }

  /**
   * Process pending lesson recordings for Smart Summaries.
   */
  public function processSummaries(): void {
    $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');
    
    // Load lessons that are zoom type, in the past, and don't have a summary tag yet.
    // In a real app, we'd use an explicit field to track summary status.
    $query = $lessonStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('field_type', 'zoom')
      ->condition('field_zoom_schedule', time() - 3600, '<') // At least 1 hr past
      ->range(0, 5); // Process batches of 5

    $lessonIds = $query->execute();
    if (!$lessonIds) {
      return;
    }

    $lessons = $lessonStorage->loadMultiple($lessonIds);

    foreach ($lessons as $lesson) {
      /** @var \Drupal\lms_lesson\Entity\Lesson $lesson */
      if (strpos((string) $lesson->get('field_content')->value, '<!-- AI_SUMMARY_DONE -->') !== FALSE) {
        continue;
      }

      $meetingId = $lesson->get('field_zoom_meeting_id')->value;
      if (!$meetingId) {
        continue;
      }

      $recordingsResponse = $this->zoomApi->getRecordings($meetingId);
      if (empty($recordingsResponse['recording_files'])) {
        continue;
      }

      $transcriptUrl = NULL;
      foreach ($recordingsResponse['recording_files'] as $file) {
        if (in_array($file['file_type'], ['TRANSCRIPT', 'TIMELINE'])) {
          $transcriptUrl = $file['download_url'];
          break;
        }
      }

      if ($transcriptUrl) {
        $this->generateAndSaveSummary($lesson, $transcriptUrl);
      }
    }
  }

  private function generateAndSaveSummary(Lesson $lesson, string $downloadUrl): void {
    try {
      // Download VTT. Needs Zoom Access Token attached.
      // Easiest is to use the ZoomService to fetch it, but requires custom request.
      // As a placeholder, we simulate fetching the raw VTT text.
      $vttText = "Simulated Transcript: The instructor discussed the future of E-Learning extensively.";

      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $ai */
      $ai = $this->aiProviderManager->createInstance('openai');
      $prompt = "Below is a raw VTT transcript of a Zoom Class. Summarize the Key Takeaways in HTML:\n\n" . $vttText;
      
      $summaryHtml = $ai->generateText($prompt);

      if ($summaryHtml) {
        $currentContent = $lesson->get('field_content')->value;
        $updatedContent = $currentContent . "\n<hr>\n<h2>Class Summary (AI)</h2>\n" . $summaryHtml . "\n<!-- AI_SUMMARY_DONE -->";
        $lesson->set('field_content', ['value' => $updatedContent, 'format' => 'full_html']);
        $lesson->save();
        $this->logger->info("Generated Zoom summary for lesson @id", ['@id' => $lesson->id()]);
      }

    } catch (\Exception $e) {
      $this->logger->error('Failed Zoom summary: @m', ['@m' => $e->getMessage()]);
    }
  }

}

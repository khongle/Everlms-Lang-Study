<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Controller for previewing AI JSON outputs via AJAX modals.
 */
class AiPreviewController extends ControllerBase {

  /**
   * Returns an AJAX response containing a modal with the User JSON contents.
   */
  public function previewUsersJson(): AjaxResponse {
    $module_path = \Drupal::moduleHandler()->getModule('lms_ai')->getPath();
    $file_path = \Drupal::root() . '/' . $module_path . '/JSON/ai_users.json';
    
    if (file_exists($file_path)) {
      $json = file_get_contents($file_path);
      $content = [
        '#markup' => '<pre style="max-height: 500px; overflow-y: auto; background: #2b2b2b; color: #a9b7c6; padding: 15px; border-radius: 5px;">' . htmlspecialchars($json) . '</pre>',
      ];
    } else {
      $content = [
        '#markup' => '<div style="padding: 20px; text-align: center; color: #d00;">' . $this->t('File JSON chưa được tạo. Hãy bấm "Run User" trước.') . '</div>',
      ];
    }

    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($this->t('Preview AI Users JSON'), $content, ['width' => 900]));
    return $response;
  }

}

<?php

namespace Drupal\lms_certificate\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Form controller for Certificate Template edit forms.
 */
class CertificateTemplateForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->entity;

    // Attach AJAX callback to the Twig Template string_long input
    if (isset($form['field_layout']['widget'][0]['value'])) {
      $form['field_layout']['widget'][0]['value']['#ajax'] = [
        'callback' => '::updatePreview',
        'wrapper' => 'certificate-preview-wrapper',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Updating preview...'),
        ],
      ];
      $form['field_layout']['widget'][0]['value']['#rows'] = 15;
    }

    $preview_html = '';
    $user_input = $form_state->getUserInput();
    if (isset($user_input['field_layout'][0]['value'])) {
      $preview_html = $user_input['field_layout'][0]['value'];
    } elseif (!$entity->isNew() && $entity->hasField('field_layout') && !$entity->get('field_layout')->isEmpty()) {
      $preview_html = $entity->get('field_layout')->value;
    }

    // Lấy URL thực tế từ form_state (nếu vừa upload chưa save) hoặc từ entity
    $file_url_generator = \Drupal::service('file_url_generator');
    $get_image_url = function($field_name) use ($form_state, $entity, $file_url_generator) {
      $fid = NULL;
      if ($form_state->hasValue($field_name) && !empty($form_state->getValue($field_name)[0]['fids'][0])) {
        $fid = $form_state->getValue($field_name)[0]['fids'][0];
      } elseif ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty() && $entity->get($field_name)->target_id) {
        $fid = $entity->get($field_name)->target_id;
      }
      
      if ($fid) {
        $file = \Drupal\file\Entity\File::load($fid);
        if ($file) {
          return $file_url_generator->generateAbsoluteString($file->getFileUri());
        }
      }
      return '';
    };

    $logo_url = $get_image_url('field_logo');
    $signature_url = $get_image_url('field_signature');
    $background_url = $get_image_url('field_background');

    $placeholders = [
      '[student_name]' => 'Nguyen Van A',
      '[course_name]' => 'LMS Advanced Architecture',
      '[issue_date]' => date('F j, Y'),
      '[certificate_code]' => 'CERT-VNM-123456',
      '[logo]' => $logo_url ?: 'https://via.placeholder.com/200x50?text=[LOGO]',
      '[signature]' => $signature_url ?: 'https://via.placeholder.com/150x50?text=[SIGNATURE]',
      '[background]' => $background_url ?: '',
    ];

    if (!empty($preview_html)) {
      $preview_html = str_replace(array_keys($placeholders), array_values($placeholders), $preview_html);
    } else {
      $preview_html = '<div style="padding:40px; text-align:center; color:#999;">' . $this->t('No template provided yet.') . '</div>';
    }

    // Set height/aspect ratio based on orientation (Landscape default)
    $orientation = 'landscape';
    if ($form_state->hasValue('field_orientation') && !empty($form_state->getValue('field_orientation')[0]['value'])) {
      $orientation = $form_state->getValue('field_orientation')[0]['value'];
    } elseif (!$entity->isNew() && $entity->hasField('field_orientation') && !$entity->get('field_orientation')->isEmpty()) {
      $orientation = $entity->get('field_orientation')->value;
    }
    
    $aspect_ratio = ($orientation === 'portrait') ? '0.707' : '1.414';

    // The background image trick (if available) applies to the preview container.
    $bg_style = $background_url ? "background-image: url('{$background_url}'); background-size: 100% 100%; background-position: center; background-repeat: no-repeat;" : 'background-color: #fff;';

    $form['template_preview'] = [
      '#type' => 'details',
      '#title' => $this->t('Template Default (Live Preview)'),
      '#open' => TRUE,
      '#weight' => 99,
      'preview_container' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'certificate-preview-wrapper', 
          'style' => 'border: 1px solid #ccc; box-shadow: 0 5px 15px rgba(0,0,0,0.15); width: 100%; aspect-ratio: ' . $aspect_ratio . '; position: relative; overflow: hidden; margin-top: 20px; ' . $bg_style
        ],
        'markup' => [
          '#markup' => Markup::create($preview_html),
        ],
      ],
    ];

    return $form;
  }

  public function updatePreview(array &$form, FormStateInterface $form_state) {
    return $form['template_preview']['preview_container'];
  }

  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Certificate Template.', ['%label' => $entity->label()]));
        break;
      default:
        $this->messenger()->addMessage($this->t('Saved the %label Certificate Template.', ['%label' => $entity->label()]));
    }
    $form_state->setRedirect('entity.lms_certificate_template.collection');
  }

}

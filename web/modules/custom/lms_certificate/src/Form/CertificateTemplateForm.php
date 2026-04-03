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
    $seal_url = $get_image_url('field_seal');
    $signature_url = $get_image_url('field_signature');
    $background_url = $get_image_url('field_background');

    $placeholders = [
      '[student_name]' => 'Nguyen Van A',
      '[course_name]' => 'LMS Advanced Architecture',
      '[issue_date]' => date('F j, Y'),
      '[certificate_code]' => 'CERT-VNM-123456',
      '[logo]' => $logo_url ?: 'https://via.placeholder.com/200x50?text=[LOGO]',
      '[seal]' => $seal_url ?: 'https://via.placeholder.com/100x100?text=[SEAL]',
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

    // Set A4 fixed dimensions for scaling mathematically
    $original_width = $orientation === 'portrait' ? 794 : 1123;
    $original_height = $orientation === 'portrait' ? 1123 : 794;
    
    // Scale factor to fit admin interface perfectly on all screens
    $scale = 0.85;
    $scaled_width = $original_width * $scale;
    $scaled_height = $original_height * $scale;

    $bg_style = $background_url ? "background-image: url('{$background_url}'); background-size: 100% 100%; background-position: center; background-repeat: no-repeat;" : 'background-color: #fff;';

    $scaled_preview_html = '
    <style>
      .cert-preview-wrapper-box {
        width: 100%;
        max-width: 1150px;
        margin: 20px auto 60px auto;
        display: flex;
        justify-content: center;
      }
      .cert-preview-outer {
        width: ' . $scaled_width . 'px;
        height: ' . $scaled_height . 'px;
        position: relative;
        overflow: visible;
        border: 1px solid #e2e8f0; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        ' . $bg_style . '
      }
      .cert-preview-inner {
        width: ' . $original_width . 'px;
        height: ' . $original_height . 'px;
        transform-origin: top left;
        transform: scale(' . $scale . ');
        position: absolute;
        top: 0;
        left: 0;
      }
      /* Prevent admin themes like Claro from injecting white backgrounds into layout tables */
      .cert-preview-inner table,
      .cert-preview-inner tr,
      .cert-preview-inner td,
      .cert-preview-inner th,
      .cert-preview-inner img {
        background-color: transparent !important;
        border-color: transparent;
      }
      /* CSS Reset to wipe out Admin theme block margins that push content out of bounds */
      .cert-preview-inner p,
      .cert-preview-inner h1,
      .cert-preview-inner h2,
      .cert-preview-inner h3,
      .cert-preview-inner h4,
      .cert-preview-inner h5,
      .cert-preview-inner h6 {
        margin-top: 0;
        margin-bottom: 0.25em; /* Match pure mPDF minimal padding */
        line-height: 1.2;
      }
      /* Expand the user\'s absolute safe zone so they can fit more text without spilling out */
      .cert-preview-inner > div {
        top: 8% !important;
        bottom: 5% !important;
      }
      /* Prevent admin themes from exploding high-res logos */
      .cert-preview-inner img {
        max-width: 100%;
        box-sizing: border-box;
      }
      .cert-preview-inner img:not([style*="height: 100%"], [style*="height:100%"]) {
        max-height: 200px !important;
        width: auto !important;
      }
      /* Cap the Logo size to prevent it from vertically crowding the text out */
      .cert-preview-inner > div > div:first-child img {
         max-height: 130px !important;
      }
      /* Cap the Seal and Signature size explicitly */
      .cert-preview-inner table img {
         max-height: 140px !important;
      }
    </style>
    <div class="cert-preview-wrapper-box" id="certificate-preview-wrapper">
        <div class="cert-preview-outer">
            <div class="cert-preview-inner">
                ' . $preview_html . '
            </div>
        </div>
    </div>';

    $form['template_preview'] = [
      '#type' => 'details',
      '#title' => $this->t('Template Default (Live Preview) - Scaled accurately for PDF rendering'),
      '#open' => TRUE,
      '#weight' => 99,
      'preview_container' => [
        '#markup' => Markup::create($scaled_preview_html),
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
    $form_state->setRedirect('lms_certificate.admin_template.edit', ['lms_certificate_template' => $entity->id()]);
  }

}

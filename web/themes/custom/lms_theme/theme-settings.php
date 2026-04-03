<?php

/**
 * @file
 * Theme settings file for lms_theme.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function lms_theme_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  $form['lms_theme_settings'] = [
    '#type' => 'details',
    '#title' => t('EverLMS Global Settings'),
    '#open' => TRUE,
  ];

  $form['lms_theme_settings']['homepage_style'] = [
    '#type' => 'select',
    '#title' => t('Homepage Style'),
    '#description' => t('Select the visual design style for the LMS Homepage.'),
    '#default_value' => theme_get_setting('homepage_style') ?? 'glassmorphism',
    '#options' => [
      'glassmorphism' => t('Style 1: Modern Glassmorphism (Dark)'),
      'minimalist' => t('Style 2: Minimalist Clean (Light)'),
    ],
  ];
}

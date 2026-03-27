<?php

declare(strict_types=1);

namespace Drupal\lms_blog\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the LMS Article entity edit forms, powered by AI.
 */
class LmsArticleForm extends ContentEntityForm {

  /**
   * The AI Provider Manager.
   *
   * @var \Drupal\lms_ai\Plugin\AiProviderManager|null
   */
  protected $aiProviderManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    if ($container->has('plugin.manager.lms_ai_provider')) {
      $instance->aiProviderManager = $container->get('plugin.manager.lms_ai_provider');
    }
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    // AI Blog Writer Widget in sidebar
    if ($this->aiProviderManager && $this->currentUser()->hasPermission('administer lms_blog')) {
      $form['ai_writer'] = [
        '#type' => 'details',
        '#title' => $this->t('✨ AI Blog Writer'),
        '#open' => TRUE,
        '#weight' => -99,
        '#attributes' => ['class' => ['ai-writer-widget']],
      ];

      $form['ai_writer']['ai_topic'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Topic'),
        '#description' => $this->t('e.g., The Future of E-Learning'),
        '#maxlength' => 255,
      ];

      $form['ai_writer']['ai_keywords'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Focus Keywords'),
        '#description' => $this->t('Comma separated.'),
      ];

      $form['ai_writer']['ai_style'] = [
        '#type' => 'select',
        '#title' => $this->t('Writing Style'),
        '#options' => [
          'professional' => 'Professional & Academic',
          'casual' => 'Casual & Engaging',
          'persuasive' => 'Persuasive (Marketing)',
        ],
        '#default_value' => 'professional',
      ];

      $form['ai_writer']['generate_btn'] = [
        '#type' => 'button',
        '#value' => $this->t('✨ Generate Everything'),
        '#ajax' => [
          'callback' => '::generateAiContent',
          'event' => 'click',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('AI is thinking... Pls wait ~30s'),
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * AJAX Callback to generate AI content.
   */
  public function generateAiContent(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $topic = $form_state->getValue('ai_topic');
    $keywords = $form_state->getValue('ai_keywords');
    $style = $form_state->getValue('ai_style');

    if (empty($topic)) {
      $response->addCommand(new MessageCommand('Please enter a Topic to generate content.', NULL, ['type' => 'error']));
      return $response;
    }

    $prompt = sprintf(
      "Write a comprehensive blog article about: '%s'. Ensure the tone is %s. Focus on these keywords: %s.",
      $topic,
      $style,
      $keywords ?: 'N/A'
    );

    $schema = '{
      "title": "A catchy, SEO-friendly title",
      "content": "The full article formatted in valid HTML (<h1>, <p>, <ul>). Do NOT include markdown blocks.",
      "seo_title": "SEO Meta Title (max 60 chars)",
      "seo_description": "SEO Meta Description (max 160 chars)"
    }';

    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance('openai'); // Configured default
      
      $result = $provider->generateJson($prompt, $schema);

      if ($result && isset($result['title'], $result['content'])) {
        // Fill form fields
        $response->addCommand(new InvokeCommand('input[name="title[0][value]"]', 'val', [$result['title']]));
        $response->addCommand(new InvokeCommand('input[name="seo_title[0][value]"]', 'val', [$result['seo_title'] ?? $result['title']]));
        $response->addCommand(new InvokeCommand('input[name="seo_description[0][value]"]', 'val', [$result['seo_description'] ?? '']));
        
        // For CKEditor, we usually need to update the instance data, but setting text area value triggers it mostly.
        $response->addCommand(new InvokeCommand('textarea[name="content[0][value]"]', 'val', [$result['content']]));
        // Try CKEditor 5 auto-update trick via JS
        $response->addCommand(new InvokeCommand('textarea[name="content[0][value]"]', 'trigger', ['change']));

        $response->addCommand(new MessageCommand("Success! Generated " . strlen($result['content']) . " bytes of HTML.", NULL, ['type' => 'status']));
      } else {
        $response->addCommand(new MessageCommand('AI failed to return valid JSON structure.', NULL, ['type' => 'error']));
      }

    } catch (\Exception $e) {
      $response->addCommand(new MessageCommand('Error bridging AI: ' . $e->getMessage(), NULL, ['type' => 'error']));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $entity = $this->getEntity();
    $result = $entity->save();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New article %label has been created.', $message_arguments));
      $this->logger('lms_blog')->notice('Created new article %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The article %label has been updated.', $message_arguments));
      $this->logger('lms_blog')->notice('Updated article %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.lms_article.collection');
    return $result;
  }

}

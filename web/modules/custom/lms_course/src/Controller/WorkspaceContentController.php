<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

/**
 * Controller for managing content entities via Workspace.
 */
class WorkspaceContentController extends ControllerBase {

  public static function create(ContainerInterface $container): static {
    return new static();
  }

  /**
   * Renders the content list table.
   */
  public function contentList(Request $request, string $content_type) {
    $type_map = [
      'slideshows' => 'slideshow',
      'introductions' => 'introduction',
      'faqs' => 'faq',
      'glossaries' => 'glossary',
      'news' => 'blog', 
    ];

    if (!isset($type_map[$content_type])) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $bundle = $type_map[$content_type];
    $labels = [
      'slideshows' => 'Slideshow Banners',
      'introductions' => 'Introduction Blocks',
      'faqs' => 'Frequently Asked Questions',
      'glossaries' => 'Glossary Terms',
      'news' => 'News & Blog Articles',
    ];

    $title = $labels[$content_type];
    $active_menu = 'content_' . $content_type;

    $status_filter = $request->query->get('status');
    $search_filter = $request->query->get('search');

    $is_faq = ($content_type === 'faqs');
    $storage = $this->entityTypeManager()->getStorage($is_faq ? 'lms_faq' : 'node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->pager(15);

    if ($is_faq) {
      $query->sort('id', 'DESC');
      if ($search_filter) {
        $query->condition('question', '%' . $search_filter . '%', 'LIKE');
      }
    } else {
      $query->condition('type', $bundle)
        ->sort('created', 'DESC');

      if ($status_filter && $status_filter !== 'all') {
        $query->condition('status', $status_filter === 'published' ? 1 : 0);
      }
      if ($search_filter) {
        $query->condition('title', '%' . $search_filter . '%', 'LIKE');
      }
    }

    $ids = $query->execute();
    $entities = $ids ? $storage->loadMultiple($ids) : [];

    $items = [];
    foreach ($entities as $entity) {
      $thumbnail_url = '';
      if (!$is_faq) {
        $image_fields = ['field_slide_image', 'field_intro_image', 'field_thumb', 'field_image', 'field_thumbnail'];
        foreach ($image_fields as $img_field) {
          if ($entity->hasField($img_field) && !$entity->get($img_field)->isEmpty()) {
            $file = $entity->get($img_field)->entity;
            if ($file) {
              $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
              break;
            }
          }
        }
      }

      $items[] = [
        'id' => $entity->id(),
        'thumbnail_url' => $thumbnail_url,
        'title' => $is_faq ? $entity->label() : $entity->getTitle(),
        'created' => $is_faq ? '' : $entity->getCreatedTime(),
        'status' => $is_faq ? 'N/A' : $entity->isPublished(),
        'edit_url' => $is_faq ? \Drupal\Core\Url::fromRoute('lms_course.workspace_faq.edit', ['lms_faq' => $entity->id()], ['query' => ['destination' => '/workspace/content/faqs']])->toString() : \Drupal\Core\Url::fromRoute('lms_course.workspace_content.edit', ['content_type' => $content_type, 'node' => $entity->id()], ['query' => ['destination' => '/workspace/content/' . $content_type]])->toString(),
        'view_url' => $is_faq ? \Drupal\Core\Url::fromRoute('lms_course.workspace_faq.view', ['lms_faq' => $entity->id()])->toString() : \Drupal\Core\Url::fromRoute('lms_course.workspace_content.view', ['content_type' => $content_type, 'node' => $entity->id()])->toString(),
      ];
    }

    $add_url = $is_faq ? \Drupal\Core\Url::fromRoute('lms_course.workspace_faq.add', [], ['query' => ['destination' => '/workspace/content/faqs']])->toString() : \Drupal\Core\Url::fromRoute('lms_course.workspace_content.add', ['content_type' => $content_type], ['query' => ['destination' => '/workspace/content/' . $content_type]])->toString();

    $hide_thumbnail = $is_faq || ($content_type === 'glossaries');

    $render = [
      '#theme' => 'lms_admin_content',
      '#contents' => $items,
      '#pager' => ['#type' => 'pager'],
      '#filters' => [
        'status' => $status_filter ?? 'all',
        'search' => $search_filter ?? '',
      ],
      '#content_type_label' => $title,
      '#add_content_url' => $add_url,
      '#active_menu' => $active_menu,
      '#hide_thumbnail' => $hide_thumbnail,
    ];

    if ($request->query->get('ajax') == '1') {
      $table_render = [
        '#theme' => 'lms_admin_content_table',
        '#contents' => $items,
        '#pager' => ['#type' => 'pager'],
        '#hide_thumbnail' => $hide_thumbnail,
      ];
      $html = \Drupal::service('renderer')->renderPlain($table_render);
      return new \Symfony\Component\HttpFoundation\Response((string) $html);
    }

    return $render;
  }

  /**
   * Handles bulk actions for the list.
   */
  public function bulkAction(Request $request) {
    \Drupal::logger('lms_course')->info('bulkAction triggered.');
    if ($request->getMethod() !== 'POST') {
      return new JsonResponse(['success' => FALSE, 'message' => 'Method not POST']);
    }
    
    $data = json_decode($request->getContent(), TRUE);
    if (empty($data['action']) || empty($data['ids'])) {
      return new JsonResponse(['success' => FALSE, 'message' => 'Invalid data.']);
    }

    $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($data['ids']);
    
    if (empty($nodes)) {
       return new JsonResponse(['success' => FALSE, 'message' => 'Could not find entities.']);
    }

    $count = 0;
    foreach ($nodes as $node) {
      if ($data['action'] === 'publish') {
        \Drupal::database()->update('node_field_data')->fields(['status' => 1])->condition('nid', $node->id())->execute();
        $count++;
      } elseif ($data['action'] === 'unpublish') {
        \Drupal::database()->update('node_field_data')->fields(['status' => 0])->condition('nid', $node->id())->execute();
        $count++;
      }
      \Drupal\Core\Cache\Cache::invalidateTags($node->getCacheTags());
      \Drupal::entityTypeManager()->getStorage('node')->resetCache([$node->id()]);
    }

    return new JsonResponse(['success' => TRUE, 'message' => "Successfully updated $count items."]);
  }

  /**
   * Embed Content Edit form in UI.
   */
  public function contentEditForm(\Drupal\node\NodeInterface $node, string $content_type): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'default';

    // Verify manager_edit form mode exists, fallback if not
    $display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('node.' . $node->bundle() . '.' . $form_mode);
    if (!$display) {
      $form_mode = 'default';
    }

    $form = \Drupal::service('entity.form_builder')->getForm($node, $form_mode);
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Edit @type: @title', ['@type' => ucfirst($content_type), '@title' => $node->label()]),
      '#active_menu' => 'content_' . $content_type,
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed Content Create form in UI.
   */
  public function contentCreateForm(string $content_type): array {
    $type_map = [
      'slideshows' => 'slideshow',
      'introductions' => 'introduction',
      'glossaries' => 'glossary',
      'news' => 'blog',
    ];

    if (!isset($type_map[$content_type])) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    $bundle = $type_map[$content_type];

    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'default';

    // Verify manager_edit form mode exists, fallback if not
    $display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('node.' . $bundle . '.' . $form_mode);
    if (!$display) {
      $form_mode = 'default';
    }

    $new_node = $this->entityTypeManager()->getStorage('node')->create(['type' => $bundle]);
    $form = \Drupal::service('entity.form_builder')->getForm($new_node, $form_mode);

    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Create New @type', ['@type' => ucfirst($content_type)]),
      '#active_menu' => 'content_' . $content_type,
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed FAQ Edit form in UI.
   */
  public function faqEditForm(\Drupal\Core\Entity\EntityInterface $lms_faq): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'default';

    $display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('lms_faq.lms_faq.' . $form_mode);
    if (!$display) {
      $form_mode = 'default';
    }

    $form = \Drupal::service('entity.form_builder')->getForm($lms_faq, $form_mode);
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Edit FAQ: @title', ['@title' => $lms_faq->label()]),
      '#active_menu' => 'content_faqs',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed FAQ Create form in UI.
   */
  public function faqCreateForm(): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'default';

    $display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('lms_faq.lms_faq.' . $form_mode);
    if (!$display) {
      $form_mode = 'default';
    }

    $new_faq = $this->entityTypeManager()->getStorage('lms_faq')->create();
    $form = \Drupal::service('entity.form_builder')->getForm($new_faq, $form_mode);

    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Create New FAQ'),
      '#active_menu' => 'content_faqs',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed Content View in UI.
   */
  public function contentView(\Drupal\node\NodeInterface $node, string $content_type): array {
    $preview = $this->generatePremiumPreview($node);

    return [
      '#theme' => 'lms_admin_entity_view',
      '#content' => $preview,
      '#title' => $this->t('View @type: @title', ['@type' => ucfirst($content_type), '@title' => $node->label()]),
      '#active_menu' => 'content_' . $content_type,
    ];
  }

  /**
   * Embed FAQ View in UI.
   */
  public function faqView(\Drupal\Core\Entity\EntityInterface $lms_faq): array {
    $preview = $this->generatePremiumPreview($lms_faq);

    return [
      '#theme' => 'lms_admin_entity_view',
      '#content' => $preview,
      '#title' => $this->t('View FAQ: @title', ['@title' => $lms_faq->label()]),
      '#active_menu' => 'content_faqs',
    ];
  }

  /**
   * Extract key visual and textual fields dynamically to build a robust preview.
   */
  private function generatePremiumPreview(\Drupal\Core\Entity\ContentEntityInterface $entity): array {
    $preview = [
      'image_url' => '',
      'fields' => [],
    ];

    // Image auto-detect
    $image_fields = ['field_slide_image', 'field_intro_image', 'field_thumb', 'field_image', 'field_thumbnail', 'field_banner'];
    foreach ($image_fields as $img_field) {
      if ($entity->hasField($img_field) && !$entity->get($img_field)->isEmpty()) {
        $file = $entity->get($img_field)->entity;
        if ($file) {
          $preview['image_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          break;
        }
      }
    }

    // Capture substantive textual fields, excluding meta/boilerplate fields and image.
    $exclude = array_merge($image_fields, ['nid', 'uuid', 'vid', 'type', 'langcode', 'revision_timestamp', 'revision_uid', 'revision_log', 'status', 'uid', 'created', 'changed', 'promote', 'sticky', 'default_langcode', 'revision_translation_affected', 'path', 'menu_link', 'id', 'course_id']);
    
    foreach ($entity->getFields() as $field_name => $field_item_list) {
      if (in_array($field_name, $exclude)) {
        continue;
      }
      if (!$field_item_list->isEmpty()) {
        $definition = $field_item_list->getFieldDefinition();
        $label = $definition->getLabel();
        
        $rendered_field = $field_item_list->view('default');
        
        // If Drupal doesn't know how to render it (e.g. unconfigured base fields), fallback to raw formatting
        if (empty($rendered_field) || (!isset($rendered_field[0]) && !isset($rendered_field['#markup']) && !isset($rendered_field['#theme']))) {
           $type = $definition->getType();
           
           if (in_array($type, ['text_long', 'text_with_summary', 'string_long'])) {
               $markup = $field_item_list->value;
               $format = $field_item_list->format ?? 'full_html';
               $rendered_field = ['#markup' => check_markup($markup, $format)];
           } elseif ($type === 'entity_reference') {
               $labels = [];
               foreach ($field_item_list->referencedEntities() as $ref_entity) {
                   $labels[] = $ref_entity->label();
               }
               $rendered_field = ['#markup' => implode(', ', $labels)];
           } else {
               $rendered_field = ['#markup' => htmlspecialchars($field_item_list->getString())];
           }
        } else {
           $rendered_field['#label_display'] = 'hidden'; // Hide the Drupal default label since we render our own
        }
        
        $preview['fields'][] = [
          'label' => is_string($label) ? $label : $label->render(),
          'content' => $rendered_field,
        ];
      }
    }

    return $preview;
  }
}

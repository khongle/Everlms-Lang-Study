<?php

declare(strict_types=1);

namespace Drupal\lms_blog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_blog\Entity\LmsArticle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for displaying Blog articles on the frontend.
 */
class LmsBlogController extends ControllerBase {

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Displays the list of published articles.
   */
  public function index(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'blog')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 12)
      ->accessCheck(FALSE);
    
    $ids = $query->execute();
    $articles = $storage->loadMultiple($ids);

    return [
      '#theme' => 'lms_blog_list',
      '#articles' => $articles,
      '#attached' => [
        'library' => [
          'lms_blog/frontend', // Let's define optionally if needed
        ],
      ],
      '#cache' => [
        'tags' => ['lms_article_list'],
      ],
    ];
  }

  /**
   * Displays a single article.
   */
  public function view(\Drupal\node\NodeInterface $node): array {
    $is_anonymous = $this->currentUser()->isAnonymous();
    return [
      '#theme' => 'lms_blog_single',
      '#article' => $node,
      '#is_teaser_mode' => $is_anonymous,
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'contexts' => ['user.roles:anonymous'],
      ],
    ];
  }

  /**
   * Title callback for the single article page.
   */
  public function viewTitle(\Drupal\node\NodeInterface $node): string {
    return $node->label();
  }

}

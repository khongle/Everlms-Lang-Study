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
    $storage = $this->entityTypeManager->getStorage('lms_article');
    $query = $storage->getQuery()
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 12)
      ->accessCheck(TRUE);
    
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
  public function view(LmsArticle $lms_article): array {
    $is_anonymous = $this->currentUser()->isAnonymous();
    return [
      '#theme' => 'lms_blog_single',
      '#article' => $lms_article,
      '#is_teaser_mode' => $is_anonymous,
      '#cache' => [
        'tags' => $lms_article->getCacheTags(),
        'contexts' => ['user.roles:anonymous'],
      ],
    ];
  }

  /**
   * Title callback for the single article page.
   */
  public function viewTitle(LmsArticle $lms_article): string {
    return $lms_article->label();
  }

}

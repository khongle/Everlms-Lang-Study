<?php

declare(strict_types=1);

namespace Drupal\lms_content_tools\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\lms_content_tools\Service\ContentToolsManager;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for EverLMS Content Tools.
 */
class ContentToolsCommands extends DrushCommands {

  protected ContentToolsManager $manager;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected FileSystemInterface $fileSystem,
  ) {
    parent::__construct();
    $this->manager = new ContentToolsManager($entityTypeManager, $fileSystem);
  }

  /**
   * Clean all LMS content from the system.
   *
   * @param array $options
   *   Command options.
   * @option types
   *   Comma-separated list of content types to clean.
   * @option include-users
   *   Also delete users (except uid=1).
   * @option dry-run
   *   Only show counts, don't delete.
   *
   * @command everlms:clean
   * @aliases lms-clean
   * @usage everlms:clean
   *   Delete all LMS content.
   * @usage everlms:clean --types=courses,quizzes
   *   Delete only courses and quizzes.
   * @usage everlms:clean --dry-run
   *   Show what would be deleted.
   */
  public function clean(array $options = [
    'types' => NULL,
    'include-users' => FALSE,
    'dry-run' => FALSE,
  ]): void {
    $groups = $this->manager->getEntityGroups();
    $selectedGroups = array_keys($groups);

    // Filter by types if specified.
    if (!empty($options['types'])) {
      $selectedGroups = array_intersect(
        explode(',', $options['types']),
        array_keys($groups)
      );
      if (empty($selectedGroups)) {
        $this->logger()->error(dt('No valid types specified. Available: @types', [
          '@types' => implode(', ', array_keys($groups)),
        ]));
        return;
      }
    }

    // Show what will be deleted.
    $this->io()->title('EverLMS Content Clean');
    $totalCount = 0;
    $rows = [];
    foreach ($selectedGroups as $groupKey) {
      $count = $this->manager->countGroup($groupKey);
      $rows[] = [$groups[$groupKey]['label'], number_format($count)];
      $totalCount += $count;
    }

    if ($options['include-users']) {
      $userCount = max(0, $this->manager->countEntities('user') - 2);
      $rows[] = ['Users (except admin)', number_format($userCount)];
      $totalCount += $userCount;
    }

    $this->io()->table(['Content Type', 'Count'], $rows);
    $this->io()->text(dt('Total: @count entities', ['@count' => number_format($totalCount)]));

    if ($options['dry-run']) {
      $this->logger()->notice(dt('Dry run — no entities deleted.'));
      return;
    }

    if ($totalCount === 0) {
      $this->logger()->notice(dt('No entities to delete.'));
      return;
    }

    // When -y/--yes is passed, Drush auto-confirms io()->confirm() but
    // io()->ask() still blocks. Detect --yes via input option.
    $autoConfirm = $this->input()->getOption('yes');

    if (!$autoConfirm) {
      // Confirm.
      if (!$this->io()->confirm(dt('Are you sure you want to delete @count entities? This cannot be undone.', [
        '@count' => number_format($totalCount),
      ]), FALSE)) {
        $this->logger()->notice(dt('Cancelled.'));
        return;
      }

      // Second confirmation: type CLEAN.
      $input = $this->io()->ask(dt('Type CLEAN to confirm'));
      if ($input !== 'CLEAN') {
        $this->logger()->notice(dt('Confirmation failed. Cancelled.'));
        return;
      }
    }

    // Execute deletion.
    $startTime = microtime(TRUE);
    $totalDeleted = 0;

    foreach ($selectedGroups as $groupKey) {
      $results = $this->manager->deleteGroup($groupKey);
      $groupTotal = array_sum($results);
      $totalDeleted += $groupTotal;
      $this->logger()->success(dt('Deleted @count @label', [
        '@count' => $groupTotal,
        '@label' => $groups[$groupKey]['label'],
      ]));
    }

    if ($options['include-users']) {
      $userCount = $this->manager->deleteUsers(1);
      $totalDeleted += $userCount;
      $this->logger()->success(dt('Deleted @count users', ['@count' => $userCount]));
    }

    $elapsed = microtime(TRUE) - $startTime;
    $this->logger()->success(dt('Done! Deleted @count entities in @time seconds.', [
      '@count' => number_format($totalDeleted),
      '@time' => number_format($elapsed, 1),
    ]));
  }

  /**
   * Manage seed input for AI content generation.
   *
   * @param array $options
   *   Command options.
   * @option show
   *   Display current seed input.
   * @option validate
   *   Validate the seed input file.
   * @option export
   *   Output seed input JSON to stdout.
   * @option import
   *   Import seed input from a file path.
   *
   * @command everlms:seed-input
   * @aliases lms-seed
   * @usage everlms:seed-input --show
   *   Display current seed input.
   * @usage everlms:seed-input --import=/path/to/file.json
   *   Import seed input from file.
   */
  public function seedInput(array $options = [
    'show' => FALSE,
    'validate' => FALSE,
    'export' => FALSE,
    'import' => NULL,
  ]): void {
    $seedPath = $this->manager->getSeedInputPath();

    if ($options['import']) {
      $importPath = $options['import'];
      if (!file_exists($importPath)) {
        $this->logger()->error(dt('File not found: @path', ['@path' => $importPath]));
        return;
      }
      $content = file_get_contents($importPath);
      $data = json_decode($content, TRUE);
      if ($data === NULL) {
        $this->logger()->error(dt('Invalid JSON in file: @path', ['@path' => $importPath]));
        return;
      }
      if ($this->manager->saveSeedInput($data)) {
        $this->logger()->success(dt('Seed input imported from @path', ['@path' => $importPath]));
      }
      else {
        $this->logger()->error(dt('Failed to save seed input.'));
      }
      return;
    }

    $data = $this->manager->loadSeedInput();

    if ($data === NULL) {
      $this->logger()->warning(dt('No seed input file found at @path. Use the form at /workspace/content-tools/seed-input to create one.', [
        '@path' => $seedPath,
      ]));
      return;
    }

    if ($options['export']) {
      $this->io()->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      return;
    }

    if ($options['validate']) {
      $errors = $this->validateSeedInput($data);
      if (empty($errors)) {
        $this->logger()->success(dt('Seed input is valid.'));
      }
      else {
        foreach ($errors as $error) {
          $this->logger()->error($error);
        }
      }
      return;
    }

    // Default: --show behavior.
    $this->io()->title('EverLMS Seed Input');
    $this->io()->text(dt('File: @path', ['@path' => $seedPath]));
    $this->io()->text(dt('Generated at: @date', ['@date' => $data['generated_at'] ?? 'unknown']));
    $this->io()->newLine();

    $this->io()->section('Organization');
    $this->io()->text($data['organization']['name'] ?? 'N/A');

    $this->io()->section('Training Program');
    $this->io()->text($data['training_program']['name'] ?? 'N/A');

    $this->io()->section('Courses (' . count($data['courses'] ?? []) . ')');
    foreach ($data['courses'] ?? [] as $course) {
      $this->io()->text(dt('  @id. @title [@level] — @chapters chapters x @lessons lessons', [
        '@id' => $course['id'],
        '@title' => $course['title'],
        '@level' => $course['level'],
        '@chapters' => $course['chapters_count'],
        '@lessons' => $course['lessons_per_chapter'],
      ]));
    }

    $this->io()->section('Instructors (' . count($data['instructors'] ?? []) . ')');
    foreach ($data['instructors'] ?? [] as $instructor) {
      $this->io()->text(dt('  @name <@email>', [
        '@name' => $instructor['name'],
        '@email' => $instructor['email'],
      ]));
    }
  }

  /**
   * Import generated content into the system.
   *
   * @param array $options
   *   Command options.
   * @option from-manifest
   *   Import using manifest.json order.
   * @option type
   *   Import only a specific type (courses, users, quizzes, etc).
   * @option file
   *   Import a specific generated file.
   * @option dry-run
   *   Validate files without importing.
   *
   * @command everlms:import
   * @aliases lms-import
   * @usage everlms:import
   *   Import all generated content.
   * @usage everlms:import --type=courses
   *   Import only courses.
   * @usage everlms:import --dry-run
   *   Validate generated files.
   */
  public function import(array $options = [
    'from-manifest' => FALSE,
    'type' => NULL,
    'file' => NULL,
    'dry-run' => FALSE,
  ]): void {
    $generatedPath = $this->manager->getGeneratedPath();

    if (!is_dir($generatedPath)) {
      $this->logger()->error(dt('Generated content directory not found at @path. Run AI content generation first.', [
        '@path' => $generatedPath,
      ]));
      return;
    }

    $manifest = $this->manager->loadManifest();
    if (!$manifest) {
      $this->logger()->error(dt('No manifest.json found in @path. Ensure AI has generated content with a manifest.', [
        '@path' => $generatedPath,
      ]));
      return;
    }

    $this->io()->title('EverLMS Content Import');
    $this->io()->text(dt('Generated by: @by at @date', [
      '@by' => $manifest['generated_by'] ?? 'unknown',
      '@date' => $manifest['generated_at'] ?? 'unknown',
    ]));

    // Show summary.
    if (!empty($manifest['summary'])) {
      $rows = [];
      foreach ($manifest['summary'] as $key => $value) {
        $rows[] = [str_replace('_', ' ', ucfirst($key)), $value];
      }
      $this->io()->table(['Content', 'Count'], $rows);
    }

    // Determine files to import.
    $importOrder = $manifest['import_order'] ?? [];
    if ($options['file']) {
      $importOrder = [$options['file']];
    }
    elseif ($options['type']) {
      $importOrder = array_filter($importOrder, function ($file) use ($options) {
        return str_starts_with($file, $options['type'] . '/');
      });
    }

    if (empty($importOrder)) {
      $this->logger()->warning(dt('No files to import.'));
      return;
    }

    // Validate files exist.
    $validFiles = [];
    foreach ($importOrder as $file) {
      $fullPath = $generatedPath . '/' . $file;
      if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $json = json_decode($content, TRUE);
        if ($json !== NULL) {
          $validFiles[$file] = $json;
          $this->logger()->info(dt('Valid: @file', ['@file' => $file]));
        }
        else {
          $this->logger()->error(dt('Invalid JSON: @file', ['@file' => $file]));
        }
      }
      else {
        $this->logger()->warning(dt('File not found: @file', ['@file' => $file]));
      }
    }

    if ($options['dry-run']) {
      $this->logger()->success(dt('Dry run complete. @valid/@total files are valid.', [
        '@valid' => count($validFiles),
        '@total' => count($importOrder),
      ]));
      return;
    }

    if (!$this->io()->confirm(dt('Import @count files?', ['@count' => count($validFiles)]), TRUE)) {
      $this->logger()->notice(dt('Cancelled.'));
      return;
    }

    // Import each file.
    $imported = 0;
    $errors = 0;

    foreach ($validFiles as $file => $data) {
      try {
        $count = $this->importFile($file, $data);
        $imported += $count;
        $this->logger()->success(dt('Imported @count entities from @file', [
          '@count' => $count,
          '@file' => $file,
        ]));
      }
      catch (\Exception $e) {
        $errors++;
        $this->logger()->error(dt('Error importing @file: @message', [
          '@file' => $file,
          '@message' => $e->getMessage(),
        ]));
      }
    }

    $this->logger()->success(dt('Import complete: @imported entities imported, @errors errors.', [
      '@imported' => $imported,
      '@errors' => $errors,
    ]));
  }

  /**
   * Import a single generated file.
   */
  protected function importFile(string $filename, array $data): int {
    $count = 0;

    // Determine type from filename path.
    $type = explode('/', $filename)[0] ?? '';

    switch ($type) {
      case 'users':
        $count = $this->importUsers($data);
        break;

      case 'courses':
        $count = $this->importCourse($data);
        break;

      case 'quizzes':
        $count = $this->importQuiz($data);
        break;

      case 'certificates':
        $count = $this->importCertificates($data);
        break;

      case 'commerce':
        $count = $this->importCommerce($data);
        break;

      case 'community':
        $count = $this->importCommunity($data);
        break;

      case 'user_data':
        $count = $this->importUserData($filename, $data);
        break;

      case 'slideshows':
        $count = $this->importSlideshows($data);
        break;

      case 'introductions':
        $count = $this->importIntroductions($data);
        break;

      case 'glossaries':
        $count = $this->importGlossaries($data);
        break;

      case 'blogs':
        $count = $this->importBlogs($data);
        break;

      case 'faqs':
        $count = $this->importFaqs($data);
        break;

      case 'category':
        $count = $this->importCategories($data);
        break;

      default:
        $this->logger()->warning(dt('Unknown import type: @type', ['@type' => $type]));
    }

    return $count;
  }

  /**
   * Import users from generated data.
   */
  protected function importUsers(array $data): int {
    $count = 0;
    $userStorage = $this->entityTypeManager->getStorage('user');

    $users = $data['instructors'] ?? $data['students'] ?? [];
    foreach ($users as $userData) {
      $existing = $userStorage->loadByProperties(['mail' => $userData['email']]);
      if (!empty($existing)) {
        // Nếu user đã tồn tại, ta load lên để update (vd: gắn thêm hình ảnh mới tải)
        $user = reset($existing);
      } else {
        $fields = [
          'name' => $userData['username'] ?? $userData['email'],
          'mail' => $userData['email'],
          'pass' => $userData['password'] ?? 'ChangeMe@123',
          'status' => 1,
        ];
        $user = $userStorage->create($fields);
      }

      // Safely set display name if field exists.
      $displayName = $userData['display_name'] ?? $userData['name'] ?? '';
      if ($displayName && $user->hasField('field_display_name')) {
        $user->set('field_display_name', $displayName);
      }

      $role = $userData['role'] ?? 'student';
      if ($role !== 'authenticated') {
        // Check role exists before adding.
        $roleEntities = $this->entityTypeManager->getStorage('user_role')->loadMultiple([$role]);
        if (!empty($roleEntities)) {
          $user->addRole($role);
        }
        else {
          $this->logger()->warning(dt('Role @role does not exist, skipping role assignment for @user', [
            '@role' => $role,
            '@user' => $userData['email'],
          ]));
        }
      }

      // Map profile string fields.
      if (!empty($userData['first_name']) && $user->hasField('field_first_name')) {
        $user->set('field_first_name', $userData['first_name']);
      }
      if (!empty($userData['last_name']) && $user->hasField('field_last_name')) {
        $user->set('field_last_name', $userData['last_name']);
      }
      if (!empty($userData['job_title']) && $user->hasField('field_job_title')) {
        $user->set('field_job_title', $userData['job_title']);
      }
      if (!empty($userData['company']) && $user->hasField('field_company')) {
        $user->set('field_company', $userData['company']);
      }
      if (!empty($userData['location']) && $user->hasField('field_location')) {
        $user->set('field_location', $userData['location']);
      }
      if (!empty($userData['tax_number']) && $user->hasField('field_tax_number')) {
        $user->set('field_tax_number', $userData['tax_number']);
      }

      // Map bio as rich text (text_long).
      if (!empty($userData['bio']) && $user->hasField('field_bio')) {
        $user->set('field_bio', [
          'value' => $userData['bio'],
          'format' => 'full_html',
        ]);
      }

      // Map receive_notifications boolean.
      if (isset($userData['notifications']) && $user->hasField('field_receive_notifications')) {
        $user->set('field_receive_notifications', (bool) $userData['notifications']);
      }

      // Map social_links (link field, multi-value).
      if (!empty($userData['social_links']) && $user->hasField('field_social_links')) {
        $links = [];
        foreach ($userData['social_links'] as $link) {
          if (!empty($link['url'])) {
            $links[] = [
              'uri' => $link['url'],
              'title' => $link['title'] ?? '',
            ];
          }
        }
        if (!empty($links)) {
          $user->set('field_social_links', $links);
        }
      }

      // Map preferred_categories (entity_reference → taxonomy terms).
      if (!empty($userData['preferred_categories']) && $user->hasField('field_preferred_categories')) {
        $termIds = [];
        foreach ($userData['preferred_categories'] as $categoryName) {
          $termId = $this->getOrCreateTerm('course_category', $categoryName);
          if ($termId) {
            $termIds[] = ['target_id' => $termId];
          }
        }
        if (!empty($termIds)) {
          $user->set('field_preferred_categories', $termIds);
        }
      }

      $user->save();

      // Xử lý import hình ảnh đại diện (avatar)
      if (!empty($userData['avatar_filename'])) {
        // Đúng theo chuẩn tài liệu: ảnh avatar đã được copy thẳng vào thư mục public://generated/ 
        // Bằng lệnh thủ công cp /*/* sang /generated/
        $avatarPath = $userData['avatar_filename'];
        $file = $this->createManagedFileFromGenerated($avatarPath, 'public://pictures/');
        if ($file) {
          $user->set('user_picture', [
            'target_id' => $file->id(),
            'alt' => $userData['username'],
          ]);
          $user->save();
        }
      }

      $count++;
    }

    return $count;
  }

  /**
   * Import a course with chapters and lessons.
   *
   * Relationship: Course --field_chapters--> Chapter --field_lessons--> Lesson
   * (parent references children, not the other way around)
   */
  protected function importCourse(array $data): int {
    $count = 0;
    $courseData = $data['course'] ?? [];
    if (empty($courseData['title'])) {
      return 0;
    }

    $courseStorage = $this->entityTypeManager->getStorage('lms_course');

    $existing = $courseStorage->loadByProperties(['name' => $courseData['title']]);
    if (!empty($existing)) {
      $this->logger()->notice(dt('Course already exists: @title — deleting to recreate with new content.', ['@title' => $courseData['title']]));
      $course = reset($existing);
      
      // Delete existing chapters and lessons to prevent bloat.
      $chapters = $course->get('field_chapters')->referencedEntities();
      foreach ($chapters as $chapter) {
        $lessons = $chapter->get('field_lessons')->referencedEntities();
        foreach ($lessons as $lesson) {
          $lesson->delete();
        }
        $chapter->delete();
      }
      // Finally, delete the course entity itself.
      $course->delete();
    }

    // Find instructor by email.
    $instructorUid = 1;
    if (!empty($courseData['instructor_email'])) {
      $users = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(['mail' => $courseData['instructor_email']]);
      if (!empty($users)) {
        $instructorUid = (int) reset($users)->id();
      }
    }

    // Create lessons and chapters first, then link to course.
    $chapterStorage = $this->entityTypeManager->getStorage('lms_chapter');
    $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');
    $chapterIds = [];

    foreach ($data['chapters'] ?? [] as $chapterWeight => $chapterData) {
      // Create lessons for this chapter.
      $lessonIds = [];
      foreach ($chapterData['lessons'] ?? [] as $lessonWeight => $lessonData) {
        $lessonFields = [
          'name' => $lessonData['title'],
          'status' => 1,
          'field_type' => $lessonData['type'] ?? 'text',
          'field_content' => [
            'value' => $lessonData['content'] ?? '',
            'format' => 'full_html',
          ],
          'weight' => $lessonData['weight'] ?? $lessonWeight,
          'field_duration' => (int) ($lessonData['duration'] ?? 0),
          'field_free_preview' => $lessonData['is_preview'] ?? FALSE,
        ];

        if (!empty($lessonData['video_url'])) {
          $lessonFields['field_video_url'] = $lessonData['video_url'];
        }

        // Track SCORM filename so we can extract AFTER the lesson entity is saved
        // (extraction needs the lesson ID which is only available post-save).
        $scormFilenameToExtract = NULL;
        if (($lessonData['type'] ?? '') === 'scorm' && !empty($lessonData['scorm_filename'])) {
          $scormFilenameToExtract = $lessonData['scorm_filename'];
          $scormFile = $this->createScormManagedFileFromAgent($scormFilenameToExtract);
          if ($scormFile) {
            $lessonFields['field_scorm_package'] = [
              'target_id' => $scormFile->id(),
              'display' => 1,
            ];
          }
        }

        $lesson = $lessonStorage->create($lessonFields);
        $lesson->save();

        // Now that the lesson ID exists, extract the SCORM ZIP.
        if ($scormFilenameToExtract) {
          $this->extractScormForLesson((int) $lesson->id(), $scormFilenameToExtract);
        }

        $lessonIds[] = $lesson->id();
        $count++;
      }

      // Create chapter and link lessons.
      $chapter = $chapterStorage->create([
        'name' => $chapterData['title'],
        'status' => 1,
        'field_description' => [
          'value' => $chapterData['description'] ?? '',
          'format' => 'full_html',
        ],
        'field_lessons' => $lessonIds,
        'weight' => $chapterData['weight'] ?? $chapterWeight,
      ]);
      $chapter->save();
      $chapterIds[] = $chapter->id();
      $count++;
    }

    // Create course and link chapters.
    $courseFields = [
      'name' => $courseData['title'],
      'status' => ($courseData['status'] ?? 'published') === 'published' ? 1 : 0,
      'field_description' => [
        'value' => $courseData['description'] ?? '',
        'format' => 'full_html',
      ],
      'field_chapters' => $chapterIds,
      'field_price' => $courseData['price'] ?? 0,
      'field_price_type' => $courseData['pricing_type'] ?? 'free',
      'field_featured' => $courseData['featured'] ?? FALSE,
      'uid' => $instructorUid,
      'field_instructor' => [$instructorUid],
    ];

    // field_short_description is text_long, needs value+format.
    if (!empty($courseData['short_description'])) {
      $courseFields['field_short_description'] = [
        'value' => $courseData['short_description'],
        'format' => 'full_html',
      ];
    }

    // Map Category, Level, and Language to taxonomy terms
    if (!empty($courseData['category'])) {
      $term_id = $this->getOrCreateTerm('course_category', $courseData['category']);
      if ($term_id) {
        $courseFields['field_category'] = ['target_id' => $term_id];
      }
    }

    if (!empty($courseData['level'])) {
      $levelName = ucfirst($courseData['level']);
      $term_id = $this->getOrCreateTerm('course_level', $levelName);
      if ($term_id) {
        $courseFields['field_level'] = ['target_id' => $term_id];
      }
    }

    if (!empty($courseData['language'])) {
      $term_id = $this->getOrCreateTerm('course_language', $courseData['language']);
      if ($term_id) {
        $courseFields['field_language'] = ['target_id' => $term_id];
      }
    }

    // Map Certificate Template if course has certificate
    if (!empty($courseData['has_certificate'])) {
      $certStorage = $this->entityTypeManager->getStorage('lms_certificate_template');
      $templates = $certStorage->loadByProperties(['status' => 1]);
      if (!empty($templates)) {
        // Pick a random template from available templates for variety
        $random_key = array_rand($templates);
        $template = $templates[$random_key];
        $courseFields['field_certificate_template'] = ['target_id' => $template->id()];
      }
    }

    if (!empty($courseData['thumbnail_filename'])) {
      $file = $this->createManagedFileFromGenerated($courseData['thumbnail_filename']);
      if ($file) {
        $courseFields['field_thumbnail'] = [
          'target_id' => $file->id(),
          'alt' => $courseData['title'],
        ];
      }
    }

    if (!empty($courseData['banner_filename'])) {
      $file = $this->createManagedFileFromGenerated($courseData['banner_filename']);
      if ($file) {
        $courseFields['field_banner'] = [
          'target_id' => $file->id(),
          'alt' => $courseData['title'],
        ];
      }
    }

    // Map the 3 new descriptive introduction fields.
    if (!empty($courseData['what_you_will_learn'])) {
      $courseFields['field_what_learn'] = [
        'value' => $courseData['what_you_will_learn'],
        'format' => 'full_html',
      ];
    }

    if (!empty($courseData['prerequisites'])) {
      $courseFields['field_prerequisites'] = [
        'value' => $courseData['prerequisites'],
        'format' => 'full_html',
      ];
    }

    if (!empty($courseData['who_this_course_is_for'])) {
      $courseFields['field_who_for'] = [
        'value' => $courseData['who_this_course_is_for'],
        'format' => 'full_html',
      ];
    }

    $course = $courseStorage->create($courseFields);
    $course->save();
    $count++;

    return $count;
  }

  /**
   * Import a quiz with questions and answers.
   *
   * Relationship: Quiz --field_questions--> Question --field_choices--> Answer
   * (parent references children)
   */
  protected function importQuiz(array $data): int {
    $count = 0;
    $quizData = $data['quiz'] ?? [];
    if (empty($quizData['title'])) {
      return 0;
    }

    $questionStorage = $this->entityTypeManager->getStorage('lms_question');
    $answerStorage = $this->entityTypeManager->getStorage('lms_answer');
    $questionIds = [];

    // Create questions first.
    foreach ($data['questions'] ?? [] as $qWeight => $qData) {
      $questionFields = [
        'name' => $qData['text'],
        'type' => $qData['type'],
        'field_points' => $qData['points'] ?? 5,
      ];

      // Explanation is text_long.
      if (!empty($qData['explanation'])) {
        $questionFields['field_explanation'] = [
          'value' => $qData['explanation'],
          'format' => 'full_html',
        ];
      }

      // Handle type-specific fields.
      if ($qData['type'] === 'true_false' && isset($qData['correct_answer'])) {
        $questionFields['field_correct_answer'] = $qData['correct_answer'];
      }
      if (in_array($qData['type'], ['fill_blank', 'short_answer']) && !empty($qData['correct_answers'])) {
        $questionFields['field_correct_answers'] = $qData['correct_answers'];
      }
      if ($qData['type'] === 'essay') {
        if (!empty($qData['min_words'])) {
          $questionFields['field_min_words'] = (int) $qData['min_words'];
        }
      }
      if ($qData['type'] === 'ordering' && !empty($qData['items'])) {
        // Ordered items stored as string list in correct sequence.
        $orderedTexts = [];
        $items = $qData['items'];
        usort($items, fn($a, $b) => ($a['correct_position'] ?? 0) - ($b['correct_position'] ?? 0));
        foreach ($items as $item) {
          $orderedTexts[] = $item['text'];
        }
        $questionFields['field_ordered_items'] = $orderedTexts;
      }

      $question = $questionStorage->create($questionFields);
      $question->save();
      $count++;

      // Create answers for choice-type questions and link via field_choices.
      if (in_array($qData['type'], ['single_choice', 'multiple_choice']) && !empty($qData['answers'])) {
        $answerIds = [];
        foreach ($qData['answers'] as $aData) {
          $answer = $answerStorage->create([
            'name' => $aData['text'],
            'field_correct' => $aData['is_correct'] ?? FALSE,
          ]);
          $answer->save();
          $answerIds[] = $answer->id();
          $count++;
        }
        // Link answers to question.
        $question->set('field_choices', $answerIds);
        $question->save();
      }

      // Create matching pairs and link via field_matching_pairs.
      if ($qData['type'] === 'matching' && !empty($qData['pairs'])) {
        $pairStorage = $this->entityTypeManager->getStorage('lms_matching_pair');
        $pairIds = [];
        foreach ($qData['pairs'] as $pair) {
          $matchingPair = $pairStorage->create([
            'field_left_item' => $pair['left'],
            'field_right_item' => $pair['right'],
          ]);
          $matchingPair->save();
          $pairIds[] = $matchingPair->id();
          $count++;
        }
        $question->set('field_matching_pairs', $pairIds);
        $question->save();
      }

      $questionIds[] = $question->id();
    }

    // Create quiz and link questions.
    $quizStorage = $this->entityTypeManager->getStorage('lms_quiz');
    $quiz = $quizStorage->create([
      'name' => $quizData['title'],
      'field_body' => [
        'value' => $quizData['instructions'] ?? '',
        'format' => 'full_html',
      ],
      'field_threshold' => (int) ($quizData['pass_threshold'] ?? 70),
      'field_time_limit' => (int) ($quizData['time_limit'] ?? 30),
      'field_take_quiz_number' => (int) ($quizData['max_attempts'] ?? 3),
      'field_form_style' => $quizData['display_style'] ?? 'question_step',
      'field_questions' => $questionIds,
      'uid' => 1,
    ]);
    $quiz->save();
    $count++;

    return $count;
  }

  /**
   * Import certificates from generated data.
   */
  protected function importCertificates(array $data): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_certificate_template');

    // Helper to load images from .agent folder
    $load_image = function($filename, $type) {
      if (!$filename) return NULL;
      $physicalPath = DRUPAL_ROOT . '/../.agent/images/image_' . $type . '/' . $filename;
      if (file_exists($physicalPath)) {
        $fileData = file_get_contents($physicalPath);
        $fileRepo = \Drupal::service('file.repository');
        // Extract just the filename to avoid nested directory creation issues
        $basename = basename($filename);
        // e.g. public://certificates/backgrounds/bg_1.png
        $uri = 'public://certificates/' . $type . 's/' . $basename;
        
        // Ensure the base directory exists
        $dir = 'public://certificates/' . $type . 's';
        \Drupal::service('file_system')->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);
        
        return $fileRepo->writeData($fileData, $uri, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
      }
      return NULL;
    };

    foreach ($data['templates'] ?? [] as $templateData) {
      $design = strtolower($templateData['design'] ?? 'modern');
      
      // Load respective blueprint html
      $blueprint_path = __DIR__ . '/../Blueprints/' . $design . '.html';
      if (!file_exists($blueprint_path)) {
        // Fallback to modern if specific design file doesn't exist
        $blueprint_path = __DIR__ . '/../Blueprints/modern_certificate.html';
      }
      $blueprint_html = file_exists($blueprint_path) ? file_get_contents($blueprint_path) : '';

      // Fix placeholder brackets according to the actual tokens supported
      $body_text = $templateData['body_text'] ?? '';
      $body_text = str_replace(
        ['{student_name}', '{course_name}', '{completion_date}'], 
        ['[student_name]', '[course_name]', '[issue_date]'], 
        $body_text
      );

      // Inject JSON data into blueprint
      // We ONLY replace text variables. We leave [logo], [background] and standard tokens intact.
      $injected_html = str_replace(
        ['%body_text%', '%issuer%', '%signature_label%'],
        [
          $body_text,
          $templateData['issuer'] ?? '',
          $templateData['signature_label'] ?? ''
        ],
        $blueprint_html
      );

      $bg_file = $load_image($templateData['background_filename'] ?? NULL, 'background');
      $logo_file = $load_image($templateData['logo_filename'] ?? NULL, 'logo');
      $sig_file = $load_image($templateData['signature_filename'] ?? NULL, 'signature');
      $seal_file = $load_image($templateData['seal_filename'] ?? NULL, 'seal');

      $fields = [
        'name' => $templateData['name'] ?? 'Certificate Template',
        'status' => 1,
        'field_orientation' => $templateData['layout'] ?? 'landscape',
        'field_design' => $design,
        'field_layout' => [
          'value' => $injected_html,
          'format' => 'full_html',
        ],
      ];

      if ($bg_file) $fields['field_background'] = [
        'target_id' => $bg_file->id(), 
        'alt' => 'Background - ' . ($templateData['name'] ?? 'Template')
      ];
      if ($logo_file) $fields['field_logo'] = [
        'target_id' => $logo_file->id(), 
        'alt' => 'Logo - ' . ($templateData['issuer'] ?? 'Issuer')
      ];
      if ($sig_file) $fields['field_signature'] = [
        'target_id' => $sig_file->id(), 
        'alt' => 'Signature - ' . ($templateData['signature_label'] ?? 'Official')
      ];
      if ($seal_file) $fields['field_seal'] = [
        'target_id' => $seal_file->id(), 
        'alt' => 'Seal - ' . ($templateData['issuer'] ?? 'Official')
      ];

      $template = $storage->create($fields);
      $template->save();
      $count++;
    }

    return $count;
  }

  /**
   * Import commerce data (membership plans).
   */
  protected function importCommerce(array $data): int {
    $count = 0;

    if (!empty($data['membership_plans'])) {
      $storage = $this->entityTypeManager->getStorage('lms_membership_plan');
      foreach ($data['membership_plans'] as $planData) {
        // Upsert: update existing plan if it already exists.
        $existing = $storage->loadByProperties(['name' => $planData['name']]);
        if (!empty($existing)) {
          $plan = reset($existing);
          $this->logger()->notice(dt('Updating existing membership plan: @name', ['@name' => $planData['name']]));
        }
        else {
          $plan = $storage->create(['name' => $planData['name']]);
        }

        // Core fields.
        $plan->set('status', 1);
        $plan->set('field_price', $planData['price'] ?? 0);

        // field_description is text_long → needs value + format.
        if (!empty($planData['description'])) {
          $plan->set('field_description', [
            'value'  => $planData['description'],
            'format' => 'full_html',
          ]);
        }

        // field_features is string with cardinality unlimited → array of values.
        if (!empty($planData['features']) && is_array($planData['features'])) {
          $featureValues = array_map(fn($f) => ['value' => $f], $planData['features']);
          $plan->set('field_features', $featureValues);
        }

        // field_duration_days is integer (JSON key: duration_days).
        if (isset($planData['duration_days'])) {
          $plan->set('field_duration_days', (int) $planData['duration_days']);
        }
        elseif (isset($planData['duration'])) {
          // Legacy fallback: map string "monthly" → 30 days.
          $durationMap = ['monthly' => 30, 'quarterly' => 90, 'yearly' => 365, 'lifetime' => 36500];
          $plan->set('field_duration_days', $durationMap[$planData['duration']] ?? 30);
        }

        // field_billing_cycle is list_string: monthly|quarterly|yearly|lifetime.
        if (!empty($planData['billing_cycle'])) {
          $plan->set('field_billing_cycle', $planData['billing_cycle']);
        }
        elseif (!empty($planData['duration'])) {
          // Legacy fallback: duration string may be billing cycle.
          $plan->set('field_billing_cycle', $planData['duration']);
        }

        // field_recommended is boolean.
        if (isset($planData['recommended'])) {
          $plan->set('field_recommended', (bool) $planData['recommended']);
        }

        // field_course_access is list_string: all|selected.
        if (!empty($planData['course_access'])) {
          $plan->set('field_course_access', $planData['course_access']);
        }

        // field_trial_days is integer.
        if (isset($planData['trial_days'])) {
          $plan->set('field_trial_days', (int) $planData['trial_days']);
        }

        $plan->save();
        $count++;
      }
    }

    return $count;
  }

  /**
   * Import community data (forums, ratings, badges).
   */
  protected function importCommunity(array $data): int {
    $count = 0;

    // Forum topics.
    if (!empty($data['forum_topics'])) {
      $topicStorage = $this->entityTypeManager->getStorage('lms_forum_topic');
      $replyStorage = $this->entityTypeManager->getStorage('lms_forum_reply');

      foreach ($data['forum_topics'] as $topicData) {
        $topicAuthorUid = 1;
        if (!empty($topicData['author_email'])) {
          if ($authorUsers = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $topicData['author_email']])) {
            $topicAuthorUid = reset($authorUsers)->id();
          }
        }

        // Upsert logic for topics
        $existingTopics = $topicStorage->loadByProperties(['name' => $topicData['title']]);
        if (!empty($existingTopics)) {
          $topic = reset($existingTopics);
          $topic->set('field_body', [
            'value' => $topicData['body'] ?? '',
            'format' => 'full_html',
          ]);
          $topic->set('uid', $topicAuthorUid);
          
          // Delete existing replies so they don't duplicate on re-import
          $oldReplies = $replyStorage->loadByProperties(['field_topic' => $topic->id()]);
          if (!empty($oldReplies)) {
            $replyStorage->delete($oldReplies);
          }
        } else {
          $topic = $topicStorage->create([
            'name' => $topicData['title'],
            'status' => 1,
            'field_body' => [
              'value' => $topicData['body'] ?? '',
              'format' => 'full_html',
            ],
            'uid' => $topicAuthorUid,
          ]);
        }
        $topic->save();
        $count++;

        foreach ($topicData['replies'] ?? [] as $replyData) {
          $replyAuthorUid = $topicAuthorUid; // Default to topic author if no reply author.
          if (!empty($replyData['author_email'])) {
            if ($authorUsers = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $replyData['author_email']])) {
              $replyAuthorUid = reset($authorUsers)->id();
            }
          }

          $reply = $replyStorage->create([
            'field_topic' => $topic->id(),
            'field_body' => [
              'value' => $replyData['body'] ?? '',
              'format' => 'full_html',
            ],
            'uid' => $replyAuthorUid,
          ]);
          $reply->save();
          $count++;
        }
      }
    }

    // Badges.
    if (!empty($data['badges'])) {
      $badgeStorage = $this->entityTypeManager->getStorage('lms_badge');
      foreach ($data['badges'] as $badgeData) {
        $criteriaJson = json_encode([
          'type' => $badgeData['criteria_type'] ?? 'course_completion',
        ]);
        $badge = $badgeStorage->create([
          'name' => $badgeData['name'],
          'field_xp_reward' => $badgeData['points'] ?? 10,
          'field_criteria' => $criteriaJson,
        ]);
        $badge->save();
        $count++;
      }
    }

    // Ratings.
    if (!empty($data['ratings'])) {
      $ratingStorage = $this->entityTypeManager->getStorage('lms_rating');
      $userStorage = $this->entityTypeManager->getStorage('user');

      // Load all valid students (uid > 1). We'll pick one randomly for each review.
      $studentQuery = $userStorage->getQuery()->condition('uid', 1, '>')->accessCheck(FALSE)->execute();
      $studentIds = array_values($studentQuery);

      foreach ($data['ratings'] as $ratingData) {
        // Use fuzzy lookup to handle title variations.
        $course = $this->lookupCourseByTitle($ratingData['course_title'] ?? '');

        if ($course) {
          // Fallback to uid 1 if no students found.
          $authorId = !empty($studentIds) ? $studentIds[array_rand($studentIds)] : 1;

          // Prevent duplicates: check if this user already reviewed this course.
          $existingRating = $ratingStorage->getQuery()
            ->condition('field_course', $course->id())
            ->condition('uid', $authorId)
            ->accessCheck(FALSE)
            ->execute();

          if (empty($existingRating)) {
            $rating = $ratingStorage->create([
              'field_course' => $course->id(),
              'field_score' => $ratingData['score'] ?? 5,
              'field_review' => [
                'value' => $ratingData['review'] ?? '',
                'format' => 'plain_text',
              ],
              'field_is_featured' => $ratingData['is_featured'] ?? FALSE,
              'field_approved' => TRUE,
              'uid' => $authorId,
            ]);
            $rating->save();
            $count++;
          }
        }
      }
    }

    return $count;
  }

  /**
   * Import slideshows (node:slideshow).
   */
  protected function importSlideshows(array $data): int {
    $count = 0;
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($data['slideshows'] ?? [] as $item) {
      if (empty($item['title'])) {
        continue;
      }

      $fields = [
        'type'   => 'slideshow',
        'title'  => $item['title'],
        'status' => (isset($item['status']) && $item['status'] === 'published') ? 1 : 0,
        'uid'    => 1,
      ];

      if (!empty($item['body'])) {
        $fields['body'] = [
          'value'  => $item['body'],
          'format' => 'full_html',
        ];
      }
      if (!empty($item['cta_text'])) {
        $fields['field_slide_heading'] = $item['cta_text'];
      }
      if (!empty($item['cta_link'])) {
        $fields['field_slide_link'] = ['uri' => $item['cta_link']];
      }
      if (!empty($item['heading'])) {
        $fields['field_slide_heading'] = $item['heading'];
      }
      if (!empty($item['subheading'])) {
        $fields['field_slide_subheading'] = $item['subheading'];
      }
      if (!empty($item['position'])) {
        $fields['field_slide_position'] = $item['position'];
      }

      if (!empty($item['image_filename'])) {
        $file = $this->createManagedFileFromGenerated($item['image_filename']);
        if ($file) {
          $fields['field_slide_image'] = [
            'target_id' => $file->id(),
            'alt'       => $item['image_alt'] ?? $item['title'],
          ];
        }
      }

      $node = $nodeStorage->create($fields);
      $node->save();
      $count++;
    }

    return $count;
  }

  /**
   * Import introductions (node:introduction).
   */
  protected function importIntroductions(array $data): int {
    $count = 0;
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($data['introductions'] ?? [] as $item) {
      if (empty($item['title'])) {
        continue;
      }

      $fields = [
        'type'   => 'introduction',
        'title'  => $item['title'],
        'status' => (isset($item['status']) && $item['status'] === 'published') ? 1 : 0,
        'uid'    => 1,
      ];

      if (!empty($item['body'])) {
        $fields['body'] = [
          'value'  => $item['body'],
          'format' => 'full_html',
        ];
      }
      if (!empty($item['subheading'])) {
        $fields['field_subheading'] = $item['subheading'];
      }
      if (!empty($item['image_position'])) {
        $fields['field_image_position'] = $item['image_position'];
      }
      if (!empty($item['background_color'])) {
        $fields['field_background_color'] = $item['background_color'];
      }
      if (!empty($item['cta_text']) && !empty($item['cta_link'])) {
        $fields['field_call_to_action'] = [
          'title' => $item['cta_text'],
          'uri'   => $item['cta_link'],
        ];
      }

      if (!empty($item['image_filename'])) {
        $file = $this->createManagedFileFromGenerated($item['image_filename']);
        if ($file) {
          $fields['field_intro_image'] = [
            'target_id' => $file->id(),
            'alt'       => $item['image_alt'] ?? $item['title'],
          ];
        }
      }

      $node = $nodeStorage->create($fields);
      $node->save();
      $count++;
    }

    return $count;
  }

  protected function importGlossaries(array $data): int {
    $count = 0;
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($data['glossaries'] ?? [] as $item) {
      // Support both 'term' key (AI output) or 'title' key.
      $title = $item['term'] ?? $item['title'] ?? '';
      if (empty($title)) {
        continue;
      }

      $fields = [
        'type'   => 'glossary',
        'title'  => $title,
        'status' => 1,
        'uid'    => 1,
      ];

      $definition = $item['definition'] ?? $item['body'] ?? '';
      if (!empty($definition)) {
        $fields['body'] = [
          'value'  => $definition,
          'format' => 'full_html',
        ];
      }
      if (!empty($item['synonyms'])) {
        $fields['field_synonyms'] = $item['synonyms'];
      }
      // Optional course reference (field_course → lms_course).
      if (!empty($item['course_title'])) {
        $courseIds = $this->entityTypeManager->getStorage('lms_course')
          ->loadByProperties(['name' => $item['course_title']]);
        if (!empty($courseIds)) {
          $fields['field_course'] = reset($courseIds)->id();
        }
      }

      $node = $nodeStorage->create($fields);

      // Save definition text: try 'body' first (standard Drupal), fallback to 'field_definition'.
      $definition = $item['definition'] ?? $item['body'] ?? '';
      if (!empty($definition)) {
        if ($node->hasField('body')) {
          $node->set('body', ['value' => $definition, 'format' => 'full_html']);
        }
        if ($node->hasField('field_definition')) {
          $node->set('field_definition', ['value' => $definition, 'format' => 'full_html']);
        }
        // Plain text fallback fields.
        if ($node->hasField('field_term_definition')) {
          $node->set('field_term_definition', $definition);
        }
      }

      $node->save();
      $count++;
    }

    return $count;
  }

  /**
   * Import blog articles (node:blog).
   */
  protected function importBlogs(array $data): int {
    $count = 0;
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($data['blogs'] ?? [] as $item) {
      if (empty($item['title'])) {
        continue;
      }

      $fields = [
        'type'   => 'blog',
        'title'  => $item['title'],
        'status' => 1,
        'uid'    => 1,
      ];

      $content = $item['content'] ?? $item['body'] ?? '';
      if (!empty($content)) {
        $fields['body'] = [
          'value'   => $content,
          'summary' => $item['summary'] ?? '',
          'format'  => 'full_html',
        ];
      }
      // Tags: accept array of strings, create terms if needed.
      if (!empty($item['tags'])) {
        $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
        $termIds = [];
        foreach ((array) $item['tags'] as $tagName) {
          $existing = $termStorage->loadByProperties([
            'vid'  => 'blog_tags',
            'name' => $tagName,
          ]);
          if (!empty($existing)) {
            $termIds[] = reset($existing)->id();
          }
          else {
            $term = $termStorage->create(['vid' => 'blog_tags', 'name' => $tagName]);
            $term->save();
            $termIds[] = $term->id();
          }
        }
        $fields['field_blog_tags'] = $termIds;
      }

      if (!empty($item['image_filename'])) {
        $file = $this->createManagedFileFromGenerated($item['image_filename']);
        if ($file) {
          $fields['field_thumb'] = [
            'target_id' => $file->id(),
            'alt'       => $item['image_alt'] ?? $item['title'],
          ];
        }
      }

      // Upsert: update existing node by title, or create new.
      $existingNodes = $nodeStorage->loadByProperties([
        'type'  => 'blog',
        'title' => $item['title'],
      ]);
      if (!empty($existingNodes)) {
        $node = reset($existingNodes);
        foreach ($fields as $key => $value) {
          $node->set($key, $value);
        }
      }
      else {
        $node = $nodeStorage->create($fields);
      }

      $node->save();
      $count++;
    }

    return $count;
  }

  /**
   * Creates a Drupal managed file entity from a file in public://generated/.
   *
   * @param string $filename
   *   The filename (e.g. 'slide-welcome.png').
   *
   * @return \Drupal\file\FileInterface|null
   *   The saved file entity, or NULL if the file does not exist.
   */
  protected function createManagedFileFromGenerated(string $filename): ?\Drupal\file\FileInterface {
    $uri = 'public://generated/' . $filename;
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $realpath = $fileSystem->realpath($uri);
    if (!$realpath || !file_exists($realpath)) {
      $this->logger()->warning(dt('Image file not found: @uri', ['@uri' => $uri]));
      return NULL;
    }
    try {
      /** @var \Drupal\file\FileRepositoryInterface $fileRepo */
      $fileRepo = \Drupal::service('file.repository');
      return $fileRepo->writeData(
        file_get_contents($realpath),
        $uri,
        \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE
      );
    }
    catch (\Exception $e) {
      $this->logger()->error(dt('Failed to create managed file for @filename: @msg', [
        '@filename' => $filename,
        '@msg'      => $e->getMessage(),
      ]));
      return NULL;
    }
  }

  /**
   * Import FAQs (lms_faq custom entity).
   *
   * field_scope: 'global' | 'course'
   * field_course: entity reference to lms_course (required when scope=course)
   */
  protected function importFaqs(array $data): int {

    $count = 0;
    $faqStorage = $this->entityTypeManager->getStorage('lms_faq');

    foreach ($data['faqs'] ?? [] as $weight => $item) {
      $question = $item['question'] ?? '';
      if (empty($question)) {
        continue;
      }

      $fields = [
        'question'       => $question,
        'field_answer'   => [
          'value'  => $item['answer'] ?? '',
          'format' => 'full_html',
        ],
        'field_scope'    => $item['scope'] ?? 'global',
        'weight'         => (int) ($item['weight'] ?? $weight),
      ];

      // Resolve course reference for scope=course.
      if (($item['scope'] ?? '') === 'course' && !empty($item['course_title'])) {
        $courseIds = $this->entityTypeManager->getStorage('lms_course')
          ->loadByProperties(['name' => $item['course_title']]);
        if (!empty($courseIds)) {
          $fields['field_course'] = reset($courseIds)->id();
        }
      }

      $existingFaqs = $faqStorage->loadByProperties(['question' => $question]);
      if (!empty($existingFaqs)) {
        $faq = reset($existingFaqs);
        foreach ($fields as $key => $value) {
          $faq->set($key, $value);
        }
      } else {
        $faq = $faqStorage->create($fields);
      }

      $faq->save();
      $count++;
    }

    return $count;
  }

  /**
   * Import course categories (taxonomy:course_category).
   */
  protected function importCategories(array $data): int {
    $count = 0;
    foreach ($data['categories'] ?? [] as $item) {
      if (empty($item['name'])) {
        continue;
      }
      $imageFilename = $item['image_filename'] ?? NULL;
      $description = $item['description'] ?? '';

      $termId = $this->getOrCreateTerm('course_category', $item['name'], $imageFilename, $description);
      if ($termId) {
        $count++;
      }
    }
    return $count;
  }

  /**
   * Helper function to find or create a taxonomy term.
   */
  protected function getOrCreateTerm(string $vid, string $name, ?string $imageFilename = NULL, string $description = ''): ?int {
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $termStorage->loadByProperties([
      'vid' => $vid,
      'name' => $name,
    ]);

    if (!empty($terms)) {
      $term = reset($terms);
    }
    else {
      $termFields = [
        'vid' => $vid,
        'name' => $name,
      ];
      if (!empty($description)) {
        $termFields['description'] = [
          'value' => $description,
          'format' => 'full_html',
        ];
      }
      $term = $termStorage->create($termFields);
    }

    // Detect correct image field based on bundle.
    $imageFieldName = NULL;
    if ($term->hasField('field_category_thumbnail')) {
      $imageFieldName = 'field_category_thumbnail';
    } elseif ($term->hasField('field_image')) {
      $imageFieldName = 'field_image';
    }

    if ($imageFilename && $imageFieldName) {
      $physicalPath = DRUPAL_ROOT . '/../.agent/images/image_category/' . $imageFilename;
      if (file_exists($physicalPath)) {
        $fileData = file_get_contents($physicalPath);
        /** @var \Drupal\file\FileRepositoryInterface $fileRepo */
        $fileRepo = \Drupal::service('file.repository');
        $file = $fileRepo->writeData($fileData, 'public://categories/' . $imageFilename, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
        if ($file) {
          $term->set($imageFieldName, ['target_id' => $file->id()]);
        }
      }
    }

    try {
      $term->save();
      return (int) $term->id();
    }
    catch (\Exception $e) {
      $this->logger()->error(dt('Failed to create/update term @name in @vid: @msg', [
        '@name' => $name,
        '@vid' => $vid,
        '@msg' => $e->getMessage(),
      ]));
      return NULL;
    }
  }

  /**
   * Validates seed input data.
   */
  protected function validateSeedInput(array $data): array {
    $errors = [];

    if (empty($data['organization']['name'])) {
      $errors[] = 'Missing: organization.name';
    }
    if (empty($data['training_program']['name'])) {
      $errors[] = 'Missing: training_program.name';
    }
    if (empty($data['courses'])) {
      $errors[] = 'Missing: at least one course is required';
    }
    if (empty($data['instructors'])) {
      $errors[] = 'Missing: at least one instructor is required';
    }

    foreach ($data['courses'] ?? [] as $i => $course) {
      if (empty($course['title'])) {
        $errors[] = "Missing: courses[$i].title";
      }
      if (empty($course['chapters_count']) || $course['chapters_count'] < 2) {
        $errors[] = "Invalid: courses[$i].chapters_count must be >= 2";
      }
      if (empty($course['lessons_per_chapter']) || $course['lessons_per_chapter'] < 2) {
        $errors[] = "Invalid: courses[$i].lessons_per_chapter must be >= 2";
      }
    }

    foreach ($data['instructors'] ?? [] as $i => $instructor) {
      if (empty($instructor['name'])) {
        $errors[] = "Missing: instructors[$i].name";
      }
      if (empty($instructor['email'])) {
        $errors[] = "Missing: instructors[$i].email";
      }
    }

    return $errors;
  }

  /**
   * Generates mock PDF certificates for users and courses to test the Certificate Manager UI.
   *
   * @command everlms:mock-certs
   * @aliases lms-mock-certs
   * @usage everlms:mock-certs
   *   Generate random certificates for active users.
   */
  public function mockCertificates(): void {
    $this->logger()->notice(dt('Starting Mock Certificate Generation...'));
    
    /** @var \Drupal\lms_certificate\Service\CertificateManager $certManager */
    $certManager = \Drupal::service('lms_certificate.manager');

    $user_ids = \Drupal::entityQuery('user')->condition('status', 1)->condition('uid', 1, '>')->accessCheck(FALSE)->execute();
    if (empty($user_ids)) {
      $this->logger()->error('No users found.');
      return;
    }
    $users = \Drupal\user\Entity\User::loadMultiple($user_ids);

    $course_ids = \Drupal::entityQuery('lms_course')->condition('status', 1)->accessCheck(FALSE)->execute();
    if (empty($course_ids)) {
      $this->logger()->error('No courses found.');
      return;
    }
    $courses = \Drupal\lms_course\Entity\Course::loadMultiple($course_ids);

    $success_count = 0;
    $fail_count = 0;

    foreach ($users as $user) {
      $random_course_keys = (array) array_rand($courses, min(2, count($courses)));
      foreach ($random_course_keys as $key) {
        $course = $courses[$key];
        try {
          $userCert = $certManager->checkAndIssueCertificate($user, $course, NULL);
          if ($userCert && $userCert->hasField('field_pdf_file') && !$userCert->get('field_pdf_file')->isEmpty()) {
            $success_count++;
            $this->logger()->notice(dt('Issued certificate for @user in @course', [
              '@user' => $user->getDisplayName(),
              '@course' => $course->label()
            ]));
          } else {
            $fail_count++;
          }
        } catch (\Exception $e) {
          $this->logger()->error($e->getMessage());
          $fail_count++;
        }
      }
    }

    $this->logger()->notice(dt('Mock certificates complete: @success SUCCESS, @fail SKIPPED/FAILED.', [
      '@success' => $success_count,
      '@fail' => $fail_count
    ]));
  }

  /**
   * Import mock user transactional and interaction data.
   */
  protected function importUserData(string $filename, array $data): int {
    $count = 0;
    $basename = basename($filename);

    switch ($basename) {
      case 'notes.json':
        $count = $this->importUserNotes($data['notes'] ?? []);
        break;

      case 'notifications.json':
        $count = $this->importUserNotifications($data['notifications'] ?? []);
        break;

      case 'progress.json':
        $count = $this->importUserProgress($data['progress'] ?? []);
        break;

      case 'quiz_results.json':
        $count = $this->importQuizResults($data['results'] ?? []);
        break;

      case 'certificates.json':
        $count = $this->importUserCertificates($data['certificates'] ?? []);
        break;

      case 'orders.json':
        $count = $this->importUserOrders($data['orders'] ?? []);
        break;

      case 'memberships.json':
        $count = $this->importUserMemberships($data['user_memberships'] ?? []);
        break;

      case 'wishlists.json':
        $count = $this->importUserWishlists($data['wishlists'] ?? []);
        break;
    }

    return $count;
  }

  /**
   * Helper to find a user by email.
   */
  protected function getUserByEmail(string $email) {
    if (empty($email)) return NULL;
    $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $email]);
    return !empty($users) ? reset($users) : NULL;
  }

  /**
   * Lookup a course by title with fuzzy matching (3 tiers).
   *
   * Tier 1: Exact match via loadByProperties().
   * Tier 2: CONTAINS match on the full title string.
   * Tier 3: CONTAINS match on the longest significant word (>4 chars) in title.
   *
   * This handles AI-generated data where titles are sometimes shortened or
   * slightly different from the canonical entity name.
   */
  protected function lookupCourseByTitle(string $title): ?object {
    if (empty($title)) {
      return NULL;
    }
    $storage = $this->entityTypeManager->getStorage('lms_course');

    // Tier 1: exact match.
    $results = $storage->loadByProperties(['name' => $title]);
    if (!empty($results)) {
      return reset($results);
    }

    // Tier 2: CONTAINS on full title.
    $ids = $storage->getQuery()
      ->condition('name', $title, 'CONTAINS')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    if (!empty($ids)) {
      return $storage->load(reset($ids));
    }

    // Tier 3: try each significant word (>4 chars), longest first.
    $words = array_filter(explode(' ', $title), fn($w) => strlen($w) > 4);
    usort($words, fn($a, $b) => strlen($b) - strlen($a));
    foreach ($words as $word) {
      $ids = $storage->getQuery()
        ->condition('name', $word, 'CONTAINS')
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();
      if (!empty($ids)) {
        return $storage->load(reset($ids));
      }
    }

    $this->logger()->warning(dt('Course not found for title: "@title"', ['@title' => $title]));
    return NULL;
  }

  /**
   * Lookup a quiz by title with fuzzy matching (3 tiers).
   *
   * Same strategy as lookupCourseByTitle() but targets lms_quiz entity.
   */
  protected function lookupQuizByTitle(string $title): ?object {
    if (empty($title)) {
      return NULL;
    }
    $storage = $this->entityTypeManager->getStorage('lms_quiz');

    // Tier 1: exact match.
    $results = $storage->loadByProperties(['name' => $title]);
    if (!empty($results)) {
      return reset($results);
    }

    // Tier 2: CONTAINS on full title.
    $ids = $storage->getQuery()
      ->condition('name', $title, 'CONTAINS')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    if (!empty($ids)) {
      return $storage->load(reset($ids));
    }

    // Tier 3: try each significant word (>4 chars), longest first.
    $words = array_filter(explode(' ', $title), fn($w) => strlen($w) > 4);
    usort($words, fn($a, $b) => strlen($b) - strlen($a));
    foreach ($words as $word) {
      $ids = $storage->getQuery()
        ->condition('name', $word, 'CONTAINS')
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();
      if (!empty($ids)) {
        return $storage->load(reset($ids));
      }
    }

    $this->logger()->warning(dt('Quiz not found for title: "@title"', ['@title' => $title]));
    return NULL;
  }

  /**
   * Import User Notes.
   */
  protected function importUserNotes(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_note');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      $course = $this->lookupCourseByTitle($item['course_title'] ?? '');

      if ($user && $course) {
        $storage->create([
          'uid' => $user->id(),
          'field_course' => $course->id(),
          'field_body' => $item['content'],
        ])->save();
        $count++;
      }
    }
    return $count;
  }

  /**
   * Import User Notifications.
   */
  protected function importUserNotifications(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_notification');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      if ($user) {
        $storage->create([
          'uid' => $user->id(),
          'field_title' => $item['title'],
          'field_message' => $item['message'],
          'field_type' => $item['type'],
          'field_read' => $item['is_read'] ? 1 : 0,
        ])->save();
        $count++;
      }
    }
    return $count;
  }

  /**
   * Import User Progress.
   */
  protected function importUserProgress(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_user_course');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      $course = $this->lookupCourseByTitle($item['course_title'] ?? '');

      if ($user && $course) {
        $existing = $storage->loadByProperties([
          'uid' => $user->id(),
          'field_course' => $course->id(),
        ]);
        if (empty($existing)) {
          $progressFields = [
            'uid' => $user->id(),
            'field_course' => $course->id(),
            'field_progress' => $item['percentage'],
            'field_status' => $item['status'],
          ];

          if (!empty($item['started_at'])) {
            $progressFields['field_enrolled_at'] = strtotime($item['started_at']);
          }
          if (!empty($item['completed_at'])) {
            $progressFields['field_completed_at'] = strtotime($item['completed_at']);
          }

          $progress = $storage->create($progressFields);
          $progress->save();
          $count++;
        }
      }
    }
    return $count;
  }

  /**
   * Import User Quiz Results.
   */
  protected function importQuizResults(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_quiz_result');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      $quiz = $this->lookupQuizByTitle($item['quiz_title'] ?? '');

      if ($user && $quiz) {
        $resultLabel = !empty($item['is_passed']) ? 'Passed' : 'Failed';
        $termId = $this->getOrCreateTerm('quiz_results', $resultLabel);

        $response_ids = [];
        $questions = $quiz->get('field_questions')->referencedEntities();
        $total = count($questions);
        if ($total > 0) {
          $score = (float) $item['score'];
          $num_correct = round($total * ($score / 100));
          $responseStorage = $this->entityTypeManager->getStorage('lms_question_response');
          
          foreach ($questions as $index => $question) {
            $is_correct = ($index < $num_correct);
            $fields_resp = [
              'field_question' => $question->id(),
              'field_quiz' => $quiz->id(),
              'field_correct' => $is_correct,
              'field_score' => $is_correct ? 10 : 0, 
              'field_pending_review' => FALSE,
              'field_text_answer' => $is_correct ? 'Correct dummy answer submitted.' : 'Incorrect dummy answer submitted.',
            ];
            
            if (in_array($question->bundle(), ['single_choice', 'multiple_choice'])) {
              $choices = $question->get('field_choices')->referencedEntities();
              if (!empty($choices)) {
                $fields_resp['field_answers'] = ['target_id' => $choices[0]->id()];
                $fields_resp['field_text_answer'] = $choices[0]->label();
              }
            }
            
            $resp_entity = $responseStorage->create($fields_resp);
            $resp_entity->save();
            $response_ids[] = ['target_id' => $resp_entity->id()];
          }
        }

        $fields = [
          'uid' => $user->id(),
          'field_quiz' => $quiz->id(),
          'field_score' => $item['score'],
          'field_responses' => $response_ids,
        ];

        if ($termId) {
          $fields['field_result'] = ['target_id' => $termId];
        }

        $storage->create($fields)->save();
        $count++;
      }
    }
    return $count;
  }

  /**
   * Import User Certificates.
   */
  protected function importUserCertificates(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_user_certificate');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      $course = $this->lookupCourseByTitle($item['course_title'] ?? '');

      if ($user && $course) {
        $code = $item['certificate_code'];
        
        // 1. Resolve Certificate Template
        $template = NULL;
        if ($course->hasField('field_certificate_template') && !$course->get('field_certificate_template')->isEmpty()) {
          $template_id = $course->get('field_certificate_template')->target_id;
          $template = $this->entityTypeManager->getStorage('lms_certificate_template')->load($template_id);
        }
        if (!$template) {
          $templates = $this->entityTypeManager->getStorage('lms_certificate_template')->loadByProperties(['status' => 1]);
          if (!empty($templates)) {
            $template_keys = array_keys($templates);
            // Distribute templates based on course ID so the same course gets the same beautiful template.
            $index = (int)$course->id() % count($template_keys);
            $template = $templates[$template_keys[$index]];
          }
        }

        // 2. Generate PDF via Service
        $pdf_id = NULL;
        if ($template) {
          /** @var \Drupal\lms_certificate\Service\CertificateGenerator $generator */
          $generator = \Drupal::service('lms_certificate.generator');
          $file = $generator->generate($template, $user, $course, $code);
          if ($file) {
            $pdf_id = $file->id();
          }
        }

        // 3. Save Entity
        $fields = [
          'uid' => $user->id(),
          'field_course' => $course->id(),
          'field_certificate_number' => $code,
        ];
        if ($template) {
          $fields['field_template'] = $template->id();
        }
        if ($pdf_id) {
          $fields['field_pdf_file'] = $pdf_id;
        }

        $storage->create($fields)->save();
        $count++;
      }
    }
    return $count;
  }

  /**
   * Import User Orders.
   */
  protected function importUserOrders(array $items): int {
    $count = 0;
    $orderStorage = $this->entityTypeManager->getStorage('lms_order');
    $itemStorage = $this->entityTypeManager->getStorage('lms_order_item');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      if (!$user) {
        continue;
      }

      $order = $orderStorage->create([
        'uid' => $user->id(),
        'field_order_number' => $item['order_number'],
        'field_total' => $item['total'],
      ]);
      $order->save();

      foreach ($item['items'] as $oi) {
        $itemType = $oi['type'] ?? 'course';
        $newItem = $itemStorage->create([
          'uid'        => $user->id(),
          'field_price' => $oi['price'],
          'field_type' => $itemType,
        ]);

        // Resolve and link the referenced entity.
        if ($itemType === 'membership') {
          $plans = $this->entityTypeManager->getStorage('lms_membership_plan')
            ->loadByProperties(['name' => $oi['title'] ?? '']);
          $plan = reset($plans);
          if ($plan) {
            $newItem->set('field_membership_plan', $plan->id());
          }
        }
        else {
          // Default: treat as course.
          $course = $this->lookupCourseByTitle($oi['title'] ?? '');
          if ($course) {
            $newItem->set('field_course', $course->id());
          }
        }

        $newItem->save();
        $order->get('field_items')->appendItem($newItem);
      }

      $order->save();
      $count++;
    }
    return $count;
  }

  /**
   * Import User Memberships.
   */
  protected function importUserMemberships(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_user_membership');
    $planStorage = $this->entityTypeManager->getStorage('lms_membership_plan');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      $plans = $planStorage->loadByProperties(['name' => $item['plan_name']]);
      $plan = reset($plans);

      if ($user && $plan) {
        $storage->create([
          'uid' => $user->id(),
          'field_plan' => $plan->id(),
          'field_status' => $item['status'] === 'active' ? 'active' : 'expired',
        ])->save();
        $count++;
      }
    }
    return $count;
  }

  /**
   * Import User Wishlists.
   */
  protected function importUserWishlists(array $items): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_wishlist');

    foreach ($items as $item) {
      $user = $this->getUserByEmail($item['user_email']);
      if (!$user) {
        continue;
      }

      foreach ($item['items'] as $courseTitle) {
        $course = $this->lookupCourseByTitle($courseTitle);
        if ($course) {
          $storage->create([
            'uid' => $user->id(),
            'field_course' => $course->id(),
          ])->save();
          $count++;
        }
      }
    }
    return $count;
  }

  /**
   * Creates a Drupal managed file entity for a SCORM zip from the agent directory.
   */
  protected function createScormManagedFileFromAgent(string $filename): ?\Drupal\file\FileInterface {
    $agentPath = $this->manager->getGeneratedPath() . '/lesson_scorm/' . $filename;
    
    if (!file_exists($agentPath)) {
      $this->logger()->warning(dt('SCORM zip not found at path: @path', ['@path' => $agentPath]));
      return NULL;
    }
    
    $destinationUri = 'public://lessons/scorm_uploads/' . $filename;
    
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $scormDir = 'public://lessons/scorm_uploads';
    $fileSystem->prepareDirectory($scormDir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);
    
    try {
      /** @var \Drupal\file\FileRepositoryInterface $fileRepo */
      $fileRepo = \Drupal::service('file.repository');
      return $fileRepo->writeData(
        file_get_contents($agentPath),
        $destinationUri,
        \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE
      );
    }
    catch (\Exception $e) {
      $this->logger()->error(dt('Failed to create managed SCORM file for @filename: @msg', [
        '@filename' => $filename,
        '@msg'      => $e->getMessage(),
      ]));
      return NULL;
    }
  }

  /**
   * Extracts a SCORM ZIP into public://scorm_extracted/{lessonId}/
   * so the SCORM player can load index.html from the correct path.
   */
  protected function extractScormForLesson(int $lessonId, string $filename): void {
    $agentPath = $this->manager->getGeneratedPath() . '/lesson_scorm/' . $filename;

    if (!file_exists($agentPath)) {
      $this->logger()->warning(dt('SCORM extract skipped — ZIP not found: @path', ['@path' => $agentPath]));
      return;
    }

    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $publicReal = $fileSystem->realpath('public://');
    $destDir    = $publicReal . '/scorm_extracted/' . $lessonId;

    // Skip if already extracted.
    if (is_file($destDir . '/index.html')) {
      return;
    }

    if (!is_dir($destDir)) {
      mkdir($destDir, 0755, true);
    }

    $zip = new \ZipArchive();
    if ($zip->open($agentPath) === TRUE) {
      $zip->extractTo($destDir);
      $zip->close();
      $this->logger()->info(dt('SCORM extracted: lesson @id → @dir', [
        '@id'  => $lessonId,
        '@dir' => $destDir,
      ]));
    }
    else {
      $this->logger()->error(dt('Failed to extract SCORM ZIP for lesson @id (@file)', [
        '@id'   => $lessonId,
        '@file' => $filename,
      ]));
    }
  }

}

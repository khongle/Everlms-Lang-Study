<?php

namespace Drupal\lms_base\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A Drush commandfile to generate EverLMS demo content.
 */
class LmsDemoContentCommand extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LmsDemoContentCommand object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct();
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Generates demo content for EverLMS.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option courses
   *   Number of courses to generate. Defaults to 5.
   * @option chapters
   *   Number of chapters per course. Defaults to 3.
   * @option lessons
   *   Number of lessons per chapter. Defaults to 5.
   * @usage lms:generate-demo
   *   Generates 5 courses, each with 3 chapters, and each chapter with 5 lessons.
   * @usage lms:generate-demo --courses=2 --chapters=2 --lessons=2
   *   Generates 2 courses, each with 2 chapters, and each chapter with 2 lessons.
   *
   * @command lms:generate-demo
   * @aliases lms-gen
   */
  public function generateDemo(array $options = ['courses' => 5, 'chapters' => 3, 'lessons' => 5]) {
    $num_courses = (int) $options['courses'];
    $num_chapters_per_course = (int) $options['chapters'];
    $num_lessons_per_chapter = (int) $options['lessons'];

    $this->logger()->notice(dt('Starting generation of @courses courses...', ['@courses' => $num_courses]));

    try {
      $course_storage = $this->entityTypeManager->getStorage('lms_course');
      $chapter_storage = $this->entityTypeManager->getStorage('lms_chapter');
      $lesson_storage = $this->entityTypeManager->getStorage('lms_lesson');

      for ($i = 1; $i <= $num_courses; $i++) {
        // Create Course
        $course = $course_storage->create([
          'name' => "Khóa học Demo số {$i}",
          'status' => 1,
          'field_price' => random_int(100, 1000) * 1000, // Gia VND ngau nhien
          'field_description' => [
            'value' => "<p>Đây là nội dung giới thiệu tổng quan cho Khóa học Demo số {$i}. Khóa học này cung cấp kiến thức nền tảng và nâng cao.</p>",
            'format' => 'full_html',
          ],
          'field_short_description' => "Khóa học demo ngắn lms_course số {$i}",
          'field_what_learn' => [
            'value' => "<ul>
              <li>Kiến thức nền tảng vững chắc</li>
              <li>Thực hành dự án thực tế</li>
              <li>Tối ưu hiệu suất làm việc</li>
              <li>Nắm vững công cụ và framework</li>
            </ul>",
            'format' => 'full_html',
          ],
          'field_prerequisites' => [
            'value' => "<ul><li>Máy tính kết nối internet.</li><li>Khát khao học hỏi và kiên nhẫn.</li></ul>",
            'format' => 'full_html',
          ],
          'field_who_for' => [
            'value' => "<ul><li>Người mới bắt đầu học.</li><li>Người đi làm muốn chuyển ngành.</li></ul>",
            'format' => 'full_html',
          ],
        ]);
        $course->save();
        $this->logger()->success(dt('Created Course: @name', ['@name' => $course->label()]));

        // Create Chapters for this course
        for ($j = 1; $j <= $num_chapters_per_course; $j++) {
          $chapter = $chapter_storage->create([
            'name' => "Chương {$j} của Khóa học {$i}",
            'status' => 1,
            'field_course' => $course->id(),
            'field_description' => [
              'value' => "Nội dung tóm tắt của chương {$j}.",
              'format' => 'full_html',
            ],
            'weight' => $j,
          ]);
          $chapter->save();
          $this->logger()->success(dt('  Created Chapter: @name', ['@name' => $chapter->label()]));

          // Create Lessons for this chapter
          for ($k = 1; $k <= $num_lessons_per_chapter; $k++) {
            $lesson = $lesson_storage->create([
              'name' => "Bài giảng {$k} (Chương {$j})",
              'status' => 1,
              'field_chapter' => $chapter->id(),
              'field_type' => 'video', // Gia su loai mac dinh la video
              'field_content' => [
                'value' => "<p>Đây là nội dung chi tiết bài giảng. Bạn sẽ học được xyz...</p>",
                'format' => 'full_html',
              ],
              'weight' => $k,
              // Giả sử có một link video demo
              'field_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 
            ]);
            $lesson->save();
            $this->logger()->info(dt('    Created Lesson: @name', ['@name' => $lesson->label()]));
          }
        }
      }

      $this->logger()->success(dt('Successfully generated demo content!'));
    }
    catch (\Exception $e) {
      $this->logger()->error(dt('An error occurred during content generation: @message', ['@message' => $e->getMessage()]));
    }
  }

}

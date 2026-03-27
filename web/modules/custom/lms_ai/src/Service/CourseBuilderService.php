<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\lms_ai\Plugin\AiProviderManager;
use Drupal\lms_course\Entity\Course;
use Psr\Log\LoggerInterface;

/**
 * Service to orchestrate AI generation of entire courses.
 */
class CourseBuilderService {

  public function __construct(
    private readonly AiProviderManager $aiProviderManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly QueueFactory $queueFactory,
    private readonly LoggerInterface $logger
  ) {}

  /**
   * Scaffolds the course structure based on a prompt.
   *
   * @return \Drupal\lms_course\Entity\Course|null
   *   The newly created course, or NULL on failure.
   */
  public function generateCourseStructure(string $topic, string $targetAudience, string $tone): ?Course {
    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $ai */
      $ai = $this->aiProviderManager->createInstance('openai');
      
      $prompt = sprintf(
        "Design a comprehensive online course about '%s' for '%s' in a '%s' tone. Return a strict JSON structure.",
        $topic, $targetAudience, $tone
      );

      $schema = '{
        "title": "A catchy, SEO-friendly course title",
        "description": "2-3 paragraphs describing the course benefits and overview.",
        "sections": [
          {
            "title": "Section Title (e.g., Module 1: Basics)",
            "lessons": [
              {
                "title": "Lesson Title",
                "type": "text",
                "prompt_for_content": "A detailed 1-sentence instruction for an AI to write the complete content of this lesson later."
              }
            ]
          }
        ]
      }';

      $structure = $ai->generateJson($prompt, $schema);

      if (!$structure || empty($structure['sections'])) {
        $this->logger->error('AI failed to return valid course array structure.');
        return NULL;
      }

      // Create Course Entity
      $courseStorage = $this->entityTypeManager->getStorage('lms_course');
      $course = $courseStorage->create([
        'name' => $structure['title'],
        'field_description' => [
          'value' => $structure['description'],
          'format' => 'full_html',
        ],
        'status' => 0, // Draft during generation
      ]);
      $course->save();

      $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');
      $queue = $this->queueFactory->get('lms_ai_content_generator');
      
      $weight = 0;

      // Create Sections and Lessons
      foreach ($structure['sections'] as $sectionData) {
        $sectionTitle = $sectionData['title'];
        
        // Emulate a section header using a text lesson for simplicity, or 
        // if your LMS has a distinct Section entity, create that here.
        // Assuming EverLMS uses weight-based lesson ordering inside field_lessons.
        
        foreach ($sectionData['lessons'] as $lessonData) {
          $lesson = $lessonStorage->create([
            'name' => "[$sectionTitle] " . $lessonData['title'],
            'field_type' => 'text',
            'field_content' => [
              'value' => "<i>AI is writing this content. Please wait...</i>",
              'format' => 'full_html',
            ],
            'status' => 0,
            'weight' => $weight++,
          ]);
          $lesson->save();

          // Queue the actual heavy content generation
          $queue->createItem([
            'lesson_id' => $lesson->id(),
            'prompt' => $lessonData['prompt_for_content'],
            'tone' => $tone,
          ]);
          
          // Link lesson to course
          $course->get('field_lessons')->appendItem(['target_id' => $lesson->id()]);
        }
      }

      $course->save();
      $this->logger->info('Successfully scaffolded course @id', ['@id' => $course->id()]);
      
      return $course;

    } catch (\Exception $e) {
      $this->logger->error('Course Builder Error: ' . $e->getMessage());
      return NULL;
    }
  }

}

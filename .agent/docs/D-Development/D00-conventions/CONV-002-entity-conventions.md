# CONV-002 — Entity Conventions

> **Rule:** Content entities use the PHP 8 `#[ContentEntityType]` attribute (not Drupal 9-era `@ContentEntityType` docblock annotations). Every content entity declares `views_data => EntityViewsData` in `handlers`. Bundles are implemented as a paired `#[ConfigEntityType]` that extends `ConfigEntityBundleBase`.
>
> **Why:** PHP attribute-style annotations are the Drupal 11 standard. `EntityViewsData` makes every entity available to Views without extra code — required for the workspace custom admin UI. Config-entity bundles ship their default types via `config/install/` YAML and are tracked in `config/sync`, making the bundle inventory source-controlled and deployable.
>
> **Backed by:** Project convention; aligns with Drupal 11 core entity API.

---

## Pattern

### Content Entity — `#[ContentEntityType]` attribute

Canonical example: `lms_quiz` entity (`web/modules/custom/lms_quiz/src/Entity/Quiz.php`):

```php
<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'lms_quiz',
  label: new TranslatableMarkup('Quiz'),
  label_collection: new TranslatableMarkup('Quizzes'),
  base_table: 'lms_quiz',
  data_table: 'lms_quiz_field_data',   // required when translatable: TRUE
  translatable: TRUE,
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id'        => 'id',
    'uuid'      => 'uuid',
    'label'     => 'name',
    'langcode'  => 'langcode',
    'owner'     => 'uid',          // only when implementing EntityOwnerInterface
    'published' => 'status',       // only when a boolean status field exists
  ],
  handlers: [
    'view_builder'   => EntityViewBuilder::class,
    'views_data'     => EntityViewsData::class,    // ALWAYS include
    'form' => [
      'default' => 'Drupal\lms_quiz\Form\QuizForm',
      'add'     => 'Drupal\lms_quiz\Form\QuizForm',
      'edit'    => 'Drupal\lms_quiz\Form\QuizForm',
      'delete'  => ContentEntityDeleteForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
    'access' => 'Drupal\lms_quiz\Access\QuizAccessControlHandler',
  ],
  links: [
    'canonical'   => '/manage/quizzes/{lms_quiz}',
    'add-form'    => '/manage/quizzes/add',
    'edit-form'   => '/manage/quizzes/{lms_quiz}/edit',
    'delete-form' => '/manage/quizzes/{lms_quiz}/delete',
    'collection'  => '/manage/quizzes',
  ],
)]
class Quiz extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Quiz Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -10])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // … additional fields …

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Published'))
      ->setDefaultValue(TRUE);

    return $fields;
  }
}
```

### Minimum required `entity_keys` per entity type

| Key | Required when |
|-----|---------------|
| `id` | Always |
| `uuid` | Always |
| `label` | Always (the human-readable name field) |
| `langcode` | Always when `translatable: TRUE` |
| `owner` → `uid` | Entity implements `EntityOwnerInterface` |
| `published` → `status` | Entity has a boolean publish/unpublish status field |
| `bundle` → `type` | Bundleable entity (references a config-entity bundle) |

### Bundle via Config Entity — `#[ConfigEntityType]`

Canonical example: `lms_question_type` (`web/modules/custom/lms_question/src/Entity/QuestionType.php`):

```php
<?php

declare(strict_types=1);

namespace Drupal\lms_question\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[ConfigEntityType(
  id: 'lms_question_type',
  label: new TranslatableMarkup('Question Type'),
  admin_permission: 'administer lms_course',
  config_prefix: 'question_type',
  bundle_of: 'lms_question',          // references the bundleable content entity
  entity_keys: [
    'id'    => 'id',
    'label' => 'label',
  ],
  config_export: [
    'id',
    'label',
    'description',
  ],
)]
class QuestionType extends ConfigEntityBundleBase {

  protected string $id;
  protected string $label;
  protected string $description = '';

}
```

Default bundle instances are installed via `config/install/lms_question.question_type.*.yml`:

```yaml
# web/modules/custom/lms_question/config/install/
# lms_question.question_type.single_choice.yml
id: single_choice
label: 'Single Choice'
description: 'Single Choice question type.'
```

A `config/schema/lms_question.schema.yml` must declare the schema for `lms_question.question_type.*` so Drupal can validate config on import.

### `baseFieldDefinitions` field display pattern

Always call both `setDisplayConfigurable('form', TRUE)` and `setDisplayConfigurable('view', TRUE)` to allow form/view display management in the UI. Use `setDisplayOptions` only to provide sensible defaults — editors can override via `/admin/structure/…/display`.

---

## Don't

- Do not use Drupal 9-era `@ContentEntityType` docblock annotations — use PHP 8 attributes (`#[ContentEntityType(...)]`).
- Do not omit `views_data => EntityViewsData::class` from handlers — without it the entity is invisible to Views, breaking the workspace admin UI.
- Do not define bundle types as plain strings in the content entity annotation — use a paired config entity that extends `ConfigEntityBundleBase`.
- Do not ship bundle config only in `config/sync` — bundle defaults belong in `config/install/` so they are created on module install.
- Do not omit `data_table` when `translatable: TRUE` — the data table holds the translated field values.
- Do not define `field_ui_base_route` unless the entity actually has a field configuration UI route (most EverLMS entities do not expose field UI).

---

## Checklist

- [ ] Entity class uses `#[ContentEntityType(...)]` PHP attribute, not docblock.
- [ ] `handlers` includes `'views_data' => EntityViewsData::class`.
- [ ] `entity_keys` includes at minimum `id`, `uuid`, `label`; adds `langcode` when translatable, `owner` when EntityOwnerInterface, `published` when status field exists, `bundle` when bundleable.
- [ ] `links` declares `canonical`, `add-form`, `edit-form`, `delete-form`, and `collection`.
- [ ] `admin_permission` matches an existing permission in a `*.permissions.yml` file.
- [ ] `data_table` is set when `translatable: TRUE`.
- [ ] Bundle defaults (if any) live in `config/install/` and are schema-declared in `config/schema/`.
- [ ] `baseFieldDefinitions` calls `parent::baseFieldDefinitions($entity_type)` first.
- [ ] All base fields call `->setDisplayConfigurable('form', TRUE)` and `->setDisplayConfigurable('view', TRUE)`.

---

## EverLMS Entity Inventory Reference

All 29 custom entities are documented in [`../C04-entities/`](../../C-Design/C04-entities/). The 28 content entities all follow the `#[ContentEntityType]` pattern above. The single config entity is `lms_question_type`, which provides 8 bundles (essay, fill_blank, matching, multiple_choice, ordering, short_answer, single_choice, true_false) for `lms_question`.

---

## Related

[`CONV-001-php-strict-types-di.md`](CONV-001-php-strict-types-di.md) — entity classes also use `declare(strict_types=1)` and constructor-promoted DI in their form handlers.
[`CONV-003-twig-first-rendering.md`](CONV-003-twig-first-rendering.md) — entity view builders feed into Twig templates.

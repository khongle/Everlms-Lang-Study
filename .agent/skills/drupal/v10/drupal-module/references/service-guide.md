# Drupal 10 Service Guide

> **Ref:** [Drupal 10 Coding Standards](../../drupal-best-practices/references/coding-standards.md)

## 1. Defining a Service (`my_module.services.yml`)

```yaml
services:
  my_module.helper:
    class: Drupal\my_module\Service\MyHelper
    arguments: ['@entity_type.manager', '@logger.factory']
    tags:
      - { name: backend_overridable }
```

## 2. Service Class Implementation

- **Strict Types**: `declare(strict_types=1);`.
- **Constructor Promotion**: Use PHP 8.1+ features.
- **Interfaces**: Always code against interfaces.

```php
declare(strict_types=1);

namespace Drupal\my_module\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Psr\Log\LoggerInterface;

class MyHelper {

  protected LoggerInterface $logger;

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $lcf,
  ) {
    $this->logger = $lcf->get('my_module');
  }

  public function doSomething(): void {
    $this->logger->info('Doing something');
  }

}
```

## 3. Common Service Tags

- `event_subscriber`: Hook implementation via Event Subscriber.
- `cron`: Custom cron job.
- `twig.extension`: Twig functions/filters.
- `access_check`: Custom route access logic.

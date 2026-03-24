<?php

namespace Drupal\lms_certificate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class CertificateTemplateListBuilder extends EntityListBuilder {

  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Name');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['name'] = $entity->label();
    $row['status'] = $entity->get('status')->value ? $this->t('Active') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

}

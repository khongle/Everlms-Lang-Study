<?php

namespace Drupal\lms_payment\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a PaymentGateway item annotation object.
 *
 * @see \Drupal\lms_payment\Plugin\PaymentGatewayManager
 * @see plugin_api
 *
 * @Annotation
 */
class PaymentGateway extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the payment gateway.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the payment gateway.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}

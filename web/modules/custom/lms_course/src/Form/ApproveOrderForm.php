<?php

namespace Drupal\lms_course\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_order\Entity\Order;
use Drupal\file\Entity\File;

/**
 * Form for admins to review and approve an order.
 */
class ApproveOrderForm extends FormBase {

  /**
   * The order being reviewed.
   *
   * @var \Drupal\lms_order\Entity\Order
   */
  protected $lmsOrder;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lms_course_approve_order_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?Order $lms_order = NULL) {
    if (!$lms_order) {
      return $form;
    }
    $this->lmsOrder = $lms_order;

    $user = $lms_order->getOwner();
    $amount = number_format((float) ($lms_order->get('field_total')->value ?? 0), 2);
    $date = \Drupal::service('date.formatter')->format($lms_order->get('created')->value, 'custom', 'F d, Y H:i');

    $form['#attributes']['class'][] = 'lms-form';

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['style' => 'display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; margin-bottom: 20px; font-family: "Inter", sans-serif;'],
    ];

    // Left Column: Details & Status
    $form['wrapper']['left_col'] = [
      '#type' => 'container',
      '#attributes' => ['style' => 'flex: 1; min-width: 320px; display: flex; flex-direction: column; gap: 24px;'],
    ];

    // Fetch Order Items
    $items_markup = '<div style="margin-top: 24px;">
      <div style="margin-top: 0; margin-bottom: 12px; font-size: 1rem; font-weight: 700; color: #1e293b !important; display: block;">Purchased Items</div>
      <ul style="list-style-type: none; padding: 0; margin: 0;">';
    
    if ($lms_order->hasField('field_items') && !$lms_order->get('field_items')->isEmpty()) {
      $order_items = $lms_order->get('field_items')->referencedEntities();
      foreach ($order_items as $item) {
        $item_name = 'Unknown Item';
        $type = $item->hasField('field_type') ? $item->get('field_type')->value : 'course';
        
        if ($type === 'course' && $item->hasField('field_course') && !$item->get('field_course')->isEmpty()) {
          $course = $item->get('field_course')->entity;
          if ($course) $item_name = $course->label() . ' (Course)';
        } elseif ($type === 'membership' && $item->hasField('field_membership_plan') && !$item->get('field_membership_plan')->isEmpty()) {
          $plan = $item->get('field_membership_plan')->entity;
          if ($plan) $item_name = $plan->label() . ' (Membership)';
        }

        $item_price = $item->hasField('field_price') ? number_format((float) $item->get('field_price')->value, 2) : '0.00';

        $items_markup .= '
          <li style="display: flex; justify-content: space-between; padding: 14px 0; border-top: 1px solid #f1f5f9; align-items: center;">
            <span style="color: #334155; font-weight: 600; font-size: 0.95rem;">' . htmlspecialchars($item_name) . '</span>
            <span style="color: #64748b; font-weight: 500; font-size: 0.95rem;">$' . $item_price . '</span>
          </li>
        ';
      }
    } else {
      $items_markup .= '<li style="color: #94a3b8; font-style: italic; padding: 10px 0;">No items found in this order.</li>';
    }
    $items_markup .= '</ul></div>';

    // Card 1: Order Details
    $form['wrapper']['left_col']['order_info'] = [
      '#type' => 'inline_template',
      '#template' => '
      <div class="lms-card" style="padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: \'Inter\', sans-serif;">
        <div style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700; color: #1e293b !important; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; display: block;">Order Details</div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
          <span style="color: #64748b; font-weight: 500;">Order ID</span>
          <span style="color: #0f172a; font-weight: 600;">#{{ order_id }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
          <span style="color: #64748b; font-weight: 500;">Learner</span>
          <span style="color: #0f172a; font-weight: 600;">{{ learner_name }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
          <span style="color: #64748b; font-weight: 500;">Total Amount</span>
          <span style="color: #10b981; font-weight: 700;">${{ amount }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0;">
          <span style="color: #64748b; font-weight: 500;">Date</span>
          <span style="color: #0f172a; font-weight: 600;">{{ date }}</span>
        </div>
        {{ items_markup | raw }}
      </div>',
      '#context' => [
        'order_id' => $lms_order->id(),
        'learner_name' => $user ? $user->getDisplayName() : 'Unknown',
        'amount' => $amount,
        'date' => $date,
        'items_markup' => $items_markup,
      ],
    ];

    // Card 2: Status Control
    $form['wrapper']['left_col']['status_control'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-card'], 'style' => 'padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: \'Inter\', sans-serif;'],
    ];

    $form['wrapper']['left_col']['status_control']['field_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Payment Status'),
      '#title_display' => 'before',
      '#options' => [
        'pending' => $this->t('Pending'),
        'processing' => $this->t('Processing'),
        'completed' => $this->t('Completed (Approve & Enroll)'),
        'cancelled' => $this->t('Cancelled'),
        'failed' => $this->t('Failed'),
      ],
      '#default_value' => $lms_order->get('field_status')->value ?? 'pending',
      '#required' => TRUE,
      '#attributes' => ['style' => 'width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: "Inter", sans-serif; font-weight: 600; font-size: 15px; margin-top: 8px; color: #1e293b; background-color: #f8fafc; outline: none;'],
    ];

    $form['wrapper']['left_col']['status_control']['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['style' => 'margin-top: 24px; font-family: \'Inter\', sans-serif;'],
    ];
    $form['wrapper']['left_col']['status_control']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Order'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
        'style' => 'width: 100%; background-color: #3b82f6; color: #ffffff !important; border: none; padding: 14px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: background 0.2s;'
      ],
    ];

    // Right Column: Receipt Image
    $receipt_markup = '
      <div style="margin-top: 0; margin-bottom: 1.5rem; text-align: left; font-size: 1.25rem; font-weight: 700; color: #1e293b !important; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; display: block;">Payment Receipt</div>
      <div style="padding: 60px 20px; background: #f8fafc; border-radius: 8px; color: #94a3b8; border: 2px dashed #cbd5e1;">
        <i class="fas fa-image" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
        <p style="margin: 0; font-weight: 500; font-size: 15px;">No receipt uploaded</p>
      </div>';

    if ($lms_order->hasField('field_payment_receipt') && !$lms_order->get('field_payment_receipt')->isEmpty()) {
      $file = $lms_order->get('field_payment_receipt')->entity;
      if ($file) {
        $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        $receipt_markup = '
          <div style="margin-top: 0; margin-bottom: 1.5rem; text-align: left; font-size: 1.25rem; font-weight: 700; color: #1e293b !important; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; display: block;">Payment Receipt</div>
          <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block; width: 100%; box-sizing: border-box; text-align: center;">
            <a href="' . $url . '" target="_blank" style="display: block; overflow: hidden; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
              <img src="' . $url . '" style="width: 100%; max-height: 600px; object-fit: contain; transition: transform 0.3s ease; display: block;" alt="Receipt">
            </a>
            <p style="font-size: 13px; color: #64748b; margin-top: 16px; margin-bottom: 0; font-weight: 500;">
              <i class="fas fa-search-plus" style="margin-right: 6px;"></i>Click image to view full size
            </p>
          </div>
        ';
      }
    }

    $form['wrapper']['right_col'] = [
      '#type' => 'inline_template',
      '#template' => '
      <div class="lms-card" style="flex: 1.5; min-width: 350px; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); text-align: center; font-family: \'Inter\', sans-serif;">
        {{ receipt_markup | raw }}
      </div>',
      '#context' => [
        'receipt_markup' => $receipt_markup,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->lmsOrder) {
      $new_status = $form_state->getValue('field_status');
      $this->lmsOrder->set('field_status', $new_status);
      $this->lmsOrder->save();

      $this->messenger()->addMessage($this->t('Order #@id status has been updated to @status.', [
        '@id' => $this->lmsOrder->id(),
        '@status' => strtoupper($new_status),
      ]));
      $form_state->setRedirect('lms_course.admin_orders');
    }
  }

}

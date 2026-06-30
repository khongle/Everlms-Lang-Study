<?php

declare(strict_types=1);

namespace Drupal\lms_order\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\lms_order\Entity\Order;

/**
 * Provides a receipt upload form for pending Bank Transfer orders.
 */
final class BankTransferForm extends FormBase {

  /**
   * The order object.
   *
   * @var \Drupal\lms_order\Entity\Order
   */
  protected $order;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_order_bank_transfer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Order $lms_order = NULL): array {
    if (!$lms_order) {
      return [];
    }

    $this->order = $lms_order;

    // Attach the QR code rendering variables via theme
    $config = \Drupal::config('lms_payment.settings');
    $amount = (float) $lms_order->get('field_total')->value;
    $order_number = $lms_order->get('field_order_number')->value;

    // Explicitly show messages inside the form so users see validation errors.
    $form['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -100,
    ];

    $form['payment_instructions'] = [
      '#theme' => 'lms_bank_transfer',
      '#order' => $lms_order,
      '#amount' => $amount,
      '#bank_bin' => $config->get('bank_bin'),
      '#bank_account_no' => $config->get('bank_account_no'),
      '#bank_account_name' => $config->get('bank_account_name'),
      '#instructions' => $config->get('bank_instructions'),
      '#order_number' => $order_number,
      '#weight' => 0,
    ];

    // Check if receipt is already uploaded.
    $form['receipt_upload'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lms-receipt-upload'],
        'style' => 'max-width: 600px; margin: 0 auto 2rem auto; background: #1e293b; border-radius: 1rem; padding: 2rem; color: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); text-align: left;'
      ],
      '#weight' => 99,
    ];

    $form['receipt_upload']['title'] = [
      '#markup' => '<h3 style="margin-top: 0; font-size: 1.25rem; margin-bottom: 0.5rem;">Upload Payment Receipt</h3>',
    ];

    $form['receipt_upload']['custom_style'] = [
      '#markup' => \Drupal\Core\Render\Markup::create('<style>
        .lms-receipt-upload .form-managed-file {
          display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 15px;
        }
        /* Hide the native input using display none, we use JS to construct custom button */
        .lms-receipt-upload input[type="file"] {
          display: none !important;
        }
        /* Style the custom button (created by JS) */
        .lms-receipt-upload .custom-upload-btn {
          background-color: #3b82f6;
          color: #ffffff;
          padding: 8px 16px;
          border-radius: 6px;
          border: none;
          font-weight: 600;
          font-size: 14px;
          cursor: pointer;
          font-family: "Inter", sans-serif;
          transition: background-color 0.2s;
        }
        .lms-receipt-upload .custom-upload-btn:hover {
          background-color: #2563eb;
        }
        /* Style the drupal upload button */
        .lms-receipt-upload input.button {
          background: #374151;
          color: #ffffff;
          border: none;
          padding: 8px 16px;
          border-radius: 6px;
          cursor: pointer;
          font-weight: 600;
          font-size: 14px;
          margin-left: auto;
        }
        .lms-receipt-upload input.button:hover {
          background: #4b5563;
        }
        .lms-receipt-upload .file {
          color: #10b981; font-weight: 600; text-decoration: none; padding: 0 10px;
        }
      </style>
      <script>
        setInterval(function() {
          var inputs = document.querySelectorAll(".lms-receipt-upload input[type=\'file\']");
          for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            if (!input.dataset.styled) {
              input.dataset.styled = "true";
              input.style.display = "none";
              
              var btn = document.createElement("button");
              btn.type = "button";
              btn.className = "custom-upload-btn";
              btn.innerText = "Choose Image";
              
              var span = document.createElement("span");
              span.className = "custom-file-name";
              span.style.color = "#94a3b8";
              span.style.fontSize = "14px";
              span.style.marginLeft = "10px";
              span.innerText = "No file chosen";

              input.parentNode.insertBefore(btn, input);
              input.parentNode.insertBefore(span, input.nextSibling);

              btn.addEventListener("click", function(e) {
                e.preventDefault();
                input.click();
              });

              input.addEventListener("change", function(e) {
                if (e.target.files && e.target.files.length > 0) {
                  span.innerText = e.target.files[0].name;
                  span.style.color = "#ffffff";
                } else {
                  span.innerText = "No file chosen";
                  span.style.color = "#94a3b8";
                }
              });
            }
          }
        }, 500);
      </script>'),
    ];

    $existing_receipt = [];
    if ($lms_order->hasField('field_payment_receipt') && !$lms_order->get('field_payment_receipt')->isEmpty()) {
      $existing_receipt = [$lms_order->get('field_payment_receipt')->target_id];
      $form['receipt_upload']['description'] = [
        '#markup' => '<p class="text-muted" style="color: #94a3b8; font-size: 0.9em;">You have already uploaded a receipt. You can upload a new one to replace it.</p>',
      ];
    }

    $form['receipt_upload']['receipt_file'] = [
      '#type' => 'managed_file',
      '#title' => clone $this->t('Receipt Image'),
      '#title_display' => 'invisible',
      '#upload_location' => 'public://payment_receipts/' . date('Y-m'),
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'png jpg jpeg'],
        'FileSizeLimit' => ['fileLimit' => 5 * 1024 * 1024],
      ],
      '#required' => empty($existing_receipt),
      '#required_error' => clone $this->t('Please choose an image before submitting.'),
      '#default_value' => $existing_receipt,
    ];

    $form['actions'] = [
      '#type' => 'actions', 
      '#weight' => 100,
      '#attributes' => [
        'style' => 'text-align: center; margin-bottom: 3rem;'
      ]
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => empty($existing_receipt) ? 'Submit Receipt' : 'Update Receipt',
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => ['btn', 'btn-primary'],
        'style' => 'background-color: #10b981; border-color: #10b981; font-weight: bold; width: 100%; max-width: 600px; padding: 0.75rem; border-radius: 0.5rem;',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (!$this->order->hasField('field_payment_receipt')) {
      $this->messenger()->addError($this->t('System error: Receipt field not configured on Order entity. Pls contact admin.'));
      return;
    }

    $file_id_array = $form_state->getValue('receipt_file');
    if (!empty($file_id_array)) {
      $file_id = reset($file_id_array);
      
      /** @var \Drupal\file\Entity\File $file */
      $file = File::load($file_id);
      if ($file) {
        $file->setPermanent();
        $file->save();

        // Attach to order.
        $this->order->set('field_payment_receipt', $file_id);
        $this->order->set('field_status', 'processing');
        $this->order->save();

        $this->messenger()->addStatus($this->t('Payment receipt uploaded successfully! We will verify your payment shortly.'));
      }
    }

    // Redirect to front or dashboard. For now, go to <front> or keep them here?
    // Going to <front> is better so they know it's done. 
    $form_state->setRedirect('<front>');
  }

}

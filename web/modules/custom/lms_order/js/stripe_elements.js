(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.lmsStripeElements = {
    attach: function (context, settings) {
      const $formElement = $('#lms-checkout-form', context);
      
      if ($formElement.length === 0 || $formElement.hasClass('js-stripe-processed')) {
        return;
      }
      $formElement.addClass('js-stripe-processed');

      const clientSecret = settings.lms_payment ? settings.lms_payment.client_secret : null;
      if (!clientSecret) {
        console.warn('LMS Payment: No client secret found for Stripe.');
        return;
      }

      const publishableKey = settings.lms_payment ? settings.lms_payment.publishable_key : null;
      if (!publishableKey) {
        console.warn('LMS Payment: No publishable key found for Stripe.');
        return;
      }

      // Initialize Stripe with the configured publishable key
      const stripe = Stripe(publishableKey);
      const elements = stripe.elements();
      const cardElement = elements.create('card', {
        style: {
          base: {
            iconColor: '#1d70f5',
            color: '#fff',
            fontWeight: '500',
            fontFamily: 'Inter, sans-serif',
            fontSize: '16px',
            fontSmoothing: 'antialiased',
            ':-webkit-autofill': { color: '#fce883' },
            '::placeholder': { color: '#64748b' },
          },
          invalid: {
            iconColor: '#f5576c',
            color: '#f5576c',
          },
        },
      });

      cardElement.mount('#card-element');

      const submitBtn = document.getElementById('submit-button');
      const $paymentMethods = $('input[name="payment_method"]');
      const $stripeContainer = $('#stripe-card-container');
      
      // Auto toggle the card input field depending on the selected Payment Method.
      function toggleStripeContainer() {
        if ($('input[name="payment_method"]:checked').val() === 'stripe') {
          $stripeContainer.slideDown();
        } else {
          $stripeContainer.slideUp();
        }
      }
      $paymentMethods.on('change', toggleStripeContainer);
      toggleStripeContainer();

      $formElement.on('submit', async function(e) {
        const submitter = e.originalEvent && e.originalEvent.submitter;
        const isCheckoutSubmit = submitter
          ? submitter.id === 'submit-button'
          : document.activeElement && document.activeElement.id === 'submit-button';

        if (!isCheckoutSubmit) {
          return;
        }

        const selectedMethod = $('input[name="payment_method"]:checked').val();
        
        // Intercept form submit ONLY if Stripe is selected AND hasn't been confirmed yet
        if (selectedMethod === 'stripe' && !$('#payment-intent-id').val()) {
          e.preventDefault();
          
          if (submitBtn) {
             submitBtn.disabled = true;
             submitBtn.value = 'Processing Secure Payment...';
          }
          $('#card-errors').text('');

          // Attempt to confirm the payment
          const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
              card: cardElement,
            }
          });

          if (error) {
            // Display error cleanly below the card component
            $('#card-errors').text(error.message);
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.value = 'Complete Purchase →';
            }
          } else {
            if (paymentIntent.status === 'succeeded') {
              // Successfully charged!
              $('#payment-intent-id').val(paymentIntent.id);
              // Submit normally to our Drupal backend to fulfill the order
              $formElement[0].submit();
            }
          }
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);

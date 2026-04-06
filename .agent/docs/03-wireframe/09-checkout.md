# Wireframe: Checkout Flow

## Step 1: Cart Review

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ PROGRESS: [① Cart ●]──[② Payment ○]──[③ Confirmation ○]            │
├──────────────────────────────────────────┬──────────────────────────┤
│ CART ITEMS (col-8)                       │ ORDER SUMMARY (col-4)    │
│                                          │                          │
│ ┌──────────────────────────────────────┐│ Subtotal:     $79.98     │
│ │ [Thumb] Web Dev Bootcamp    $49.99  ││ Discount:     -$10.00    │
│ │         By John Doe         [✕ Rm]  ││ ──────────────────       │
│ ├──────────────────────────────────────┤│ Total:        $69.98     │
│ │ [Thumb] React Masterclass   $29.99  ││                          │
│ │         By Sarah L.         [✕ Rm]  ││ Coupon: [_________] [Go] │
│ └──────────────────────────────────────┘│ ✅ SAVE20 applied        │
│                                          │                          │
│                                          │ [Proceed to Payment →]   │
├──────────────────────────────────────────┴──────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```

## Step 2: Payment

```
┌─────────────────────────────────────────────────────────────────────┐
│ PROGRESS: [① Cart ✅]──[② Payment ●]──[③ Confirmation ○]            │
├──────────────────────────────────────────┬──────────────────────────┤
│ PAYMENT METHOD (col-8)                   │ ORDER SUMMARY (col-4)    │
│                                          │ (same as step 1)         │
│ ● Credit/Debit Card (Stripe)            │                          │
│   Card Number: [________________]        │ Total: $69.98            │
│   Expiry: [MM/YY] CVV: [___]           │                          │
│   Name: [_________________________]     │                          │
│                                          │                          │
│ ○ PayPal                                │                          │
│   [Pay with PayPal]                     │                          │
│                                          │                          │
│ [🔒 Secure Payment]                    │ [Complete Purchase]       │
└──────────────────────────────────────────┴──────────────────────────┘
```

## Step 3: Confirmation

```
┌─────────────────────────────────────────────────────────────────────┐
│ PROGRESS: [① Cart ✅]──[② Payment ✅]──[③ Confirmation ✅]           │
├─────────────────────────────────────────────────────────────────────┤
│                    ✅ Payment Successful!                            │
│                                                                      │
│  Order #12345                                                        │
│  Date: March 8, 2024                                                 │
│                                                                      │
│  Items:                                                              │
│  • Web Dev Bootcamp — $49.99                                        │
│  • React Masterclass — $29.99                                       │
│  Discount: -$10.00                                                   │
│  Total: $69.98                                                       │
│                                                                      │
│  📧 Confirmation email sent to john@example.com                     │
│                                                                      │
│  [Start Learning →]  [View My Courses]  [📄 Download Invoice]       │
└─────────────────────────────────────────────────────────────────────┘
```

# Goal Description
Enhance the Workspace Orders dashboard (`/workspace/orders`) by introducing functional filtering capabilities. Managers should be able to search orders by ID or Learner name, and filter the list by Order Status (Pending, Completed, Cancelled).

## Proposed Changes

### Controller Logic Updates
#### [MODIFY] AdminController.php
- Inject the Request service `$request = \Drupal::request();` into `adminOrders()`.
- Read `$search = $request->query->get('search');` and `$status = $request->query->get('status');`.
- Apply filtering conditions to the `lms_order` entity query:
  - If `$status` is active (not 'all'), add `condition('field_status', $status)`.
  - If `$search` is active, apply an OR condition group for `id` (if numeric) or learner name (needs to join user entity or filter locally).
- Pass `$search` and `$status` down as Twig variables `['#filters' => ['search' => $search, 'status' => $status]]`.

### Frontend Template Updates
#### [MODIFY] lms-admin-orders.html.twig
- Introduce the generic Manager UI filter bar markup (`.lms-admin-filter`) above the data table.
- Use a `<form method="GET">` so the URL becomes filterable (e.g., `?search=...&status=...`).
- Bind the input value properties so that the template re-populates the current active query state.

## Verification Plan
1. Go to the Orders dashboard.
2. Filter the table to only show 'Pending' orders.
3. Search for a specific user.
4. Verify the backend successfully whittles down the table display according to the URL parameters.

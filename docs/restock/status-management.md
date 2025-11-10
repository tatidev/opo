# Status Management System

## Overview

The Restock system uses a status-based workflow to track orders from creation through completion. Each status change can trigger different actions like email notifications, automatic completion, or order archival.

## Status Definitions

### Status Constants
**Code**: [`Restock.php:6-10`](../../application/controllers/Restock.php#L6)

```php
class Restock extends MY_Controller {
    var $_BACKORDER_ID = [7];   // Items need to be purchased from supplier
    var $_COMPLETED_ID = [5];   // Orders fully fulfilled
    var $_CANCEL_ID = [6];      // Orders cancelled
}
```

### Standard Status Types

| Status ID | Name | Description | Actions Triggered |
|-----------|------|-------------|------------------|
| 1 | Pending | Just created, awaiting processing | None |
| 2 | Processing | Being prepared for shipment | None |
| 3 | Ready to Ship | Samples prepared, ready to send | None |
| 4 | Shipped | Samples sent to destination | Update tracking |
| 5 | Completed | Order fully fulfilled | Move to completed table |
| 6 | Cancelled | Order cancelled before completion | Move to completed table |
| 7 | Backorder | Items not available, need purchasing | Send email alert |

**Note**: Status names and IDs are configurable in the `restock_status` database table.

## Status Change Processing

### Main Status Update Logic
**Code**: [`Restock.php:214-270`](../../application/controllers/Restock.php#L214)

```php
foreach ($orders_data as $order) {
    $new_status = intval($restock_updates[$ix]['restock_status_id']);
    
    // Check for backorder status (triggers email)
    if (in_array($new_status, $this->_BACKORDER_ID)) {
        $order_ids_backorder[] = $order['id'];
    }
    
    // Check for completion/cancellation (moves to archive)
    if (in_array($new_status, $this->_CANCEL_ID)) {
        $order_updates[] = [
            'id' => $order_id,
            'restock_status_id' => $new_status,
            'date_modif' => date('Y-m-d H:i:s'),
            'user_id_modif' => $this->data['user_id']
        ];
        $order_ids_completed[] = $order_id;
    }
}
```

**Simple explanation**: When order statuses are changed, the system checks what actions should be triggered based on the new status and queues those actions for execution.

## Status-Triggered Actions

### 1. Backorder Email Notifications
**Trigger**: Status changed to Backorder (ID: 7)
**Action**: Automatic email to purchasing team
**Code**: [`Restock.php:221-224`](../../application/controllers/Restock.php#L221)

```php
if (in_array($new_status, $this->_BACKORDER_ID)) {
    // Send notification email that some items need to be purchased
    $order_ids_backorder[] = $order['id'];
}
```

**What happens**:
1. Order ID added to backorder list
2. After all status updates processed, `send_backorders_email()` called
3. Email sent to purchasing team with item details
4. Includes links to supplier ordering system

### 2. Order Completion and Archival
**Trigger**: Status changed to Completed (ID: 5) or Cancelled (ID: 6)
**Action**: Move order to completed table
**Code**: [`Restock.php:251-268`](../../application/controllers/Restock.php#L251)

```php
if (in_array($new_status, $this->_CANCEL_ID)) {
    // Update order status to CANCEL, then move to the COMPLETED table
    $order_updates[] = [
        'id' => $order_id,
        'restock_status_id' => $new_status,
        'date_modif' => date('Y-m-d H:i:s'),
        'user_id_modif' => $this->data['user_id']
    ];
    $order_ids_completed[] = $order_id;
}
```

**What happens**:
1. Order status updated in database
2. Order moved from `restock_orders` to `restock_completed` table
3. Order no longer appears in active orders list
4. Order remains searchable in completed orders view

### 3. All Other Status Changes
**Action**: Update database record only
**Code**: [`Restock.php:261-268`](../../application/controllers/Restock.php#L261)

```php
else {
    // Status changed but NOT COMPLETED, don't move from RESTOCK_ORDER table
    $order_updates[] = [
        'id' => $order_id,
        'restock_status_id' => $new_status,
        'date_modif' => date('Y-m-d H:i:s'),
        'user_id_modif' => $this->data['user_id']
    ];
}
```

## Automatic Status Changes

### Shipment-Based Completion
**Code**: [`Restock.php:231-236`](../../application/controllers/Restock.php#L231)

```php
if ($new_ship_quantity_samples >= $qty_pending_samples && 
    $new_ship_quantity_ringset >= $qty_pending_ringset) {
    // Just move from ORDER to COMPLETED table
    $order_ids_completed[] = $order_id;
}
```

**Logic**:
1. When shipment quantities are recorded
2. System calculates remaining quantities needed
3. If all quantities fulfilled, order automatically marked complete
4. Order moved to completed table without manual status change

**Simple explanation**: When warehouse staff records that they've shipped all requested samples, the system automatically marks the order as complete without requiring manual status updates.

### Completion Detection Formula
```php
$qty_pending_samples = intval($order['quantity_total']) - intval($order['quantity_shipped']);
$qty_pending_ringset = intval($order['quantity_ringsets']) - intval($order['quantity_ringsets_shipped']);

// Order complete when both pending quantities <= 0
$is_complete = ($qty_pending_samples <= 0 && $qty_pending_ringset <= 0);
```

## Status Dropdowns in UI

### Dynamic Dropdown Generation
**Code**: [`Restock.php:48-53`](../../application/controllers/Restock.php#L48)

```php
$restock_options = $this->decode_array($this->specs->get_restock_status(), 'id', 'name');
unset($restock_options[$this->_COMPLETED_ID[0]]);  // Remove "Completed" from dropdown

foreach ($items_to_view as &$item) {
    $_dropdown_id = "restock_status_" . $item['id'];
    $item['status_dropdown'] = form_dropdown($_dropdown_id, $restock_options, 
        set_value($_dropdown_id, $item['restock_status_id']), 
        " id='" . $_dropdown_id . "' data-id='" . $item['id'] . "' onchange='mark_row_as_edit(this)' class='' tabindex='-1' ");
}
```

**Features**:
- Each order row has its own status dropdown
- Current status is pre-selected
- "Completed" status hidden (orders auto-complete via shipments)
- JavaScript triggers edit mode when changed

### Status Filtering
**Code**: [`Restock.php:32-35`](../../application/controllers/Restock.php#L32)

```php
$restock_status = $this->decode_array($this->specs->get_restock_status(), 'id', 'name');
$restock_status[0] = 'All';
unset($restock_status[$this->_COMPLETED_ID[0]]);  // Remove completed from filter
$this->data['restock_filter_status'] = form_dropdown('restock_filter_status', $restock_status, 0, ...);
```

Users can filter orders by status, excluding completed orders (which are in separate view).

## Status Transition Rules

### Valid Transitions
The system allows flexible status transitions, but some business rules apply:

**From Pending**:
- Can change to: Processing, Backorder, Cancelled
- Cannot directly go to: Completed (must ship samples first)

**From Processing**: 
- Can change to: Ready to Ship, Backorder, Cancelled
- Auto-changes to: Completed (when fully shipped)

**From Backorder**:
- Can change to: Processing, Ready to Ship, Cancelled
- Common path: Backorder → Processing (when items received from supplier)

**From any status**:
- Can change to: Cancelled (business decision)
- Auto-changes to: Completed (when fully shipped)

### Forbidden Transitions
- **From Completed**: No changes allowed (orders archived)
- **From Cancelled**: No changes allowed (orders archived)

## Status Audit Trail

### Change Tracking
Every status change records:
- **user_id_modif**: Who made the change
- **date_modif**: When change was made
- **Previous status**: Stored in audit log (if implemented)

**Code**: [`Restock.php:255-257`](../../application/controllers/Restock.php#L255)

```php
$order_updates[] = [
    'id' => $order_id,
    'restock_status_id' => $new_status,
    'date_modif' => date('Y-m-d H:i:s'),
    'user_id_modif' => $this->data['user_id']
];
```

### Historical Reporting
- View status change history by order
- Track user activity patterns
- Identify bottlenecks in workflow
- Monitor time spent in each status

## Status-Based Business Logic

### Performance Metrics
Status data enables reporting on:
- **Average Processing Time**: Time from Pending to Shipped
- **Backorder Frequency**: How often items are backordered
- **Completion Rate**: Percentage of orders that complete vs cancel
- **User Efficiency**: Which users process orders fastest

### Workflow Optimization
Status transitions reveal:
- **Common Bottlenecks**: Statuses where orders get stuck
- **Skip Patterns**: Statuses that are commonly skipped
- **Process Improvements**: Opportunities to streamline workflow

### Integration Opportunities
Status changes can trigger:
- **Inventory Updates**: Adjust sample inventory levels
- **Customer Notifications**: Alert sales reps when samples ready
- **Supplier Integration**: Automatic purchase orders for backorders
- **Shipping Integration**: Generate shipping labels for completed orders

## Troubleshooting Status Issues

### Status Not Updating
**Symptoms**: Dropdown changes but status doesn't save
**Causes**: 
- JavaScript errors preventing form submission
- Permission issues (user lacks edit rights)
- Database connection problems

**Debug Steps**:
1. Check browser console for JavaScript errors
2. Verify user has `hasEditPermission` set to true
3. Check network tab for failed AJAX requests
4. Review server logs for PHP/database errors

### Automatic Completion Not Working
**Symptoms**: Orders remain active after all samples shipped
**Causes**:
- Quantity calculation errors
- Database trigger issues
- Logic errors in completion detection

**Debug Code**: [`Restock.php:231-236`](../../application/controllers/Restock.php#L231)

```php
// Add debugging output
$qty_pending_samples = intval($order['quantity_total']) - intval($order['quantity_shipped']);
$qty_pending_ringset = intval($order['quantity_ringsets']) - intval($order['quantity_ringsets_shipped']);

error_log("Order {$order_id}: Pending samples={$qty_pending_samples}, ringsets={$qty_pending_ringset}");

if ($new_ship_quantity_samples >= $qty_pending_samples && 
    $new_ship_quantity_ringset >= $qty_pending_ringset) {
    error_log("Order {$order_id} should be completed");
    $order_ids_completed[] = $order_id;
}
```

### Email Notifications Not Sending
**Symptoms**: Backorder status set but no email received
**Causes**:
- Email configuration issues
- Wrong recipient settings
- SMTP server problems

**Debug Steps**:
1. Verify status change actually sets backorder flag
2. Check email configuration in CodeIgniter
3. Test SMTP connectivity manually
4. Review email logs for send attempts

**See**: [Email Notifications](email-notifications.md#troubleshooting) for detailed debugging
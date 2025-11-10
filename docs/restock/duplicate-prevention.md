# Duplicate Prevention System

## Overview

The Restock system includes sophisticated duplicate detection to prevent over-ordering of samples. When users try to create orders for items that already have pending orders, the system shows existing orders and allows users to combine quantities or create separate orders.

## How Duplicate Detection Works

### Detection Logic
**Code**: [`Restock.php:107-116`](../../application/controllers/Restock.php#L107)

```php
if (!$OK_to_proceed_for_duplicates) {
    // Extract item IDs and sizes from the new order
    $item_ids = array_column($this->input->post('items'), 'item_id');
    $item_sizes = array_column($this->input->post('items'), 'size');
    
    // Check for existing orders with same items, sizes, and destination
    $duplicates_data = $this->model->get_duplicates($item_ids, $item_sizes, $destination_id);
    
    if (count($duplicates_data) > 0) {
        // Show confirmation dialog to user
        return;
    }
}
```

**Simple explanation**: Before creating any order, the system checks if there are already pending orders for the same items, same sizes, going to the same destination.

### What Constitutes a Duplicate

A duplicate order is detected when **all three** conditions match:
1. **Same Item ID** - Same fabric/product
2. **Same Size** - Same sample size (6x6, 12x12, 18x18)
3. **Same Destination** - Same warehouse/office location

### Duplicate Detection Query
The model method `get_duplicates()` searches for existing orders with:
```sql
SELECT * FROM restock_orders 
WHERE item_id IN (?, ?, ?) 
  AND size IN (?, ?, ?) 
  AND destination_id = ?
  AND status NOT IN (completed_status_ids)
```

## User Experience Flow

### Step 1: Order Submission
User selects items and tries to create restock order.

### Step 2: Duplicate Check
**Code**: [`Restock.php:109-111`](../../application/controllers/Restock.php#L109)
System automatically checks for duplicates without user interaction.

### Step 3: Duplicate Found - Show Confirmation
**Code**: [`Restock.php:118-142`](../../application/controllers/Restock.php#L118)

If duplicates exist, system:
1. Builds HTML table showing existing orders
2. Includes order details (product, quantities, dates, who requested)
3. Returns JSON response with confirmation dialog
4. Waits for user decision

**Confirmation Table Columns**:
```php
$values_to_get = [
    'product_name' => 'Product Name',
    'code' => 'Code',
    'color' => 'Color',
    'quantity_total' => 'Total Samples',
    'quantity_priority' => '# Priority',
    'quantity_ringsets' => '# Ringsets',
    'size' => 'Size',
    'status' => 'Status',
    'date_add' => 'Date Req',
    'username' => 'By'
];
```

### Step 4: User Decision
User sees existing orders and can choose:
- **Combine Orders** - Add new quantities to existing orders
- **Create Separate** - Create new orders anyway
- **Cancel** - Don't create any orders

### Step 5A: Combine Orders (Most Common)
**Code**: [`Restock.php:168-183`](../../application/controllers/Restock.php#L168)

```php
if ($ix !== false) {
    // Update existing order with combined quantities
    $existing_order = $duplicates_data[$ix];
    $data = [
        'id' => $existing_order['id'],
        'quantity_priority' => intval($existing_order['quantity_priority']) + $qty_priority,
        'quantity_ringsets' => intval($existing_order['quantity_ringsets']) + $qty_ringsets,
        'quantity_total' => intval($existing_order['quantity_total']) + $qty_order + $qty_priority,
        'date_modif' => date('Y-m-d H:i:s'),
        'user_id_modif' => $this->data['user_id']
    ];
    $update_data[] = $data;
}
```

**Simple explanation**: The new quantities are added to the existing order quantities, and the order is marked as modified with current user and timestamp.

### Step 5B: Create Separate Orders
**Code**: [`Restock.php:185-192`](../../application/controllers/Restock.php#L185)

```php
else {
    // Create new separate order
    $i['quantity_total'] = $qty_order + $qty_priority;
    $i['destination_id'] = $destination_id;
    $i['user_id'] = intval($this->data['user_id']);
    $insert_data[] = $i;
}
```

**Simple explanation**: New orders are created as originally requested, even though duplicates exist.

## User Confirmation Process

### Confirmation Response Format
**Code**: [`Restock.php:142`](../../application/controllers/Restock.php#L142)

```json
{
    "success": false,
    "status": "duplicates", 
    "response": "<table class='table table-borderless table-sm'>...</table><input type='hidden' name='duplicate_order_ids' value='[123,456]' />"
}
```

### Hidden Form Data
The confirmation includes hidden input with duplicate order IDs:
```php
form_hidden('duplicate_order_ids', str_replace('"', "", json_encode($duplicates_order_ids)))
```

This allows the system to know which existing orders to update if user chooses to combine.

### Re-submission with Confirmation
**Code**: [`Restock.php:148-156`](../../application/controllers/Restock.php#L148)

When user confirms combining orders:
```php
$duplicates_order_ids = $this->input->post('duplicate_order_ids');
sort($duplicates_order_ids);
$duplicates_data = $this->model->get_restocks([
    'ids' => $duplicates_order_ids,
]);
$duplicate_item_ids = array_column($duplicates_data, 'item_id');
```

## Benefits of Duplicate Prevention

### Prevents Over-Ordering
- Avoids requesting more samples than needed
- Reduces waste and storage costs
- Prevents confusion about quantities

### Consolidates Requests
- Multiple people can contribute to same order
- Easier fulfillment for warehouse staff
- Single shipment instead of multiple small ones

### Maintains Accuracy
- Clear audit trail of who requested what
- Quantities properly tracked and combined
- Historical record of order modifications

## Example Scenarios

### Scenario 1: Sales Rep Addition
1. **Existing Order**: 5 samples of Fabric A, size 12x12, to Chicago office
2. **New Request**: Sales rep needs 3 more samples of same fabric/size/destination
3. **System Action**: Shows existing order, asks to combine
4. **Result**: Single order for 8 samples total

### Scenario 2: Different Sizes - No Duplicate
1. **Existing Order**: 5 samples of Fabric A, size 12x12, to Chicago office
2. **New Request**: 3 samples of Fabric A, size 6x6, to Chicago office  
3. **System Action**: No duplicate detected (different sizes)
4. **Result**: Two separate orders created

### Scenario 3: Different Destinations - No Duplicate
1. **Existing Order**: 5 samples of Fabric A, size 12x12, to Chicago office
2. **New Request**: 3 samples of Fabric A, size 12x12, to New York office
3. **System Action**: No duplicate detected (different destinations)
4. **Result**: Two separate orders created

## Advanced Duplicate Handling

### Multiple Item Duplicates
**Code**: [`Restock.php:161-194`](../../application/controllers/Restock.php#L161)

When an order contains multiple items, some duplicates and some new:
```php
foreach ($this->input->post('items') as $i) {
    $ix = array_search($i['item_id'], $duplicate_item_ids);
    if ($ix !== false) {
        // This item is a duplicate - update existing
        $update_data[] = $combined_data;
    } else {
        // This item is new - create new order
        $insert_data[] = $new_order_data;
    }
}
```

**Simple explanation**: Each item in the order is checked individually - some may be combined with existing orders while others create new orders.

### Batch Operations
**Code**: [`Restock.php:196-197`](../../application/controllers/Restock.php#L196)

```php
if (count($update_data) > 0) $this->model->update_batch_on_order($update_data);
if (count($insert_data) > 0) $this->model->add_batch_on_order($insert_data);
```

All updates and inserts are done in batches for better performance and data consistency.

## Configuration Options

### Bypass Duplicate Detection
Users can bypass duplicate detection by setting:
```php
$OK_to_proceed_for_duplicates = $this->input->post('OK_with_duplicates') == '1';
```

This is used when user has already confirmed they want to create separate orders.

### Duplicate Detection Scope
Currently detects duplicates based on:
- Same item_id
- Same size  
- Same destination_id
- Pending orders only (not completed/cancelled)

Could be extended to include:
- Same user (only show user's own duplicates)
- Time-based (only recent orders)
- Quantity thresholds (only large orders)

## Error Handling

### Database Errors
If duplicate check query fails:
- System logs error but proceeds with order creation
- User notification about potential duplicates
- Manual verification required

### Invalid Duplicate Data
If duplicate order IDs are invalid:
- System validates IDs before processing
- Ignores invalid IDs
- Proceeds with valid orders only

### Partial Failures
If some updates succeed and others fail:
- Database transactions ensure consistency
- User informed of partial success
- Failed operations logged for review

## Performance Considerations

### Query Optimization
- Duplicate check query uses indexes on item_id, destination_id
- Limited to active orders only
- Results limited to reasonable number of duplicates

### User Experience
- Duplicate check happens before UI confirmation
- Fast response for most common cases
- Progressive enhancement for complex scenarios

### Memory Usage
- Duplicate data cached during request processing
- Minimal memory footprint for large orders
- Batch operations reduce database overhead
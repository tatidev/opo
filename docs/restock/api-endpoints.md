# Restock API Endpoints

## Controller Methods Overview

All methods are in [`application/controllers/Restock.php`](../../application/controllers/Restock.php)

| Method | Purpose | HTTP | Parameters | Response |
|--------|---------|------|------------|----------|
| `index()` | Main page | GET | `$completed` (optional) | HTML view |
| `get()` | Fetch orders | POST | Filter data | JSON orders |
| `add()` | Create orders | POST | Order items, destination | JSON success/duplicates |
| `save()` | Update orders | POST | Status changes, shipments | JSON status |
| `get_destinations()` | Destination dropdown | GET | None | JSON dropdown HTML |

## Detailed Method Documentation

### `index($completed = null)`
**Line**: [`22-39`](../../application/controllers/Restock.php#L22)  
**Purpose**: Renders main restock management page with filters

**Process**:
1. Sets up breadcrumbs and permissions
2. Loads destination and status filter dropdowns
3. Determines if starting with completed orders view
4. Renders `restock/list.php` view

**Code snippet**:
```php
public function index($completed = null) {
    array_push($this->data['crumbs'], 'On Order');
    $this->data['ajaxUrl'] = site_url('restock/get');
    
    // Build filter dropdowns
    $restock_destinations = $this->decode_array($this->specs->get_restock_destinations(), 'id', 'name');
    $restock_status = $this->decode_array($this->specs->get_restock_status(), 'id', 'name');
    
    $this->view('restock/list');
}
```

**Simple explanation**: Sets up the main restock page with all the filter options and table configuration.

### `get($completed = null)`  
**Line**: [`41-64`](../../application/controllers/Restock.php#L41)  
**Purpose**: Returns order data for DataTables via AJAX

**POST Parameters**:
- `restock_filter_order_history` - 'pendings' or 'completed'
- `restock_filter_destinations` - Destination ID or '0' for all
- `restock_filter_from` - Date from filter
- `restock_filter_to` - Date to filter  
- `restock_filter_status` - Status ID or '0' for all

**Process**:
1. Decodes POST filters using `decode_post_filters()`
2. Fetches orders via `$this->model->get_restocks()`
3. Builds status dropdown for each order
4. Calculates completion status
5. Returns JSON with `tableData` array

**Code snippet**:
```php
public function get($completed = null) {
    $items_to_view = $this->model->get_restocks($this->decode_post_filters());
    
    foreach ($items_to_view as &$item) {
        // Build status dropdown for this order
        $_dropdown_id = "restock_status_" . $item['id'];
        $item['status_dropdown'] = form_dropdown($_dropdown_id, $restock_options, ...);
        
        // Calculate if order is completed
        $item['is_completed'] = (
            ($item["quantity_total"] == $item["quantity_shipped"]) &&
            ($item["quantity_ringsets"] == $item["quantity_ringsets_shipped"])
        );
    }
    
    echo json_encode(['tableData' => $items_to_view]);
}
```

**Response format**:
```json
{
    "tableData": [
        {
            "id": "123",
            "item_id": "456", 
            "product_name": "Fabric Name",
            "code": "FAB123",
            "color": "Blue",
            "quantity_total": "10",
            "quantity_shipped": "5",
            "status_dropdown": "<select>...</select>",
            "is_completed": false
        }
    ]
}
```

### `add()`
**Line**: [`100-200`](../../application/controllers/Restock.php#L100)  
**Purpose**: Creates new restock orders with duplicate detection

**POST Parameters**:
- `destination` - Destination ID
- `items[]` - Array of items with quantities
- `OK_with_duplicates` - '1' if user confirmed duplicates
- `duplicate_order_ids` - IDs of existing orders (when combining)

**Duplicate Detection Process**:
```php
if (!$OK_to_proceed_for_duplicates) {
    $item_ids = array_column($this->input->post('items'), 'item_id');
    $duplicates_data = $this->model->get_duplicates($item_ids, $item_sizes, $destination_id);
    
    if (count($duplicates_data) > 0) {
        // Build confirmation table
        return json_encode(['success' => false, 'status' => 'duplicates', 'response' => $table_html]);
    }
}
```

**Order Processing**:
```php
foreach ($this->input->post('items') as $i) {
    if ($is_duplicate) {
        // Update existing order with combined quantities
        $update_data[] = [
            'quantity_priority' => $existing_priority + $new_priority,
            'quantity_total' => $existing_total + $new_total,
            // ...
        ];
    } else {
        // Create new order
        $insert_data[] = $new_order_data;
    }
}
```

**Response Types**:
```json
// Success
{"success": true, "status": null}

// Duplicates found
{"success": false, "status": "duplicates", "response": "<table>...</table>"}
```

### `save()`
**Line**: [`202-302`](../../application/controllers/Restock.php#L202)  
**Purpose**: Updates order statuses and records shipments

**POST Parameters**:
- `restock_updates[]` - Array of order updates with:
  - `id` - Order ID
  - `restock_status_id` - New status
  - `ship_quantity_samples` - Samples shipped
  - `ship_quantity_ringset` - Ringsets shipped

**Processing Logic**:
```php
foreach ($orders_data as $order) {
    $new_status = intval($restock_updates[$ix]['restock_status_id']);
    
    // Check for backorders (triggers email)
    if (in_array($new_status, $this->_BACKORDER_ID)) {
        $order_ids_backorder[] = $order['id'];
    }
    
    // Record shipments
    if ($new_ship_quantity_samples > 0 || $new_ship_quantity_ringset > 0) {
        $new_shipments[] = [
            'order_id' => $order_id,
            'quantity' => $new_ship_quantity_samples,
            'quantity_ringsets' => $new_ship_quantity_ringset
        ];
        
        // Check if order is now complete
        if ($shipped_qty >= $pending_qty) {
            $order_ids_completed[] = $order_id;
        }
    }
}
```

**Database Operations**:
1. `add_restock_shipments($new_shipments)` - Record shipments
2. `update_orders($order_updates)` - Update statuses  
3. `move_completed_orders($order_ids_completed)` - Archive completed
4. `send_backorders_email($order_ids_backorder)` - Send notifications

**Response**:
```json
{
    "status": true,
    "completed": [123, 456],  // Order IDs moved to completed
    "updates": [...]          // Status changes applied
}
```

### `get_destinations()`
**Line**: [`90-98`](../../application/controllers/Restock.php#L90)  
**Purpose**: Returns destination dropdown HTML for modals

**Process**:
1. Gets user's default destination: `get_user_restock_destination_id()`
2. Builds dropdown HTML with CodeIgniter's `form_dropdown()`
3. Returns JSON with dropdown HTML

**Response**:
```json
{
    "dropdown_html": "<select id='dropdown_restock_destination_all'>...</select>"
}
```

## Private Helper Methods

### `decode_post_filters()`
**Line**: [`66-88`](../../application/controllers/Restock.php#L66)  
**Purpose**: Converts POST data into filter array for model

**Logic**:
```php
// Handle completed vs pending filter
$this->filters['only_completed'] = ($this->input->post('restock_filter_order_history') == 'completed');

// Optional filters only added if not default values
if ($this->input->post('restock_filter_destinations') !== '0') {
    $this->filters['destination_id'] = [$this->input->post('restock_filter_destinations')];
}
```

### `send_backorders_email()`
**Line**: [`304-376`](../../application/controllers/Restock.php#L304)  
**Purpose**: Sends email notifications for backorders

**Process**:
1. Fetches backorder details with product info
2. Builds HTML table using CodeIgniter's Table library
3. Sets up email with environment-specific recipients
4. Sends email with backorder details and purchasing links

See [Email Notifications](email-notifications.md) for detailed documentation.

## Error Handling

### Common Response Patterns
- **Success**: `{"success": true, "status": null}`
- **Validation Error**: `{"success": false, "message": "Error description"}`  
- **Duplicates**: `{"success": false, "status": "duplicates", "response": "HTML table"}`

### Exception Handling
- Database errors are caught by CodeIgniter's error handling
- Email failures are logged but don't stop order processing
- Invalid parameters default to safe values (e.g., destination '0' = all)

## Testing API Endpoints

### Using cURL Examples

**Get Orders:**
```bash
curl -X POST "http://localhost/restock/get" \
  -d "restock_filter_order_history=pendings" \
  -d "restock_filter_destinations=0" \
  -d "restock_filter_status=0"
```

**Create Order:**
```bash
curl -X POST "http://localhost/restock/add" \
  -d "destination=1" \
  -d "items[0][item_id]=123" \
  -d "items[0][quantity]=5" \
  -d "items[0][size]=12"
```

**Update Order:**
```bash
curl -X POST "http://localhost/restock/save" \
  -d "restock_updates[0][id]=456" \
  -d "restock_updates[0][restock_status_id]=7" \
  -d "restock_updates[0][ship_quantity_samples]=3"
```
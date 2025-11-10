# Code References

## Quick Reference Guide

This document provides line-by-line references to key functionality in the Restock system for developers who need to understand or modify the code.

## Main Controller: `application/controllers/Restock.php`

### Class Definition and Constants
```php
// Lines 4-10: Class definition and status constants
class Restock extends MY_Controller {
    var $_BACKORDER_ID = [7];    // Triggers email notifications
    var $_COMPLETED_ID = [5];    // Moves orders to completed table
    var $_CANCEL_ID = [6];       // Moves orders to completed table
}
```

### Constructor and Initialization
```php
// Lines 11-20: Constructor setup
function __construct() {
    parent::__construct();
    $this->thisC = 'restock';
    $this->load->library('table');        // For email HTML tables
    $this->load->library('email');        // For backorder notifications
    $this->load->model('Restock_model', 'model');
    $this->data['hasEditPermission'] = $this->hasPermission('restock', 'edit');
}
```

### Page Rendering Methods

#### `index()` - Main Restock Page
**Lines 22-39**: [`Restock.php:22-39`](../../application/controllers/Restock.php#L22)

```php
public function index($completed = null) {
    // Line 24: Set breadcrumb navigation
    array_push($this->data['crumbs'], 'On Order');
    
    // Line 25: Set AJAX URL for DataTables
    $this->data['ajaxUrl'] = site_url('restock/get');
    
    // Lines 27-30: Build destination filter dropdown
    $restock_destinations = $this->decode_array($this->specs->get_restock_destinations(), 'id', 'name');
    $restock_destinations[0] = 'All';
    $this->data['restock_filter_destinations'] = form_dropdown(...);
    
    // Lines 32-35: Build status filter dropdown (excluding completed)
    $restock_status = $this->decode_array($this->specs->get_restock_status(), 'id', 'name');
    unset($restock_status[$this->_COMPLETED_ID[0]]);
    
    // Line 37: Determine if starting with completed view
    $this->data['start_completed'] = !is_null($completed);
    
    // Line 38: Render the main view
    $this->view('restock/list');
}
```

#### `get()` - AJAX Data for DataTables
**Lines 41-64**: [`Restock.php:41-64`](../../application/controllers/Restock.php#L41)

```php
public function get($completed = null) {
    // Line 46: Get orders based on POST filters
    $items_to_view = $this->model->get_restocks($this->decode_post_filters());
    
    // Line 48: Get status options for dropdowns
    $restock_options = $this->decode_array($this->specs->get_restock_status(), 'id', 'name');
    
    // Lines 50-61: Process each order
    foreach ($items_to_view as &$item) {
        // Lines 52-53: Build status dropdown for this order
        $_dropdown_id = "restock_status_" . $item['id'];
        $item['status_dropdown'] = form_dropdown($_dropdown_id, $restock_options, ...);
        
        // Lines 54-57: Calculate completion status
        $item['is_completed'] = (
            ($item["quantity_total"] == $item["quantity_shipped"]) &&
            ($item["quantity_ringsets"] == $item["quantity_ringsets_shipped"])
        );
    }
    
    // Line 63: Return JSON response
    echo json_encode(['tableData' => $items_to_view]);
}
```

### Filter Processing

#### `decode_post_filters()` - Convert POST to Filter Array
**Lines 66-88**: [`Restock.php:66-88`](../../application/controllers/Restock.php#L66)

```php
private function decode_post_filters() {
    // Line 68: Handle completed vs pending filter
    $this->filters['only_completed'] = ($this->input->post('restock_filter_order_history') == 'completed');
    
    // Lines 70-72: Destination filter (if not 'All')
    if ($this->input->post('restock_filter_destinations') !== '0') {
        $this->filters['destination_id'] = [$this->input->post('restock_filter_destinations')];
    }
    
    // Lines 74-80: Date range filters
    if ($this->input->post('restock_filter_from')) {
        $this->filters['date_from'] = $this->input->post('restock_filter_from');
    }
    if ($this->input->post('restock_filter_to')) {
        $this->filters['date_to'] = $this->input->post('restock_filter_to');
    }
    
    // Lines 82-84: Status filter (if not 'All')
    if ($this->input->post('restock_filter_status') !== '0') {
        $this->filters['status_id'] = [$this->input->post('restock_filter_status')];
    }
}
```

### Utility Methods

#### `get_destinations()` - Destination Dropdown for Modals
**Lines 90-98**: [`Restock.php:90-98`](../../application/controllers/Restock.php#L90)

```php
public function get_destinations() {
    // Line 93: Get user's default destination
    $user_restock_destination_id = $this->specs->get_user_restock_destination_id($this->data['user_id']);
    
    // Line 94: Get all destinations
    $restock_destinations = $this->decode_array($this->specs->get_restock_destinations(), 'id', 'name');
    
    // Lines 95-96: Build dropdown HTML
    $_dropdown_id = 'dropdown_restock_destination_all';
    $dropdown_html = form_dropdown($_dropdown_id, $restock_destinations, $user_restock_destination_id, ...);
    
    // Line 97: Return JSON
    echo json_encode(['dropdown_html' => $dropdown_html]);
}
```

## Order Management Methods

### `add()` - Create New Orders with Duplicate Detection
**Lines 100-200**: [`Restock.php:100-200`](../../application/controllers/Restock.php#L100)

#### Duplicate Detection Logic
```php
// Lines 102-105: Extract form data
$destination_id = intval($this->input->post('destination'));
$OK_to_proceed_for_duplicates = $this->input->post('OK_with_duplicates') == '1';

// Lines 107-116: Check for duplicates (unless user already confirmed)
if (!$OK_to_proceed_for_duplicates) {
    $item_ids = array_column($this->input->post('items'), 'item_id');
    $item_sizes = array_column($this->input->post('items'), 'size');
    $duplicates_data = $this->model->get_duplicates($item_ids, $item_sizes, $destination_id);
    
    if (count($duplicates_data) > 0) {
        // Lines 118-142: Build confirmation table and return
        $this->load->library('table');
        // ... table building code ...
        echo json_encode(['success' => false, 'status' => 'duplicates', 'response' => $table_html]);
        return;
    }
}
```

#### Order Processing Logic
```php
// Lines 158-194: Process each item for insert/update
foreach ($this->input->post('items') as $i) {
    $qty_order = intval($i['quantity']);
    $qty_priority = intval($i['quantity_priority']);
    $qty_ringsets = intval($i['quantity_ringsets']);
    
    if ($qty_order > 0 || $qty_priority > 0 || $qty_ringsets > 0) {
        $ix = array_search($i['item_id'], $duplicate_item_ids);
        
        if ($ix !== false) {
            // Lines 172-183: Update existing order
            $data = [
                'quantity_priority' => intval($existing_order['quantity_priority']) + $qty_priority,
                'quantity_ringsets' => intval($existing_order['quantity_ringsets']) + $qty_ringsets,
                'quantity_total' => intval($existing_order['quantity_total']) + $qty_order + $qty_priority,
                // ... more fields ...
            ];
            $update_data[] = $data;
        } else {
            // Lines 185-192: Create new order
            $i['quantity_total'] = $qty_order + $qty_priority;
            $i['destination_id'] = $destination_id;
            $insert_data[] = $i;
        }
    }
}

// Lines 196-197: Execute database operations
if (count($update_data) > 0) $this->model->update_batch_on_order($update_data);
if (count($insert_data) > 0) $this->model->add_batch_on_order($insert_data);
```

### `save()` - Update Orders and Record Shipments
**Lines 202-302**: [`Restock.php:202-302`](../../application/controllers/Restock.php#L202)

#### Main Processing Loop
```php
// Lines 214-270: Process each order update
foreach ($orders_data as $order) {
    $ix = array_search($order['id'], $order_ids);
    $order_id = intval($order['id']);
    $new_ship_quantity_samples = intval($restock_updates[$ix]['ship_quantity_samples']);
    $new_ship_quantity_ringset = intval($restock_updates[$ix]['ship_quantity_ringset']);
    $new_status = intval($restock_updates[$ix]['restock_status_id']);
    
    // Lines 221-224: Check for backorder status (triggers email)
    if (in_array($new_status, $this->_BACKORDER_ID)) {
        $order_ids_backorder[] = $order['id'];
    }
    
    // Lines 226-244: Record shipments
    if ($new_ship_quantity_samples > 0 || $new_ship_quantity_ringset > 0) {
        // Calculate pending quantities
        $qty_pending_samples = intval($order['quantity_total']) - intval($order['quantity_shipped']);
        $qty_pending_ringset = intval($order['quantity_ringsets']) - intval($order['quantity_ringsets_shipped']);
        
        // Lines 231-236: Check if order is now complete
        if ($new_ship_quantity_samples >= $qty_pending_samples && 
            $new_ship_quantity_ringset >= $qty_pending_ringset) {
            $order_ids_completed[] = $order_id;
        }
        
        // Lines 237-243: Add shipment record
        $new_shipments[] = [
            'order_id' => $order_id,
            'quantity' => $new_ship_quantity_samples,
            'quantity_ringsets' => $new_ship_quantity_ringset,
            'user_id' => $this->data['user_id']
        ];
    }
    
    // Lines 246-269: Handle status changes
    if ($new_status != intval($order['restock_status_id'])) {
        if (in_array($new_status, $this->_CANCEL_ID)) {
            // Lines 252-259: Cancel order - update status and move to completed
            $order_updates[] = [...];
            $order_ids_completed[] = $order_id;
        } else {
            // Lines 261-268: Regular status change
            $order_updates[] = [...];
        }
    }
}
```

#### Database Operations
```php
// Lines 282-295: Execute all database operations
if (count($new_shipments) > 0) {
    $this->model->add_restock_shipments($new_shipments);
}
if (count($order_updates) > 0) {
    $this->model->update_orders($order_updates);
}
if (count($order_ids_completed) > 0) {
    $this->model->move_completed_orders($order_ids_completed, $this->data['user_id']);
}
if (count($order_ids_backorder) > 0) {
    $this->send_backorders_email($order_ids_backorder);
}
```

## Email Notification System

### `send_backorders_email()` - Email Alert for Backorders
**Lines 304-376**: [`Restock.php:304-376`](../../application/controllers/Restock.php#L304)

#### Data Collection
```php
// Lines 308-313: Get detailed order information
$orders_data = $this->model->get_restocks([
    'ids' => $order_ids_backorder,
    'include_items_description' => true,
    'only_completed' => false,
    'include_stock' => true
]);
```

#### HTML Table Generation
```php
// Lines 316-325: Define table columns
$_COL_NAMES = [
    'product_name' => 'Product Name',
    'code' => 'Code',
    'color' => 'Color',
    'quantity_total' => 'Total Quantity',
    'quantity_priority' => 'Priority Quantity',
    'quantity_ringsets' => 'Ringsets Quantity',
    'destination' => 'Destination',
    'sales_link' => 'Stock Link'
];

// Lines 331-338: Build table rows
foreach ($orders_data as $d) {
    // Line 333: Create sales system link
    $d['sales_link'] = !is_null($d['sales_id']) ? 
        "<a href='https://sales.opuzen-service.com/index.php/bolt/index/" . $d['sales_id'] . "' target='_blank'>link</a>" 
        : 'N/A';
    
    // Add row to table
    $this->table->add_row($data);
}
```

#### Email Configuration and Sending
```php
// Lines 361-375: Configure and send email
$mail_to = 'development@opuzen.com' .(ENVIRONMENT === 'prod' ? ', matt@opuzen.com' : '');
$this->email->message($message_content);
$this->email->to($mail_to);

// Lines 364-367: Add test environment warning
if($environment !== "prod"){
    $isTest = "TEST from ". strtoupper($environment) . " IGNORE. <br />";
}

$this->email->subject("Sampling Restock Alert: New items are on backorder");
$this->email->from("From: Opuzen.com <development@opuzen.com>\r\n");

if ($this->email->send()) {
    echo 'Email sent successfully!';
} else {
    echo $this->email->print_debugger();  // Show debug info on failure
}
```

## View Files

### Main Interface: `application/views/restock/list.php`

#### Filter Form Section
**Lines 47-81**: [`list.php:47-81`](../../application/views/restock/list.php#L47)

```php
<form id="restock_filters">
    <div class="row py-1">
        <!-- History toggle: Pendings vs Completed -->
        <div class="col">
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                <label class="btn btn-secondary <?php echo (!$start_completed ? "active" : "")?> w-50">
                    <input type="radio" name="restock_filter_order_history" value="pendings" ...>
                </label>
                <label class="btn btn-secondary <?php echo ($start_completed ? "active" : "")?> w-50">
                    <input type="radio" name="restock_filter_order_history" value="completed" ...>
                </label>
            </div>
        </div>
        
        <!-- Date range filters -->
        <div class="col">
            Date From: <input class='form-control' type="date" name="restock_filter_from" ...>
        </div>
        <div class="col">
            Date To: <input class='form-control' type="date" name="restock_filter_to" ...>
        </div>
        
        <!-- Destination dropdown -->
        <div class="col">
            Destination <?php echo $restock_filter_destinations;?>
        </div>
    </div>
</form>
```

#### DataTables Configuration
**Lines 138-189**: [`list.php:138-189`](../../application/views/restock/list.php#L138)

```javascript
this_table = $(mtable_id)
    .DataTable({
        'serverSide': false,        // Client-side processing for speed
        "processing": false,        // No loading indicator
        
        // AJAX configuration
        "ajax": {
            "url": '<?php echo $ajaxUrl?>',     // Points to restock/get
            "type": "POST",
            "dataSrc": "tableData",
            "data": build_ajax_data             // Function to build POST data
        },
        
        "paging": false,            // Show all results
        "columns": [
            {"title": "Item ID", "data": "item_id", "visible": false},
            {"title": "Date Req", "data": "date_add"},
            {"title": "Product Name", "data": "product_name"},
            {"title": "Item #", "data": "code"},
            {"title": "Color", "data": "color"},
            // ... more columns ...
            
            // Custom quantity rendering with styling
            {
                "title": "Pending Total", 
                "render": function (data, type, row, meta) {
                    let qty = parseInt(row.quantity_total) - parseInt(row.quantity_shipped);
                    return '<span class="'+(qty > 0 ? '' : 'qty-null')+'"><i class="fas fa-tag"></i> ' + qty + '</span>';
                }
            }
        ]
    });
```

## JavaScript Integration

### DataTables Buttons: `assets/js/init_datatables.js`

#### Add Restock Button
**Lines 651-656**: [`init_datatables.js:651-656`](../../assets/js/init_datatables.js#L651)

```javascript
{
    text: '<i class="far fa-layer-group"></i> Add Restock',
    className: 'btn-dt-add-list btn btn-outline-primary' + (!hasEditPermission ? ' hide ' : ''),
    enabled: false,      // Disabled until rows selected
    action: function (e, dt, node, config) {
        $('#restock_modal').modal('show');
    }
}
```

## CSS Styling

### Status and Quantity Indicators
**Lines 39-45**: [`list.php:39-45`](../../application/views/restock/list.php#L39)

```css
<style>
.status_bg_BACKORDER { background: orange; color: white; }
.status_bg_COMPLETED { background: green; color: white; }
.qty-priority { color: red; font-weight: bold; }
.qty-null { color: #ccc; }
</style>
```

## Model Integration

### Key Model Methods Called

```php
// Get orders with filters
$this->model->get_restocks($filters)

// Check for duplicate orders  
$this->model->get_duplicates($item_ids, $item_sizes, $destination_id)

// Batch insert new orders
$this->model->add_batch_on_order($insert_data)

// Batch update existing orders
$this->model->update_batch_on_order($update_data)

// Record shipments
$this->model->add_restock_shipments($new_shipments)

// Update order statuses
$this->model->update_orders($order_updates)

// Move completed orders to archive
$this->model->move_completed_orders($order_ids_completed, $user_id)
```

## Configuration Constants

### Email Recipients by Environment
```php
// Production recipients
$mail_to = 'development@opuzen.com, matt@opuzen.com';

// Development recipients  
$mail_to = 'development@opuzen.com';
```

### Status ID Constants
```php
var $_BACKORDER_ID = [7];   // Triggers email notifications
var $_COMPLETED_ID = [5];   // Orders move to completed table  
var $_CANCEL_ID = [6];      // Orders move to completed table
```

### Sample Size Options
```html
<select name="dropdown_restock_sizes_all">
    <option value="6">6x6</option>      <!-- 6 inch samples -->
    <option value="12">12x12</option>   <!-- 12 inch samples -->
    <option value="18">18x18</option>   <!-- 18 inch samples -->
</select>
```
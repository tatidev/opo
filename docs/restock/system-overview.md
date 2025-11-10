# Restock System Overview

## System Architecture

The Restock system follows the MVC pattern with these key components:

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   View Layer    │    │  Controller      │    │  Model Layer    │
│                 │    │                  │    │                 │
│ • list.php      │◄──►│ Restock.php      │◄──►│ Restock_model   │
│ • DataTables    │    │ • index()        │    │ • Database ops  │
│ • Filters       │    │ • add()          │    │ • Query methods │
│ • Modals        │    │ • save()         │    │ • Validation    │
└─────────────────┘    │ • get()          │    └─────────────────┘
                       │ • Email alerts   │
                       └──────────────────┘
```

## Core Workflow

### 1. Order Creation Process

**File**: [`application/controllers/Restock.php:100-200`](../../application/controllers/Restock.php#L100)

```php
public function add() {
    // 1. Extract destination and items data
    $destination_id = intval($this->input->post('destination'));
    
    // 2. Check for duplicates (unless user confirmed)
    if (!$OK_to_proceed_for_duplicates) {
        $duplicates_data = $this->model->get_duplicates($item_ids, $item_sizes, $destination_id);
        if (count($duplicates_data) > 0) {
            // Show confirmation table to user
            return; 
        }
    }
    
    // 3. Process items for insert/update
    foreach ($this->input->post('items') as $i) {
        if ($is_duplicate) {
            $update_data[] = $combined_quantities;
        } else {
            $insert_data[] = $new_order_data;
        }
    }
    
    // 4. Execute database operations
    if (count($update_data) > 0) $this->model->update_batch_on_order($update_data);
    if (count($insert_data) > 0) $this->model->add_batch_on_order($insert_data);
}
```

**Simple explanation**: When creating orders, the system checks for duplicates, lets users combine orders if needed, then either creates new orders or adds to existing ones.

### 2. Status Management System

**File**: [`application/controllers/Restock.php:202-302`](../../application/controllers/Restock.php#L202)

Status constants defined at class level:
```php
var $_BACKORDER_ID = [7];   // Items need to be purchased
var $_COMPLETED_ID = [5];   // Orders fully shipped  
var $_CANCEL_ID = [6];      // Orders cancelled
```

Status change processing:
```php
public function save() {
    foreach ($orders_data as $order) {
        $new_status = intval($restock_updates[$ix]['restock_status_id']);
        
        if (in_array($new_status, $this->_BACKORDER_ID)) {
            // Trigger email notification
            $order_ids_backorder[] = $order['id'];
        }
        
        if (in_array($new_status, $this->_CANCEL_ID)) {
            // Cancel and move to completed
            $order_ids_completed[] = $order_id;
        }
    }
}
```

**Simple explanation**: Different status changes trigger different actions - backorders send emails, cancellations move orders to completed table.

### 3. Automatic Completion Detection

**File**: [`application/controllers/Restock.php:54-57`](../../application/controllers/Restock.php#L54)

```php
$item['is_completed'] = (
    ($item["quantity_total"] == $item["quantity_shipped"]) &&
    ($item["quantity_ringsets"] == $item["quantity_ringsets_shipped"])
);
```

**File**: [`application/controllers/Restock.php:231-236`](../../application/controllers/Restock.php#L231)

```php
if ($new_ship_quantity_samples >= $qty_pending_samples && 
    $new_ship_quantity_ringset >= $qty_pending_ringset) {
    // Mark as completed and move to completed table
    $order_ids_completed[] = $order_id;
}
```

**Simple explanation**: When all requested samples have been shipped, the system automatically marks orders as completed.

## Data Flow Architecture

```
User Input → Duplicate Check → Order Creation/Update → Status Tracking → Shipment Recording → Auto-Completion → Historical Archive
     ↓              ↓                  ↓                    ↓                  ↓                ↓                    ↓
Filter Form    Check existing    Insert/Update      Status dropdown    Shipment form    Move to completed  Search archive
```

## Integration Points

### With Item Management System
- Orders reference `item_id` from items table
- Product and color information pulled from item/product tables
- Links to sales system for purchasing: [`Restock.php:333`](../../application/controllers/Restock.php#L333)

### With User System  
- All orders track `user_id` for creator and modifier
- User permissions control edit capabilities
- User default destinations: [`Restock.php:93`](../../application/controllers/Restock.php#L93)

### With Email System
- CodeIgniter email library integration: [`Restock.php:16`](../../application/controllers/Restock.php#L16)
- Environment-based recipient lists: [`Restock.php:361`](../../application/controllers/Restock.php#L361)
- HTML email templates with tables: [`Restock.php:340-350`](../../application/controllers/Restock.php#L340)

## Performance Considerations

### Database Optimization
- Separate tables for active vs completed orders
- Indexed lookups on `item_id`, `destination_id`, `status_id`
- Batch operations for multiple order updates

### UI Optimization  
- Server-side processing disabled for faster loading: [`list.php:147`](../../application/views/restock/list.php#L147)
- AJAX pagination and filtering
- Request cancellation to prevent multiple simultaneous requests: [`list.php:143`](../../application/views/restock/list.php#L143)

### Memory Management
- Processes orders in batches to avoid memory issues
- Limits duplicate detection to prevent excessive loading
- Streaming output for large result sets

## Security Considerations

### Access Control
- Permission-based editing controlled by `hasEditPermission`
- User ID validation for all order modifications
- Session-based authentication required

### Input Validation
- All numeric inputs cast to integers
- POST data sanitized through CodeIgniter input library
- SQL injection prevention through parameterized queries

### Data Integrity
- Database transactions ensure atomic operations
- Foreign key constraints maintain referential integrity
- Audit trail with user ID and timestamps on all changes
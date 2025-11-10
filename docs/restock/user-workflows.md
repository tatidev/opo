# User Workflows

## 1. Creating New Restock Orders

### Step-by-Step Process

**Starting Point**: User is viewing item list and wants to order samples

1. **Select Items**  
   - User selects rows in item DataTable
   - "Add Restock" button becomes enabled
   - **Code**: [`init_datatables.js:651-656`](../../assets/js/init_datatables.js#L651)

2. **Open Restock Modal**
   - Click "Add Restock" button
   - Modal opens with destination and size options
   - **View**: [`list.php:87-131`](../../application/views/restock/list.php#L87)

3. **Configure Order**
   - Select destination (warehouse/office)
   - Choose sample size (6x6, 12x12, 18x18)
   - Specify quantities for each item:
     - Regular samples
     - Priority samples (urgent)  
     - Ringsets (bound sample books)

4. **Submit Order**
   - Click "Save" to submit
   - **Controller**: [`Restock.php:add()`](../../application/controllers/Restock.php#L100)

### Duplicate Detection Flow

If system finds existing orders for same items:

```
Order Submission
      ↓
Duplicate Check
      ↓
   ┌─────────────────┐
   │ Duplicates      │ NO  →  Create New Orders
   │ Found?          │
   └─────────────────┘
           │ YES
           ↓
Display Confirmation Table
    • Show existing orders  
    • Ask user to confirm
           ↓
   ┌─────────────────┐
   │ User Confirms   │ NO  →  Cancel Operation
   │ Combine?        │
   └─────────────────┘
           │ YES
           ↓
Combine with Existing Orders
```

**Code**: [`Restock.php:107-156`](../../application/controllers/Restock.php#L107)

## 2. Managing Existing Orders

### Viewing Orders

**Filter Options** ([`list.php:47-81`](../../application/views/restock/list.php#L47)):
- **History**: Pending vs Completed orders
- **Date Range**: From/To date filters  
- **Destination**: Specific warehouse/office
- **Status**: Order status filter

### Updating Order Status

1. **Change Status Dropdown**
   - Each order row has status dropdown
   - **Generated in**: [`Restock.php:52-53`](../../application/controllers/Restock.php#L52)
   
2. **Available Statuses**:
   - **Pending** - Just created, awaiting processing
   - **Processing** - Being prepared for shipment
   - **Backorder** - Items not available, need to purchase
   - **Shipped** - Samples sent out
   - **Completed** - Order fully fulfilled
   - **Cancelled** - Order cancelled

3. **Status Change Effects**:
   ```php
   // Backorder status triggers email
   if (in_array($new_status, $this->_BACKORDER_ID)) {
       $order_ids_backorder[] = $order['id'];
   }
   
   // Cancelled orders move to completed table
   if (in_array($new_status, $this->_CANCEL_ID)) {
       $order_ids_completed[] = $order_id;
   }
   ```
   **Code**: [`Restock.php:221-268`](../../application/controllers/Restock.php#L221)

### Recording Shipments

1. **Edit Order**
   - Click on order row to edit
   - Modal opens with shipment form

2. **Enter Shipment Quantities**
   - Samples shipped
   - Ringsets shipped
   - **Validation**: Can't ship more than ordered

3. **Automatic Completion**
   - If all quantities fulfilled, order auto-completes
   - **Logic**: [`Restock.php:231-236`](../../application/controllers/Restock.php#L231)

## 3. Email Notifications

### Backorder Alerts

**Trigger**: When order status changed to "Backorder"
**Recipients**: 
- Development team (always)
- Management (production only)
- **Code**: [`Restock.php:361`](../../application/controllers/Restock.php#L361)

**Email Content**:
```
Subject: Sampling Restock Alert: New items are on backorder

This email is to notify that the following items are not available 
at Sampling. They are needed and may need to be ordered.

[TABLE WITH:]
- Product Name
- Item Code  
- Color
- Quantities needed
- Destination
- Link to sales system

Thanks,
Opuzen Admin
```

## 4. Common User Scenarios

### Scenario A: Rush Order for Customer Meeting

1. Sales rep needs urgent samples for tomorrow's meeting
2. Selects items from product list
3. Creates restock order with high priority quantities
4. Warehouse staff sees priority quantities highlighted
5. Priority samples shipped first
6. System tracks regular vs priority separately

### Scenario B: Bulk Sample Restocking

1. Warehouse manager reviews low stock
2. Creates large restock orders for multiple items
3. System detects some items already have pending orders
4. Manager combines new quantities with existing orders
5. Avoids duplicate work and over-ordering

### Scenario C: Backorder Processing

1. Warehouse can't fulfill order (out of stock)
2. Changes order status to "Backorder"
3. System automatically emails purchasing team
4. Email includes links to supplier ordering system
5. Purchasing orders materials from supplier
6. When received, order status updated to "Ready to Ship"

## 5. Permission-Based Workflows

### Read-Only Users
- Can view all orders and filters
- Cannot create new orders
- Cannot modify existing orders
- **Control**: `$hasEditPermission` variable

### Edit Users  
- Full access to create and modify orders
- Can change statuses and record shipments
- Can see all destinations and statuses
- **Check**: [`Restock.php:19`](../../application/controllers/Restock.php#L19)

## 6. Data Validation Rules

### Order Creation
- Destination must be selected (not '0')
- At least one quantity must be > 0
- Item IDs must be valid existing items
- User must be logged in with valid session

### Shipment Recording
- Shipped quantity ≤ ordered quantity  
- Can't ship negative quantities
- Must have edit permissions
- **Validation**: Happens in controller before database

### Status Changes
- Only valid status IDs accepted
- Status transitions follow business rules
- User and timestamp recorded for audit trail

## 7. User Interface Guide

### Main Restock List View

**Filter Section** ([`list.php:47-81`](../../application/views/restock/list.php#L47)):
```
┌─────────────────────────────────────────────────────────────┐
│ History: [Pendings] [Completed]  Date From: [____]          │
│ Date To: [____]  Destination: [All ▼]  Status: [All ▼]     │
│                                            [Refresh Button] │
└─────────────────────────────────────────────────────────────┘
```

**Order Table Columns**:
- Date Requested / Date Modified
- Product Name / Item Code / Color
- Destination / Requested By
- Sample Size
- Pending Quantities (Total, Priority, Ringsets)
- Status Dropdown
- Action Buttons

### Status Visual Indicators

**CSS Classes** ([`list.php:39-45`](../../application/views/restock/list.php#L39)):
```css
.status_bg_BACKORDER { background: orange; color: white; }
.status_bg_COMPLETED { background: green; color: white; }
.qty-priority { color: red; font-weight: bold; }
.qty-null { color: #ccc; }
```

## 8. Troubleshooting User Issues

### "Add Restock Button Not Working"
**Symptoms**: Button remains disabled when items selected
**Causes**:
- Items not properly selected in DataTable
- User lacks edit permissions
- JavaScript errors in browser console

**Solution**: 
1. Check browser console for JavaScript errors
2. Verify user has `hasEditPermission` set to true
3. Ensure items are selected (highlighted rows)
4. **Code Reference**: [`init_datatables.js:652`](../../assets/js/init_datatables.js#L652)

### "Duplicate Detection Not Working"
**Symptoms**: Orders created without checking duplicates
**Causes**:
- AJAX request failing
- Database connection issues
- Incorrect item/destination data

**Solution**:
1. Check network tab for failed AJAX requests
2. Verify database connectivity
3. Check server logs for PHP errors
4. **Debug Code**: [`Restock.php:107-116`](../../application/controllers/Restock.php#L107)

### "Email Not Received for Backorders"
**Symptoms**: Status changed to backorder but no email sent
**Causes**:
- Email configuration issues
- Wrong environment settings
- SMTP server problems

**Solution**:
1. Check CodeIgniter email configuration
2. Verify environment variable `APP_ENV`
3. Test SMTP connectivity
4. **Debug Code**: [`Restock.php:371-375`](../../application/controllers/Restock.php#L371)

### "Orders Not Completing Automatically"
**Symptoms**: Fully shipped orders remain in pending status
**Causes**:
- Quantity calculation errors
- Missing shipment records
- Status update logic issues

**Solution**:
1. Verify quantities: ordered vs shipped
2. Check shipment records in database
3. Debug completion logic
4. **Logic Code**: [`Restock.php:231-236`](../../application/controllers/Restock.php#L231)

## 9. Advanced User Features

### Bulk Operations
- Select multiple orders for batch status updates
- Export order data to Excel/CSV
- Print pick lists for warehouse staff

### Historical Reporting
- View completed orders by date range
- Track user activity and order patterns
- Generate monthly restock reports

### Integration Features
- Link to item management system
- Connect with inventory tracking
- Integration with sales ordering system
# Email Notifications System

## Overview

The Restock system automatically sends email notifications when items are marked as "backorder" status, alerting purchasing staff that materials need to be ordered from suppliers.

## Email Triggers

### Backorder Status Change
**When**: Order status changed to backorder (ID: 7)
**Code**: [`Restock.php:221-224`](../../application/controllers/Restock.php#L221)

```php
if (in_array($new_status, $this->_BACKORDER_ID)) {
    // Send notification email that some items need to be purchased
    $order_ids_backorder[] = $order['id'];
}
```

**Simple explanation**: Whenever someone changes an order's status to "backorder", the system automatically adds it to a list of orders that will trigger an email notification.

## Email Configuration

### Recipients
**Code**: [`Restock.php:361`](../../application/controllers/Restock.php#L361)

```php
$mail_to = 'development@opuzen.com' .(ENVIRONMENT === 'prod' ? ', matt@opuzen.com' : '');
```

**Recipients by Environment**:
- **Development/Testing**: `development@opuzen.com` only
- **Production**: `development@opuzen.com, matt@opuzen.com`

### Email Settings
**Library**: CodeIgniter Email Library
**From Address**: `development@opuzen.com`
**Subject**: `"Sampling Restock Alert: New items are on backorder"`
**Format**: HTML email with table layout

## Email Content Structure

### Email Template
**Code**: [`Restock.php:340-350`](../../application/controllers/Restock.php#L340)

```html
<html><body><div>
    This email is to notify that the following items are not available at Sampling. 
    They are needed and may need to be ordered.
    <br><br>
    [HTML TABLE OF BACKORDERED ITEMS]
    <br><br>
    <em>Thanks,<br>
        Opuzen Admin<br><br>
        <small>-DO NOT RESPOND. This message was sent automatically by the system-</small>
    </em>
</div></body></html>
```

### Data Table Columns
**Code**: [`Restock.php:316-325`](../../application/controllers/Restock.php#L316)

| Column | Database Field | Purpose |
|--------|----------------|---------|
| Product Name | `product_name` | Fabric product name |
| Code | `code` | Item code/SKU |
| Color | `color` | Item color name |
| Total Quantity | `quantity_total` | Total samples needed |
| Priority Quantity | `quantity_priority` | Urgent samples needed |
| Ringsets Quantity | `quantity_ringsets` | Ring-bound samples needed |
| Destination | `destination` | Where samples should be sent |
| Stock Link | `sales_link` | Link to sales system for ordering |

### Sales System Links
**Code**: [`Restock.php:333`](../../application/controllers/Restock.php#L333)

```php
$d['sales_link'] = !is_null($d['sales_id']) ? 
    "<a href='https://sales.opuzen-service.com/index.php/bolt/index/" . $d['sales_id'] . "' target='_blank'>link</a>" 
    : 'N/A';
```

**Simple explanation**: If an item has a sales system ID, the email includes a clickable link that takes purchasing staff directly to the ordering page for that item.

## Email Generation Process

### Step 1: Data Collection
**Code**: [`Restock.php:308-313`](../../application/controllers/Restock.php#L308)

```php
$orders_data = $this->model->get_restocks([
    'ids' => $order_ids_backorder,
    'include_items_description' => true,
    'only_completed' => false,
    'include_stock' => true
]);
```

**Simple explanation**: System fetches detailed information about all backordered items, including product descriptions and stock system links.

### Step 2: Table Generation
**Code**: [`Restock.php:326-338`](../../application/controllers/Restock.php#L326)

```php
$this->table->set_heading(array_values($_COL_NAMES));
$this->table->set_template([
    'table_open' => "<table width='100%' cellspacing='1' cellpadding='1' align='center' border='1'>"
]);

foreach ($orders_data as $d) {
    $data = [];
    foreach (array_keys($_COL_NAMES) as $sql_col_name) {
        $data[] = $d[$sql_col_name];
    }
    $this->table->add_row($data);
}
$table_html = $this->table->generate();
```

**Simple explanation**: Uses CodeIgniter's table library to create a professional-looking HTML table with borders and proper formatting for the email.

### Step 3: Email Composition and Sending
**Code**: [`Restock.php:362-375`](../../application/controllers/Restock.php#L362)

```php
$this->email->message($message_content);
$this->email->to($mail_to);
$isTest = '';
if($environment !== "prod"){
    $isTest = "TEST from ". strtoupper($environment) . " IGNORE. <br />";
}
$this->email->subject("Sampling Restock Alert: New items are on backorder");
$this->email->from("From: Opuzen.com <development@opuzen.com>\r\n");

if ($this->email->send()) {
    echo 'Email sent successfully!';
} else {
    echo $this->email->print_debugger();
}
```

**Simple explanation**: Sets up all email parameters and attempts to send. If sending fails, it shows debugging information to help troubleshoot the issue.

## Environment Handling

### Test Environment Indicators
**Code**: [`Restock.php:364-367`](../../application/controllers/Restock.php#L364)

```php
if($environment !== "prod"){
    $isTest = "TEST from ". strtoupper($environment) . " IGNORE. <br />";
}
```

**Purpose**: Adds warning text to emails sent from non-production environments to prevent confusion.

### Recipient Lists by Environment
- **Production (`prod`)**: Real recipients get notifications
- **Development/QA**: Only development team gets notifications
- **Local Development**: Typically no emails sent or sent to developer only

## Email Debugging

### Common Issues and Solutions

#### Email Not Sending
**Check**: Email configuration in CodeIgniter
```php
// In application/config/email.php
$config['protocol'] = 'smtp';  // or 'mail', 'sendmail'
$config['smtp_host'] = 'your-smtp-host';
$config['smtp_user'] = 'your-email@domain.com';
$config['smtp_pass'] = 'your-password';
```

#### Wrong Recipients
**Check**: Environment variable and recipient logic
```bash
# Verify environment
echo $APP_ENV

# Check recipient code
grep -n "mail_to" application/controllers/Restock.php
```

#### Malformed HTML Table
**Debug**: Test table generation separately
```php
// Add debugging before email send
var_dump($table_html);
file_put_contents('/tmp/email_debug.html', $message_content);
```

### Debug Mode
**Code**: [`Restock.php:204`](../../application/controllers/Restock.php#L204)

```php
$debug = !is_null($this->input->post('debug'));
```

**Usage**: Add `debug=1` to POST request to see debug output instead of sending email.

### Email Library Debugging
**Code**: [`Restock.php:374`](../../application/controllers/Restock.php#L374)

```php
echo $this->email->print_debugger();
```

**Purpose**: Shows detailed SMTP transaction log when email sending fails.

## Customizing Email Notifications

### Adding New Recipients
**Modify**: [`Restock.php:361`](../../application/controllers/Restock.php#L361)

```php
// Example: Add accounting team in production
$mail_to = 'development@opuzen.com' .
    (ENVIRONMENT === 'prod' ? ', matt@opuzen.com, accounting@opuzen.com' : '');
```

### Changing Email Template
**Modify**: [`Restock.php:340-350`](../../application/controllers/Restock.php#L340)

```php
// Example: Add company branding
$message_content = "
    <html><body>
        <div style='font-family: Arial, sans-serif;'>
            <img src='https://opuzen.com/logo.png' alt='Opuzen Logo' />
            <h2>Backorder Alert</h2>
            <p>The following items need to be ordered:</p>
            " . $table_html . "
        </div>
    </body></html>";
```

### Adding New Data Columns
**Modify**: [`Restock.php:316-325`](../../application/controllers/Restock.php#L316)

```php
$_COL_NAMES = [
    'product_name' => 'Product Name',
    'code' => 'Code',
    'color' => 'Color',
    // Add new column
    'vendor_name' => 'Supplier',
    'cost_roll' => 'Unit Cost',
    'quantity_total' => 'Total Quantity',
    // ... existing columns
];
```

## Integration with Other Systems

### Sales System Links
- Links point to `https://sales.opuzen-service.com/`
- Uses `sales_id` field from items table
- Allows direct ordering from suppliers
- **Code**: [`Restock.php:333`](../../application/controllers/Restock.php#L333)

### Inventory System
- Could be extended to check current stock levels
- Integration with warehouse management systems
- Real-time availability checking

### Purchasing System
- Email notifications could trigger automatic PO creation
- Integration with supplier ordering systems
- Purchase approval workflows

## Security Considerations

### Email Security
- Uses authenticated SMTP when configured
- Prevents email injection through input sanitization
- Environment-based recipient filtering

### Data Privacy
- Only internal staff receive notifications
- No customer data included in emails
- Links require authentication to access

### Access Control
- Only users with edit permissions can trigger backorders
- All actions logged with user ID and timestamp
- Audit trail maintained for compliance
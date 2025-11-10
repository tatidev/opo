# PHP 7.3 Specific Troubleshooting

## Error Symptoms After PHP 7.3 Upgrade

This document supplements the main troubleshooting guide with specific issues that appeared after upgrading to PHP 7.3.

## 🚨 Fatal Error Patterns

### "Fatal error: Uncaught TypeError: array_column() expects parameter 1 to be array"

**Location**: Lines 109-110, 206  
**Trigger**: When POST data is null or not an array
**Code causing issue**:
```php
$item_ids = array_column($this->input->post('items'), 'item_id');
$order_ids = array_column($restock_updates, 'id');
```

**User symptoms**:
- "Add Restock" button stops working
- Orders appear to submit but don't get created
- No error message shown to user
- AJAX requests return 500 status

**Log entries**:
```
PHP Fatal error: Uncaught TypeError: array_column() expects parameter 1 to be array, null given
```

### "Fatal error: Uncaught TypeError: Invalid argument supplied for foreach()"

**Location**: Lines 161, 214  
**Trigger**: When POST arrays are null
**Code causing issue**:
```php
foreach ($this->input->post('items') as $i) {
foreach ($orders_data as $order) {
```

**User symptoms**:
- Forms submit but processing fails
- No feedback to user about failure
- Database records not created/updated

**Log entries**:
```
PHP Fatal error: Invalid argument supplied for foreach()
```

### "Fatal error: sort() expects parameter 1 to be array"

**Location**: Line 150  
**Trigger**: During duplicate processing when POST data is null
**Code causing issue**:
```php
$duplicates_order_ids = $this->input->post('duplicate_order_ids');
sort($duplicates_order_ids);
```

**User symptoms**:
- Duplicate confirmation dialog breaks
- Orders get created without duplicate checking
- Possible over-ordering of samples

## ⚠️ Warning Patterns

### "Deprecated: Methods with the same name as their class will not be constructors"

**Location**: Lines 7-9  
**Code causing issue**:
```php
var $_BACKORDER_ID = [7];
var $_COMPLETED_ID = [5];
```

**Impact**: 
- Currently just warnings, but may become fatal in future PHP versions
- Properties may not have expected visibility

**Log entries**:
```
PHP Deprecated: The use of var is deprecated
```

### "Notice: Array to string conversion"  

**Location**: Line 142  
**Trigger**: Complex JSON encoding with array data
**Code causing issue**:
```php
echo json_encode(['response' => $this->table->generate() . form_hidden('duplicate_order_ids', 
    str_replace('"', "", json_encode($duplicates_order_ids)))]);
```

**User symptoms**:
- Duplicate confirmation dialog shows malformed data
- JavaScript errors in browser console
- Broken HTML in modal dialogs

## 🔍 Detection Methods

### Check PHP Error Logs
```bash
# Common log locations
tail -f /var/log/php7.3-fpm.log
tail -f /var/log/apache2/error.log
tail -f application/logs/log-*.php
```

### Browser Console Errors
Look for these JavaScript errors that result from PHP failures:
```javascript
Uncaught SyntaxError: Unexpected token < in JSON
TypeError: Cannot read property 'success' of undefined
```

### Test with Debug Mode
Add to any controller method:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$debug = true;
```

## 🛠️ Immediate Hotfixes

### Quick Fix for Array Operations
Add these checks before array operations:

```php
// Before line 109
$items_post = $this->input->post('items');
if (!is_array($items_post)) {
    echo json_encode(['success' => false, 'message' => 'Invalid items data']);
    return;
}
$item_ids = array_column($items_post, 'item_id');

// Before line 161  
$items_post = $this->input->post('items');
if (!is_array($items_post)) {
    echo json_encode(['success' => false, 'message' => 'Invalid items data']);
    return;
}
foreach ($items_post as $i) {

// Before line 150
$duplicates_order_ids = $this->input->post('duplicate_order_ids');
if (!is_array($duplicates_order_ids)) {
    $duplicates_order_ids = [];
}
sort($duplicates_order_ids);
```

### Quick Fix for Class Properties
```php
// Replace lines 7-9
private $_BACKORDER_ID = [7];
private $_COMPLETED_ID = [5]; 
private $_CANCEL_ID = [6];
```

### Quick Fix for Environment Variables
```php
// Replace line 307
$environment = getenv('APP_ENV') ?: 'development';

// Replace lines 365-367
if($environment !== "prod"){
    $env_display = $environment ?: 'UNKNOWN';
    $isTest = "TEST from ". strtoupper($env_display) . " IGNORE. <br />";
}
```

## 📊 Impact Timeline

Based on the code analysis, here's the likely sequence of failures after PHP 7.3 upgrade:

### Day 1: Immediate Failures
- Order creation stops working (array_column errors)
- Duplicate detection fails (sort errors)
- Status updates break (foreach errors)

### Week 1: Secondary Effects  
- Backorder emails stop sending
- Data inconsistencies appear
- User reports of missing orders

### Month 1: System Degradation
- Sample inventory becomes unreliable
- Manual workarounds implemented
- Business process disruption

## 🎯 Priority Testing After Fixes

### Critical Path Testing
1. **Order Creation**: Create orders with various item combinations
2. **Duplicate Handling**: Test duplicate detection with edge cases
3. **Status Updates**: Update order statuses and verify database changes
4. **Email Notifications**: Trigger backorder emails and verify delivery

### Edge Case Testing
1. **Empty POST Data**: Submit forms with missing data
2. **Malformed Data**: Submit forms with unexpected data types
3. **Large Datasets**: Test with many items/orders
4. **Environment Variables**: Test with missing/incorrect environment settings

### Browser Compatibility
1. **JavaScript Errors**: Check browser console for JSON parsing errors
2. **Modal Dialogs**: Verify duplicate confirmation dialogs work
3. **AJAX Responses**: Confirm all AJAX requests return valid JSON
4. **Form Validation**: Test client-side and server-side validation

## 📝 Code Review Checklist

Before deploying fixes, verify:

- [ ] All array operations have null checks
- [ ] All foreach loops validate input data
- [ ] Class properties use proper visibility modifiers
- [ ] Environment variables have fallback values
- [ ] JSON responses are properly formatted
- [ ] Error handling covers edge cases
- [ ] Database transactions are atomic
- [ ] User feedback is provided for all error cases

This PHP 7.3 compatibility analysis reveals why the Restock system began malfunctioning - the upgrade introduced stricter type checking and array handling that exposed previously hidden issues in the code.
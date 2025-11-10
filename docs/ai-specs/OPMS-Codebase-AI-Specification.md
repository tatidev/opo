# OPMS Codebase - AI Model Specification

**Version:** 1.0.0  
**Date:** October 9, 2025  
**Framework:** CodeIgniter 3.1.11  
**PHP Version:** 7.3+  
**Application:** OPMS (Opuzen Product Management System)

---

## 🎯 **EXECUTIVE SUMMARY**

OPMS is a **20+ year legacy textile/fabric product management system** built on Code Igniter 3 and PHP 7.3, managing ~6,600 products and ~41,000 item variations (colorlines) with complex relationships, specifications, and integrations.

### **Critical Context for AI Models**
- ✅ **Legacy System**: Respect existing patterns, never assume modern frameworks
- ✅ **Production System**: Handle with extreme care, always examine actual code
- ✅ **No Breaking Changes**: Regression protection is MANDATORY
- ✅ **Security First**: Follow OWASP principles, validate all input
- ✅ **Examine Before Acting**: NEVER guess - always read actual code

---

## 🏗️ **APPLICATION ARCHITECTURE**

### **CodeIgniter 3 MVC Structure**

```
application/
├── controllers/          # Request handlers, business logic coordination
│   ├── Product.php      # Product CRUD, specs, pricing
│   ├── Item.php         # Item/colorline CRUD
│   ├── Lists.php        # List management
│   ├── Reports.php      # Reporting and analytics
│   ├── cli/             # CLI scripts (migrations, batch jobs)
│   └── api/             # REST API endpoints
│
├── models/              # Data access layer, database queries
│   ├── Product_model.php
│   ├── Item_model.php
│   ├── RevisedQueries_model.php  # Optimized queries
│   └── Specs_model.php           # Specifications/lookups
│
├── views/               # HTML presentation layer
│   ├── product/         # Product forms and lists
│   ├── item/            # Item forms and lists
│   ├── reports/         # Report pages
│   └── _header.php      # Shared header/nav
│
├── libraries/           # Custom business logic classes
│   ├── FileUploadToS3.php    # S3 file management
│   ├── REST_Controller.php   # REST API base
│   └── Ion_auth.php          # Authentication
│
├── core/                # Framework extensions
│   ├── MY_Controller.php     # Base controller with auth
│   ├── MY_Model.php          # Base model with common methods
│   └── MY_Constants.php      # Application constants
│
└── config/              # Environment-specific configuration
    ├── localdev/        # Local development
    ├── tnd_dev/         # Developer environment
    ├── qa/              # QA environment
    └── prod/            # Production
```

---

## 🔑 **CRITICAL CONVENTIONS**

### **1. Base Controller Pattern** (`MY_Controller.php`)

**ALL controllers extend `MY_Controller`**:
```php
class Product extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->thisC = 'product';  // Controller identifier
        $this->load->model('Product_model', 'model');  // Standard model alias
        array_push($this->data['crumbs'], 'Products');  // Breadcrumbs
        $this->data['hasEditPermission'] = $this->hasPermission('product', 'edit');
    }
}
```

**Key Properties:**
- `$this->data` - Array passed to all views
- `$this->thisC` - Controller identifier for permissions
- `$this->model` - Primary model (standard alias)

**Authentication:**
- Ion Auth library handles authentication
- `$this->ion_auth->logged_in()` - Check if user logged in
- `$this->hasPermission($controller, $action)` - Permission checks
- Guest URLs defined in MY_Controller for public access

---

### **2. Data Array Pattern**

**CRITICAL**: All data passed to views via `$this->data` array:

```php
// Controller
$this->data['title'] = 'Product List';
$this->data['tableData'] = $products;
$this->data['hasEditPermission'] = true;

// Load view
$this->load->view('product/list', $this->data);

// In view, access as:
<?php echo $title; ?>  // NOT $this->data['title']
<?php foreach ($tableData as $row): ?>
```

**Standard Keys:**
- `$this->data['crumbs']` - Breadcrumb array
- `$this->data['library_head']` - CSS/JS for `<head>`
- `$this->data['library_foot']` - JS for before `</body>`
- `$this->data['user_id']` - Current user ID
- `$this->data['is_admin']` - Admin flag
- `$this->data['hasEditPermission']` - Permission flags

---

### **3. Product Type System**

**Three Product Types** (defined as constants in `MY_Constants.php`):

```php
define('Regular', 'R');      // Regular products (majority)
define('Digital', 'D');      // Digital/printed products
define('ScreenPrint', 'SP'); // Screen print products (deprecated)
```

**Product ID Format:**
- Passed as: `"{product_id}-{product_type}"` (e.g., "6530-R")
- Parsed with: `explode('-', $this->input->post('product_id'))`

```php
// Standard pattern in controllers
$post = explode('-', $this->input->post('product_id'));
$product_id = isset($post[0]) ? $post[0] : null;
$product_type = isset($post[1]) ? $post[1] : null;
```

---

### **4. Database Query Patterns**

**Use CodeIgniter Query Builder** (NOT raw SQL):

```php
// CORRECT - Query Builder with prepared statements
$this->db->select('p.id, p.name, p.width')
         ->from('T_PRODUCT p')
         ->join('T_PRODUCT_VENDOR pv', 'p.id = pv.product_id', 'left')
         ->where('p.archived', 'N')
         ->where('p.id', $product_id)  // Automatically escaped
         ->get()->result_array();

// WRONG - String concatenation
$sql = "SELECT * FROM T_PRODUCT WHERE id = " . $product_id;  // SQL injection!
```

**Mandatory WHERE Clauses:**
```php
// ALWAYS filter archived records
->where('p.archived', 'N')  // Products
->where('i.archived', 'N')  // Items
->where('v.active', 'Y')    // Vendors
->where('v.archived', 'N')  // Vendors
```

**Model Base Class Pattern:**
```php
class Product_model extends MY_Model
{
    protected $t;  // Primary table name
    
    function __construct()
    {
        parent::__construct();
        $this->model_table = array($this->t_product, $this->product_digital);
    }
}
```

---

### **5. Form Submission Pattern**

**CRITICAL**: Forms use change tracking with hidden fields:

```html
<!-- Product form example -->
<form id="frmProduct" action="<?php echo site_url('product/save_regular_product') ?>">
    <input type="hidden" name="change_product" value="0">
    <input type="hidden" name="change_showcase" value="0">
    
    <!-- When field changes, JavaScript sets change_product = '1' -->
    <input type="text" name="product_name" onchange="validator.edit('product_name', this)">
</form>
```

**JavaScript Change Tracking** (`form_validation.js`):
```javascript
var validator = {
    formID: null,
    
    edit: function(spec_name, data) {
        // When field changes, set appropriate change flag
        switch(spec_name) {
            case 'product_name':
                this.addHiddenInput('change_product');  // Sets value to '1'
                break;
        }
    }
}
```

**Controller Save Logic:**
```php
// Only save if change flag is set
if ($this->input->post('change_product') === '1') {
    $this->model->save_product($data, $product_id);
}
```

**Why This Pattern?**
- Prevents unnecessary database writes
- Tracks what changed for audit logging
- Allows conditional save logic

---

### **6. AJAX Response Patterns**

**TWO Different Response Formats** (CRITICAL):

```php
// Pattern 1: Item forms (used in item/form/view.php)
echo json_encode([
    'success' => true,
    'item' => $item_data,
    'message' => 'Item saved successfully'
]);

// Pattern 2: Other forms (used in form_validation.js)
echo json_encode([
    'status' => 'OK',
    'message' => 'Product saved successfully'
]);
```

**JavaScript must check BOTH**:
```javascript
success: function(data, msg) {
    if (data.status == 'OK' || data.success === true) {
        // Handle success
    }
}
```

---

### **7. DataTables Pattern**

**Server-Side DataTables** are used extensively:

**Controller Method:**
```php
public function get_products()
{
    $search = $this->input->post('search');
    if (strlen($search['value']) > 0) {
        $this->model->searchText = $search['value'];
        $list = $this->model->get_products_spec_view();
    }
    echo json_encode($this->return_datatables_data($list['arr'], $list));
}
```

**View Initialization:**
```javascript
var this_table = custom_datatables.items({
    table_id: '#items_table',
    serverSideUrl: getItemListUrl,
    isGeneralSearch: false
}, mysettings).search('');
```

**Table Stored in Global Variable:**
- `this_table` - Reference to DataTable instance
- Use `this_table.ajax.reload()` to refresh
- NOT accessed via selector after initialization

---

### **8. File Upload to S3 Pattern**

**Two-Stage Upload Process:**

**Stage 1: Upload to Temp Directory**
```php
// Controller action
public function uploadToTemp()
{
    $this->fileuploadtos3->uploadToTemp();
}
```

**Stage 2: Move to S3 and Update Database**
```php
// After form save, move from temp to final location
$this->fileuploadtos3->image_loc_destination_path = $final_path;
$this->fileuploadtos3->createS3fileObject();
```

**S3 URL Format:**
```
https://{bucket_name}.s3.{region}.amazonaws.com/{path}
Example: https://opuzen-web-assets-public.s3.us-west-1.amazonaws.com/showcase/images/R/6000/6530/big/36063.jpg
```

**File Paths:**
- Temp: `files/temp/{random}_{filename}`
- Final: `showcase/images/{type}/{1000s}/{product_id}/{size}/{item_id}.{ext}`

---

### **9. Environment Configuration**

**Multi-Environment Setup:**

```php
// index.php loads .env file
$dotenv = \Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
define('ENVIRONMENT', getenv('APP_ENV'));

// Config files per environment
application/config/
├── localdev/    # APP_ENV=localdev
├── tnd_dev/     # APP_ENV=tnd_dev
├── dev/         # APP_ENV=dev
├── qa/          # APP_ENV=qa
└── prod/        # APP_ENV=prod
```

**Environment-Specific Files:**
- `config.php` - Base URL, session settings
- `database.php` - Database credentials
- Autoloaded based on ENVIRONMENT constant

**CLI Considerations:**
- Must handle missing `$_SERVER` variables (HTTP_HOST, SERVER_NAME)
- Use `php_sapi_name() === 'cli'` checks
- Load .env explicitly for CLI context

---

### **10. Modal Pattern (Globalmodal)**

**Single Reusable Modal** for all forms:

```html
<!-- Modal container (in main template) -->
<div class="modal fade" id="globalmodal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>
```

**Opening Modal:**
```javascript
// Load form into modal
$('#globalmodal .modal-content').load(url, function() {
    $('#globalmodal').modal('show');
});
```

**Closing Modal:**
```javascript
$('.modal#globalmodal').modal('hide');
```

**Form Submit in Modal:**
- Form has own AJAX handler (see Item form example)
- On success, updates DataTable and closes modal
- NOT using form_validation.js (which is for non-modal forms)

---

## 📊 **DATA TYPES & CONVERSIONS**

### **CRITICAL: Data Type Awareness**

**CHAR(1) Boolean Fields:**
```php
// Database stores as CHAR(1)
SHOWCASE_PRODUCT.visible = 'Y' or 'N'  // NOT boolean
T_PRODUCT.archived = 'Y' or 'N'

// Comparison
if ($row['visible'] === 'Y') {  // CORRECT
if ($row['visible'] == true) {  // WRONG - 'Y' is truthy but not true
```

**TINYINT Boolean Fields:**
```php
// Database stores as TINYINT(1)
T_ITEM.web_vis = 1, 0, or NULL
T_ITEM.web_vis_toggle = 1 or 0

// Comparison (can use both)
if ($row['web_vis'] === 1) {    // CORRECT
if ($row['web_vis'] === '1') {  // ALSO CORRECT (POST data is string)
if ($row['web_vis'] == true) {  // WORKS but less explicit
```

**NULL Handling:**
```php
// Three-state fields (NULL has meaning)
web_vis = NULL    // Not yet calculated (triggers lazy calculation)
web_vis = 0       // Explicitly hidden
web_vis = 1       // Explicitly visible

// Always check for NULL first
if (is_null($row['web_vis'])) {
    // Calculate value
} elseif ($row['web_vis'] == 1) {
    // Is visible
}
```

**Checkbox Submission:**
```php
// Checkboxes NOT submitted when unchecked
$value = $this->input->post('checkbox_name');
if (is_null($value)) {
    $value = '0';  // Default to unchecked
} else {
    $value = '1';  // Was checked
}
```

---

## 🔄 **COMMON QUERY PATTERNS**

### **Pattern 1: Get Product with All Details**

```php
// In Product_model.php
$this->db->select('
    p.id,
    p.name,
    p.width,
    p.archived,
    sp.visible as parent_product_visibility,
    sp.pic_big_url as beauty_shot
')
->from('T_PRODUCT p')
->join('SHOWCASE_PRODUCT sp', 'p.id = sp.product_id', 'left')  // LEFT JOIN!
->join('T_PRODUCT_VENDOR pv', 'p.id = pv.product_id', 'left')
->where('p.id', $product_id)
->where('p.archived', 'N');  // MANDATORY filter
```

**Key Points:**
- Use LEFT JOIN for optional relationships (SHOWCASE_PRODUCT may not exist)
- Always filter `archived = 'N'`
- Alias joined tables (p, sp, pv)
- Use descriptive aliases for parent data (`parent_product_visibility`)

---

### **Pattern 2: Get Items with Parent and Colors**

```php
// In RevisedQueries_model.php
$this->db->select('
    i.id as item_id,
    i.code,
    i.web_vis,
    ps.name as status,
    sp.visible as parent_product_visibility,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", ") as color_name
')
->from('T_ITEM i')
->join('P_PRODUCT_STATUS ps', 'i.status_id = ps.id', 'left')
->join('SHOWCASE_PRODUCT sp', 'i.product_id = sp.product_id', 'left')
->join('T_ITEM_COLOR ic', 'i.id = ic.item_id', 'left')
->join('P_COLOR c', 'ic.color_id = c.id', 'left')
->where('i.product_id', $product_id)
->where('i.archived', 'N')
->group_by('i.id');
```

**Key Points:**
- GROUP_CONCAT for many-to-many relationships (colors)
- Consistent alias usage (`parent_product_visibility`)
- Status from lookup table (P_PRODUCT_STATUS.name)
- Always use LEFT JOIN for optional relationships

---

### **Pattern 3: Batch Updates**

```php
// Prepare array of updates
$items_to_update = [];
foreach ($items as $item) {
    $items_to_update[] = [
        'id' => $item['item_id'],
        'web_vis' => $calculated_value,
        'date_modif' => date('Y-m-d H:i:s')
    ];
}

// Single query updates all
if (!empty($items_to_update)) {
    $this->db->update_batch('T_ITEM', $items_to_update, 'id');
}
```

**When to Use:**
- Processing multiple records
- Lazy calculation updates
- Migration scripts
- Performance optimization

---

## 🎨 **FORM PATTERNS**

### **Pattern 1: Standalone Report Pages**

**Structure** (reports/web_visibility_dashboard.php):
```php
<html>
<head>
    <?php echo asset_links($library_head) ?>
    <title><?php echo $title ?></title>
    <style>/* page-specific styles */</style>
</head>
<body class='container-fluid'>
    <!-- Page content -->
    <?php echo asset_links($library_foot) ?>
</body>
</html>
```

**Controller:**
```php
function web_visibility_dashboard()
{
    $this->load_reports_libraries();  // Loads jQuery, Bootstrap, etc.
    $this->data['title'] = 'Dashboard';
    $this->data['metrics'] = $this->get_metrics();
    $this->load->view('reports/web_visibility_dashboard', $this->data);
}
```

---

### **Pattern 2: Modal Forms**

**Structure** (item/form/view.php):
```php
<form id='frmItem' action='<?php echo site_url('item/save_item') ?>'>
    <input type='hidden' name='item_id' value='<?php echo $item_id ?>'>
    <input type='hidden' name='change_item' value='1'>
    <!-- Form fields -->
</form>

<script>
$('form#frmItem').on('click', '.btnSaveItem', function() {
    $.ajax({
        method: "POST",
        url: $('#frmItem').attr('action'),
        data: $('#frmItem').serialize(),
        success: function(data) {
            if (data.success === true) {
                // Update DataTable
                update_item_in_view(data.item, this_table);
                $('.modal#globalmodal').modal('hide');
                
                // Reload DataTable for fresh data
                this_table.ajax.reload(null, false);
            }
        }
    });
});
</script>
```

**Key Points:**
- Form has OWN AJAX handler (not using form_validation.js)
- Updates single row with `update_item_in_view()`
- Then reloads entire table for fresh calculated values
- Closes modal on success

---

## 🔐 **SECURITY PATTERNS**

### **Input Sanitization**

```php
// ALWAYS use input class (applies XSS filter)
$name = $this->input->post('product_name');  // CORRECT
$name = $_POST['product_name'];              // WRONG - no sanitization

// For GET requests
$id = $this->input->get('id');

// For array data
$colors = $this->input->post('color_ids');
if (is_array($colors)) {
    // Safe to use
}
```

### **Query Security**

```php
// Query Builder automatically escapes
$this->db->where('id', $user_input);  // SAFE

// For complex queries with binding
$sql = "SELECT * FROM T_PRODUCT WHERE id = ? AND type = ?";
$this->db->query($sql, array($id, $type));  // SAFE

// NEVER concatenate
$sql = "SELECT * FROM T_PRODUCT WHERE id = $id";  // DANGER!
```

### **Output Escaping**

```php
// In views, escape output
<?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?>

// Or use CI helper
<?php echo html_escape($product_name); ?>
```

---

## 📁 **FILE ORGANIZATION PATTERNS**

### **View Organization**

```
views/
├── {controller}/          # Views for specific controller
│   ├── form/             # Forms subdirectory
│   │   └── view.php      # Standard form view
│   └── list.php          # List view
│
├── _header.php           # Shared header (underscore prefix)
├── _footer.php           # Shared footer
└── _header_menu.php      # Shared navigation
```

### **Model Naming**

```php
// Model filename: Product_model.php
class Product_model extends MY_Model

// Loaded in controller with alias
$this->load->model('Product_model', 'model');

// Used as
$this->model->get_product($id);
```

---

## 🎯 **COMMON WORKFLOWS**

### **Workflow 1: Product Edit Flow**

1. **User clicks edit** → Triggers AJAX to load form
2. **Controller loads data** → `Product::edit_product()`
3. **View renders in modal** → `product/form/form_regular.php`
4. **User edits fields** → JavaScript tracks changes
5. **User clicks save** → Form submits via form_validation.js
6. **Controller validates** → Checks `change_product === '1'`
7. **Model saves data** → `Product_model::save_product()`
8. **Cascade updates** → Child items recalculate if needed
9. **Response sent** → `{status: 'OK', ...}`
10. **View reloads** → Shows updated data

---

### **Workflow 2: Item Edit Flow**

1. **User clicks edit** → Opens modal with item form
2. **Form loads** → `item/form/view.php` in #globalmodal
3. **User edits** → Changes status, web visibility, etc.
4. **User clicks save** → `.btnSaveItem` triggers AJAX
5. **AJAX submits** → Own handler in view (NOT form_validation.js)
6. **Controller saves** → `Item::save_item()`
7. **Validation** → Checks product_id not NULL
8. **Recalculation** → web_vis calculated if in auto mode
9. **Response** → `{success: true, item: {...}}`
10. **Modal closes** → DataTable reloads with fresh data

---

### **Workflow 3: Lazy Calculation**

1. **User loads item list** → `Item::index()`
2. **Query finds NULL values** → `WHERE web_vis IS NULL`
3. **Loop calculates** → For each NULL item
4. **Batch update** → `$this->db->update_batch()`
5. **Display shows values** → From database or calculated
6. **Next load is fast** → Values cached in database

---

## 🗂️ **KEY TABLES & RELATIONSHIPS**

### **Core Entity Relationships**

```
T_PRODUCT (6,599 records)
    ├─→ T_ITEM (41,408 records)
    │   ├─→ T_ITEM_COLOR (many-to-many)
    │   │   └─→ P_COLOR (lookup)
    │   └─→ SHOWCASE_ITEM (item images)
    │
    ├─→ SHOWCASE_PRODUCT (product images/visibility)
    ├─→ T_PRODUCT_VENDOR (one vendor per product)
    │   └─→ Z_VENDOR (vendor master)
    │
    └─→ T_PRODUCT_VARIOUS (extended attributes)
```

**Table Prefixes:**
- `T_*` - Transactional/business tables
- `P_*` - Parameter/lookup tables
- `Z_*` - Vendor/contact tables
- `SHOWCASE_*` - Web display tables
- `S_HISTORY_*` - History tracking
- `RESTOCK_*` - Restock management

---

## 🔧 **JAVASCRIPT PATTERNS**

### **Common JavaScript Files**

```
assets/js/
├── commons.js           # Shared utilities, AJAX view loader
├── form_validation.js   # Form change tracking, validation
├── init_datatables.js   # DataTables initialization
└── jquery.history.js    # Browser history management
```

### **AJAX View Loading**

```javascript
// Navigate to new view
function get_ajax_view(given_url, given_data) {
    // Checks if changes need saving
    // Loads content via AJAX
    // Updates browser history
    load_content_view({url: given_url, data: given_data});
}

// Standard pattern for navigation
get_ajax_view(site_url + 'item/edit_item', {
    product_id: product_id,
    item_id: item_id
});
```

---

## 🎨 **WEB VISIBILITY IMPLEMENTATION**

### **Product Level (Parent)**

**Database:**
- Table: `SHOWCASE_PRODUCT`
- Column: `visible` CHAR(1) - 'Y' or 'N'
- Requirement: `pic_big_url` IS NOT NULL (beauty shot)

**Business Rule:**
```
Product Visible = Has Beauty Shot AND User Checked Checkbox
```

**Form:** `product/form/form_regular.php` lines 698-713
```php
$has_beauty_shot = !empty($info['pic_big_url']);
$checkbox_disabled = !$has_beauty_shot ? 'disabled' : '';
$checkbox_checked = ($info['showcase_visible'] === 'Y' && $has_beauty_shot) ? 'checked' : '';
```

**Controller:** `Product::save_regular_product()`
```php
$showcase_visible = $this->input->post('showcase_visible');
if (is_null($showcase_visible)) {
    $showcase_visible = '0';  // Unchecked
}

// Save to SHOWCASE_PRODUCT
$this->model->save_showcase_basic([
    'visible' => ($showcase_visible === '1' ? 'Y' : 'N')
], $product_id);

// CASCADE: Update all child items
$this->update_child_items_web_visibility($product_id);
```

---

### **Item Level (Child)**

**Database:**
- Table: `T_ITEM`
- Column: `web_vis` TINYINT(1) - 1, 0, or NULL
- Column: `web_vis_toggle` TINYINT(1) - 0=auto, 1=manual

**Business Rule:**
```
Item Visible (Auto Mode) = Parent Visible AND Valid Status AND Has Images

Valid Statuses: RUN, LTDQTY, RKFISH
Has Images: pic_big_url OR pic_hd_url in SHOWCASE_ITEM
```

**Form:** `item/form/view.php` lines 592-689
```php
$is_manual_override = ($info['web_vis_toggle'] === '1');

if ($is_manual_override) {
    // Use stored value
    $is_checked = ($info['web_vis'] === '1');
} else {
    // Calculate based on rules
    $has_item_images = (!empty($info['pic_big_url']) || !empty($info['pic_hd_url']));
    $has_valid_status = in_array($info['status'], ['RUN', 'LTDQTY', 'RKFISH']);
    $parent_has_beauty_shot = ($info['parent_product_visibility'] == "Y");
    
    $is_checked = ($has_item_images && $has_valid_status && $parent_has_beauty_shot);
}
```

**Controller:** `Item::save_item()`
```php
$web_vis_toggle = (is_null($this->input->post('web_vis_toggle')) ? 0 : 1);

if ($web_vis_toggle == 1) {
    // Manual mode - use form value
    $web_vis = (is_null($this->input->post('web_vis')) ? 0 : 1);
} else {
    // Auto mode - recalculate
    $web_vis = $this->calculate_web_visibility($product_id, $status, $item_id);
}

// Save to database
$data = array('web_vis' => $web_vis, 'web_vis_toggle' => $web_vis_toggle);
$this->model->save_item($data, $item_id);
```

---

## 📝 **NAMING CONVENTIONS**

### **Database Columns**

```
Timestamps:
- date_add      # Creation timestamp
- date_modif    # Last modified (ON UPDATE CURRENT_TIMESTAMP)

Boolean-like:
- archived      # CHAR(1): 'Y' = archived, 'N' = active
- active        # CHAR(1): 'Y' = active, 'N' = inactive
- visible       # CHAR(1): 'Y' = visible, 'N' = hidden
- web_vis       # TINYINT(1): 1 = visible, 0 = hidden, NULL = pending

User tracking:
- user_id       # User who created/modified
```

### **Variable Naming**

```php
// Product ID variables
$product_id     // Integer ID
$product_type   // 'R', 'D', or 'SP'
$item_id        // Integer ID

// Data arrays
$info           // Single record data
$data           // Data to save
$list           // Multiple records
$arr            // Array of records

// Query results
$query->row()          // Single row as object
$query->row_array()    // Single row as array
$query->result()       // Multiple rows as objects
$query->result_array() // Multiple rows as arrays
```

---

## 🚀 **PERFORMANCE PATTERNS**

### **Lazy Calculation Pattern**

**When:** Values expensive to calculate but rarely change

**Implementation:**
```php
// In listing display
foreach ($items as &$item) {
    if (is_null($item['web_vis'])) {
        // Calculate on first display
        $item['web_vis'] = $this->calculate_web_visibility($item);
        
        // Queue for batch update
        $updates[] = [
            'id' => $item['id'],
            'web_vis' => $item['web_vis']
        ];
    }
}

// Batch save after loop
if (!empty($updates)) {
    $this->db->update_batch('T_ITEM', $updates, 'id');
}
```

**Benefits:**
- Calculated once, cached in database
- Subsequent loads use database value
- No recalculation unless triggered by parent change

---

### **Caching Lookup Data**

```php
// In models, cache frequently used lookups
private $cached_statuses = null;

function get_statuses()
{
    if ($this->cached_statuses === null) {
        $this->cached_statuses = $this->db->get('P_PRODUCT_STATUS')->result_array();
    }
    return $this->cached_statuses;
}
```

---

## 🔄 **PARENT-CHILD CASCADE PATTERN**

**When parent changes, children must recalculate:**

```php
// In Product controller after saving showcase data
private function update_child_items_web_visibility($product_id)
{
    // Get all child items with parent visibility
    $items = $this->db->select('
        i.id,
        i.web_vis_toggle,
        i.status_id,
        sp.visible as parent_visibility
    ')
    ->from('T_ITEM i')
    ->join('SHOWCASE_PRODUCT sp', 'i.product_id = sp.product_id', 'left')
    ->where('i.product_id', $product_id)
    ->where('i.archived', 'N')
    ->get()->result_array();
    
    // Recalculate each item (respects manual override)
    $updates = [];
    foreach ($items as $item) {
        if ($item['web_vis_toggle'] == 0) {  // Auto mode only
            $new_vis = $this->calculate_web_visibility_for_item($item);
            $updates[] = ['id' => $item['id'], 'web_vis' => $new_vis];
        }
    }
    
    // Batch update
    if (!empty($updates)) {
        $this->db->update_batch('T_ITEM', $updates, 'id');
    }
}
```

**Used For:**
- Web visibility cascade
- Price updates
- Status changes affecting children

---

## 📊 **DATATABLE REFRESH PATTERNS**

### **When to Refresh DataTable**

**After Item Save:**
```javascript
// In item form AJAX success handler
if (data.success === true) {
    $('.modal#globalmodal').modal('hide');
    
    setTimeout(function() {
        this_table.ajax.reload(null, false);  // false = stay on same page
    }, 300);
}
```

**Why Delay?**
- Allows modal close animation to complete
- Prevents visual glitches
- 300ms is optimal (tested)

**DataTable Access:**
- Use global variable: `this_table` (NOT selector)
- Initialized once on page load
- Persists for entire session

---

## 🔍 **ERROR HANDLING PATTERNS**

### **Controller Error Handling**

```php
function save_item()
{
    $ret = array();
    $errors = array();
    
    // Validation
    if (empty($color_ids)) {
        array_push($errors, 'Select at least one color.');
    }
    
    // Critical validation
    if (is_null($product_id)) {
        array_push($errors, 'CRITICAL ERROR: product_id is missing.');
        log_message('error', 'Item save failed - NULL product_id');
        
        $ret['success'] = false;
        $ret['errors'] = $errors;
        echo json_encode($ret);
        return;  // Early return prevents database error
    }
    
    // Proceed if no errors
    if (empty($errors)) {
        // Save logic
    }
}
```

### **Logging Pattern**

```php
// Info level - routine operations
log_message('info', 'Web visibility recalculated for item ' . $item_id);

// Error level - problems that need attention
log_message('error', 'Item save failed: ' . $error_message);

// Debug level - development debugging
log_message('debug', 'POST data: ' . print_r($_POST, true));
```

---

## 🎨 **ASSET LOADING PATTERN**

### **Library Loading in Controllers**

```php
// Standard pattern
protected function load_reports_libraries()
{
    $this->load_jquery();
    $this->load_fontawesome();
    $this->load_datatables();
    $this->load_bootstrap();
    $this->load_multiselect_dropdown();
    
    // Custom JS/CSS
    $this->add_lib('head', 'js', asset_url() . 'others/notifyjs/dist/notify.js');
    $this->add_lib('foot', 'js', asset_url() . 'js/commons.js?v=' . rand());
    $this->add_lib('foot', 'css', asset_url() . 'css/style.css?v=' . rand());
}
```

**Methods Available** (in MY_Controller):
- `load_jquery()` - jQuery library
- `load_bootstrap()` - Bootstrap CSS/JS
- `load_datatables()` - DataTables plugin
- `load_fontawesome()` - Font Awesome icons
- `add_lib($location, $type, $url)` - Custom assets

**Asset Versioning:**
```php
// Use rand() for cache busting
asset_url() . 'js/commons.js?v=' . rand()
```

---

## 🎯 **CLI SCRIPT PATTERNS**

### **CLI Controller Structure**

```php
// Location: application/controllers/cli/Script_name.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Script_name extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Ensure CLI only
        if (!$this->input->is_cli_request()) {
            show_error('This script can only be run from command line', 403);
        }
        
        $this->load->database();
        $this->load->model('Model_name', 'model');
    }
    
    public function run()
    {
        // Main script logic
        $this->log_header();
        $this->process_data();
        $this->log_summary();
    }
    
    private function log_info($msg)
    {
        echo "[INFO] {$msg}\n";
    }
}
```

**Execution:**
```bash
php index.php cli/script_name run [--option=value]
```

**CLI Environment Handling:**
- Check `php_sapi_name() === 'cli'`
- Set defaults for missing $_SERVER variables
- Load .env file explicitly
- Use echo for output (not return values)

---

## 🔐 **AUTHENTICATION & AUTHORIZATION**

### **Ion Auth Pattern**

```php
// In MY_Controller constructor
if ($this->ion_auth->logged_in() === FALSE) {
    redirect('/auth/');
}

// Check admin
if ($this->ion_auth->is_admin() === 1) {
    // Admin logic
}

// Check permissions
if ($this->hasPermission('product', 'edit')) {
    // Show edit buttons
}
```

**Permission Structure:**
- Stored in Ion Auth tables
- Checked per controller/action
- Passed to views via `$this->data['hasEditPermission']`

**Guest Access:**
```php
// Whitelist URLs in MY_Controller
$guestUrls = [
    'reps/product/specsheet/',
    'lists/sourcebook'
];
```

---

## 📦 **COMMON HELPER PATTERNS**

### **URL Generation**

```php
// CodeIgniter site_url() helper
site_url('product/edit')  // Generates full URL with base_url

// In views
<?php echo site_url('item/save_item') ?>
```

### **Asset URLs**

```php
// Custom helper (defined in application)
asset_url() . 'js/commons.js'

// Generates:
// https://localhost/assets/js/commons.js
```

### **Data Encoding**

```php
// Decode array for dropdowns
$options = $this->decode_array($list, 'id', 'name');

// Creates associative array:
// [1 => 'Option 1', 2 => 'Option 2', ...]
```

---

## ⚠️ **CRITICAL AI MODEL GUIDELINES**

### **1. NEVER Assume - Always Examine**

```
❌ WRONG: "The Product model probably has a get_product() method"
✅ CORRECT: Read Product_model.php, find actual method names
```

### **2. Respect Data Types**

```php
// CHAR(1) comparisons
if ($row['archived'] === 'Y') {  // CORRECT
if ($row['archived'] == true) {  // WRONG

// TINYINT(1) comparisons  
if ($row['web_vis'] === 1 || $row['web_vis'] === '1') {  // CORRECT (both)
```

### **3. Use Query Builder**

```php
// CORRECT
$this->db->where('id', $id)->get('T_PRODUCT');

// WRONG
$this->db->query("SELECT * FROM T_PRODUCT WHERE id = $id");
```

### **4. Check Both Response Formats**

```javascript
// Item forms return {success: true}
// Product forms return {status: 'OK'}
// ALWAYS check both:
if (data.status == 'OK' || data.success === true) {
```

### **5. Understand Modal vs Non-Modal Forms**

```
Item forms:
- In #globalmodal
- Own AJAX handler in view file
- Close modal on success
- Reload DataTable

Product forms:
- May use form_validation.js
- May reload entire view
- Different response handling
```

### **6. Parent-Child Relationships**

```
Products (parent):
- Have ONE vendor (T_PRODUCT_VENDOR)
- Have MANY items (T_ITEM)
- Changes CASCADE to children

Items (child):
- Belong to ONE product
- Have MANY colors (T_ITEM_COLOR → P_COLOR)
- Inherit parent visibility constraints
```

---

## 📚 **REFERENCE SPECIFICATIONS**

### **Related Documentation**

1. **Database Schema:** `docs/ai-specs/opms-database-spec.md`
   - Complete table definitions
   - Query patterns
   - Field validation rules

2. **Web Visibility:** `docs/ai-specs/Web-Visibility-ACTUAL-IMPLEMENTATION.md`
   - Current implementation details
   - Form patterns
   - Calculation logic

3. **Security Rules:** `.cursorrules.mdc`
   - Security mandates
   - Coding standards
   - Approval protocols

4. **Migration Tools:** `docs/Web-Visibility-Migration/`
   - Batch processing patterns
   - CLI script examples
   - Testing workflows

---

## 🎓 **KEY LEARNINGS FOR AI MODELS**

### **1. Always Examine Actual Code**
- Don't assume column names match spec
- Check actual table structure
- Find real method names in models
- Verify response formats in controllers

### **2. Understand Legacy Patterns**
- 20+ year old codebase has established patterns
- Not all modern practices apply
- Respect existing conventions
- Match existing code style

### **3. CodeIgniter 3 Specific**
- Query Builder is mandatory (security)
- Views receive data directly (not `$this->data`)
- Models extend MY_Model (has common methods)
- Controllers extend MY_Controller (has auth)

### **4. AJAX Response Formats Vary**
- Check success handlers for both formats
- Item forms: `{success: true, item: {...}}`
- Other forms: `{status: 'OK', message: '...'}`

### **5. DataTables via Global Variables**
- Stored in `this_table` variable
- NOT accessed via `$('#table_id').DataTable()`
- Reload with `this_table.ajax.reload()`

### **6. Forms May Have Own Handlers**
- Modal forms often have own AJAX in view file
- Non-modal forms may use form_validation.js
- Always check actual form submit handler location

### **7. Change Tracking is Intentional**
- Hidden fields track what changed
- Controller checks before saving
- Prevents unnecessary database writes
- Enables audit logging

### **8. NULL Has Meaning**
- NULL ≠ 0 or false
- NULL often triggers lazy calculation
- Always check for NULL explicitly: `is_null()`
- Don't assume NULL = default value

---

## 🛠️ **COMMON DEBUGGING PATTERNS**

### **Enable Debug Logging**

```php
// In controller
log_message('debug', 'POST data: ' . print_r($_POST, true));
log_message('debug', 'SQL Query: ' . $this->db->last_query());

// View logs
tail -f application/logs/log-*.php
```

### **Examine Queries**

```php
// Before ->get()
echo $this->db->get_compiled_select();
exit;

// After ->get()
echo $this->db->last_query();
```

### **AJAX Debugging**

```javascript
// In success handler
console.log('Response data:', data);
console.log('Data type:', typeof data);
console.log('Has success?', data.success);
console.log('Has status?', data.status);
```

---

## 📋 **QUICK REFERENCE**

### **File Paths**
- Controllers: `application/controllers/{Name}.php`
- Models: `application/models/{Name}_model.php`
- Views: `application/views/{controller}/{view}.php`
- Libraries: `application/libraries/{Name}.php`
- Config: `application/config/{env}/config.php`

### **Database Access**
- Query Builder: `$this->db->...`
- Active Record: Preferred method
- Prepared Statements: Automatic with Query Builder
- Batch Updates: `update_batch()` for multiple records

### **Data Passing**
- Controller → View: `$this->data` array
- AJAX Response: JSON with status/success key
- Form Submit: Via POST, accessed with `$this->input->post()`

### **Common Gotchas**
- ❌ Unchecked checkboxes don't submit (use `is_null()` check)
- ❌ CHAR(1) ≠ boolean (use 'Y'/'N' strings)
- ❌ DataTable accessed via global variable (not selector)
- ❌ Product ID format: "id-type" (must split)
- ❌ Images in SHOWCASE_ITEM (not T_ITEM)
- ❌ Status in P_PRODUCT_STATUS.name (not .abrev)

---

## 🎯 **SUCCESS CRITERIA FOR AI MODELS**

An AI model successfully understands OPMS when it can:

1. ✅ Identify the correct controller/model for a feature
2. ✅ Write queries using Query Builder with proper JOINs
3. ✅ Handle CHAR(1) boolean fields correctly
4. ✅ Parse product_id format (id-type)
5. ✅ Understand change tracking pattern
6. ✅ Know which table has which data (SHOWCASE_ vs T_)
7. ✅ Respect parent-child cascade relationships
8. ✅ Use proper AJAX response format
9. ✅ Access DataTables via correct method
10. ✅ Follow security patterns (Query Builder, input sanitization)

---

## 🚨 **CRITICAL WARNINGS FOR AI MODELS**

### **NEVER:**
- ❌ Modify database schema
- ❌ Assume method names without reading code
- ❌ Use string concatenation in SQL
- ❌ Access `$_POST` directly (use `$this->input->post()`)
- ❌ Commit .env files
- ❌ Break existing functionality
- ❌ Use deprecated PHP functions
- ❌ Assume column names match documentation

### **ALWAYS:**
- ✅ Examine actual code before making claims
- ✅ Use Query Builder for database access
- ✅ Filter archived records
- ✅ Validate user input
- ✅ Handle NULL explicitly
- ✅ Test changes before committing
- ✅ Follow existing patterns
- ✅ Check actual table structure

---

## 📞 **SUPPORT REFERENCES**

**Official Documentation:**
- CodeIgniter 3: https://codeigniter.com/userguide3/
- PHP 7.3: https://www.php.net/manual/en/migration73.php

**Project Documentation:**
- Database Schema: `docs/ai-specs/opms-database-spec.md`
- Web Visibility: `docs/ai-specs/Web-Visibility-ACTUAL-IMPLEMENTATION.md`
- Security Rules: `.cursorrules.mdc`

**Example Code:**
- Product Controller: `application/controllers/Product.php`
- Item Controller: `application/controllers/Item.php`
- Migration Script: `application/controllers/cli/Migrate_web_visibility.php`

---

## ✅ **FINAL NOTES**

This specification is based on **actual examination of production code**, not assumptions or documentation. All patterns, methods, and conventions are **verified against real code** as of October 9, 2025, tag v2.6.

When working with OPMS:
1. Read this specification first
2. Examine actual code second
3. Test changes third
4. Never assume or guess

**END OF SPECIFICATION**

---

**Version:** 1.0.0  
**Author:** AI Assistant (based on code examination)  
**Approved:** Paul Leasure  
**Date:** October 9, 2025  
**Tag:** v2.6


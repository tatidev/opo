# OPMS AI Model Specification
## CodeIgniter 3 + JavaScript Expert System - Technical AI Model Spec with Prompt Engineering

**Version:** 1.1.0  
**Date:** January 15, 2025  
**Target Framework:** CodeIgniter 3.1.11 + PHP 7.3 + JavaScript ES6+  
**Application:** OPMS (Opuzen Product Management System)  

---

## 🎯 **EXECUTIVE SUMMARY**

This AI Model Specification defines the complete technical architecture, patterns, and prompt engineering strategies for an expert AI assistant specialized in the OPMS CodeIgniter 3 + JavaScript codebase. The system manages fabric inventory, product specifications, vendor relationships, restock operations, NetSuite integration, and provides rich interactive user interfaces.

### **Core Business Domain**
- **Fabric & Textile Management**: Product specifications, inventory tracking, vendor relationships
- **Multi-Database Architecture**: Master app database + Sales database + Showroom database
- **Vendor Integration**: NetSuite ERP integration with custom field mappings
- **Restock Operations**: Order management, shipment tracking, completion workflows
- **Authentication & Security**: Ion Auth + REST API with role-based permissions
- **Interactive User Interfaces**: DataTables, jQuery, AJAX, real-time updates, responsive design

---

## 🏗️ **TECHNICAL ARCHITECTURE OVERVIEW**

### **Framework Stack**
```
CodeIgniter 3.1.11 + JavaScript ES6+
├── Backend Stack
│   ├── PHP 7.3 (Production)
│   ├── MySQL 5.7+ (Multi-database)
│   ├── AWS S3 (File Storage)
│   ├── NetSuite REST API (ERP Integration)
│   └── Custom REST Server (API Layer)
├── Frontend Stack
│   ├── JavaScript ES6+ (Modern JavaScript)
│   ├── jQuery 3.x (DOM manipulation & AJAX)
│   ├── DataTables (Advanced table functionality)
│   ├── Bootstrap 4/5 (Responsive UI framework)
│   ├── Font Awesome (Icon library)
│   └── Custom OPMS UI Components
└── Integration Layer
    ├── AJAX Communication (jQuery-based)
    ├── REST API Integration (JavaScript)
    ├── Real-time Updates (WebSocket/AJAX polling)
    └── File Upload (S3 integration)
```

### **Database Architecture**
```
opuzen_prod_master_app (Primary - 150+ Tables)
├── Core Business Tables (T_*)
│   ├── T_PRODUCT (Product families & specifications)
│   ├── T_ITEM (Individual SKUs/items)
│   ├── T_ITEM_COLOR (Item-color relationships)
│   ├── T_PRODUCT_VENDOR (Product-vendor relationships)
│   ├── T_PRODUCT_VARIOUS (Extended product attributes)
│   └── T_PRODUCT_* (Mini-forms: Content, Abrasion, Firecode)
├── Parameter Tables (P_*)
│   ├── P_COLOR (Color master data)
│   ├── P_CONTENT (Fabric content types)
│   ├── P_ABRASION_TEST (Abrasion test types)
│   ├── P_FIRECODE_TEST (Fire safety test types)
│   └── P_* (25+ lookup tables)
├── Vendor Tables (Z_*)
│   ├── Z_VENDOR (Vendor master data)
│   ├── Z_CONTACT (Vendor contacts)
│   └── Z_SHOWROOM (Showroom data)
├── Integration Tables
│   └── opms_netsuite_vendor_mapping (CRITICAL - NetSuite sync)
└── Support Tables (90+ history, cache, utility)

opuzen_prod_sales (Inventory & Orders)
├── op_products_bolts (Bolt-level inventory)
├── op_products (Product inventory summary)
├── op_orders_header (Order management)
└── v_products_stock (Aggregated stock view)

opuzen_prod_showroom (Showroom Data)
└── [Showroom-specific tables]
```

### **Critical Database Context**
- **Legacy System**: 20+ year old MySQL database with established business processes
- **Production Data**: Contains live business-critical data - handle with extreme care
- **No Schema Changes**: AI models must work with existing schema - no modifications allowed
- **Direct Integration**: API reads directly from OPMS tables without abstraction layers
- **Data Quality**: Mandatory filters required for all queries (archived, active, non-null)

---

## 🤖 **AI MODEL CORE CAPABILITIES**

### **1. CodeIgniter 3 Expert Knowledge**

#### **Framework Patterns**
- **MVC Architecture**: Controllers extend `MY_Controller`, Models extend `CI_Model`
- **Database Layer**: Active Record pattern with `$this->db->` methods
- **Security**: XSS filtering, CSRF protection, input validation
- **Authentication**: Ion Auth integration with role-based permissions
- **REST API**: Custom REST_Controller with API key authentication

#### **Key Controllers**
```php
// Core Business Controllers
Product.php          // Product management & specifications
Item.php            // Individual item management
Vendor.php          // Vendor relationship management
Restock.php         // Restock order & shipment management
Reports.php         // Business intelligence & reporting
Lists.php           // Data list management
Cart.php            // Shopping cart functionality
```

#### **Data Models**
```php
// Primary Models
Product_model.php   // Product specifications & search
Item_model.php      // Item-level operations
Search_model.php    // Advanced search & filtering
Restock_model.php   // Restock operations
Vendor_model.php    // Vendor management
Lists_model.php     // List operations
```

### **2. JavaScript & Frontend Expertise**

#### **Modern JavaScript Patterns**
- **ES6+ Features**: Arrow functions, destructuring, template literals, async/await
- **Module Systems**: ES6 modules, CommonJS patterns, AMD compatibility
- **DOM Manipulation**: Native JavaScript and jQuery integration
- **Event Handling**: Event delegation, custom events, performance optimization
- **AJAX Communication**: RESTful API integration, error handling, loading states

#### **jQuery Mastery**
- **DOM Selection**: Efficient selectors, chaining, performance optimization
- **Event Management**: Event binding, delegation, custom events
- **AJAX Operations**: GET/POST/PUT/DELETE requests, JSON handling
- **Animation & Effects**: Smooth transitions, loading indicators, user feedback
- **Plugin Integration**: Custom plugins, third-party library integration

#### **DataTables Expertise**
- **Advanced Configuration**: Server-side processing, custom rendering, column definitions
- **Data Sources**: AJAX data loading, JSON formatting, real-time updates
- **User Interactions**: Sorting, filtering, pagination, row selection
- **Custom Features**: Inline editing, bulk operations, export functionality
- **Performance Optimization**: Large dataset handling, lazy loading, memory management

#### **UI/UX Integration**
- **Responsive Design**: Bootstrap integration, mobile-first approach
- **Real-time Updates**: WebSocket integration, AJAX polling, live data refresh
- **Form Handling**: Validation, submission, error display, success feedback
- **File Operations**: Upload progress, drag-and-drop, preview functionality
- **User Experience**: Loading states, error handling, success notifications

### **3. Database Expertise**

#### **Core Table Specifications**

**T_PRODUCT - Product Master Data**
- **Purpose**: Product families and specifications
- **Key Fields**: `id`, `name`, `width`, `type`, `archived`
- **NetSuite Mapping**: `id` → `custitem_opms_prod_id`, `name` → display name component
- **Critical Rules**: Always filter `archived = 'N'` for active products

**T_ITEM - Product Item Variations (SKUs)**
- **Purpose**: Individual items/SKUs within product families
- **Key Fields**: `id`, `product_id`, `code`, `vendor_code`, `vendor_color`, `archived`
- **NetSuite Mapping**: `code` → `itemid`, `id` → `custitem_opms_item_id`
- **Critical Rules**: `code` must be unique and non-null for NetSuite sync

**T_ITEM_COLOR - Item-Color Relationships**
- **Purpose**: Many-to-many relationship between items and colors
- **Key Fields**: `item_id`, `color_id`, `n_order`
- **Usage**: Required for display name generation and color filtering

**Z_VENDOR - Vendor Master Data**
- **Purpose**: Vendor/supplier information
- **Key Fields**: `id`, `name`, `abrev`, `active`, `archived`
- **Critical Integration**: Requires `opms_netsuite_vendor_mapping` for NetSuite sync

**T_PRODUCT_VARIOUS - Extended Product Attributes**
- **Purpose**: Additional product specifications and vendor data
- **Key Fields**: `vendor_product_name`, `yards_per_roll`, `lead_time`, `min_order_qty`
- **NetSuite Mapping**: `vendor_product_name` → `custitem_opms_vendor_prod_name`

#### **Mini-Forms Tables (Rich Content)**
- **T_PRODUCT_CONTENT_FRONT/BACK**: Fabric content percentages with descriptions
- **T_PRODUCT_ABRASION**: Abrasion test results with file attachments
- **T_PRODUCT_FIRECODE**: Fire safety certifications with file attachments
- **Purpose**: Generate beautiful HTML for NetSuite rich text fields

#### **Lookup/Parameter Tables**
- **P_COLOR**: Master color list (7,585+ colors)
- **P_CONTENT**: Fabric content types (Cotton, Polyester, etc.)
- **P_ABRASION_TEST**: Test types (Wyzenbeek, Martindale, etc.)
- **P_FIRECODE_TEST**: Fire safety test types (NFPA, CAL 117, etc.)

#### **Complex Query Patterns**
- **Multi-table JOINs**: Product-vendor-color relationships
- **Aggregated Views**: Stock calculations, pricing summaries
- **Stored Procedures**: Daily stock updates, complex calculations
- **Cross-Database Queries**: Master app ↔ Sales database integration

#### **Critical SQL Patterns**
```sql
-- Master Export Query (NetSuite Integration)
SELECT DISTINCT
    i.id as item_id,
    i.code as item_code,
    i.vendor_code,
    i.vendor_color,
    p.id as product_id,
    p.name as product_name,
    p.width,
    v.id as vendor_id,
    v.name as vendor_name,
    m.netsuite_vendor_id,
    m.netsuite_vendor_name,
    pvar.vendor_product_name,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as color_name
FROM T_ITEM i
JOIN T_PRODUCT p ON i.product_id = p.id
JOIN T_PRODUCT_VENDOR pv ON p.id = pv.product_id
JOIN Z_VENDOR v ON pv.vendor_id = v.id
JOIN opms_netsuite_vendor_mapping m ON v.id = m.opms_vendor_id
LEFT JOIN T_PRODUCT_VARIOUS pvar ON p.id = pvar.product_id
LEFT JOIN T_ITEM_COLOR ic ON i.id = ic.item_id
LEFT JOIN P_COLOR c ON ic.color_id = c.id
WHERE i.code IS NOT NULL
  AND p.name IS NOT NULL
  AND v.name IS NOT NULL
  AND i.archived = 'N'
  AND p.archived = 'N'
  AND v.active = 'Y'
  AND v.archived = 'N'
  AND m.opms_vendor_name = m.netsuite_vendor_name
GROUP BY i.id, i.code, i.vendor_code, i.vendor_color, p.id, p.name, p.width, v.id, v.name, m.netsuite_vendor_id, m.netsuite_vendor_name, pvar.vendor_product_name
HAVING color_name IS NOT NULL;
```

#### **Data Quality Constraints (MANDATORY)**
```sql
-- ALWAYS include these WHERE conditions in all queries
WHERE i.archived = 'N'           -- Active items only
  AND p.archived = 'N'           -- Active products only
  AND v.active = 'Y'             -- Active vendors only
  AND v.archived = 'N'           -- Non-archived vendors
  AND i.code IS NOT NULL         -- Valid item codes required
  AND p.name IS NOT NULL         -- Valid product names required
  AND v.name IS NOT NULL         -- Valid vendor names required
```

### **4. Security & Authentication**

#### **Security Patterns**
- **Input Validation**: `$this->form_validation->set_rules()`
- **XSS Protection**: `$this->security->xss_clean()` or global filtering
- **SQL Injection Prevention**: `$this->db->escape()` for user input
- **CSRF Protection**: Form tokens and Origin/Referer checks
- **Session Security**: Secure cookies with `HttpOnly`, `Secure`, `SameSite`

#### **Authentication Flow**
```php
// Ion Auth Integration
if ($this->ion_auth->logged_in() === FALSE) {
    redirect('/auth/');
} else {
    $this->init_logged();
}

// Permission Checking
$this->data['hasEditPermission'] = $this->hasPermission('product', 'edit');
$this->data['hasMPLPermission'] = $this->hasPermission('product', 'master_price_list');
```

### **4. Mini-Forms & Rich Content Expertise**

#### **Mini-Forms Data Processing**
The OPMS system includes sophisticated "mini-forms" that store rich content data for products, which must be transformed into beautiful HTML for NetSuite integration.

**Front/Back Content Processing**
```php
// Extract front content with percentages
public function get_front_content($product_id)
{
    $this->db->select('pcf.perc, pc.name as content_name, pcfd.content as front_description');
    $this->db->from('T_PRODUCT_CONTENT_FRONT pcf');
    $this->db->join('P_CONTENT pc', 'pcf.content_id = pc.id');
    $this->db->join('T_PRODUCT_CONTENT_FRONT_DESCR pcfd', 'pcf.product_id = pcfd.product_id', 'left');
    $this->db->where('pcf.product_id', $product_id);
    $this->db->order_by('pcf.perc', 'DESC');
    
    return $this->db->get()->result_array();
}
```

**Abrasion Test Data Processing**
```php
// Extract abrasion tests with file attachments
public function get_abrasion_tests($product_id)
{
    $this->db->select('pa.n_rubs, pat.name as test_name, pal.name as limit_name, GROUP_CONCAT(paf.url_dir) as file_urls');
    $this->db->from('T_PRODUCT_ABRASION pa');
    $this->db->join('P_ABRASION_TEST pat', 'pa.abrasion_test_id = pat.id');
    $this->db->join('P_ABRASION_LIMIT pal', 'pa.abrasion_limit_id = pal.id');
    $this->db->join('T_PRODUCT_ABRASION_FILES paf', 'pa.id = paf.abrasion_id', 'left');
    $this->db->where('pa.product_id', $product_id);
    $this->db->where('pa.visible', 'Y');
    $this->db->group_by('pa.id');
    
    return $this->db->get()->result_array();
}
```

**Fire Code Data Processing**
```php
// Extract fire code certifications with files
public function get_fire_codes($product_id)
{
    $this->db->select('pft.name as test_name, GROUP_CONCAT(pff.url_dir) as file_urls');
    $this->db->from('T_PRODUCT_FIRECODE pf');
    $this->db->join('P_FIRECODE_TEST pft', 'pf.firecode_test_id = pft.id');
    $this->db->join('T_PRODUCT_FIRECODE_FILES pff', 'pf.id = pff.firecode_id', 'left');
    $this->db->where('pf.product_id', $product_id);
    $this->db->where('pf.visible', 'Y');
    $this->db->group_by('pf.id');
    
    return $this->db->get()->result_array();
}
```

#### **HTML Generation for NetSuite**
Mini-forms data must be transformed into beautiful, styled HTML for NetSuite rich text fields.

**Front Content HTML Generation**
```php
public function generate_front_content_html($front_data)
{
    $html = '<div style="font-family: Arial, sans-serif; margin: 10px 0;">';
    $html .= '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 12px; border-radius: 4px; font-weight: bold; margin-bottom: 8px;">Front Content</div>';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">';
    
    foreach ($front_data as $item) {
        $html .= '<tr style="border-bottom: 1px solid #eee;">';
        $html .= '<td style="padding: 4px 8px; font-weight: bold;">' . $item['perc'] . '%</td>';
        $html .= '<td style="padding: 4px 8px;">' . $item['content_name'] . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    
    if (!empty($front_data[0]['front_description'])) {
        $html .= '<div style="background: #f8f9fa; padding: 8px; border-radius: 4px; border-left: 3px solid #667eea; margin-top: 8px;">';
        $html .= $front_data[0]['front_description'];
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
```

**Abrasion Test HTML Generation**
```php
public function generate_abrasion_html($abrasion_data)
{
    $html = '<div style="font-family: Arial, sans-serif; margin: 10px 0;">';
    $html .= '<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 8px 12px; border-radius: 4px; font-weight: bold; margin-bottom: 8px;">Abrasion Tests</div>';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">';
    
    foreach ($abrasion_data as $test) {
        $html .= '<tr style="border-bottom: 1px solid #eee;">';
        $html .= '<td style="padding: 4px 8px; font-weight: bold;">' . $test['n_rubs'] . ' rubs</td>';
        $html .= '<td style="padding: 4px 8px;">' . $test['test_name'] . '</td>';
        $html .= '<td style="padding: 4px 8px;">' . $test['limit_name'] . '</td>';
        
        if (!empty($test['file_urls'])) {
            $files = explode(',', $test['file_urls']);
            $html .= '<td style="padding: 4px 8px;">';
            foreach ($files as $file) {
                $html .= '<a href="' . $file . '" target="_blank" style="color: #007bff; text-decoration: none; margin-right: 8px;">📄 Certificate</a>';
            }
            $html .= '</td>';
        }
        
        $html .= '</tr>';
    }
    
    $html .= '</table></div>';
    
    return $html;
}
```

#### **Field Validation for Mini-Forms**
```php
// MANDATORY validation for all OPMS fields
public function validate_opms_field($field_name, $field_data)
{
    if ($field_data === null) {
        return 'query_failed';  // Field not accessible
    } elseif ($field_data === '' || (is_array($field_data) && empty($field_data))) {
        return 'src_empty_data';  // Empty but accessible
    } else {
        return 'has_data';  // Contains actual data
    }
}

// Process mini-forms with validation
public function process_mini_forms($product_id)
{
    $front_data = $this->get_front_content($product_id);
    $back_data = $this->get_back_content($product_id);
    $abrasion_data = $this->get_abrasion_tests($product_id);
    $firecode_data = $this->get_fire_codes($product_id);
    
    $result = [
        'frontContent' => $this->validate_opms_field('frontContent', $front_data) === 'has_data' 
            ? $this->generate_front_content_html($front_data) 
            : 'src empty data',
        'backContent' => $this->validate_opms_field('backContent', $back_data) === 'has_data' 
            ? $this->generate_back_content_html($back_data) 
            : 'src empty data',
        'abrasion' => $this->validate_opms_field('abrasion', $abrasion_data) === 'has_data' 
            ? $this->generate_abrasion_html($abrasion_data) 
            : 'src empty data',
        'firecodes' => $this->validate_opms_field('firecodes', $firecode_data) === 'has_data' 
            ? $this->generate_firecode_html($firecode_data) 
            : 'src empty data'
    ];
    
    return $result;
}
```

### **5. API & Integration Expertise**

#### **REST API Patterns**
- **REST_Controller**: Custom REST server implementation
- **API Key Authentication**: Database-backed API key management
- **Response Formatting**: JSON responses with proper HTTP status codes
- **Rate Limiting**: Built-in API rate limiting and logging

#### **NetSuite Integration**
- **Custom Field Mapping**: OPMS-specific custom fields
- **Vendor Integration**: ItemVendor sublist population
- **Data Transformation**: OPMS → JSON → NetSuite payload conversion
- **Error Handling**: Comprehensive error logging and retry logic

---

## 💻 **JAVASCRIPT IMPLEMENTATION PATTERNS**

### **1. DataTables Integration Patterns**

#### **Advanced DataTables Configuration**
```javascript
// OPMS DataTables with Server-Side Processing
class OPMSDataTable {
    constructor(tableId, config = {}) {
        this.tableId = tableId;
        this.config = this.mergeConfig(config);
        this.table = null;
        this.init();
    }
    
    mergeConfig(config) {
        return {
            processing: true,
            serverSide: true,
            ajax: {
                url: config.ajaxUrl || '/api/data',
                type: 'POST',
                data: (d) => {
                    d.csrf_test_name = $('input[name="csrf_test_name"]').val();
                    return d;
                },
                error: (xhr, error, thrown) => {
                    this.handleAjaxError(xhr, error, thrown);
                }
            },
            columns: config.columns || [],
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print',
                {
                    text: 'Refresh',
                    action: (e, dt, node, config) => {
                        dt.ajax.reload();
                    }
                }
            ],
            language: {
                processing: "Loading data...",
                emptyTable: "No data available",
                zeroRecords: "No matching records found"
            },
            ...config
        };
    }
    
    init() {
        this.table = $(`#${this.tableId}`).DataTable(this.config);
        this.bindEvents();
    }
    
    bindEvents() {
        // Custom event handlers for OPMS-specific functionality
        this.table.on('draw', () => {
            this.initializeRowActions();
            this.updateRowStates();
        });
        
        this.table.on('select', (e, dt, type, indexes) => {
            this.handleRowSelection(indexes);
        });
    }
    
    handleAjaxError(xhr, error, thrown) {
        console.error('DataTable AJAX Error:', error, thrown);
        this.showNotification('Error loading data. Please try again.', 'error');
    }
    
    initializeRowActions() {
        // Initialize action buttons for each row
        $(`#${this.tableId} tbody tr`).each((index, row) => {
            this.attachRowActions($(row));
        });
    }
    
    attachRowActions($row) {
        const itemId = $row.data('item-id');
        if (!itemId) return;
        
        // Add action buttons
        const actionCell = $row.find('.actions');
        if (actionCell.length === 0) return;
        
        actionCell.html(`
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-primary edit-item" data-item-id="${itemId}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-info view-item" data-item-id="${itemId}">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-item" data-item-id="${itemId}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `);
        
        // Bind click events
        actionCell.find('.edit-item').on('click', (e) => this.editItem(itemId));
        actionCell.find('.view-item').on('click', (e) => this.viewItem(itemId));
        actionCell.find('.delete-item').on('click', (e) => this.deleteItem(itemId));
    }
    
    editItem(itemId) {
        window.location.href = `/item/edit/${itemId}`;
    }
    
    viewItem(itemId) {
        window.open(`/item/view/${itemId}`, '_blank');
    }
    
    deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            this.performDelete(itemId);
        }
    }
    
    async performDelete(itemId) {
        try {
            const response = await $.ajax({
                url: `/api/item/delete/${itemId}`,
                method: 'POST',
                data: {
                    csrf_test_name: $('input[name="csrf_test_name"]').val()
                }
            });
            
            if (response.success) {
                this.showNotification('Item deleted successfully', 'success');
                this.table.ajax.reload();
            } else {
                this.showNotification(response.error || 'Failed to delete item', 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting item', 'error');
        }
    }
    
    showNotification(message, type = 'info') {
        // Use OPMS notification system
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
            alert(message);
        }
    }
}

// Usage Example
const productTable = new OPMSDataTable('products-table', {
    ajaxUrl: '/api/products/list',
    columns: [
        { data: 'id', title: 'ID' },
        { data: 'name', title: 'Product Name' },
        { data: 'vendor_name', title: 'Vendor' },
        { data: 'status', title: 'Status' },
        { data: 'actions', title: 'Actions', orderable: false, searchable: false }
    ]
});
```

#### **Real-time Data Updates**
```javascript
// Real-time DataTable Updates for OPMS
class RealTimeDataTable extends OPMSDataTable {
    constructor(tableId, config = {}) {
        super(tableId, config);
        this.updateInterval = config.updateInterval || 30000; // 30 seconds
        this.isUpdating = false;
        this.startAutoUpdate();
    }
    
    startAutoUpdate() {
        setInterval(() => {
            if (!this.isUpdating && document.visibilityState === 'visible') {
                this.updateData();
            }
        }, this.updateInterval);
    }
    
    async updateData() {
        if (this.isUpdating) return;
        
        this.isUpdating = true;
        try {
            // Get current page and search state
            const currentPage = this.table.page.info().page;
            const searchValue = this.table.search();
            
            // Reload data
            await this.table.ajax.reload(null, false);
            
            // Restore page and search state
            this.table.page(currentPage);
            this.table.search(searchValue).draw();
            
        } catch (error) {
            console.error('Auto-update failed:', error);
        } finally {
            this.isUpdating = false;
        }
    }
}
```

### **2. AJAX Communication Patterns**

#### **RESTful API Integration**
```javascript
// OPMS AJAX Manager for RESTful API Communication
class OPMSAjaxManager {
    constructor() {
        this.baseUrl = window.location.origin;
        this.csrfToken = $('input[name="csrf_test_name"]').val();
        this.setupAjaxDefaults();
    }
    
    setupAjaxDefaults() {
        $.ajaxSetup({
            beforeSend: (xhr, settings) => {
                // Add CSRF token to all requests
                if (this.csrfToken) {
                    xhr.setRequestHeader('X-CSRF-Token', this.csrfToken);
                }
                
                // Add loading indicator
                this.showLoadingIndicator();
            },
            complete: () => {
                this.hideLoadingIndicator();
            },
            error: (xhr, status, error) => {
                this.handleGlobalError(xhr, status, error);
            }
        });
    }
    
    async get(url, data = {}) {
        return this.request('GET', url, data);
    }
    
    async post(url, data = {}) {
        return this.request('POST', url, data);
    }
    
    async put(url, data = {}) {
        return this.request('PUT', url, data);
    }
    
    async delete(url, data = {}) {
        return this.request('DELETE', url, data);
    }
    
    async request(method, url, data = {}) {
        const config = {
            url: this.baseUrl + url,
            method: method,
            data: {
                ...data,
                csrf_test_name: this.csrfToken
            },
            dataType: 'json'
        };
        
        try {
            const response = await $.ajax(config);
            return response;
        } catch (error) {
            throw this.processError(error);
        }
    }
    
    processError(error) {
        if (error.responseJSON) {
            return {
                message: error.responseJSON.message || 'An error occurred',
                code: error.responseJSON.code || error.status,
                details: error.responseJSON.details || null
            };
        }
        
        return {
            message: 'Network error occurred',
            code: error.status || 0,
            details: error.statusText || 'Unknown error'
        };
    }
    
    handleGlobalError(xhr, status, error) {
        if (xhr.status === 401) {
            this.showNotification('Session expired. Please log in again.', 'error');
            setTimeout(() => {
                window.location.href = '/auth/login';
            }, 2000);
        } else if (xhr.status === 403) {
            this.showNotification('Access denied. Insufficient permissions.', 'error');
        } else if (xhr.status >= 500) {
            this.showNotification('Server error. Please try again later.', 'error');
        }
    }
    
    showLoadingIndicator() {
        if (!$('#ajax-loading').length) {
            $('body').append(`
                <div id="ajax-loading" class="ajax-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            `);
        }
        $('#ajax-loading').show();
    }
    
    hideLoadingIndicator() {
        $('#ajax-loading').hide();
    }
    
    showNotification(message, type = 'info') {
        // Implementation depends on OPMS notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Global AJAX manager instance
window.opmsAjax = new OPMSAjaxManager();
```

### **3. Form Handling Patterns**

#### **Dynamic Form Management**
```javascript
// OPMS Dynamic Form Handler
class OPMSFormHandler {
    constructor(formId, config = {}) {
        this.formId = formId;
        this.form = $(`#${formId}`);
        this.config = this.mergeConfig(config);
        this.init();
    }
    
    mergeConfig(config) {
        return {
            validation: true,
            ajaxSubmit: true,
            showProgress: true,
            resetOnSuccess: false,
            ...config
        };
    }
    
    init() {
        this.bindEvents();
        this.initializeValidation();
    }
    
    bindEvents() {
        this.form.on('submit', (e) => this.handleSubmit(e));
        this.form.on('change', 'input, select, textarea', (e) => this.handleFieldChange(e));
    }
    
    initializeValidation() {
        if (!this.config.validation) return;
        
        // Real-time validation
        this.form.find('input, select, textarea').on('blur', (e) => {
            this.validateField($(e.target));
        });
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (this.config.validation && !this.validateForm()) {
            return false;
        }
        
        if (this.config.ajaxSubmit) {
            await this.submitAjax();
        } else {
            this.form[0].submit();
        }
    }
    
    validateForm() {
        let isValid = true;
        
        this.form.find('input[required], select[required], textarea[required]').each((index, field) => {
            if (!this.validateField($(field))) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField($field) {
        const value = $field.val().trim();
        const fieldName = $field.attr('name');
        const fieldType = $field.attr('type');
        const isRequired = $field.prop('required');
        
        // Clear previous validation
        $field.removeClass('is-invalid is-valid');
        $field.siblings('.invalid-feedback').remove();
        
        // Required field validation
        if (isRequired && !value) {
            this.showFieldError($field, `${this.getFieldLabel($field)} is required`);
            return false;
        }
        
        // Type-specific validation
        if (value && fieldType === 'email' && !this.isValidEmail(value)) {
            this.showFieldError($field, 'Please enter a valid email address');
            return false;
        }
        
        if (value && fieldType === 'number' && isNaN(value)) {
            this.showFieldError($field, 'Please enter a valid number');
            return false;
        }
        
        // Custom validation rules
        if (this.config.customValidation && this.config.customValidation[fieldName]) {
            const customResult = this.config.customValidation[fieldName](value, $field);
            if (customResult !== true) {
                this.showFieldError($field, customResult);
                return false;
            }
        }
        
        // Mark as valid
        $field.addClass('is-valid');
        return true;
    }
    
    showFieldError($field, message) {
        $field.addClass('is-invalid');
        $field.after(`<div class="invalid-feedback">${message}</div>`);
    }
    
    getFieldLabel($field) {
        const label = $field.closest('.form-group').find('label').text();
        return label || $field.attr('name') || 'This field';
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    async submitAjax() {
        const formData = new FormData(this.form[0]);
        
        try {
            if (this.config.showProgress) {
                this.showProgressIndicator();
            }
            
            const response = await window.opmsAjax.post(this.config.submitUrl, formData);
            
            if (response.success) {
                this.handleSuccess(response);
            } else {
                this.handleError(response);
            }
            
        } catch (error) {
            this.handleError(error);
        } finally {
            if (this.config.showProgress) {
                this.hideProgressIndicator();
            }
        }
    }
    
    handleSuccess(response) {
        this.showNotification(response.message || 'Form submitted successfully', 'success');
        
        if (this.config.resetOnSuccess) {
            this.form[0].reset();
        }
        
        if (this.config.onSuccess) {
            this.config.onSuccess(response);
        }
    }
    
    handleError(error) {
        this.showNotification(error.message || 'Form submission failed', 'error');
        
        if (this.config.onError) {
            this.config.onError(error);
        }
    }
    
    showProgressIndicator() {
        this.form.find('button[type="submit"]').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Submitting...
        `);
    }
    
    hideProgressIndicator() {
        this.form.find('button[type="submit"]').prop('disabled', false).html('Submit');
    }
    
    showNotification(message, type) {
        // Use OPMS notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Usage Example
const productForm = new OPMSFormHandler('product-form', {
    submitUrl: '/api/products/save',
    validation: true,
    ajaxSubmit: true,
    resetOnSuccess: true,
    customValidation: {
        'product_name': (value) => {
            if (value.length < 3) {
                return 'Product name must be at least 3 characters';
            }
            return true;
        }
    },
    onSuccess: (response) => {
        // Refresh data table or redirect
        if (window.productTable) {
            window.productTable.ajax.reload();
        }
    }
});
```

### **4. File Upload Patterns**

#### **S3 File Upload with Progress**
```javascript
// OPMS S3 File Upload Handler
class OPMSFileUpload {
    constructor(inputId, config = {}) {
        this.input = $(`#${inputId}`);
        this.config = this.mergeConfig(config);
        this.init();
    }
    
    mergeConfig(config) {
        return {
            maxFileSize: 5 * 1024 * 1024, // 5MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
            uploadUrl: '/api/upload/s3',
            preview: true,
            multiple: false,
            ...config
        };
    }
    
    init() {
        this.bindEvents();
        this.createUploadArea();
    }
    
    bindEvents() {
        this.input.on('change', (e) => this.handleFileSelect(e));
    }
    
    createUploadArea() {
        const uploadArea = $(`
            <div class="file-upload-area">
                <div class="upload-zone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Drag and drop files here or click to browse</p>
                    <input type="file" id="${this.input.attr('id')}" style="display: none;">
                </div>
                <div class="upload-progress" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="upload-status">Uploading...</div>
                </div>
                <div class="file-preview"></div>
            </div>
        `);
        
        this.input.after(uploadArea);
        this.uploadArea = uploadArea;
        
        // Drag and drop functionality
        this.uploadArea.find('.upload-zone').on('click', () => this.input.click());
        this.uploadArea.on('dragover', (e) => this.handleDragOver(e));
        this.uploadArea.on('drop', (e) => this.handleDrop(e));
    }
    
    handleFileSelect(e) {
        const files = Array.from(e.target.files);
        this.processFiles(files);
    }
    
    handleDragOver(e) {
        e.preventDefault();
        this.uploadArea.addClass('drag-over');
    }
    
    handleDrop(e) {
        e.preventDefault();
        this.uploadArea.removeClass('drag-over');
        
        const files = Array.from(e.dataTransfer.files);
        this.processFiles(files);
    }
    
    processFiles(files) {
        files.forEach(file => {
            if (this.validateFile(file)) {
                this.uploadFile(file);
            }
        });
    }
    
    validateFile(file) {
        // Check file size
        if (file.size > this.config.maxFileSize) {
            this.showError(`File ${file.name} is too large. Maximum size is ${this.config.maxFileSize / 1024 / 1024}MB`);
            return false;
        }
        
        // Check file type
        if (!this.config.allowedTypes.includes(file.type)) {
            this.showError(`File type ${file.type} is not allowed`);
            return false;
        }
        
        return true;
    }
    
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('upload_type', this.config.uploadType || 'general');
        
        try {
            this.showProgress();
            
            const response = await $.ajax({
                url: this.config.uploadUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: () => {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            this.updateProgress(percentComplete);
                        }
                    });
                    return xhr;
                }
            });
            
            if (response.success) {
                this.handleUploadSuccess(file, response);
            } else {
                this.handleUploadError(file, response);
            }
            
        } catch (error) {
            this.handleUploadError(file, error);
        } finally {
            this.hideProgress();
        }
    }
    
    showProgress() {
        this.uploadArea.find('.upload-progress').show();
        this.uploadArea.find('.upload-zone').hide();
    }
    
    hideProgress() {
        this.uploadArea.find('.upload-progress').hide();
        this.uploadArea.find('.upload-zone').show();
    }
    
    updateProgress(percent) {
        this.uploadArea.find('.progress-bar').css('width', `${percent}%`);
        this.uploadArea.find('.upload-status').text(`Uploading... ${Math.round(percent)}%`);
    }
    
    handleUploadSuccess(file, response) {
        this.showSuccess(`File ${file.name} uploaded successfully`);
        
        if (this.config.preview) {
            this.showFilePreview(file, response.data);
        }
        
        if (this.config.onSuccess) {
            this.config.onSuccess(file, response);
        }
    }
    
    handleUploadError(file, error) {
        this.showError(`Failed to upload ${file.name}: ${error.message || 'Unknown error'}`);
        
        if (this.config.onError) {
            this.config.onError(file, error);
        }
    }
    
    showFilePreview(file, fileData) {
        const preview = $(`
            <div class="file-preview-item" data-file-id="${fileData.id}">
                <div class="file-info">
                    <i class="fas fa-file"></i>
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${this.formatFileSize(file.size)}</span>
                </div>
                <div class="file-actions">
                    <button class="btn btn-sm btn-outline-danger remove-file">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `);
        
        this.uploadArea.find('.file-preview').append(preview);
        
        // Bind remove action
        preview.find('.remove-file').on('click', () => {
            this.removeFile(fileData.id);
            preview.remove();
        });
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showNotification(message, type) {
        // Use OPMS notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Usage Example
const beautyShotUpload = new OPMSFileUpload('beauty_shot', {
    uploadType: 'beauty_shot',
    maxFileSize: 10 * 1024 * 1024, // 10MB
    allowedTypes: ['image/jpeg', 'image/png', 'image/gif'],
    onSuccess: (file, response) => {
        // Update product form with uploaded file info
        $('#beauty_shot_url').val(response.data.url);
        $('#beauty_shot_id').val(response.data.id);
    }
});
```

---

## 🎯 **PROMPT ENGINEERING STRATEGIES**

### **1. Context-Aware Prompts**

#### **System Context Injection**
```
You are an EXPERT PHP ENGINEER and JAVASCRIPT ENGINEER specializing in CodeIgniter 3.1.11, PHP 7.3, and modern JavaScript ES6+.
You are working with the OPMS (Opuzen Product Management System) codebase.

CRITICAL CONTEXT:
- Backend: CodeIgniter 3.1.11 with custom MY_Controller base class
- Frontend: JavaScript ES6+, jQuery 3.x, DataTables, Bootstrap 4/5
- Database: Multi-database architecture (master_app, sales, showroom)
- Security: Ion Auth + REST API with role-based permissions
- Business Domain: Fabric/textile product management with vendor integration
- Integration: NetSuite ERP with custom field mappings
- UI/UX: Interactive data tables, real-time updates, responsive design

MANDATORY RULES:
- NEVER assume or guess - always examine actual code before making claims
- ALWAYS use CodeIgniter 3 patterns and conventions for backend
- ALWAYS use modern JavaScript ES6+ patterns for frontend
- ALWAYS validate input and use prepared statements
- ALWAYS follow the established MVC architecture
- ALWAYS consider multi-database relationships
- ALWAYS implement responsive, accessible user interfaces
- ALWAYS use jQuery and DataTables for interactive components
```

#### **Domain-Specific Context**
```
OPMS BUSINESS CONTEXT:
- Products: Fabric/textile specifications with colors, weaves, cleaning instructions
- Vendors: Multi-vendor relationships with NetSuite integration
- Inventory: Bolt-level tracking with yards in stock/on hold/available
- Restock: Order management with shipment tracking and completion workflows
- Reports: Business intelligence with complex multi-table queries
- Security: Role-based permissions with guest access for specific endpoints
```

### **2. Task-Specific Prompt Templates**

#### **Code Analysis Prompts**
```
ANALYZE CODE REQUEST:
- File: [specific file path]
- Purpose: [what you need to understand]
- Context: [related business process]
- Constraints: [security, performance, compatibility requirements]

ANALYSIS FRAMEWORK:
1. Code Structure: MVC pattern compliance, method organization
2. Security: Input validation, SQL injection prevention, XSS protection
3. Database: Query efficiency, relationship integrity, transaction handling
4. Business Logic: Domain-specific rules, error handling, edge cases
5. Integration: API endpoints, external system connections
6. Performance: Query optimization, memory usage, caching opportunities
7. Frontend: JavaScript patterns, jQuery usage, DataTables configuration
8. UI/UX: Responsive design, accessibility, user experience
```

#### **JavaScript-Specific Prompts**
```
JAVASCRIPT IMPLEMENTATION REQUEST:
- Component: [DataTable, form handler, file upload, etc.]
- Functionality: [specific JavaScript behavior needed]
- Integration: [CodeIgniter backend integration requirements]
- UI/UX: [user interface and experience requirements]
- Performance: [large datasets, real-time updates, memory management]

JAVASCRIPT IMPLEMENTATION FRAMEWORK:
1. Modern JavaScript: ES6+ features, async/await, module patterns
2. jQuery Integration: DOM manipulation, AJAX communication, event handling
3. DataTables: Server-side processing, custom rendering, user interactions
4. Form Handling: Validation, submission, error display, success feedback
5. File Operations: Upload progress, drag-and-drop, preview functionality
6. Real-time Updates: WebSocket integration, AJAX polling, live data refresh
7. Performance: Memory management, lazy loading, batch operations
8. Security: CSRF protection, input validation, XSS prevention
9. Responsive Design: Mobile-first approach, Bootstrap integration
10. Accessibility: ARIA attributes, keyboard navigation, screen reader support
```

#### **DataTables-Specific Prompts**
```
DATATABLES IMPLEMENTATION REQUEST:
- Data Source: [API endpoint, data structure, filtering requirements]
- User Interactions: [sorting, filtering, pagination, row selection]
- Custom Features: [inline editing, bulk operations, export functionality]
- Performance: [large datasets, server-side processing, memory optimization]
- Integration: [CodeIgniter backend, real-time updates, user permissions]

DATATABLES IMPLEMENTATION FRAMEWORK:
1. Configuration: Server-side processing, column definitions, language settings
2. Data Loading: AJAX integration, error handling, loading states
3. User Interface: Custom buttons, responsive design, accessibility
4. Interactions: Row selection, inline editing, bulk operations
5. Performance: Large dataset handling, lazy loading, memory management
6. Security: CSRF tokens, permission-based column visibility
7. Real-time: Auto-refresh, live updates, WebSocket integration
8. Export: CSV, Excel, PDF export with custom formatting
```

#### **Bug Fix Prompts**
```
BUG FIX REQUEST:
- Issue: [specific problem description]
- Environment: [dev/qa/prod]
- Error Details: [error messages, stack traces]
- Expected Behavior: [what should happen]
- Current Behavior: [what actually happens]

DEBUGGING APPROACH:
1. Reproduce: Identify exact steps to reproduce the issue
2. Isolate: Determine if it's frontend, backend, database, or integration
3. Analyze: Check logs, database state, and code execution flow
4. Fix: Apply minimal, targeted fix following CodeIgniter 3 patterns
5. Test: Verify fix works and doesn't break existing functionality
6. Deploy: Follow deployment protocol with proper testing
```

#### **Feature Development Prompts**
```
FEATURE DEVELOPMENT REQUEST:
- Feature: [specific feature description]
- Business Value: [why this feature is needed]
- User Stories: [who will use it and how]
- Technical Requirements: [performance, security, integration needs]
- Dependencies: [existing code, database changes, external APIs]

DEVELOPMENT APPROACH:
1. Architecture: Design following CodeIgniter 3 MVC patterns
2. Database: Design tables, relationships, and migration scripts
3. Security: Implement proper validation, authentication, and authorization
4. API: Create REST endpoints if needed for external integration
5. Testing: Unit tests, integration tests, and user acceptance testing
6. Documentation: Update API docs, user guides, and technical specs
```

### **3. Error Handling Prompts**

#### **Database Error Analysis**
```
DATABASE ERROR ANALYSIS:
- Error Type: [SQL error, connection error, constraint violation]
- Query: [the problematic SQL query]
- Context: [what operation was being performed]
- Data State: [relevant table states and relationships]

RESOLUTION STRATEGY:
1. Query Analysis: Check syntax, table relationships, and data types
2. Constraint Check: Verify foreign keys, unique constraints, and data integrity
3. Transaction Review: Ensure proper transaction handling and rollback
4. Performance Check: Analyze query execution plan and optimization opportunities
5. Data Validation: Ensure input data meets database requirements
6. Error Handling: Implement proper error logging and user feedback
```

#### **Integration Error Analysis**
```
INTEGRATION ERROR ANALYSIS:
- System: [NetSuite, S3, external API]
- Error Type: [authentication, data format, network, timeout]
- Payload: [data being sent/received]
- Context: [what operation was being performed]

RESOLUTION STRATEGY:
1. Authentication: Verify API keys, tokens, and credentials
2. Data Format: Check payload structure and field mappings
3. Network: Test connectivity and timeout settings
4. Rate Limiting: Check API rate limits and retry logic
5. Error Handling: Implement proper error logging and retry mechanisms
6. Fallback: Design graceful degradation for integration failures
```

---

## 🔧 **DEVELOPMENT WORKFLOW PATTERNS**

### **1. Code Review Checklist**

#### **Security Review**
- [ ] Input validation using `$this->form_validation->set_rules()`
- [ ] SQL injection prevention with `$this->db->escape()` or prepared statements
- [ ] XSS protection with `$this->security->xss_clean()` or global filtering
- [ ] CSRF protection with form tokens
- [ ] Authentication and authorization checks
- [ ] File upload security (type, size, malware scanning)
- [ ] Session security configuration

#### **Code Quality Review**
- [ ] Follows CodeIgniter 3 MVC patterns
- [ ] Proper error handling and logging
- [ ] Database transaction management
- [ ] Performance optimization (query efficiency, caching)
- [ ] Code documentation and comments
- [ ] Consistent naming conventions
- [ ] No hardcoded values or credentials

#### **Integration Review**
- [ ] API endpoint design follows REST conventions
- [ ] Proper HTTP status codes and error responses
- [ ] Data validation and sanitization
- [ ] Rate limiting and authentication
- [ ] Error handling and logging
- [ ] Documentation and examples

### **2. Testing Strategies**

#### **Unit Testing**
```php
// Example test structure
class Product_model_test extends PHPUnit_Framework_TestCase
{
    public function test_get_products_with_filters()
    {
        // Arrange
        $filters = ['vendor_id' => 1, 'status_id' => [1, 2]];
        
        // Act
        $result = $this->model->get_products($filters);
        
        // Assert
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('product_name', $result[0]);
    }
}
```

#### **Integration Testing**
```php
// API endpoint testing
public function test_restock_api_endpoint()
{
    $response = $this->request('POST', 'api/restock/save', [
        'order_id' => 123,
        'quantity' => 10,
        'status' => 'completed'
    ]);
    
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertJson($response->getBody());
}
```

### **3. Deployment Protocol**

#### **Pre-Deployment Checklist**
- [ ] Code review completed and approved
- [ ] All tests passing (unit, integration, security)
- [ ] Database migrations tested
- [ ] Security scan completed
- [ ] Performance testing completed
- [ ] Documentation updated
- [ ] Rollback plan prepared

#### **Deployment Steps**
1. **Backup**: Create database and file backups
2. **Deploy**: Upload code to staging environment
3. **Test**: Run smoke tests and integration tests
4. **Migrate**: Run database migrations if needed
5. **Verify**: Check all critical functionality
6. **Monitor**: Watch logs and performance metrics
7. **Rollback**: Be prepared to rollback if issues arise

---

## 📊 **PERFORMANCE OPTIMIZATION PATTERNS**

### **1. Database Optimization**

#### **Critical Query Patterns**

**Master Export Query (NetSuite Integration)**
```php
// MANDATORY query pattern for NetSuite integration
public function get_master_export_data()
{
    $sql = "SELECT DISTINCT
        i.id as item_id,
        i.code as item_code,
        i.vendor_code,
        i.vendor_color,
        p.id as product_id,
        p.name as product_name,
        p.width,
        v.id as vendor_id,
        v.name as vendor_name,
        m.netsuite_vendor_id,
        m.netsuite_vendor_name,
        pvar.vendor_product_name,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as color_name
    FROM T_ITEM i
    JOIN T_PRODUCT p ON i.product_id = p.id
    JOIN T_PRODUCT_VENDOR pv ON p.id = pv.product_id
    JOIN Z_VENDOR v ON pv.vendor_id = v.id
    JOIN opms_netsuite_vendor_mapping m ON v.id = m.opms_vendor_id
    LEFT JOIN T_PRODUCT_VARIOUS pvar ON p.id = pvar.product_id
    LEFT JOIN T_ITEM_COLOR ic ON i.id = ic.item_id
    LEFT JOIN P_COLOR c ON ic.color_id = c.id
    WHERE i.code IS NOT NULL
      AND p.name IS NOT NULL
      AND v.name IS NOT NULL
      AND i.archived = 'N'
      AND p.archived = 'N'
      AND v.active = 'Y'
      AND v.archived = 'N'
      AND m.opms_vendor_name = m.netsuite_vendor_name
    GROUP BY i.id, i.code, i.vendor_code, i.vendor_color, p.id, p.name, p.width, v.id, v.name, m.netsuite_vendor_id, m.netsuite_vendor_name, pvar.vendor_product_name
    HAVING color_name IS NOT NULL";
    
    return $this->db->query($sql)->result_array();
}
```

**Mini-Forms Data Extraction**
```php
// Front Content with percentages
public function get_front_content_data($product_id)
{
    $sql = "SELECT 
        pcf.perc,
        pc.name as content_name,
        pcfd.content as front_description
    FROM T_PRODUCT_CONTENT_FRONT pcf
    JOIN P_CONTENT pc ON pcf.content_id = pc.id
    LEFT JOIN T_PRODUCT_CONTENT_FRONT_DESCR pcfd ON pcf.product_id = pcfd.product_id
    WHERE pcf.product_id = ?
    ORDER BY pcf.perc DESC";
    
    return $this->db->query($sql, [$product_id])->result_array();
}

// Abrasion Tests with files
public function get_abrasion_data($product_id)
{
    $sql = "SELECT 
        pa.n_rubs,
        pat.name as test_name,
        pal.name as limit_name,
        GROUP_CONCAT(paf.url_dir) as file_urls
    FROM T_PRODUCT_ABRASION pa
    JOIN P_ABRASION_TEST pat ON pa.abrasion_test_id = pat.id
    JOIN P_ABRASION_LIMIT pal ON pa.abrasion_limit_id = pal.id
    LEFT JOIN T_PRODUCT_ABRASION_FILES paf ON pa.id = paf.abrasion_id
    WHERE pa.product_id = ? AND pa.visible = 'Y'
    GROUP BY pa.id";
    
    return $this->db->query($sql, [$product_id])->result_array();
}
```

**Data Quality Constraints (MANDATORY)**
```php
// MANDATORY validation for all OPMS fields
public function validate_opms_field($field_name, $field_data)
{
    if ($field_data === null) {
        log_message('error', "OPMS field '{$field_name}' query failed or field not accessible");
        return 'query_failed';
    } elseif ($field_data === '' || (is_array($field_data) && empty($field_data))) {
        log_message('info', "OPMS field '{$field_name}': src empty data");
        return 'src_empty_data';
    } else {
        return 'has_data';
    }
}

// NetSuite value conversion with validation
public function get_netsuite_value($field_data, $validation_status, $field_name, $item_id)
{
    if ($validation_status === 'src_empty_data') {
        return 'src empty data';  // Shows in NetSuite UI
    } elseif ($validation_status === 'query_failed') {
        // Log detailed error for developer notification
        log_message('error', "IMPORT ERROR: OPMS field '{$field_name}' query failed for item {$item_id}", [
            'itemId' => $item_id,
            'fieldName' => $field_name,
            'severity' => 'medium',
            'action' => 'continue_import',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return null;  // Shows as blank in NetSuite UI
    } else {
        return $field_data;
    }
}

// ALWAYS include these WHERE conditions in all queries
public function apply_mandatory_filters()
{
    $this->db->where('i.archived', 'N');           // Active items only
    $this->db->where('p.archived', 'N');           // Active products only
    $this->db->where('v.active', 'Y');             // Active vendors only
    $this->db->where('v.archived', 'N');           // Non-archived vendors
    $this->db->where('i.code IS NOT NULL');        // Valid item codes required
    $this->db->where('p.name IS NOT NULL');        // Valid product names required
    $this->db->where('v.name IS NOT NULL');        // Valid vendor names required
}
```

#### **Query Optimization**
```php
// Efficient product search with proper indexing
public function get_products_optimized($filters)
{
    $this->db->select('P.id, P.name, P.width, V.abrev, V.name as vendor_name');
    $this->db->from('T_PRODUCT P');
    $this->db->join('T_PRODUCT_VENDOR PV', 'P.id = PV.product_id');
    $this->db->join('Z_VENDOR V', 'PV.vendor_id = V.id');
    $this->db->where('P.archived', 'N');
    $this->db->where('V.active', 'Y');
    
    // Apply filters efficiently
    if (!empty($filters['vendor_id'])) {
        $this->db->where_in('V.id', $filters['vendor_id']);
    }
    
    return $this->db->get()->result_array();
}
```

#### **Caching Strategies**
```php
// Implement caching for expensive operations
public function get_products_cached($filters)
{
    $cache_key = 'products_' . md5(serialize($filters));
    
    if (!$result = $this->cache->get($cache_key)) {
        $result = $this->get_products_optimized($filters);
        $this->cache->save($cache_key, $result, 300); // 5 minutes
    }
    
    return $result;
}
```

### **2. Memory Management**

#### **Large Dataset Handling**
```php
// Process large datasets in chunks
public function process_large_dataset($table, $batch_size = 1000)
{
    $offset = 0;
    
    do {
        $query = $this->db->get($table, $batch_size, $offset);
        $results = $query->result_array();
        
        foreach ($results as $row) {
            // Process each row
            $this->process_row($row);
        }
        
        $offset += $batch_size;
        
    } while (count($results) == $batch_size);
}
```

---

## 🔒 **SECURITY IMPLEMENTATION PATTERNS**

### **1. Input Validation**

#### **Form Validation**
```php
// Comprehensive form validation
public function validate_product_form()
{
    $this->form_validation->set_rules('product_name', 'Product Name', 'required|trim|xss_clean|max_length[255]');
    $this->form_validation->set_rules('width', 'Width', 'required|numeric|greater_than[0]');
    $this->form_validation->set_rules('vendor_id', 'Vendor', 'required|integer|is_natural');
    $this->form_validation->set_rules('colors[]', 'Colors', 'required|is_array');
    
    return $this->form_validation->run();
}
```

#### **API Input Validation**
```php
// REST API input validation
public function validate_api_input($data, $rules)
{
    foreach ($rules as $field => $rule) {
        if (!isset($data[$field])) {
            $this->response(['error' => "Field {$field} is required"], 400);
            return false;
        }
        
        if (!$this->validate_field($data[$field], $rule)) {
            $this->response(['error' => "Field {$field} is invalid"], 400);
            return false;
        }
    }
    
    return true;
}
```

### **2. SQL Injection Prevention**

#### **Prepared Statements**
```php
// Use prepared statements for complex queries
public function get_products_by_vendor($vendor_id)
{
    $sql = "SELECT * FROM T_PRODUCT P 
            JOIN T_PRODUCT_VENDOR PV ON P.id = PV.product_id 
            WHERE PV.vendor_id = ? AND P.archived = 'N'";
    
    return $this->db->query($sql, [$vendor_id])->result_array();
}
```

#### **Active Record Security**
```php
// Safe Active Record usage
public function update_product($id, $data)
{
    $this->db->where('id', $id);
    $this->db->update('T_PRODUCT', $data);
    
    return $this->db->affected_rows() > 0;
}
```

---

## 🚀 **INTEGRATION PATTERNS**

### **1. NetSuite Integration**

#### **Complete Field Mapping Specification**

**Core NetSuite Fields**
```php
// Transform OPMS data to NetSuite format with complete field mapping
public function transform_to_netsuite($opms_data)
{
    return [
        // Native NetSuite Fields
        'itemid' => $opms_data['item_code'],                    // T_ITEM.code
        'displayname' => $opms_data['product_name'] . ': ' . $opms_data['color_name'], // T_PRODUCT.name + colors
        'vendor' => $opms_data['netsuite_vendor_id'],          // opms_netsuite_vendor_mapping.netsuite_vendor_id
        
        // OPMS Custom Fields (20+ fields)
        'custitem_opms_prod_id' => $opms_data['product_id'],   // T_PRODUCT.id (REQUIRED)
        'custitem_opms_item_id' => $opms_data['item_id'],      // T_ITEM.id (REQUIRED)
        'custitem_opms_fabric_width' => $opms_data['width'],   // T_PRODUCT.width
        'custitem_opms_vendor_color' => $opms_data['vendor_color'], // T_ITEM.vendor_color
        'custitem_opms_vendor_prod_name' => $opms_data['vendor_product_name'], // T_PRODUCT_VARIOUS.vendor_product_name
        'custitem_opms_item_colors' => $opms_data['color_name'], // GROUP_CONCAT(P_COLOR.name)
        'custitem_opms_parent_product_name' => $opms_data['product_name'], // T_PRODUCT.name
        
        // Mini-Forms Rich Text Fields
        'custitem_opms_front_content' => $opms_data['front_content_html'], // T_PRODUCT_CONTENT_FRONT + HTML
        'custitem_opms_back_content' => $opms_data['back_content_html'],   // T_PRODUCT_CONTENT_BACK + HTML
        'custitem_opms_abrasion' => $opms_data['abrasion_html'],           // T_PRODUCT_ABRASION + HTML
        'custitem_opms_firecodes' => $opms_data['firecodes_html'],         // T_PRODUCT_FIRECODE + HTML
    ];
}
```

**ItemVendor Sublist Population (CRITICAL)**
```php
// MANDATORY: NetSuite "Multiple Vendors" feature must be enabled
public function populate_itemvendor_sublist($inventory_item, $request_body)
{
    if ($request_body['vendor']) {
        $vendor_id = intval($request_body['vendor']);
        if (!is_nan($vendor_id) && $vendor_id > 0) {
            try {
                // Add line to itemvendor sublist
                $inventory_item->selectNewLine(['sublistId' => 'itemvendor']);
                
                // Set vendor ID (required field)
                $inventory_item->setCurrentSublistValue([
                    'sublistId' => 'itemvendor',
                    'fieldId' => 'vendor',
                    'value' => $vendor_id
                ]);
                
                // Set vendor code if provided (supports "src empty data")
                if ($request_body['vendorcode']) {
                    $inventory_item->setCurrentSublistValue([
                        'sublistId' => 'itemvendor',
                        'fieldId' => 'vendorcode',
                        'value' => $request_body['vendorcode']
                    ]);
                }
                
                // Set as preferred vendor
                $inventory_item->setCurrentSublistValue([
                    'sublistId' => 'itemvendor',
                    'fieldId' => 'preferredvendor',
                    'value' => true
                ]);
                
                // Commit the sublist line
                $inventory_item->commitLine(['sublistId' => 'itemvendor']);
                
            } catch (Exception $sublist_error) {
                log_message('error', "CRITICAL: ItemVendor sublist population failed for vendor {$vendor_id}", [
                    'vendorId' => $vendor_id,
                    'itemId' => $request_body['itemId'],
                    'error' => $sublist_error->getMessage(),
                    'possibleCauses' => [
                        'Multiple Vendors feature not enabled in NetSuite',
                        'Invalid vendor ID',
                        'Vendor record does not exist',
                        'Permission issue with itemvendor sublist'
                    ]
                ]);
                throw $sublist_error;
            }
        }
    }
}
```

**Display Name Convention (MANDATORY)**
```php
// MANDATORY: Display name format with colon separator
public function generate_display_name($product_name, $color_name)
{
    // CORRECT format: "Product Name: Color Name"
    return $product_name . ': ' . $color_name;
    
    // WRONG formats to avoid:
    // return $product_name . ' - ' . $color_name;  // Uses dash instead of colon
    // return $product_name . '_' . $color_name;    // Uses underscore instead of colon
}
```

**Field Validation with "src empty data" Logic**
```php
// MANDATORY validation for all OPMS fields
public function validate_and_transform_field($field_name, $field_data, $item_id)
{
    $validation_status = $this->validate_opms_field($field_name, $field_data);
    
    if ($validation_status === 'src_empty_data') {
        return 'src empty data';  // Shows in NetSuite UI
    } elseif ($validation_status === 'query_failed') {
        // Log detailed error for developer notification
        log_message('error', "IMPORT ERROR: OPMS field '{$field_name}' query failed for item {$item_id}", [
            'itemId' => $item_id,
            'fieldName' => $field_name,
            'opmsItemCode' => $this->get_item_code($item_id),
            'opmsProductName' => $this->get_product_name($item_id),
            'opmsVendorName' => $this->get_vendor_name($item_id),
            'severity' => 'medium',
            'action' => 'continue_import',
            'timestamp' => date('Y-m-d H:i:s'),
            'sqlQuery' => 'Check OPMS database connectivity and field accessibility',
            'possibleCauses' => [
                'OPMS database connection issue',
                'Field renamed or removed in OPMS schema',
                'Permission issue accessing OPMS table',
                'Network connectivity problem'
            ],
            'recommendedActions' => [
                'Check OPMS database connection',
                'Verify field exists in OPMS schema',
                'Review OPMS database permissions',
                'Monitor field failure rate for patterns'
            ]
        ]);
        
        return null;  // Shows as blank in NetSuite UI
    } else {
        return $field_data;  // Contains actual data
    }
}
```

#### **Error Handling**
```php
// Robust NetSuite integration error handling
public function sync_to_netsuite($data)
{
    try {
        $payload = $this->transform_to_netsuite($data);
        $response = $this->netsuite_client->create_inventory_item($payload);
        
        if ($response['success']) {
            $this->log_success($data['item_id'], $response);
            return true;
        } else {
            $this->log_error($data['item_id'], $response['error']);
            return false;
        }
        
    } catch (Exception $e) {
        $this->log_exception($data['item_id'], $e);
        return false;
    }
}
```

### **2. S3 File Upload**

#### **Secure File Upload**
```php
// Secure S3 file upload with validation
public function upload_to_s3($file, $folder = 'temp')
{
    // Validate file type and size
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['extension'], $allowed_types)) {
        throw new Exception('Invalid file type');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File too large');
    }
    
    // Generate secure filename
    $filename = uniqid() . '_' . $file['name'];
    $path = $folder . '/' . $filename;
    
    // Upload to S3
    return $this->s3_client->upload($path, $file['tmp_name']);
}
```

---

## 📈 **MONITORING & LOGGING PATTERNS**

### **1. Application Logging**

#### **Structured Logging**
```php
// Comprehensive logging for debugging and monitoring
public function log_operation($operation, $data, $level = 'info')
{
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'operation' => $operation,
        'user_id' => $this->session->userdata('user_id'),
        'ip_address' => $this->input->ip_address(),
        'data' => $data,
        'level' => $level
    ];
    
    log_message($level, json_encode($log_data));
}
```

#### **Error Tracking**
```php
// Detailed error tracking for production issues
public function log_error($context, $error, $data = [])
{
    $error_data = [
        'context' => $context,
        'error' => $error,
        'data' => $data,
        'user_id' => $this->session->userdata('user_id'),
        'timestamp' => date('Y-m-d H:i:s'),
        'stack_trace' => debug_backtrace()
    ];
    
    log_message('error', json_encode($error_data));
}
```

### **2. Performance Monitoring**

#### **Query Performance Tracking**
```php
// Track slow queries for optimization
public function track_query_performance($query, $execution_time)
{
    if ($execution_time > 1.0) { // Log queries over 1 second
        $this->log_operation('slow_query', [
            'query' => $query,
            'execution_time' => $execution_time
        ], 'warning');
    }
}
```

---

## 🎯 **PROMPT ENGINEERING BEST PRACTICES**

### **1. Context Injection Strategies**

#### **System-Level Context**
- Always include framework version and PHP version
- Specify the business domain (fabric/textile management)
- Include security requirements and compliance needs
- Mention integration points (NetSuite, S3, external APIs)

#### **Request-Level Context**
- Include specific file paths and line numbers when relevant
- Provide error messages and stack traces for debugging
- Include user roles and permissions for security context
- Specify environment (dev/qa/prod) for appropriate responses

### **2. Response Formatting**

#### **Code Examples**
- Always include complete, runnable code examples
- Use proper CodeIgniter 3 syntax and conventions
- Include error handling and validation
- Add comments explaining complex logic

#### **Documentation**
- Provide clear explanations of what the code does
- Include security considerations and best practices
- Mention performance implications and optimization opportunities
- Include testing strategies and deployment considerations

### **3. Error Prevention**

#### **Validation Prompts**
- Always ask for clarification when requirements are ambiguous
- Suggest alternative approaches when the requested solution has security risks
- Include warnings about potential side effects or breaking changes
- Recommend testing strategies before implementing changes

#### **Safety Checks**
- Verify that proposed changes follow security best practices
- Ensure database operations use proper transaction handling
- Check that API endpoints include proper authentication and validation
- Confirm that file operations include security measures

---

## 📚 **REFERENCE MATERIALS**

### **CodeIgniter 3 Documentation**
- [Official CodeIgniter 3 User Guide](https://codeigniter.com/userguide3/)
- [CodeIgniter 3 Database Class](https://codeigniter.com/userguide3/database/)
- [CodeIgniter 3 Security](https://codeigniter.com/userguide3/libraries/security.html)

### **PHP 7.3 Documentation**
- [PHP 7.3 Manual](https://www.php.net/manual/en/migration73.php)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

### **OPMS-Specific Documentation**
- **OPMS Database Schema Specification** (`opms-database-spec.md`) - Complete database schema with 150+ tables
- **NetSuite Inventory Item Import Specification** - Field mappings and integration patterns
- **API Endpoint Documentation** - REST API patterns and authentication
- **Security Implementation Guide** - CodeIgniter 3 security best practices

---

## 🔄 **VERSION HISTORY**

### **v1.2.0 - JavaScript & Frontend Expertise Enhancement**
- **Date**: January 15, 2025
- **Status**: Production Ready
- **Features**:
  - Added comprehensive JavaScript ES6+ expertise and patterns
  - Integrated jQuery 3.x mastery with DOM manipulation and AJAX
  - Added DataTables expertise with server-side processing and custom features
  - Enhanced UI/UX integration with Bootstrap 4/5 and responsive design
  - Added modern JavaScript patterns: async/await, modules, event handling
  - Integrated file upload patterns with S3 integration and progress tracking
  - Added real-time update patterns with WebSocket and AJAX polling
  - Enhanced form handling with validation, submission, and error management
  - Updated prompt engineering with JavaScript-specific templates
  - Added comprehensive frontend security patterns and best practices
- **Coverage**: Complete frontend expertise with backend integration
- **New Capabilities**:
  - Interactive data tables with advanced functionality
  - Real-time user interface updates
  - Modern JavaScript development patterns
  - Responsive and accessible user interfaces
  - Advanced file upload and management
  - Comprehensive form handling and validation

### **v1.1.0 - Database Integration Update**
- **Date**: January 15, 2025
- **Status**: Production Ready
- **Features**: 
  - Integrated comprehensive OPMS database schema (150+ tables)
  - Added detailed table specifications and relationships
  - Integrated mini-forms and rich content processing patterns
  - Added critical query patterns and data quality constraints
  - Updated NetSuite integration with complete field mappings
  - Added "src empty data" validation logic
- **Coverage**: Complete database integration with AI model patterns

### **v1.0.0 - Initial Release**
- **Date**: January 15, 2025
- **Status**: Production Ready
- **Features**: Complete AI Model Specification for OPMS CodeIgniter 3 system
- **Coverage**: All major components, patterns, and prompt engineering strategies

---

## 📝 **MAINTENANCE NOTES**

### **Regular Updates Required**
- Monitor CodeIgniter 3 security updates and PHP 7.3 patches
- Update NetSuite API integration patterns as needed
- Review and update security patterns based on new threats
- Maintain prompt engineering strategies based on AI model improvements

### **Performance Monitoring**
- Track query performance and optimize as needed
- Monitor memory usage patterns for large datasets
- Review caching strategies and update as appropriate
- Monitor API response times and optimize integration points

---

**End of OPMS AI Model Specification**

*This document serves as the definitive guide for AI assistance with the OPMS CodeIgniter 3 system. It should be updated regularly to reflect system changes and improvements.*

# Web Visibility AI Model Specification
## CodeIgniter 3 Expert System - Web Visibility Logic Implementation

**Version:** 1.1.0  
**Date:** January 15, 2025  
**Target Framework:** CodeIgniter 3.1.11 + PHP 7.3  
**Feature:** Web Visibility Logic Flow Management  
**Application:** OPMS (Opuzen Product Management System)  
**Status:** ⚠️ THEORETICAL SPECIFICATION (Not fully implemented)

---

## 🚨 **IMPORTANT NOTICE**

**This document contains theoretical and planned features that are NOT fully implemented in production.**

**For the ACTUAL working implementation, see:**
- **`docs/ai-specs/Web-Visibility-ACTUAL-IMPLEMENTATION.md`** - Current production code documentation

**Key Differences:**
- This spec uses `T_PRODUCT.web_visibility` → Actual uses `SHOWCASE_PRODUCT.visible`
- This spec includes audit logging → Not implemented
- This spec includes file verification methods → Not implemented
- This spec uses complex validation helpers → Actual uses simpler inline logic

---

## 📚 **PREREQUISITE READING REQUIRED**

**⚠️ MANDATORY: This specification REQUIRES reading the main OPMS AI Model Specification first.**

**Before implementing or working with this Web Visibility feature, you MUST read:**
- **`docs/ai-specs/OPMS-AI-Model-Specification.md`** - Complete OPMS system architecture, patterns, and prompt engineering strategies

**Why This Prerequisite is Critical:**
- **System Context**: Web Visibility is a feature within the larger OPMS system architecture
- **Framework Patterns**: CodeIgniter 3 patterns, security practices, and database operations
- **Business Domain**: Understanding of fabric/textile management, vendor relationships, and inventory tracking
- **Integration Points**: How Web Visibility integrates with existing Product, Item, and Vendor management
- **Security Requirements**: OPMS-specific security patterns and validation requirements
- **Performance Considerations**: System-wide performance patterns and optimization strategies

**This Web Visibility specification builds upon and extends the core OPMS AI Model Specification.**

---

## 🎯 **EXECUTIVE SUMMARY**

This AI Model Specification defines the complete technical implementation, patterns, and prompt engineering strategies for the Web Visibility Logic Flow feature in the OPMS CodeIgniter 3 system. The feature manages web visibility at both Product (Parent) and Colorline (Child Item) levels with automatic logic, manual override capabilities, lazy calculation, and UI/UX considerations.

### **Core Feature Requirements**
- **Product-Level Web Visibility**: Beauty shot dependency with checkbox enable/disable logic
- **Colorline-Level Web Visibility**: Automatic determination based on parent product and status
- **Manual Override System**: Toggle-based manual control for colorline visibility
- **Lazy Calculation**: Automatic web visibility calculation during colorline listing display
- **Database Schema Updates**: New columns in T_ITEM table for visibility state storage
- **UI/UX Integration**: Eye icon reflection (always database-driven), form controls, and state persistence

### **Calculation Triggers**
- **Explicit Editing**: Product Edit Form and Item Edit Forms (immediate calculation and storage)
- **Lazy Calculation**: Colorline Listing (calculate and store NULL values during display)
- **Visual Indicators**: Blue eye icons always reflect stored `T_ITEM.web_vis` database values

---

## 🏗️ **TECHNICAL ARCHITECTURE OVERVIEW**

### **Database Schema (ACTUAL IMPLEMENTATION)**

#### **T_ITEM Table (Item/Colorline Level)**
```sql
-- Existing columns used for web visibility
web_vis         TINYINT(1) NULL DEFAULT NULL     -- Final visibility state (NULL = needs calculation, 0 = hidden, 1 = visible)
web_vis_toggle  TINYINT(1) NOT NULL DEFAULT 0    -- Manual override flag (0 = auto-calculated, 1 = manual)
web_vis2        INT NOT NULL DEFAULT 0           -- Legacy/unused
web_vis_checkbox TINYINT(1) NULL DEFAULT 0       -- Legacy/unused

-- Indexes
KEY idx_web_vis (web_vis)
KEY idx_web_vis_toggle (web_vis_toggle)
```

#### **SHOWCASE_PRODUCT Table (Product/Parent Level)**
```sql
-- Existing table structure - NO CHANGES NEEDED
product_id   INT NOT NULL PRIMARY KEY
product_type VARCHAR(2) NOT NULL
visible      CHAR(1) NOT NULL              -- Parent web visibility ('Y' = visible, 'N' = hidden)
pic_big_url  VARCHAR(150) NULL             -- Beauty shot URL (enables visibility checkbox)
url_title    VARCHAR(100) NOT NULL
descr        TEXT NULL
date_modif   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
user_id      INT NOT NULL DEFAULT 0
```

#### **Notes on Implementation**
- ✅ Uses existing `SHOWCASE_PRODUCT.visible` (CHAR) instead of creating new BOOLEAN column
- ✅ Uses existing `SHOWCASE_PRODUCT.pic_big_url` for beauty shot dependency
- ✅ T_ITEM columns already exist - no migration needed
- ❌ Audit logging table NOT implemented (future enhancement)

### **Core Logic Components**
```
Web Visibility System
├── Calculation Triggers
│   ├── Explicit Editing (Product/Item Edit Forms)
│   └── Lazy Calculation (Colorline Listing)
├── Product Level Logic
│   ├── Beauty Shot Dependency Check
│   ├── Checkbox Enable/Disable Logic
│   └── Value Forcing (FALSE when no beauty shot)
├── Colorline Level Logic
│   ├── Auto-Determined Visibility
│   ├── Manual Override Logic
│   ├── Status-Based Filtering
│   └── Lazy Calculation for NULL Values
├── Database Integration
│   ├── T_ITEM.web_vis (Final computed visibility, can be NULL)
│   ├── T_ITEM.web_vis_toggle (Manual override state)
│   ├── Explicit Updates on Save
│   └── Lazy Updates on Listing Display
└── UI/UX Integration
    ├── Eye Icon State Reflection (Always Database-Driven)
    ├── Form Control States
    └── Real-time State Updates
```

---

## 🤖 **AI MODEL CORE CAPABILITIES**

### **1. Web Visibility Logic Expertise**

#### **Product-Level Logic Patterns**

**Beauty Shot Dependency Check (ACTUAL IMPLEMENTATION)**
```php
// Simple beauty shot check using SHOWCASE_PRODUCT.pic_big_url
// In Product Edit Form view (application/views/product/form/form_regular.php):
$has_beauty_shot = !empty($info['pic_big_url']) && $info['pic_big_url'] !== '';
$checkbox_disabled = !$has_beauty_shot ? 'disabled' : '';
$checkbox_checked = (!$isNew && $info['showcase_visible'] === 'Y' && $has_beauty_shot) ? 'checked' : '';

// In Product Controller (application/controllers/Product.php):
$showcase_visible = $this->input->post('showcase_visible');
if (is_null($showcase_visible)) {
    $showcase_visible = '0';  // Unchecked checkbox not submitted
}
$has_beauty_shot = !is_null($this->input->post('pic_big_delete')) || strlen($new_location_db) > 0;

// Force visibility to FALSE if no beauty shot
if (!$has_beauty_shot) {
    $showcase_visible = '0';
}

// Save to database
$ret = array(
    'product_id' => $product_id,
    'product_type' => $product_type,
    'url_title' => $url_title,
    'descr' => $this->input->post('showcase_descr'),
    'visible' => ($showcase_visible === '1' ? 'Y' : 'N'),  // Convert to CHAR(1)
    'pic_big_url' => $new_location_db,
    'user_id' => $user_id
);
$this->model->save_showcase_basic($ret, $product_id, $this->data['product_type']);
```

// Product Web Visibility State with Comprehensive Logic
public function get_product_web_visibility_state($product_id)
{
    // Validate product exists and is active
    if (!$this->validate_product_exists($product_id)) {
        return [
            'enabled' => false,
            'value' => false,
            'reason' => 'Product not found or archived'
        ];
    }
    
    $has_beauty_shot = $this->check_beauty_shot_uploaded($product_id);
    
    if (!$has_beauty_shot) {
        // Force web visibility to FALSE when no beauty shot
        $this->force_product_web_visibility_false($product_id);
        
        return [
            'enabled' => false,
            'value' => false,
            'reason' => 'No beauty shot uploaded - web visibility disabled'
        ];
    }
    
    // Get current checkbox value when beauty shot exists
    $this->db->select('web_visibility, name');
    $this->db->from('T_PRODUCT');
    $this->db->where('id', $product_id);
    $this->db->where('archived', 'N');
    
    $result = $this->db->get()->row();
    
    if (!$result) {
        return [
            'enabled' => false,
            'value' => false,
            'reason' => 'Product data not found'
        ];
    }
    
    return [
        'enabled' => true,
        'value' => (bool) $result->web_visibility,
        'reason' => 'Beauty shot exists - web visibility enabled',
        'product_name' => $result->name
    ];
}

// Force Product Web Visibility to FALSE (No Beauty Shot)
private function force_product_web_visibility_false($product_id)
{
    $this->db->where('id', $product_id);
    $this->db->update('T_PRODUCT', [
        'web_visibility' => 0,
        'date_modif' => date('Y-m-d H:i:s')
    ]);
    
    // Log the forced change
    $this->log_web_visibility_change($product_id, null, [
        'web_visibility' => 0,
        'reason' => 'No beauty shot - forced to FALSE'
    ], 'explicit');
}

// Validate Product Exists and is Active
private function validate_product_exists($product_id)
{
    $this->db->select('id');
    $this->db->from('T_PRODUCT');
    $this->db->where('id', $product_id);
    $this->db->where('archived', 'N');
    
    return $this->db->get()->num_rows() > 0;
}
```

#### **Colorline-Level Logic Patterns**

**Auto-Determined Visibility Logic (OPMS-Specific)**
```php
// Auto-Determined Visibility Logic with OPMS Status Validation
public function calculate_auto_visibility($item_id, $product_id, $product_status)
{
    // Validate item exists and is active
    if (!$this->validate_item_exists($item_id)) {
        return false;
    }
    
    // Get product web visibility state
    $product_visibility = $this->get_product_web_visibility_state($product_id);
    
    // Check if product web visibility is enabled
    if (!$product_visibility['enabled'] || !$product_visibility['value']) {
        return false;
    }
    
    // Validate status is in allowed auto-visibility statuses
    if (!$this->is_valid_auto_visibility_status($product_status)) {
        return false;
    }
    
    // Check manual override is disabled
    $manual_override = $this->get_manual_override_state($item_id);
    if ($manual_override) {
        return false; // Manual override takes precedence
    }
    
    // All conditions met for auto-visibility
    return true;
}

// Manual Override Logic with Comprehensive Validation
public function calculate_manual_visibility($item_id, $product_id, $checkbox_value)
{
    // Validate item exists and is active
    if (!$this->validate_item_exists($item_id)) {
        return false;
    }
    
    // Get product web visibility state
    $product_visibility = $this->get_product_web_visibility_state($product_id);
    
    // Check if product web visibility is enabled
    if (!$product_visibility['enabled'] || !$product_visibility['value']) {
        return false;
    }
    
    // Check manual override is enabled
    $manual_override = $this->get_manual_override_state($item_id);
    if (!$manual_override) {
        return false; // Manual override must be enabled
    }
    
    // Return checkbox value (user's manual choice)
    return (bool) $checkbox_value;
}

// Final Web Visibility Calculation with Comprehensive Logic
public function calculate_final_web_visibility($item_id, $product_id, $product_status, $manual_override, $checkbox_value = null)
{
    // Validate all required parameters
    if (!$this->validate_calculation_parameters($item_id, $product_id, $product_status)) {
        return false;
    }
    
    // Get current manual override state from database
    $manual_override_state = $this->get_manual_override_state($item_id);
    
    if ($manual_override_state) {
        // Manual Override Logic
        $result = $this->calculate_manual_visibility($item_id, $product_id, $checkbox_value);
        $calculation_method = 'manual_override';
    } else {
        // Auto-Determined Visibility Logic
        $result = $this->calculate_auto_visibility($item_id, $product_id, $product_status);
        $calculation_method = 'auto_determined';
    }
    
    // Log the calculation for audit purposes
    $this->log_visibility_calculation($item_id, $product_id, $result, $calculation_method, [
        'product_status' => $product_status,
        'manual_override' => $manual_override_state,
        'checkbox_value' => $checkbox_value
    ]);
    
    return $result;
}

// Validate Calculation Parameters
private function validate_calculation_parameters($item_id, $product_id, $product_status)
{
    // Validate item ID
    if (!is_numeric($item_id) || $item_id <= 0) {
        log_message('error', "Invalid item_id for web visibility calculation: {$item_id}");
        return false;
    }
    
    // Validate product ID
    if (!is_numeric($product_id) || $product_id <= 0) {
        log_message('error', "Invalid product_id for web visibility calculation: {$product_id}");
        return false;
    }
    
    // Validate status is not empty
    if (empty($product_status)) {
        log_message('error', "Empty product_status for web visibility calculation: item_id={$item_id}");
        return false;
    }
    
    return true;
}

// Validate Item Exists and is Active
private function validate_item_exists($item_id)
{
    $this->db->select('id, product_id, archived');
    $this->db->from('T_ITEM');
    $this->db->where('id', $item_id);
    $this->db->where('archived', 'N');
    
    return $this->db->get()->num_rows() > 0;
}

// Get Manual Override State from Database
public function get_manual_override_state($item_id)
{
    $this->db->select('web_vis_toggle');
    $this->db->from('T_ITEM');
    $this->db->where('id', $item_id);
    
    $result = $this->db->get()->row();
    return $result ? (bool) $result->web_vis_toggle : false;
}

// Valid Statuses for Auto-Visibility (OPMS-Specific)
private $valid_auto_visibility_statuses = ['RUN', 'LTDQTY', 'RKFISH'];

// Check if Status is Valid for Auto-Visibility
public function is_valid_auto_visibility_status($status)
{
    return in_array($status, $this->valid_auto_visibility_statuses);
}

// Log Visibility Calculation for Audit
private function log_visibility_calculation($item_id, $product_id, $result, $method, $context)
{
    $log_data = [
        'item_id' => $item_id,
        'product_id' => $product_id,
        'calculated_visibility' => $result ? 1 : 0,
        'calculation_method' => $method,
        'context' => json_encode($context),
        'user_id' => $this->session->userdata('user_id'),
        'ip_address' => $this->input->ip_address(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('web_visibility_calculation_log', $log_data);
}
```

#### **Lazy Calculation Patterns (Performance Optimized)**

**Colorline Listing with Lazy Calculation (OPMS-Optimized)**
```php
// Colorline Listing with Lazy Calculation and Performance Optimization
public function get_colorline_list_with_visibility($filters = [], $limit = 1000, $offset = 0)
{
    $start_time = microtime(true);
    
    // Get colorline items with current web_vis state
    $items = $this->get_colorline_items_optimized($filters, $limit, $offset);
    
    if (empty($items)) {
        return $items;
    }
    
    // Identify items needing lazy calculation (NULL web_vis)
    $items_needing_calculation = [];
    $items_with_visibility = [];
    
    foreach ($items as $item) {
        if (is_null($item['web_vis'])) {
            $items_needing_calculation[] = $item;
        } else {
            $items_with_visibility[] = $item;
        }
    }
    
    // Perform lazy calculation for NULL items
    if (!empty($items_needing_calculation)) {
        $calculated_items = $this->batch_calculate_web_visibility($items_needing_calculation);
        
        // Merge calculated items with existing items
        $items_with_visibility = array_merge($items_with_visibility, $calculated_items);
        
        // Sort by original order if needed
        usort($items_with_visibility, function($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });
    }
    
    // Add eye icon states (always database-driven)
    foreach ($items_with_visibility as &$item) {
        $item['eye_icon_state'] = $item['web_vis'] ? 'visible' : 'hidden';
        $item['eye_icon_class'] = $item['web_vis'] ? 'fa-eye text-primary' : 'fa-eye-slash text-muted';
    }
    
    // Log performance metrics
    $execution_time = microtime(true) - $start_time;
    $this->log_lazy_calculation_performance(count($items), count($items_needing_calculation), $execution_time);
    
    return $items_with_visibility;
}

// Optimized Colorline Items Query
private function get_colorline_items_optimized($filters = [], $limit = 1000, $offset = 0)
{
    $this->db->select('
        i.id, i.code, i.product_id, i.status_id, i.web_vis, i.web_vis_toggle,
        p.name as product_name, p.web_visibility as product_web_visibility,
        s.name as status_name,
        c.name as color_name,
        i.sort_order
    ');
    $this->db->from('T_ITEM i');
    $this->db->join('T_PRODUCT p', 'i.product_id = p.id', 'left');
    $this->db->join('P_PRODUCT_STATUS s', 'i.status_id = s.id', 'left');
    $this->db->join('T_ITEM_COLOR ic', 'i.id = ic.item_id', 'left');
    $this->db->join('P_COLOR c', 'ic.color_id = c.id', 'left');
    
    // Apply mandatory filters
    $this->apply_mandatory_filters();
    
    // Apply custom filters
    if (!empty($filters['product_id'])) {
        $this->db->where('i.product_id', $filters['product_id']);
    }
    if (!empty($filters['status_id'])) {
        $this->db->where('i.status_id', $filters['status_id']);
    }
    if (!empty($filters['color_id'])) {
        $this->db->where('ic.color_id', $filters['color_id']);
    }
    
    // Apply pagination
    $this->db->limit($limit, $offset);
    $this->db->order_by('i.sort_order', 'ASC');
    $this->db->order_by('i.id', 'ASC');
    
    return $this->db->get()->result_array();
}

// Batch Calculate Web Visibility for Multiple Items
private function batch_calculate_web_visibility($items)
{
    if (empty($items)) {
        return [];
    }
    
    $calculated_items = [];
    $items_to_update = [];
    
    foreach ($items as $item) {
        // Calculate web visibility using existing logic
        $calculated_visibility = $this->calculate_web_visibility_for_item($item);
        
        // Update item with calculated value
        $item['web_vis'] = $calculated_visibility;
        $calculated_items[] = $item;
        
        // Prepare for batch update
        $items_to_update[] = [
            'id' => $item['id'],
            'web_vis' => $calculated_visibility ? 1 : 0,
            'date_modif' => date('Y-m-d H:i:s')
        ];
    }
    
    // Batch update calculated values to database
    if (!empty($items_to_update)) {
        $this->batch_update_web_visibility_values($items_to_update);
    }
    
    return $calculated_items;
}

// Calculate Web Visibility for Individual Item (Enhanced)
public function calculate_web_visibility_for_item($item)
{
    // Validate item data
    if (empty($item['id']) || empty($item['product_id'])) {
        return false;
    }
    
    // Get product data if not already loaded
    if (empty($item['product_name']) || empty($item['status_name'])) {
        $item_data = $this->get_item_data_for_calculation($item['id']);
        if (!$item_data) {
            return false;
        }
        $item = array_merge($item, $item_data);
    }
    
    // Check beauty shot dependency
    $beauty_shot_state = $this->get_product_web_visibility_state($item['product_id']);
    
    if (!$beauty_shot_state['value']) {
        return false; // No beauty shot = not visible
    }
    
    // Check manual override
    $manual_override = $this->get_manual_override_state($item['id']);
    
    if ($manual_override) {
        // Manual override logic - use stored checkbox value
        return $this->get_stored_checkbox_value($item['id']);
    } else {
        // Auto-determination logic
        return $this->calculate_auto_visibility(
            $item['id'], 
            $item['product_id'], 
            $item['status_name']
        );
    }
}

// Get Item Data for Calculation (Cached)
private function get_item_data_for_calculation($item_id)
{
    $cache_key = "item_calculation_data_{$item_id}";
    
    if (!$item_data = $this->cache->get($cache_key)) {
        $this->db->select('
            i.product_id, i.status_id, i.web_vis_toggle,
            p.name as product_name, p.web_visibility as product_web_visibility,
            s.name as status_name
        ');
        $this->db->from('T_ITEM i');
        $this->db->join('T_PRODUCT p', 'i.product_id = p.id', 'left');
        $this->db->join('P_PRODUCT_STATUS s', 'i.status_id = s.id', 'left');
        $this->db->where('i.id', $item_id);
        $this->db->where('i.archived', 'N');
        
        $item_data = $this->db->get()->row_array();
        
        // Cache for 5 minutes
        $this->cache->save($cache_key, $item_data, 300);
    }
    
    return $item_data;
}

// Log Lazy Calculation Performance
private function log_lazy_calculation_performance($total_items, $calculated_items, $execution_time)
{
    $performance_data = [
        'total_items' => $total_items,
        'calculated_items' => $calculated_items,
        'execution_time' => $execution_time,
        'items_per_second' => $total_items / $execution_time,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log slow operations
    if ($execution_time > 2.0) {
        log_message('warning', "Slow lazy calculation: " . json_encode($performance_data));
    }
    
    // Store performance metrics
    $this->db->insert('web_visibility_performance_log', $performance_data);
}
```

// Batch Update Web Visibility Values (Enhanced)
public function batch_update_web_visibility_values($updates)
{
    if (empty($updates)) {
        return true;
    }
    
    $this->db->trans_start();
    
    try {
        // Use CodeIgniter's update_batch for efficiency
        $this->db->update_batch('T_ITEM', $updates, 'id');
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === false) {
            log_message('error', 'Batch update web visibility failed: ' . $this->db->last_query());
            return false;
        }
        
        // Log successful batch update
        $this->log_batch_update_success(count($updates));
        
        return true;
        
    } catch (Exception $e) {
        $this->db->trans_rollback();
        log_message('error', 'Batch update web visibility exception: ' . $e->getMessage());
        return false;
    }
}

// Log Batch Update Success
private function log_batch_update_success($count)
{
    $log_data = [
        'operation' => 'batch_update_web_visibility',
        'items_updated' => $count,
        'user_id' => $this->session->userdata('user_id'),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('web_visibility_operation_log', $log_data);
}
```

### **2. Database Integration Patterns**

#### **T_ITEM Table Operations**
```php
// Update Web Visibility State
public function update_item_web_visibility($item_id, $web_vis, $web_vis_toggle)
{
    $data = [
        'web_vis' => $web_vis ? 1 : 0,
        'web_vis_toggle' => $web_vis_toggle ? 1 : 0,
        'date_modif' => date('Y-m-d H:i:s')
    ];
    
    $this->db->where('id', $item_id);
    $this->db->update('T_ITEM', $data);
    
    return $this->db->affected_rows() > 0;
}

// Get Item Web Visibility State
public function get_item_web_visibility_state($item_id)
{
    $this->db->select('web_vis, web_vis_toggle, product_id, status_id');
    $this->db->from('T_ITEM');
    $this->db->where('id', $item_id);
    
    return $this->db->get()->row();
}

// Batch Update for Multiple Items
public function batch_update_web_visibility($items_data)
{
    $this->db->trans_start();
    
    foreach ($items_data as $item) {
        $this->update_item_web_visibility(
            $item['item_id'],
            $item['web_vis'],
            $item['web_vis_toggle']
        );
    }
    
    $this->db->trans_complete();
    return $this->db->trans_status() !== false;
}
```

#### **Status Mapping and Validation**
```php
// Valid Statuses for Auto-Visibility
private $valid_auto_visibility_statuses = ['RUN', 'LTDQTY', 'RKFISH'];

// Status Validation
public function is_valid_auto_visibility_status($status)
{
    return in_array($status, $this->valid_auto_visibility_statuses);
}

// Get Status Name from ID
public function get_status_name($status_id)
{
    $this->db->select('name');
    $this->db->from('P_PRODUCT_STATUS');
    $this->db->where('id', $status_id);
    
    $result = $this->db->get()->row();
    return $result ? $result->name : null;
}
```

### **3. Controller Integration Patterns**

#### **Product Controller Integration**
```php
// Product Edit Form Handler
public function update_product_web_visibility()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
    
    $product_id = $this->input->post('product_id');
    $web_visibility = $this->input->post('web_visibility');
    
    // Validate product exists and user has permission
    if (!$this->hasPermission('product', 'edit')) {
        $this->response(['error' => 'Insufficient permissions'], 403);
        return;
    }
    
    // Check beauty shot requirement
    $beauty_shot_state = $this->model->check_beauty_shot_uploaded($product_id);
    
    if (!$beauty_shot_state) {
        // Force to FALSE when no beauty shot
        $web_visibility = false;
    }
    
    // Update product web visibility
    $success = $this->model->update_product_web_visibility($product_id, $web_visibility);
    
    if ($success) {
        // Trigger colorline visibility recalculation
        $this->model->recalculate_all_colorline_visibility($product_id);
        
        $this->response([
            'success' => true,
            'message' => 'Product web visibility updated',
            'beauty_shot_required' => !$beauty_shot_state
        ]);
    } else {
        $this->response(['error' => 'Failed to update product web visibility'], 500);
    }
}
```

#### **Item Controller Integration**
```php
// Colorline Edit Form Handler
public function update_colorline_web_visibility()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
    
    $item_id = $this->input->post('item_id');
    $manual_override = $this->input->post('manual_override');
    $checkbox_value = $this->input->post('checkbox_value');
    
    // Get item data
    $item_data = $this->model->get_item_web_visibility_state($item_id);
    if (!$item_data) {
        $this->response(['error' => 'Item not found'], 404);
        return;
    }
    
    // Get product status name
    $status_name = $this->model->get_status_name($item_data->status_id);
    
    // Calculate final visibility
    $final_visibility = $this->model->calculate_final_web_visibility(
        $item_id,
        $item_data->product_id,
        $status_name,
        $manual_override,
        $checkbox_value
    );
    
    // Update database
    $success = $this->model->update_item_web_visibility(
        $item_id,
        $final_visibility,
        $manual_override
    );
    
    if ($success) {
        $this->response([
            'success' => true,
            'web_visibility' => $final_visibility,
            'manual_override' => $manual_override
        ]);
    } else {
        $this->response(['error' => 'Failed to update colorline web visibility'], 500);
    }
}
```

### **4. UI/UX Integration Patterns**

#### **JavaScript Integration (OPMS-Specific)**

**Web Visibility State Management with OPMS Integration**
```javascript
// Web Visibility Manager for OPMS CodeIgniter 3 System
class OPMSWebVisibilityManager {
    constructor() {
        this.csrfToken = $('input[name="csrf_test_name"]').val();
        this.baseUrl = window.location.origin;
        this.initEventListeners();
        this.initializeStates();
    }
    
    initEventListeners() {
        // Product beauty shot upload handler
        $(document).on('change', '#beauty_shot_upload', (e) => {
            this.handleBeautyShotUpload(e);
        });
        
        // Colorline manual override toggle
        $(document).on('change', '.manual_override_toggle', (e) => {
            this.handleManualOverrideToggle(e);
        });
        
        // Colorline checkbox change
        $(document).on('change', '.colorline_web_visibility', (e) => {
            this.handleColorlineCheckboxChange(e);
        });
        
        // Product web visibility checkbox
        $(document).on('change', '#product_web_visibility', (e) => {
            this.handleProductWebVisibilityChange(e);
        });
        
        // Eye icon click handler for quick toggle
        $(document).on('click', '.eye-icon-toggle', (e) => {
            this.handleEyeIconClick(e);
        });
    }
    
    initializeStates() {
        // Initialize all form states based on current data
        this.initializeProductStates();
        this.initializeColorlineStates();
    }
    
    initializeProductStates() {
        const productId = $('#product_id').val();
        if (productId) {
            this.checkProductBeautyShotState(productId);
        }
    }
    
    initializeColorlineStates() {
        // Initialize all colorline items in current view
        $('.colorline-item').each((index, element) => {
            this.initializeColorlineItemState($(element));
        });
    }
    
    handleBeautyShotUpload(event) {
        const file = event.target.files[0];
        const productId = $('#product_id').val();
        
        if (file) {
            // Show upload progress
            this.showUploadProgress();
            
            // Upload file first, then update visibility state
            this.uploadBeautyShot(file, productId);
        } else {
            // Disable and force to FALSE
            this.disableProductWebVisibility();
        }
    }
    
    uploadBeautyShot(file, productId) {
        const formData = new FormData();
        formData.append('beauty_shot', file);
        formData.append('product_id', productId);
        formData.append('csrf_test_name', this.csrfToken);
        
        $.ajax({
            url: this.baseUrl + '/product/upload_beauty_shot',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                this.hideUploadProgress();
                if (response.success) {
                    this.enableProductWebVisibility();
                    this.showNotification('Beauty shot uploaded successfully', 'success');
                } else {
                    this.showNotification('Beauty shot upload failed: ' + response.error, 'error');
                }
            },
            error: (xhr) => {
                this.hideUploadProgress();
                this.showNotification('Beauty shot upload failed', 'error');
            }
        });
    }
    
    handleManualOverrideToggle(event) {
        const $toggle = $(event.target);
        const itemId = $toggle.data('item-id');
        const isEnabled = $toggle.is(':checked');
        const $checkbox = $toggle.closest('.colorline-item').find('.colorline_web_visibility');
        
        if (isEnabled) {
            $checkbox.prop('disabled', false);
            this.updateManualOverrideState(itemId, true);
        } else {
            $checkbox.prop('disabled', true);
            this.recalculateAutoVisibility(itemId);
        }
    }
    
    handleColorlineCheckboxChange(event) {
        const $checkbox = $(event.target);
        const itemId = $checkbox.data('item-id');
        const isChecked = $checkbox.is(':checked');
        
        this.updateColorlineVisibility(itemId, isChecked);
    }
    
    handleProductWebVisibilityChange(event) {
        const isChecked = $(event.target).is(':checked');
        const productId = $('#product_id').val();
        
        this.updateProductWebVisibility(productId, isChecked);
    }
    
    handleEyeIconClick(event) {
        const $icon = $(event.target);
        const itemId = $icon.data('item-id');
        const currentState = $icon.hasClass('fa-eye');
        
        // Toggle the state
        this.toggleColorlineVisibility(itemId, !currentState);
    }
    
    // API Methods
    updateProductWebVisibility(productId, webVisibility) {
        $.ajax({
            url: this.baseUrl + '/product/update_web_visibility',
            method: 'POST',
            data: {
                product_id: productId,
                web_visibility: webVisibility,
                csrf_test_name: this.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.updateAllColorlineVisibility(productId);
                    this.showNotification('Product web visibility updated', 'success');
                } else {
                    this.showNotification('Failed to update product web visibility', 'error');
                }
            },
            error: (xhr) => {
                this.showNotification('Failed to update product web visibility', 'error');
            }
        });
    }
    
    updateColorlineVisibility(itemId, webVisibility) {
        $.ajax({
            url: this.baseUrl + '/item/update_web_visibility',
            method: 'POST',
            data: {
                item_id: itemId,
                web_visibility: webVisibility,
                csrf_test_name: this.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.updateEyeIconState(itemId, webVisibility);
                    this.showNotification('Colorline visibility updated', 'success');
                } else {
                    this.showNotification('Failed to update colorline visibility', 'error');
                }
            },
            error: (xhr) => {
                this.showNotification('Failed to update colorline visibility', 'error');
            }
        });
    }
    
    recalculateAutoVisibility(itemId) {
        $.ajax({
            url: this.baseUrl + '/item/recalculate_visibility',
            method: 'POST',
            data: {
                item_id: itemId,
                csrf_test_name: this.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.updateColorlineItemState(itemId, response.web_visibility, false);
                }
            }
        });
    }
    
    // UI Update Methods
    enableProductWebVisibility() {
        $('#product_web_visibility').prop('disabled', false);
        $('#beauty_shot_status').removeClass('text-danger').addClass('text-success')
            .text('Beauty shot uploaded - Web visibility enabled');
    }
    
    disableProductWebVisibility() {
        $('#product_web_visibility').prop('disabled', true).prop('checked', false);
        $('#beauty_shot_status').removeClass('text-success').addClass('text-danger')
            .text('No beauty shot - Web visibility disabled');
    }
    
    updateEyeIconState(itemId, isVisible) {
        const $icon = $(`.eye-icon-toggle[data-item-id="${itemId}"]`);
        if (isVisible) {
            $icon.removeClass('fa-eye-slash text-muted').addClass('fa-eye text-primary');
        } else {
            $icon.removeClass('fa-eye text-primary').addClass('fa-eye-slash text-muted');
        }
    }
    
    updateColorlineItemState(itemId, webVisibility, manualOverride) {
        const $item = $(`.colorline-item[data-item-id="${itemId}"]`);
        const $checkbox = $item.find('.colorline_web_visibility');
        const $toggle = $item.find('.manual_override_toggle');
        
        $checkbox.prop('checked', webVisibility);
        $toggle.prop('checked', manualOverride);
        $checkbox.prop('disabled', !manualOverride);
        
        this.updateEyeIconState(itemId, webVisibility);
    }
    
    updateAllColorlineVisibility(productId) {
        // Update all colorlines for this product
        $('.colorline-item').each((index, element) => {
            const $item = $(element);
            const itemId = $item.data('item-id');
            this.recalculateAutoVisibility(itemId);
        });
    }
    
    // Utility Methods
    showUploadProgress() {
        $('#upload_progress').show();
        $('#beauty_shot_upload').prop('disabled', true);
    }
    
    hideUploadProgress() {
        $('#upload_progress').hide();
        $('#beauty_shot_upload').prop('disabled', false);
    }
    
    showNotification(message, type) {
        // Use OPMS notification system or create simple alert
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
            alert(message);
        }
    }
    
    checkProductBeautyShotState(productId) {
        $.ajax({
            url: this.baseUrl + '/product/check_beauty_shot',
            method: 'GET',
            data: { product_id: productId },
            success: (response) => {
                if (response.has_beauty_shot) {
                    this.enableProductWebVisibility();
                } else {
                    this.disableProductWebVisibility();
                }
            }
        });
    }
    
    initializeColorlineItemState($item) {
        const itemId = $item.data('item-id');
        const webVisibility = $item.data('web-visibility');
        const manualOverride = $item.data('manual-override');
        
        this.updateColorlineItemState(itemId, webVisibility, manualOverride);
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.webVisibilityManager = new OPMSWebVisibilityManager();
});
```

#### **HTML/CSS Integration (OPMS-Specific)**

**Product Edit Form Integration**
```html
<!-- Product Web Visibility Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-globe"></i> Web Visibility Settings
        </h5>
    </div>
    <div class="card-body">
        <!-- Beauty Shot Upload Section -->
        <div class="form-group mb-3">
            <label for="beauty_shot_upload" class="form-label">
                <i class="fas fa-camera"></i> Beauty Shot
                <span class="text-danger">*</span>
            </label>
            <input type="file" 
                   class="form-control" 
                   id="beauty_shot_upload" 
                   name="beauty_shot" 
                   accept="image/*">
            <div id="beauty_shot_status" class="form-text text-danger">
                No beauty shot - Web visibility disabled
            </div>
            <div id="upload_progress" class="progress mt-2" style="display: none;">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
        
        <!-- Web Visibility Checkbox -->
        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="product_web_visibility" 
                       name="web_visibility" 
                       value="1" 
                       disabled>
                <label class="form-check-label" for="product_web_visibility">
                    <i class="fas fa-eye"></i> Enable Web Visibility
                </label>
            </div>
            <div class="form-text">
                Web visibility is only available when a beauty shot is uploaded
            </div>
        </div>
    </div>
</div>
```

**Colorline List Integration**
```html
<!-- Colorline List with Web Visibility Controls -->
<div class="table-responsive">
    <table class="table table-hover" id="colorline_list">
        <thead class="table-dark">
            <tr>
                <th>Code</th>
                <th>Color</th>
                <th>Status</th>
                <th>Web Visibility</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr class="colorline-item" data-item-id="123" data-web-visibility="true" data-manual-override="false">
                <td>ABC-001</td>
                <td>Red</td>
                <td>
                    <span class="badge bg-success">RUN</span>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <!-- Eye Icon (Database-Driven) -->
                        <i class="fas fa-eye text-primary eye-icon-toggle me-2" 
                           data-item-id="123" 
                           style="cursor: pointer;" 
                           title="Click to toggle visibility"></i>
                        
                        <!-- Manual Override Toggle -->
                        <div class="form-check form-switch me-2">
                            <input class="form-check-input manual_override_toggle" 
                                   type="checkbox" 
                                   data-item-id="123" 
                                   id="manual_override_123">
                            <label class="form-check-label" for="manual_override_123">
                                Manual
                            </label>
                        </div>
                        
                        <!-- Web Visibility Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input colorline_web_visibility" 
                                   type="checkbox" 
                                   data-item-id="123" 
                                   id="web_vis_123" 
                                   checked 
                                   disabled>
                            <label class="form-check-label" for="web_vis_123">
                                Visible
                            </label>
                        </div>
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" 
                            onclick="recalculateVisibility(123)">
                        <i class="fas fa-sync"></i> Recalculate
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

**CSS Styling (OPMS-Integrated)**
```css
/* Web Visibility Specific Styles */
.web-visibility-section {
    border-left: 4px solid #007bff;
    background-color: #f8f9fa;
}

.eye-icon-toggle {
    transition: all 0.3s ease;
    font-size: 1.2em;
}

.eye-icon-toggle:hover {
    transform: scale(1.1);
}

.eye-icon-toggle.fa-eye {
    color: #007bff !important;
}

.eye-icon-toggle.fa-eye-slash {
    color: #6c757d !important;
}

.manual-override-section {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin: 0.5rem 0;
}

.colorline-item {
    transition: background-color 0.2s ease;
}

.colorline-item:hover {
    background-color: #f8f9fa;
}

.colorline-item.manual-override {
    background-color: #fff3cd;
}

.upload-progress {
    height: 6px;
    border-radius: 3px;
}

.beauty-shot-status {
    font-size: 0.875em;
    font-weight: 500;
}

.beauty-shot-status.text-success {
    color: #198754 !important;
}

.beauty-shot-status.text-danger {
    color: #dc3545 !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .colorline-item .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .colorline-item .d-flex > * {
        margin-bottom: 0.25rem;
    }
}

/* Animation for State Changes */
.visibility-state-change {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
```

#### **Eye Icon State Management (Database-Driven)**
```php
// Get Eye Icon State for Colorline List (Always Database-Driven)
public function get_eye_icon_state($item_id)
{
    $this->db->select('web_vis');
    $this->db->from('T_ITEM');
    $this->db->where('id', $item_id);
    
    $result = $this->db->get()->row();
    
    if (!$result) {
        return 'hidden'; // Item not found
    }
    
    // Eye icon always reflects stored database value
    return $result->web_vis ? 'visible' : 'hidden';
}

// Batch Eye Icon State for List View (Database-Driven)
public function get_batch_eye_icon_states($item_ids)
{
    $this->db->select('id, web_vis');
    $this->db->from('T_ITEM');
    $this->db->where_in('id', $item_ids);
    
    $results = $this->db->get()->result_array();
    
    $states = [];
    foreach ($results as $row) {
        // Always use stored database value, never calculate on-the-fly
        $states[$row['id']] = $row['web_vis'] ? 'visible' : 'hidden';
    }
    
    return $states;
}

// Get Eye Icon State After Lazy Calculation
public function get_eye_icon_state_after_calculation($item_id)
{
    // First ensure lazy calculation has been performed
    $this->ensure_lazy_calculation($item_id);
    
    // Then return database-driven state
    return $this->get_eye_icon_state($item_id);
}

// Ensure Lazy Calculation for Single Item
public function ensure_lazy_calculation($item_id)
{
    if ($this->needs_lazy_calculation($item_id)) {
        $item = $this->get_item_data($item_id);
        $calculated_visibility = $this->calculate_web_visibility_for_item($item);
        
        $this->db->where('id', $item_id);
        $this->db->update('T_ITEM', [
            'web_vis' => $calculated_visibility ? 1 : 0,
            'date_modif' => date('Y-m-d H:i:s')
        ]);
    }
}
```

---

## 🎯 **PROMPT ENGINEERING STRATEGIES**

### **1. Web Visibility Logic Prompts**

#### **Implementation Analysis Prompt**
```
WEB VISIBILITY IMPLEMENTATION ANALYSIS:
- Feature: Web Visibility Logic Flow Management
- Context: OPMS CodeIgniter 3 system with Product/Colorline hierarchy
- Requirements: Auto-determination, manual override, beauty shot dependency
- Database: T_ITEM table with new web_vis and web_vis_toggle columns

ANALYSIS FRAMEWORK:
1. Logic Flow: Product-level beauty shot dependency, colorline auto-determination
2. Database Design: Schema changes, data integrity, performance considerations
3. UI/UX Integration: Form controls, eye icon states, real-time updates
4. Security: Input validation, permission checks, CSRF protection
5. Testing: Unit tests, integration tests, edge case scenarios
6. Performance: Query optimization, batch operations, caching strategies
```

#### **Bug Fix Prompt for Web Visibility**
```
WEB VISIBILITY BUG FIX REQUEST:
- Issue: [Specific web visibility problem description]
- Context: [Product/Colorline level, auto/manual mode]
- Expected Behavior: [What should happen according to logic flow]
- Current Behavior: [What actually happens]
- Database State: [Current T_ITEM.web_vis and web_vis_toggle values]

DEBUGGING APPROACH:
1. Logic Flow Analysis: Trace through auto-determination vs manual override logic
2. Database State Check: Verify T_ITEM table values and relationships
3. Beauty Shot Dependency: Confirm product-level beauty shot requirements
4. Status Validation: Check colorline status against valid auto-visibility statuses
5. UI State Sync: Ensure form controls reflect database state correctly
6. Permission Check: Verify user has appropriate edit permissions
```

#### **Feature Enhancement Prompt**
```
WEB VISIBILITY FEATURE ENHANCEMENT:
- Enhancement: [Specific feature improvement request]
- Business Value: [Why this enhancement is needed]
- Current Implementation: [Existing web visibility logic and UI]
- Technical Requirements: [Performance, security, integration needs]

DEVELOPMENT APPROACH:
1. Logic Extension: Extend existing auto-determination or manual override logic
2. Database Updates: Modify T_ITEM schema or add supporting tables
3. UI Enhancement: Update form controls, eye icon behavior, or list views
4. API Integration: Create/update REST endpoints for new functionality
5. Testing Strategy: Unit tests for logic, integration tests for UI, edge cases
6. Migration Plan: Database migration, data migration, deployment strategy
```

### **2. Lazy Calculation Prompts**

#### **Lazy Calculation Implementation Prompt**
```
LAZY CALCULATION IMPLEMENTATION:
- Feature: Automatic web visibility calculation during colorline listing display
- Trigger: Colorline listing load with NULL web_vis values
- Logic: Apply existing web visibility logic to NULL values only
- Storage: Batch update calculated values to T_ITEM.web_vis
- UI: Eye icons reflect stored database values after calculation
- Performance: Optimize for large lists with mixed NULL/non-NULL values

IMPLEMENTATION FRAMEWORK:
1. NULL Detection: Efficiently identify items needing calculation
2. Logic Application: Use existing web visibility calculation methods
3. Batch Storage: Update multiple items in single transaction
4. UI Update: Refresh eye icon states after database update
5. Performance: Optimize for large lists with mixed NULL/non-NULL values
6. Consistency: Ensure same logic as explicit editing
```

#### **Lazy Calculation Debugging Prompt**
```
LAZY CALCULATION DEBUGGING:
- Issue: [Specific lazy calculation problem description]
- Context: [Colorline listing, NULL values, batch operations]
- Expected Behavior: [What should happen during lazy calculation]
- Current Behavior: [What actually happens]
- Database State: [Current T_ITEM.web_vis values, NULL vs calculated]

DEBUGGING APPROACH:
1. NULL Detection: Verify items are correctly identified as needing calculation
2. Logic Application: Check web visibility calculation for each NULL item
3. Batch Operations: Verify batch update operations complete successfully
4. Database State: Confirm T_ITEM.web_vis values are properly stored
5. UI Sync: Ensure eye icons reflect stored values after calculation
6. Performance: Check for memory issues or slow queries during batch operations
```

### **3. Testing Scenario Prompts**

#### **Test Case Implementation Prompt**
```
WEB VISIBILITY TEST CASE IMPLEMENTATION:
- Test Scenarios: [Reference to testing scenarios A-E from requirements]
- Framework: CodeIgniter 3 with PHPUnit testing
- Database: T_ITEM table with web_vis and web_vis_toggle columns
- UI Components: Product form, colorline form, eye icon display
- Lazy Calculation: Colorline listing with NULL web_vis values

TEST IMPLEMENTATION FRAMEWORK:
1. Test Data Setup: Create test products, colorlines, beauty shots, statuses
2. Logic Testing: Verify auto-determination and manual override calculations
3. Database Testing: Confirm T_ITEM table updates and data integrity
4. UI Testing: Test form control states, eye icon updates, real-time changes
5. Lazy Calculation Testing: Test NULL value detection and batch calculation
6. Edge Cases: Invalid statuses, missing beauty shots, permission failures
7. Integration Testing: End-to-end workflow from product to colorline visibility
```

#### **Performance Testing Prompt**
```
WEB VISIBILITY PERFORMANCE TESTING:
- Scope: Large product catalogs with thousands of colorlines
- Metrics: Query performance, UI responsiveness, batch operations
- Scenarios: Bulk visibility updates, list view loading, real-time calculations

PERFORMANCE TESTING FRAMEWORK:
1. Database Performance: Query optimization for T_ITEM table operations
2. Batch Operations: Efficient processing of multiple colorline updates
3. UI Responsiveness: Real-time updates without blocking user interface
4. Caching Strategy: Cache frequently accessed visibility states
5. Memory Management: Handle large datasets without memory exhaustion
6. Load Testing: Simulate concurrent users updating visibility states
```

### **3. Security and Validation Prompts**

#### **Security Implementation Prompt**
```
WEB VISIBILITY SECURITY IMPLEMENTATION:
- Security Requirements: Input validation, permission checks, CSRF protection
- Data Integrity: Prevent unauthorized visibility state changes
- Business Logic: Ensure visibility rules cannot be bypassed

SECURITY IMPLEMENTATION FRAMEWORK:
1. Input Validation: Validate all form inputs and API parameters
2. Permission Checks: Verify user has edit permissions for products/colorlines
3. CSRF Protection: Implement CSRF tokens for all state-changing operations
4. Business Logic Validation: Server-side validation of visibility rules
5. Audit Logging: Log all visibility state changes for security monitoring
6. Data Sanitization: Sanitize all user inputs before database operations
```

---

## 🔧 **DEVELOPMENT WORKFLOW PATTERNS**

### **1. Implementation Checklist**

#### **Database Schema Updates**
- [ ] Create migration script for T_ITEM table columns
- [ ] Add indexes for performance optimization
- [ ] Update existing data with default values
- [ ] Test migration on development environment
- [ ] Verify data integrity after migration

#### **Model Layer Implementation**
- [ ] Implement beauty shot dependency checking
- [ ] Create auto-determination logic methods
- [ ] Implement manual override logic methods
- [ ] Add database update methods for T_ITEM table
- [ ] Create batch update methods for performance
- [ ] Add validation methods for status and permissions

#### **Controller Layer Implementation**
- [ ] Update Product controller for web visibility management
- [ ] Update Item controller for colorline visibility management
- [ ] Implement AJAX endpoints for real-time updates
- [ ] Add proper error handling and response formatting
- [ ] Implement permission checks for all operations
- [ ] Add CSRF protection for all state-changing operations

#### **View Layer Implementation**
- [ ] Update product edit form with beauty shot dependency
- [ ] Add manual override toggle to colorline forms
- [ ] Implement eye icon state management in lists
- [ ] Add JavaScript for real-time UI updates
- [ ] Ensure responsive design for all screen sizes
- [ ] Test form validation and error display

### **2. Testing Implementation**

#### **Unit Testing**
```php
// Web Visibility Logic Unit Tests
class WebVisibilityTest extends PHPUnit_Framework_TestCase
{
    public function test_auto_visibility_calculation()
    {
        // Test Case A: All conditions met
        $result = $this->model->calculate_auto_visibility(1, 1, 'RUN');
        $this->assertTrue($result);
        
        // Test Case B: Invalid status
        $result = $this->model->calculate_auto_visibility(1, 1, 'HOLD');
        $this->assertFalse($result);
        
        // Test Case C: No beauty shot
        $result = $this->model->calculate_auto_visibility(1, 1, 'RUN');
        $this->assertFalse($result);
    }
    
    public function test_manual_override_calculation()
    {
        // Test Case D: Manual override enabled
        $result = $this->model->calculate_manual_visibility(1, 1, true);
        $this->assertTrue($result);
        
        // Test Case E: Manual override disabled
        $result = $this->model->calculate_manual_visibility(1, 1, false);
        $this->assertFalse($result);
    }
}
```

#### **Integration Testing**
```php
// Web Visibility Integration Tests
class WebVisibilityIntegrationTest extends PHPUnit_Framework_TestCase
{
    public function test_product_web_visibility_update()
    {
        $response = $this->request('POST', 'product/update_web_visibility', [
            'product_id' => 1,
            'web_visibility' => true
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function test_colorline_visibility_recalculation()
    {
        $response = $this->request('POST', 'item/recalculate_visibility', [
            'item_id' => 1
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
    }
    
    public function test_lazy_calculation_on_colorline_listing()
    {
        // Create test items with NULL web_vis values
        $this->create_test_items_with_null_web_vis();
        
        // Load colorline listing (should trigger lazy calculation)
        $response = $this->request('GET', 'item/colorline_list');
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify items now have calculated web_vis values
        $items = $this->model->get_colorline_items();
        foreach ($items as $item) {
            $this->assertNotNull($item['web_vis'], 'Item should have calculated web_vis value');
        }
    }
    
    public function test_eye_icon_database_driven_state()
    {
        // Set specific web_vis value in database
        $this->db->where('id', 1);
        $this->db->update('T_ITEM', ['web_vis' => 1]);
        
        // Get eye icon state (should reflect database value)
        $eye_state = $this->model->get_eye_icon_state(1);
        $this->assertEquals('visible', $eye_state);
        
        // Change database value
        $this->db->where('id', 1);
        $this->db->update('T_ITEM', ['web_vis' => 0]);
        
        // Eye icon should reflect new database value
        $eye_state = $this->model->get_eye_icon_state(1);
        $this->assertEquals('hidden', $eye_state);
    }
}
```

### **3. Performance Optimization**

#### **Query Optimization**
```php
// Optimized Web Visibility Queries
public function get_batch_visibility_states($item_ids)
{
    // Use single query with IN clause instead of multiple queries
    $this->db->select('id, web_vis, web_vis_toggle, product_id, status_id');
    $this->db->from('T_ITEM');
    $this->db->where_in('id', $item_ids);
    
    return $this->db->get()->result_array();
}

// Cached Status Lookup
public function get_cached_status_name($status_id)
{
    $cache_key = "status_name_{$status_id}";
    
    if (!$status_name = $this->cache->get($cache_key)) {
        $status_name = $this->get_status_name($status_id);
        $this->cache->save($cache_key, $status_name, 3600); // 1 hour cache
    }
    
    return $status_name;
}
```

#### **Batch Operations**
```php
// Efficient Batch Updates
public function batch_update_visibility_states($updates)
{
    $this->db->trans_start();
    
    // Prepare batch update data
    $update_data = [];
    foreach ($updates as $update) {
        $update_data[] = [
            'id' => $update['item_id'],
            'web_vis' => $update['web_vis'] ? 1 : 0,
            'web_vis_toggle' => $update['web_vis_toggle'] ? 1 : 0,
            'date_modif' => date('Y-m-d H:i:s')
        ];
    }
    
    // Single batch update
    $this->db->update_batch('T_ITEM', $update_data, 'id');
    
    $this->db->trans_complete();
    return $this->db->trans_status() !== false;
}
```

#### **Lazy Calculation Performance Optimization**
```php
// Optimized Lazy Calculation for Large Lists
public function get_colorline_list_optimized($filters = [], $limit = 1000)
{
    // Get items with NULL web_vis values only
    $this->db->select('id, product_id, status_id, web_vis');
    $this->db->from('T_ITEM');
    $this->db->where('web_vis IS NULL');
    $this->db->limit($limit);
    
    $null_items = $this->db->get()->result_array();
    
    if (empty($null_items)) {
        // No lazy calculation needed, return regular list
        return $this->get_colorline_items($filters);
    }
    
    // Batch calculate visibility for NULL items
    $calculated_values = [];
    foreach ($null_items as $item) {
        $visibility = $this->calculate_web_visibility_for_item($item);
        $calculated_values[] = [
            'id' => $item['id'],
            'web_vis' => $visibility ? 1 : 0
        ];
    }
    
    // Batch update calculated values
    $this->batch_update_web_visibility_values($calculated_values);
    
    // Return full list with updated values
    return $this->get_colorline_items($filters);
}

// Memory-Efficient Lazy Calculation
public function lazy_calculate_in_chunks($chunk_size = 100)
{
    $offset = 0;
    $total_updated = 0;
    
    do {
        // Get chunk of items with NULL web_vis
        $this->db->select('id, product_id, status_id');
        $this->db->from('T_ITEM');
        $this->db->where('web_vis IS NULL');
        $this->db->limit($chunk_size, $offset);
        
        $items = $this->db->get()->result_array();
        
        if (empty($items)) {
            break;
        }
        
        // Calculate and update chunk
        $updates = [];
        foreach ($items as $item) {
            $visibility = $this->calculate_web_visibility_for_item($item);
            $updates[] = [
                'id' => $item['id'],
                'web_vis' => $visibility ? 1 : 0
            ];
        }
        
        $this->batch_update_web_visibility_values($updates);
        $total_updated += count($updates);
        $offset += $chunk_size;
        
    } while (count($items) == $chunk_size);
    
    return $total_updated;
}

// Cached Status Lookup for Performance
private $status_cache = [];

public function get_cached_status_name($status_id)
{
    if (!isset($this->status_cache[$status_id])) {
        $this->db->select('name');
        $this->db->from('P_PRODUCT_STATUS');
        $this->db->where('id', $status_id);
        
        $result = $this->db->get()->row();
        $this->status_cache[$status_id] = $result ? $result->name : null;
    }
    
    return $this->status_cache[$status_id];
}
```

---

## 🔒 **SECURITY IMPLEMENTATION PATTERNS**

### **1. Input Validation (OPMS-Specific)**

#### **Comprehensive Form Validation Rules**
```php
// Web Visibility Form Validation with OPMS Security
public function get_web_visibility_validation_rules()
{
    return [
        [
            'field' => 'product_id',
            'label' => 'Product ID',
            'rules' => 'required|integer|is_natural|callback_validate_product_exists'
        ],
        [
            'field' => 'web_visibility',
            'label' => 'Web Visibility',
            'rules' => 'in_list[0,1]|callback_validate_web_visibility_permission'
        ],
        [
            'field' => 'manual_override',
            'label' => 'Manual Override',
            'rules' => 'in_list[0,1]'
        ],
        [
            'field' => 'item_id',
            'label' => 'Item ID',
            'rules' => 'required|integer|is_natural|callback_validate_item_exists'
        ],
        [
            'field' => 'beauty_shot',
            'label' => 'Beauty Shot',
            'rules' => 'callback_validate_beauty_shot_upload'
        ]
    ];
}

// Custom Validation Callbacks
public function validate_product_exists($product_id)
{
    if (empty($product_id)) {
        $this->form_validation->set_message('validate_product_exists', 'Product ID is required');
        return false;
    }
    
    $this->db->select('id, name, archived');
    $this->db->from('T_PRODUCT');
    $this->db->where('id', $product_id);
    $this->db->where('archived', 'N');
    
    $product = $this->db->get()->row();
    
    if (!$product) {
        $this->form_validation->set_message('validate_product_exists', 'Product not found or archived');
        return false;
    }
    
    // Check user permission for this product
    if (!$this->hasPermission('product', 'edit', $product_id)) {
        $this->form_validation->set_message('validate_product_exists', 'Insufficient permissions for this product');
        return false;
    }
    
    return true;
}

public function validate_item_exists($item_id)
{
    if (empty($item_id)) {
        $this->form_validation->set_message('validate_item_exists', 'Item ID is required');
        return false;
    }
    
    $this->db->select('id, product_id, archived');
    $this->db->from('T_ITEM');
    $this->db->where('id', $item_id);
    $this->db->where('archived', 'N');
    
    $item = $this->db->get()->row();
    
    if (!$item) {
        $this->form_validation->set_message('validate_item_exists', 'Item not found or archived');
        return false;
    }
    
    // Check user permission for this item
    if (!$this->hasPermission('item', 'edit', $item_id)) {
        $this->form_validation->set_message('validate_item_exists', 'Insufficient permissions for this item');
        return false;
    }
    
    return true;
}

public function validate_web_visibility_permission($web_visibility)
{
    // Check if user has web visibility permission
    if (!$this->hasPermission('web_visibility', 'edit')) {
        $this->form_validation->set_message('validate_web_visibility_permission', 'Insufficient permissions for web visibility changes');
        return false;
    }
    
    return true;
}

public function validate_beauty_shot_upload($beauty_shot)
{
    if (empty($_FILES['beauty_shot']['name'])) {
        return true; // Optional field
    }
    
    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($_FILES['beauty_shot']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        $this->form_validation->set_message('validate_beauty_shot_upload', 'Invalid file type. Allowed: ' . implode(', ', $allowed_types));
        return false;
    }
    
    // Validate file size (max 5MB)
    if ($_FILES['beauty_shot']['size'] > 5 * 1024 * 1024) {
        $this->form_validation->set_message('validate_beauty_shot_upload', 'File size too large. Maximum 5MB allowed');
        return false;
    }
    
    // Validate image dimensions
    $image_info = getimagesize($_FILES['beauty_shot']['tmp_name']);
    if ($image_info === false) {
        $this->form_validation->set_message('validate_beauty_shot_upload', 'Invalid image file');
        return false;
    }
    
    // Check minimum dimensions
    if ($image_info[0] < 300 || $image_info[1] < 300) {
        $this->form_validation->set_message('validate_beauty_shot_upload', 'Image must be at least 300x300 pixels');
        return false;
    }
    
    return true;
}
```

#### **API Input Validation (OPMS-Specific)**
```php
// REST API Input Validation with OPMS Security
public function validate_web_visibility_api_input($data)
{
    // Check CSRF token for state-changing operations
    if (!$this->validate_csrf_token($data)) {
        $this->response(['error' => 'Invalid CSRF token'], 403);
        return false;
    }
    
    // Validate required fields
    $required_fields = ['product_id', 'item_id'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $this->response(['error' => "Field {$field} is required"], 400);
            return false;
        }
    }
    
    // Validate data types and ranges
    if (!is_numeric($data['product_id']) || $data['product_id'] <= 0) {
        $this->response(['error' => 'Invalid product ID format'], 400);
        return false;
    }
    
    if (!is_numeric($data['item_id']) || $data['item_id'] <= 0) {
        $this->response(['error' => 'Invalid item ID format'], 400);
        return false;
    }
    
    // Validate web_visibility if provided
    if (isset($data['web_visibility']) && !in_array($data['web_visibility'], [0, 1, '0', '1', true, false])) {
        $this->response(['error' => 'Invalid web_visibility value'], 400);
        return false;
    }
    
    // Validate manual_override if provided
    if (isset($data['manual_override']) && !in_array($data['manual_override'], [0, 1, '0', '1', true, false])) {
        $this->response(['error' => 'Invalid manual_override value'], 400);
        return false;
    }
    
    // Sanitize all string inputs
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = $this->security->xss_clean($value);
        }
    }
    
    return true;
}

// CSRF Token Validation
private function validate_csrf_token($data)
{
    if (!isset($data['csrf_test_name'])) {
        return false;
    }
    
    $csrf_token = $data['csrf_test_name'];
    $session_token = $this->session->userdata('csrf_test_name');
    
    if (empty($csrf_token) || empty($session_token)) {
        return false;
    }
    
    return hash_equals($session_token, $csrf_token);
}

// Rate Limiting for Web Visibility Operations
private function check_rate_limit($operation, $user_id)
{
    $rate_limits = [
        'update_web_visibility' => 100, // 100 per hour
        'upload_beauty_shot' => 20,     // 20 per hour
        'recalculate_visibility' => 200 // 200 per hour
    ];
    
    $limit = $rate_limits[$operation] ?? 50;
    $cache_key = "rate_limit_{$operation}_{$user_id}";
    
    $current_count = $this->cache->get($cache_key) ?: 0;
    
    if ($current_count >= $limit) {
        $this->response(['error' => 'Rate limit exceeded. Please try again later.'], 429);
        return false;
    }
    
    // Increment counter
    $this->cache->save($cache_key, $current_count + 1, 3600); // 1 hour
    
    return true;
}
```

### **2. Permission Checks (OPMS-Specific)**

#### **Comprehensive Permission System**
```php
// Product Web Visibility Permission Check with OPMS Integration
public function check_product_web_visibility_permission($product_id)
{
    // Check if user is logged in
    if (!$this->ion_auth->logged_in()) {
        $this->log_security_event('unauthorized_access', [
            'action' => 'check_product_web_visibility_permission',
            'product_id' => $product_id,
            'reason' => 'user_not_logged_in'
        ]);
        return false;
    }
    
    // Check if user has product edit permission
    if (!$this->hasPermission('product', 'edit')) {
        $this->log_security_event('insufficient_permissions', [
            'action' => 'check_product_web_visibility_permission',
            'product_id' => $product_id,
            'user_id' => $this->session->userdata('user_id'),
            'required_permission' => 'product.edit'
        ]);
        return false;
    }
    
    // Check if user has web visibility permission
    if (!$this->hasPermission('web_visibility', 'edit')) {
        $this->log_security_event('insufficient_permissions', [
            'action' => 'check_product_web_visibility_permission',
            'product_id' => $product_id,
            'user_id' => $this->session->userdata('user_id'),
            'required_permission' => 'web_visibility.edit'
        ]);
        return false;
    }
    
    // Check if user has access to this specific product
    $this->db->select('id, name, archived');
    $this->db->from('T_PRODUCT');
    $this->db->where('id', $product_id);
    $this->db->where('archived', 'N');
    
    $product = $this->db->get()->row();
    
    if (!$product) {
        $this->log_security_event('product_not_found', [
            'action' => 'check_product_web_visibility_permission',
            'product_id' => $product_id,
            'user_id' => $this->session->userdata('user_id')
        ]);
        return false;
    }
    
    // Check if user has access to this specific product (vendor-based access)
    if (!$this->check_vendor_access($product_id)) {
        $this->log_security_event('vendor_access_denied', [
            'action' => 'check_product_web_visibility_permission',
            'product_id' => $product_id,
            'user_id' => $this->session->userdata('user_id')
        ]);
        return false;
    }
    
    return true;
}

// Item Web Visibility Permission Check with OPMS Integration
public function check_item_web_visibility_permission($item_id)
{
    // Check if user is logged in
    if (!$this->ion_auth->logged_in()) {
        $this->log_security_event('unauthorized_access', [
            'action' => 'check_item_web_visibility_permission',
            'item_id' => $item_id,
            'reason' => 'user_not_logged_in'
        ]);
        return false;
    }
    
    // Check if user has item edit permission
    if (!$this->hasPermission('item', 'edit')) {
        $this->log_security_event('insufficient_permissions', [
            'action' => 'check_item_web_visibility_permission',
            'item_id' => $item_id,
            'user_id' => $this->session->userdata('user_id'),
            'required_permission' => 'item.edit'
        ]);
        return false;
    }
    
    // Check if user has web visibility permission
    if (!$this->hasPermission('web_visibility', 'edit')) {
        $this->log_security_event('insufficient_permissions', [
            'action' => 'check_item_web_visibility_permission',
            'item_id' => $item_id,
            'user_id' => $this->session->userdata('user_id'),
            'required_permission' => 'web_visibility.edit'
        ]);
        return false;
    }
    
    // Check if user has access to this specific item
    $this->db->select('i.id, i.product_id, i.archived, p.name as product_name');
    $this->db->from('T_ITEM i');
    $this->db->join('T_PRODUCT p', 'i.product_id = p.id', 'left');
    $this->db->where('i.id', $item_id);
    $this->db->where('i.archived', 'N');
    
    $item = $this->db->get()->row();
    
    if (!$item) {
        $this->log_security_event('item_not_found', [
            'action' => 'check_item_web_visibility_permission',
            'item_id' => $item_id,
            'user_id' => $this->session->userdata('user_id')
        ]);
        return false;
    }
    
    // Check if user has access to the parent product
    if (!$this->check_vendor_access($item->product_id)) {
        $this->log_security_event('vendor_access_denied', [
            'action' => 'check_item_web_visibility_permission',
            'item_id' => $item_id,
            'product_id' => $item->product_id,
            'user_id' => $this->session->userdata('user_id')
        ]);
        return false;
    }
    
    return true;
}

// Check Vendor-Based Access (OPMS-Specific)
private function check_vendor_access($product_id)
{
    $user_id = $this->session->userdata('user_id');
    
    // Get user's vendor access
    $this->db->select('v.id as vendor_id');
    $this->db->from('users u');
    $this->db->join('user_vendor_access uva', 'u.id = uva.user_id', 'left');
    $this->db->join('Z_VENDOR v', 'uva.vendor_id = v.id', 'left');
    $this->db->where('u.id', $user_id);
    $this->db->where('v.active', 'Y');
    $this->db->where('v.archived', 'N');
    
    $user_vendors = $this->db->get()->result_array();
    $user_vendor_ids = array_column($user_vendors, 'vendor_id');
    
    // If user has no vendor restrictions, allow access
    if (empty($user_vendor_ids)) {
        return true;
    }
    
    // Check if product belongs to user's vendors
    $this->db->select('pv.vendor_id');
    $this->db->from('T_PRODUCT_VENDOR pv');
    $this->db->where('pv.product_id', $product_id);
    $this->db->where_in('pv.vendor_id', $user_vendor_ids);
    
    return $this->db->get()->num_rows() > 0;
}

// Security Event Logging
private function log_security_event($event_type, $data)
{
    $log_data = [
        'event_type' => $event_type,
        'user_id' => $this->session->userdata('user_id'),
        'ip_address' => $this->input->ip_address(),
        'user_agent' => $this->input->user_agent(),
        'data' => json_encode($data),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log to database
    $this->db->insert('security_audit_log', $log_data);
    
    // Log to file for immediate monitoring
    log_message('warning', "Security Event: {$event_type} - " . json_encode($data));
}
```

### **3. Database Security Patterns**

#### **SQL Injection Prevention**
```php
// Secure Database Queries with Parameter Binding
public function get_web_visibility_data_secure($filters = [])
{
    $sql = "SELECT 
                i.id, i.code, i.web_vis, i.web_vis_toggle,
                p.name as product_name, p.web_visibility,
                s.name as status_name
            FROM T_ITEM i
            LEFT JOIN T_PRODUCT p ON i.product_id = p.id
            LEFT JOIN P_PRODUCT_STATUS s ON i.status_id = s.id
            WHERE i.archived = ? AND p.archived = ?";
    
    $params = ['N', 'N'];
    
    // Add dynamic filters with parameter binding
    if (!empty($filters['product_id'])) {
        $sql .= " AND i.product_id = ?";
        $params[] = (int) $filters['product_id'];
    }
    
    if (!empty($filters['status_id'])) {
        $sql .= " AND i.status_id = ?";
        $params[] = (int) $filters['status_id'];
    }
    
    if (!empty($filters['web_vis'])) {
        $sql .= " AND i.web_vis = ?";
        $params[] = (int) $filters['web_vis'];
    }
    
    return $this->db->query($sql, $params)->result_array();
}

// Secure Update with Parameter Binding
public function update_web_visibility_secure($item_id, $web_vis, $web_vis_toggle)
{
    $sql = "UPDATE T_ITEM 
            SET web_vis = ?, web_vis_toggle = ?, date_modif = ?
            WHERE id = ? AND archived = ?";
    
    $params = [
        $web_vis ? 1 : 0,
        $web_vis_toggle ? 1 : 0,
        date('Y-m-d H:i:s'),
        (int) $item_id,
        'N'
    ];
    
    return $this->db->query($sql, $params);
}
```

#### **XSS Prevention**
```php
// XSS Prevention for Output
public function sanitize_web_visibility_output($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = $this->sanitize_web_visibility_output($value);
        }
    } elseif (is_string($data)) {
        // Use CodeIgniter's XSS cleaning
        $data = $this->security->xss_clean($data);
        
        // Additional HTML entity encoding for display
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

// Safe JSON Response
public function send_secure_json_response($data, $status_code = 200)
{
    // Sanitize output data
    $sanitized_data = $this->sanitize_web_visibility_output($data);
    
    // Set security headers
    $this->output->set_header('Content-Type: application/json; charset=utf-8');
    $this->output->set_header('X-Content-Type-Options: nosniff');
    $this->output->set_header('X-Frame-Options: DENY');
    $this->output->set_header('X-XSS-Protection: 1; mode=block');
    
    // Send response
    $this->output->set_status_header($status_code);
    $this->output->set_output(json_encode($sanitized_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
}
```

---

## 📊 **MONITORING & LOGGING PATTERNS**

### **1. Web Visibility Change Logging (OPMS-Enhanced)**

#### **Comprehensive Audit Logging**
```php
// Log Web Visibility Changes with OPMS Integration
public function log_web_visibility_change($item_id, $old_state, $new_state, $user_id, $change_type = 'explicit')
{
    $log_data = [
        'item_id' => $item_id,
        'product_id' => $this->get_item_product_id($item_id),
        'old_web_vis' => $old_state['web_vis'] ?? null,
        'new_web_vis' => $new_state['web_vis'] ?? null,
        'old_web_vis_toggle' => $old_state['web_vis_toggle'] ?? null,
        'new_web_vis_toggle' => $new_state['web_vis_toggle'] ?? null,
        'change_type' => $change_type,
        'user_id' => $user_id,
        'ip_address' => $this->input->ip_address(),
        'user_agent' => $this->input->user_agent(),
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => $this->session->userdata('session_id'),
        'notes' => $this->generate_change_notes($old_state, $new_state, $change_type)
    ];
    
    $this->db->insert('web_visibility_audit_log', $log_data);
    
    // Also log to file for immediate monitoring
    $this->log_to_file('web_visibility_change', $log_data);
}

// Generate Human-Readable Change Notes
private function generate_change_notes($old_state, $new_state, $change_type)
{
    $notes = [];
    
    if ($change_type === 'explicit') {
        $notes[] = 'User explicitly changed web visibility';
    } elseif ($change_type === 'lazy_calculation') {
        $notes[] = 'Automatically calculated during lazy calculation';
    } elseif ($change_type === 'batch_update') {
        $notes[] = 'Updated via batch operation';
    }
    
    if (isset($old_state['web_vis']) && isset($new_state['web_vis'])) {
        if ($old_state['web_vis'] != $new_state['web_vis']) {
            $notes[] = sprintf('Web visibility changed from %s to %s', 
                $old_state['web_vis'] ? 'visible' : 'hidden',
                $new_state['web_vis'] ? 'visible' : 'hidden'
            );
        }
    }
    
    if (isset($old_state['web_vis_toggle']) && isset($new_state['web_vis_toggle'])) {
        if ($old_state['web_vis_toggle'] != $new_state['web_vis_toggle']) {
            $notes[] = sprintf('Manual override %s', 
                $new_state['web_vis_toggle'] ? 'enabled' : 'disabled'
            );
        }
    }
    
    return implode('; ', $notes);
}

// Get Item Product ID for Logging
private function get_item_product_id($item_id)
{
    $this->db->select('product_id');
    $this->db->from('T_ITEM');
    $this->db->where('id', $item_id);
    
    $result = $this->db->get()->row();
    return $result ? $result->product_id : null;
}

// Log to File for Immediate Monitoring
private function log_to_file($event_type, $data)
{
    $log_message = sprintf(
        "[%s] %s: %s\n",
        date('Y-m-d H:i:s'),
        $event_type,
        json_encode($data)
    );
    
    $log_file = APPPATH . 'logs/web_visibility_' . date('Y-m-d') . '.log';
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}
```

#### **Performance Monitoring (OPMS-Enhanced)**
```php
// Monitor Web Visibility Performance with OPMS Integration
public function monitor_visibility_calculation_performance($item_id, $start_time, $operation_type = 'calculation')
{
    $execution_time = microtime(true) - $start_time;
    $memory_usage = memory_get_usage(true);
    $peak_memory = memory_get_peak_usage(true);
    
    $performance_data = [
        'item_id' => $item_id,
        'operation_type' => $operation_type,
        'execution_time' => $execution_time,
        'memory_usage' => $memory_usage,
        'peak_memory' => $peak_memory,
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $this->session->userdata('user_id')
    ];
    
    // Log slow operations
    if ($execution_time > 0.5) {
        $this->log_operation('slow_visibility_calculation', $performance_data, 'warning');
    }
    
    // Log memory-intensive operations
    if ($memory_usage > 50 * 1024 * 1024) { // 50MB
        $this->log_operation('high_memory_usage', $performance_data, 'warning');
    }
    
    // Store performance metrics
    $this->db->insert('web_visibility_performance_log', $performance_data);
    
    return $performance_data;
}

// Performance Metrics Dashboard Data
public function get_performance_metrics($days = 7)
{
    $sql = "SELECT 
                DATE(timestamp) as date,
                operation_type,
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                AVG(memory_usage) as avg_memory_usage,
                MAX(peak_memory) as max_peak_memory,
                COUNT(*) as operation_count
            FROM web_visibility_performance_log 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(timestamp), operation_type
            ORDER BY date DESC, operation_type";
    
    return $this->db->query($sql, [$days])->result_array();
}
```

### **2. Comprehensive Testing Scenarios**

#### **Unit Testing (PHPUnit)**
```php
// Web Visibility Unit Tests
class WebVisibilityTest extends PHPUnit_Framework_TestCase
{
    private $model;
    private $controller;
    
    protected function setUp()
    {
        parent::setUp();
        $this->model = $this->getMockBuilder('WebVisibilityModel')
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->controller = $this->getMockBuilder('WebVisibilityController')
                               ->disableOriginalConstructor()
                               ->getMock();
    }
    
    public function test_auto_visibility_calculation_with_beauty_shot()
    {
        // Test Case A: All conditions met for auto-visibility
        $item_data = [
            'id' => 1,
            'product_id' => 1,
            'status_name' => 'RUN',
            'web_vis_toggle' => false
        ];
        
        $product_visibility = [
            'enabled' => true,
            'value' => true,
            'reason' => 'Beauty shot exists'
        ];
        
        $this->model->expects($this->once())
                   ->method('get_product_web_visibility_state')
                   ->with(1)
                   ->willReturn($product_visibility);
        
        $this->model->expects($this->once())
                   ->method('get_manual_override_state')
                   ->with(1)
                   ->willReturn(false);
        
        $this->model->expects($this->once())
                   ->method('is_valid_auto_visibility_status')
                   ->with('RUN')
                   ->willReturn(true);
        
        $result = $this->model->calculate_auto_visibility(1, 1, 'RUN');
        $this->assertTrue($result);
    }
    
    public function test_auto_visibility_calculation_without_beauty_shot()
    {
        // Test Case B: No beauty shot - should return false
        $product_visibility = [
            'enabled' => false,
            'value' => false,
            'reason' => 'No beauty shot uploaded'
        ];
        
        $this->model->expects($this->once())
                   ->method('get_product_web_visibility_state')
                   ->with(1)
                   ->willReturn($product_visibility);
        
        $result = $this->model->calculate_auto_visibility(1, 1, 'RUN');
        $this->assertFalse($result);
    }
    
    public function test_manual_override_calculation()
    {
        // Test Case C: Manual override enabled
        $product_visibility = [
            'enabled' => true,
            'value' => true,
            'reason' => 'Beauty shot exists'
        ];
        
        $this->model->expects($this->once())
                   ->method('get_product_web_visibility_state')
                   ->with(1)
                   ->willReturn($product_visibility);
        
        $this->model->expects($this->once())
                   ->method('get_manual_override_state')
                   ->with(1)
                   ->willReturn(true);
        
        $result = $this->model->calculate_manual_visibility(1, 1, true);
        $this->assertTrue($result);
    }
    
    public function test_invalid_status_auto_visibility()
    {
        // Test Case D: Invalid status - should return false
        $product_visibility = [
            'enabled' => true,
            'value' => true,
            'reason' => 'Beauty shot exists'
        ];
        
        $this->model->expects($this->once())
                   ->method('get_product_web_visibility_state')
                   ->with(1)
                   ->willReturn($product_visibility);
        
        $this->model->expects($this->once())
                   ->method('is_valid_auto_visibility_status')
                   ->with('HOLD')
                   ->willReturn(false);
        
        $result = $this->model->calculate_auto_visibility(1, 1, 'HOLD');
        $this->assertFalse($result);
    }
    
    public function test_lazy_calculation_performance()
    {
        // Test Case E: Lazy calculation performance
        $items = [
            ['id' => 1, 'web_vis' => null],
            ['id' => 2, 'web_vis' => null],
            ['id' => 3, 'web_vis' => 1]
        ];
        
        $start_time = microtime(true);
        
        $this->model->expects($this->exactly(2))
                   ->method('calculate_web_visibility_for_item')
                   ->willReturn(true);
        
        $result = $this->model->get_colorline_list_with_visibility();
        
        $execution_time = microtime(true) - $start_time;
        $this->assertLessThan(1.0, $execution_time, 'Lazy calculation should complete within 1 second');
    }
}
```

#### **Integration Testing**
```php
// Web Visibility Integration Tests
class WebVisibilityIntegrationTest extends PHPUnit_Framework_TestCase
{
    private $CI;
    
    protected function setUp()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('WebVisibilityModel');
        $this->CI->load->library('unit_test');
    }
    
    public function test_product_web_visibility_update_flow()
    {
        // Test complete product web visibility update flow
        $product_id = 1;
        $web_visibility = true;
        
        // Mock beauty shot check
        $this->CI->WebVisibilityModel->expects($this->once())
                                   ->method('check_beauty_shot_uploaded')
                                   ->with($product_id)
                                   ->willReturn(true);
        
        // Test controller method
        $response = $this->CI->WebVisibilityController->update_product_web_visibility();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function test_colorline_visibility_recalculation_flow()
    {
        // Test colorline visibility recalculation flow
        $item_id = 1;
        
        $response = $this->CI->WebVisibilityController->recalculate_colorline_visibility();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody());
        
        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }
    
    public function test_lazy_calculation_on_colorline_listing()
    {
        // Test lazy calculation during colorline listing
        $this->create_test_items_with_null_web_vis();
        
        $response = $this->CI->WebVisibilityController->get_colorline_list();
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify items now have calculated web_vis values
        $items = $this->CI->WebVisibilityModel->get_colorline_items();
        foreach ($items as $item) {
            $this->assertNotNull($item['web_vis'], 'Item should have calculated web_vis value');
        }
    }
    
    public function test_eye_icon_database_driven_state()
    {
        // Test eye icon always reflects database state
        $item_id = 1;
        
        // Set specific web_vis value in database
        $this->CI->db->where('id', $item_id);
        $this->CI->db->update('T_ITEM', ['web_vis' => 1]);
        
        // Get eye icon state
        $eye_state = $this->CI->WebVisibilityModel->get_eye_icon_state($item_id);
        $this->assertEquals('visible', $eye_state);
        
        // Change database value
        $this->CI->db->where('id', $item_id);
        $this->CI->db->update('T_ITEM', ['web_vis' => 0]);
        
        // Eye icon should reflect new database value
        $eye_state = $this->CI->WebVisibilityModel->get_eye_icon_state($item_id);
        $this->assertEquals('hidden', $eye_state);
    }
    
    private function create_test_items_with_null_web_vis()
    {
        // Create test data with NULL web_vis values
        $test_data = [
            ['id' => 1, 'product_id' => 1, 'web_vis' => null],
            ['id' => 2, 'product_id' => 1, 'web_vis' => null],
            ['id' => 3, 'product_id' => 2, 'web_vis' => null]
        ];
        
        foreach ($test_data as $item) {
            $this->CI->db->insert('T_ITEM', $item);
        }
    }
}
```

#### **Performance Testing**
```php
// Web Visibility Performance Tests
class WebVisibilityPerformanceTest extends PHPUnit_Framework_TestCase
{
    public function test_large_dataset_lazy_calculation()
    {
        // Test lazy calculation with large dataset
        $this->create_large_test_dataset(1000);
        
        $start_time = microtime(true);
        $items = $this->CI->WebVisibilityModel->get_colorline_list_with_visibility();
        $execution_time = microtime(true) - $start_time;
        
        $this->assertLessThan(5.0, $execution_time, 'Large dataset lazy calculation should complete within 5 seconds');
        $this->assertCount(1000, $items, 'Should return all 1000 items');
    }
    
    public function test_batch_update_performance()
    {
        // Test batch update performance
        $updates = [];
        for ($i = 1; $i <= 100; $i++) {
            $updates[] = [
                'id' => $i,
                'web_vis' => rand(0, 1),
                'web_vis_toggle' => rand(0, 1)
            ];
        }
        
        $start_time = microtime(true);
        $result = $this->CI->WebVisibilityModel->batch_update_web_visibility_values($updates);
        $execution_time = microtime(true) - $start_time;
        
        $this->assertTrue($result, 'Batch update should succeed');
        $this->assertLessThan(2.0, $execution_time, 'Batch update should complete within 2 seconds');
    }
    
    public function test_memory_usage_large_list()
    {
        // Test memory usage with large list
        $initial_memory = memory_get_usage(true);
        
        $this->create_large_test_dataset(5000);
        $items = $this->CI->WebVisibilityModel->get_colorline_list_with_visibility();
        
        $peak_memory = memory_get_peak_usage(true);
        $memory_increase = $peak_memory - $initial_memory;
        
        $this->assertLessThan(100 * 1024 * 1024, $memory_increase, 'Memory usage should not exceed 100MB');
    }
    
    private function create_large_test_dataset($count)
    {
        // Create large test dataset
        $this->CI->db->query("TRUNCATE TABLE T_ITEM");
        
        for ($i = 1; $i <= $count; $i++) {
            $this->CI->db->insert('T_ITEM', [
                'id' => $i,
                'product_id' => rand(1, 10),
                'web_vis' => null,
                'web_vis_toggle' => 0,
                'archived' => 'N'
            ]);
        }
    }
}
```

---

## 🎯 **PROMPT ENGINEERING BEST PRACTICES**

### **1. Context-Aware Prompts (OPMS-Enhanced)**

#### **Prerequisite Context (MANDATORY)**
```
OPMS SYSTEM CONTEXT (REQUIRED FIRST):
- Read: docs/ai-specs/OPMS-AI-Model-Specification.md
- Framework: CodeIgniter 3.1.11 + PHP 7.3
- Business Domain: Fabric/textile management with vendor integration
- Architecture: Multi-database (master_app, sales, showroom)
- Security: Ion Auth + REST API with role-based permissions
- Integration: NetSuite ERP, S3 file storage, custom REST endpoints
- Database: 150+ tables with T_*, P_*, Z_* naming conventions
- Performance: Lazy calculation, batch operations, caching strategies
```

#### **Web Visibility Context Injection (ENHANCED)**
```
WEB VISIBILITY CONTEXT (OPMS-SPECIFIC):
- Feature: Product/Colorline web visibility management (extends OPMS system)
- Logic: Auto-determination based on product status and beauty shot dependency
- Override: Manual toggle system for user control with database persistence
- Database: T_ITEM.web_vis (computed state) and T_ITEM.web_vis_toggle (manual override)
- UI: Eye icon reflection (always database-driven), form controls, real-time updates
- Security: OPMS permission system, vendor-based access, CSRF protection, rate limiting
- Performance: Lazy calculation for NULL values, batch operations, query optimization
- Integration: Builds upon existing Product, Item, and Vendor management systems
- Testing: Comprehensive unit, integration, and performance test scenarios
- Monitoring: Audit logging, performance metrics, security event tracking
```

#### **Business Logic Context**
```
WEB VISIBILITY BUSINESS LOGIC:
- Product Level: Beauty shot dependency check with file verification (S3/local)
- Colorline Level: Auto-determination (RUN/LTDQTY/RKFISH statuses) vs manual override
- Calculation Triggers: Explicit editing (immediate) and lazy calculation (listing display)
- Database State: web_vis allows NULL initially, calculated lazily or explicitly
- Eye Icons: Always reflect stored T_ITEM.web_vis database values (never calculated on-the-fly)
- Status Validation: Only specific statuses allow auto-visibility
- Vendor Access: User permissions based on vendor relationships
- Error Handling: Comprehensive validation with detailed error logging
```

#### **Technical Implementation Context**
```
TECHNICAL IMPLEMENTATION CONTEXT:
- CodeIgniter 3: MVC pattern, Active Record, form validation, security library
- Database: MySQL with parameterized queries, transaction support, indexing
- JavaScript: jQuery-based UI management with AJAX integration
- Security: XSS prevention, CSRF tokens, input validation, permission checks
- Performance: Caching, batch operations, query optimization, memory management
- Testing: PHPUnit framework with unit, integration, and performance tests
- Monitoring: Structured logging, performance metrics, security audit trails
```

### **2. Specialized Web Visibility Prompts**

#### **Implementation Analysis Prompt (ENHANCED)**
```
WEB VISIBILITY IMPLEMENTATION ANALYSIS (OPMS-SPECIFIC):
- Feature: Web Visibility Logic Flow Management
- Context: OPMS CodeIgniter 3 system with Product/Colorline hierarchy
- Requirements: Auto-determination, manual override, beauty shot dependency
- Database: T_ITEM table with web_vis and web_vis_toggle columns
- Business Logic: Beauty shot dependency, status validation, vendor access
- Performance: Lazy calculation, batch operations, query optimization
- Security: OPMS permission system, CSRF protection, rate limiting
- UI/UX: Eye icon states, form controls, real-time updates
- Testing: Unit, integration, and performance test scenarios

ANALYSIS FRAMEWORK:
1. Logic Flow: Product-level beauty shot dependency, colorline auto-determination
2. Database Design: Schema changes, data integrity, performance considerations
3. UI/UX Integration: Form controls, eye icon states, real-time updates
4. Security: OPMS permission system, input validation, CSRF protection
5. Testing: Unit tests, integration tests, edge case scenarios
6. Performance: Query optimization, batch operations, caching strategies
7. Monitoring: Audit logging, performance metrics, security tracking
```

#### **Bug Fix Prompt for Web Visibility (ENHANCED)**
```
WEB VISIBILITY BUG FIX REQUEST (OPMS-SPECIFIC):
- Issue: [Specific web visibility problem description]
- Context: [Product/Colorline level, auto/manual mode, OPMS system]
- Expected Behavior: [What should happen according to logic flow]
- Current Behavior: [What actually happens]
- Database State: [Current T_ITEM.web_vis and web_vis_toggle values]
- OPMS Context: [Product ID, vendor access, user permissions]

DEBUGGING APPROACH:
1. Logic Flow Analysis: Trace through auto-determination vs manual override logic
2. Database State Check: Verify T_ITEM table values and relationships
3. Beauty Shot Dependency: Confirm product-level beauty shot requirements
4. Status Validation: Check colorline status against valid auto-visibility statuses
5. UI State Sync: Ensure form controls reflect database state correctly
6. Permission Check: Verify user has appropriate edit permissions
7. Vendor Access: Check user's vendor-based access to product/item
8. Performance Impact: Assess lazy calculation and batch operation efficiency
9. Security Audit: Review CSRF tokens, input validation, rate limiting
10. Error Logging: Check audit logs and performance metrics
```

#### **Feature Enhancement Prompt (ENHANCED)**
```
WEB VISIBILITY FEATURE ENHANCEMENT (OPMS-SPECIFIC):
- Enhancement: [Specific feature improvement request]
- Business Value: [Why this enhancement is needed]
- Current Implementation: [Existing web visibility logic and UI]
- OPMS Integration: [How it fits with existing Product/Item/Vendor systems]
- Technical Requirements: [Performance, security, integration needs]

DEVELOPMENT APPROACH:
1. Logic Extension: Extend existing auto-determination or manual override logic
2. Database Updates: Modify T_ITEM schema or add supporting tables
3. UI Enhancement: Update form controls, eye icon behavior, or list views
4. API Integration: Create/update REST endpoints for new functionality
5. Security Integration: Ensure OPMS permission system compatibility
6. Performance Optimization: Consider lazy calculation and batch operations
7. Testing Strategy: Unit tests for logic, integration tests for UI, edge cases
8. Migration Plan: Database migration, data migration, deployment strategy
9. Monitoring: Add audit logging and performance metrics
10. Documentation: Update AI model specification and user guides
```

### **3. Error Prevention Strategies (OPMS-Enhanced)**

#### **Validation Prompts**
- Always validate beauty shot dependency before enabling product web visibility
- Check colorline status against valid auto-visibility statuses (RUN, LTDQTY, RKFISH)
- Verify user permissions before allowing visibility changes
- Ensure database transactions for data consistency
- Validate form inputs and API parameters with OPMS-specific rules
- Check vendor access for product/item operations
- Verify CSRF tokens for all state-changing operations
- Implement rate limiting for high-frequency operations

#### **Safety Checks**
- Confirm T_ITEM table exists and has required columns (web_vis, web_vis_toggle)
- Verify product exists and is active before checking beauty shot dependency
- Check item exists and is active before calculating visibility state
- Ensure proper error handling for database operations
- Validate UI state synchronization with database state
- Check OPMS permission system integration
- Verify vendor-based access controls
- Monitor performance metrics for lazy calculation operations

### **4. Performance Optimization Prompts**

#### **Lazy Calculation Optimization**
```
LAZY CALCULATION PERFORMANCE OPTIMIZATION:
- Context: Colorline listing with NULL web_vis values
- Goal: Efficient calculation and storage of web visibility states
- Strategy: Batch operations, query optimization, memory management
- Monitoring: Track execution time, memory usage, database performance
- Thresholds: < 2 seconds for 1000 items, < 100MB memory usage
- Caching: Implement status lookup caching for repeated calculations
- Indexing: Ensure proper database indexes for web_vis queries
```

#### **Batch Operations Optimization**
```
BATCH OPERATIONS OPTIMIZATION:
- Context: Multiple colorline visibility updates
- Goal: Efficient batch processing with transaction safety
- Strategy: Use CodeIgniter's update_batch, chunk processing
- Monitoring: Track batch size, execution time, memory usage
- Error Handling: Rollback on failure, retry mechanisms
- Performance: Target < 1 second for 100 item batch updates
```

---

## 📚 **REFERENCE MATERIALS**

### **Primary OPMS Documentation (REQUIRED)**
- **[OPMS AI Model Specification](../OPMS-AI-Model-Specification.md)** - **MUST READ FIRST** - Complete system architecture, patterns, and prompt engineering
- **OPMS CodeIgniter 3 Framework**: MVC patterns, security practices, database operations
- **OPMS Business Domain**: Fabric/textile management, vendor relationships, inventory tracking

### **CodeIgniter 3 Documentation**
- [Form Validation Library](https://codeigniter.com/userguide3/libraries/form_validation.html)
- [Database Active Record](https://codeigniter.com/userguide3/database/active_record.html)
- [Security Library](https://codeigniter.com/userguide3/libraries/security.html)

### **Web Visibility Requirements**
- Web Visibility Logic Flow Change Document
- Testing Scenarios A-E Specification
- T_ITEM Table Schema Updates
- UI/UX Requirements and Eye Icon Behavior

---

## 🔄 **VERSION HISTORY**

### **v1.2.0 - OPMS Integration Enhancement**
- **Date**: January 15, 2025
- **Status**: Ready for Implementation
- **Features**: 
  - Enhanced database schema with comprehensive T_ITEM table specifications
  - Integrated OPMS-specific business logic patterns and validation
  - Added comprehensive security patterns with CodeIgniter 3 implementations
  - Enhanced UI/UX integration with detailed JavaScript and HTML examples
  - Added comprehensive testing scenarios and performance monitoring
  - Updated prompt engineering strategies for OPMS-specific context
- **Coverage**: Complete OPMS integration with Web Visibility feature
- **New Features**:
  - OPMS permission system integration
  - Vendor-based access controls
  - Enhanced security logging and monitoring
  - Comprehensive test scenarios (unit, integration, performance)
  - Performance optimization patterns
  - Advanced prompt engineering strategies

### **v1.1.0 - Lazy Calculation Enhancement**
- **Date**: January 15, 2025
- **Status**: Ready for Implementation
- **Features**: Added lazy calculation for colorline listing, database-driven eye icons
- **Coverage**: Product-level, colorline-level, lazy calculation, database, UI/UX, security, testing
- **New Features**:
  - Lazy calculation during colorline listing display
  - Database-driven eye icon states (always reflect stored values)
  - Performance optimization for large lists
  - Batch operations for NULL web_vis values
  - Enhanced testing scenarios for lazy calculation

### **v1.0.0 - Initial Release**
- **Date**: January 15, 2025
- **Status**: Ready for Implementation
- **Features**: Complete Web Visibility Logic Flow implementation specification
- **Coverage**: Product-level, colorline-level, database, UI/UX, security, testing

---

## 📝 **MAINTENANCE NOTES**

### **Regular Updates Required**
- Monitor T_ITEM table performance with new columns
- Review web visibility logic for business rule changes
- Update test cases as requirements evolve
- Monitor UI responsiveness with large datasets
- Review OPMS permission system integration
- Monitor security audit logs and performance metrics

### **Performance Monitoring**
- Track visibility calculation performance
- Monitor database query execution times
- Review batch operation efficiency
- Monitor UI update responsiveness
- Track lazy calculation performance metrics
- Monitor memory usage during large operations

### **Security Monitoring**
- Review security audit logs regularly
- Monitor rate limiting effectiveness
- Track permission system usage
- Review vendor access patterns
- Monitor CSRF token validation
- Track input validation effectiveness

---

**End of Web Visibility AI Model Specification**

*This document serves as the definitive guide for AI assistance with Web Visibility Logic Flow implementation in the OPMS CodeIgniter 3 system. It should be updated regularly to reflect system changes and improvements.*

## 📚 **REFERENCE MATERIALS**

### **Primary OPMS Documentation (REQUIRED)**
- **[OPMS AI Model Specification](../OPMS-AI-Model-Specification.md)** - **MUST READ FIRST** - Complete system architecture, patterns, and prompt engineering
- **OPMS CodeIgniter 3 Framework**: MVC patterns, security practices, database operations
- **OPMS Business Domain**: Fabric/textile management, vendor relationships, inventory tracking

### **CodeIgniter 3 Documentation**
- [Form Validation Library](https://codeigniter.com/userguide3/libraries/form_validation.html)
- [Database Active Record](https://codeigniter.com/userguide3/database/active_record.html)
- [Security Library](https://codeigniter.com/userguide3/libraries/security.html)

### **Web Visibility Requirements**
- Web Visibility Logic Flow Change Document
- Testing Scenarios A-E Specification
- T_ITEM Table Schema Updates
- UI/UX Requirements and Eye Icon Behavior

---

## 🔄 **VERSION HISTORY**

### **v1.1.0 - Lazy Calculation Enhancement**
- **Date**: January 15, 2025
- **Status**: Ready for Implementation
- **Features**: Added lazy calculation for colorline listing, database-driven eye icons
- **Coverage**: Product-level, colorline-level, lazy calculation, database, UI/UX, security, testing
- **New Features**:
  - Lazy calculation during colorline listing display
  - Database-driven eye icon states (always reflect stored values)
  - Performance optimization for large lists
  - Batch operations for NULL web_vis values
  - Enhanced testing scenarios for lazy calculation

### **v1.0.0 - Initial Release**
- **Date**: January 15, 2025
- **Status**: Ready for Implementation
- **Features**: Complete Web Visibility Logic Flow implementation specification
- **Coverage**: Product-level, colorline-level, database, UI/UX, security, testing

---

## 📝 **MAINTENANCE NOTES**

### **Regular Updates Required**
- Monitor T_ITEM table performance with new columns
- Review web visibility logic for business rule changes
- Update test cases as requirements evolve
- Monitor UI responsiveness with large datasets

### **Performance Monitoring**
- Track visibility calculation performance
- Monitor database query execution times
- Review batch operation efficiency
- Monitor UI update responsiveness

---

**End of Web Visibility AI Model Specification**

*This document serves as the definitive guide for AI assistance with Web Visibility Logic Flow implementation in the OPMS CodeIgniter 3 system. It should be updated regularly to reflect system changes and improvements.*

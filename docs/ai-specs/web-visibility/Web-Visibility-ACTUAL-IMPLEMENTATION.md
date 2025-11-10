# Web Visibility - ACTUAL IMPLEMENTATION
## CodeIgniter 3 - Current Production Code

**Version:** 2.0.0  
**Date:** October 8, 2025  
**Target Framework:** CodeIgniter 3.1.11 + PHP 7.3  
**Status:** ✅ CURRENT IMPLEMENTATION (Reflects actual production code)  
**Application:** OPMS (Opuzen Product Management System)

---

## 🎯 **EXECUTIVE SUMMARY**

This document describes the **ACTUAL working implementation** of web visibility in OPMS, not theoretical or planned features. This reflects the code that is currently running in production.

### **Core Features**
- ✅ **Product-Level Web Visibility**: Checkbox in Product Edit Form (dependent on beauty shot)
- ✅ **Item-Level Web Visibility**: Auto-calculated OR manual override with toggle
- ✅ **Parent-Child Cascade**: When parent visibility changes, all child items recalculate
- ✅ **Lazy Calculation**: Items with `web_vis = NULL` are calculated during listing display
- ✅ **Visual Indicators**: Blue eye icons in DataTables reflect `T_ITEM.web_vis` database values

---

## 📊 **DATABASE SCHEMA (ACTUAL)**

### **SHOWCASE_PRODUCT Table (Parent Product Level)**
```sql
-- Existing table - NO CHANGES NEEDED
product_id   INT NOT NULL PRIMARY KEY
product_type VARCHAR(2) NOT NULL
visible      CHAR(1) NOT NULL              -- 'Y' = visible on web, 'N' = hidden
pic_big_url  VARCHAR(150) NULL             -- Beauty shot URL (NULL = no beauty shot)
url_title    VARCHAR(100) NOT NULL
descr        TEXT NULL
date_modif   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
user_id      INT NOT NULL DEFAULT 0
```

**Key Points:**
- `visible` is CHAR(1), not BOOLEAN - use 'Y'/'N' comparisons
- `pic_big_url` determines if checkbox is enabled (not null = enabled)
- No separate `web_visibility` column in T_PRODUCT

### **T_ITEM Table (Item/Colorline Level)**
```sql
-- Existing columns used for web visibility
web_vis         TINYINT(1) NULL DEFAULT NULL     -- 0=hidden, 1=visible, NULL=needs calculation
web_vis_toggle  TINYINT(1) NOT NULL DEFAULT 0    -- 0=auto mode, 1=manual mode
web_vis2        INT NOT NULL DEFAULT 0           -- LEGACY - NOT USED
web_vis_checkbox TINYINT(1) NULL DEFAULT 0       -- LEGACY - NOT USED

-- Indexes
KEY idx_web_vis (web_vis)
KEY idx_web_vis_toggle (web_vis_toggle)
```

**Key Points:**
- `web_vis` can be NULL - triggers lazy calculation on listing display
- `web_vis_toggle` determines mode: auto (0) or manual (1)
- Both are TINYINT(1), not BOOLEAN - use numeric comparisons

---

## 🏢 **PRODUCT (PARENT) LEVEL IMPLEMENTATION**

### **Location:** `application/views/product/form/form_regular.php` (lines 698-713)

### **How It Works:**

```php
// 1. Check if beauty shot exists
$has_beauty_shot = !empty($info['pic_big_url']) && $info['pic_big_url'] !== '';

// 2. Disable checkbox if no beauty shot
$checkbox_disabled = !$has_beauty_shot ? 'disabled' : '';

// 3. Set checked state from database
$checkbox_checked = (!$isNew && $info['showcase_visible'] === 'Y' && $has_beauty_shot) ? 'checked' : '';
```

```html
<!-- Checkbox HTML -->
<input type="checkbox" class="custom-control-input form-control" 
       id="showcase_visible" name="showcase_visible" value="1" 
       <?php echo $checkbox_checked . ' ' . $checkbox_disabled ?> >
<label class="custom-control-label" for="showcase_visible">Web Visible</label>
```

### **Saving (Product Controller)**

**Location:** `application/controllers/Product.php::save_regular_product()` (lines 1620-1644)

```php
// Handle checkbox submission
$showcase_visible = $this->input->post('showcase_visible');
if (is_null($showcase_visible)) {
    $showcase_visible = '0';  // Unchecked = not submitted
}

// Force to '0' if no beauty shot
$has_beauty_shot = !is_null($this->input->post('pic_big_delete')) || strlen($new_location_db) > 0;
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

// CRITICAL: After saving, update all child items
$this->update_child_items_web_visibility($product_id);
```

### **Key JavaScript Fix**

**Location:** `assets/js/form_validation.js` (lines 207-221)

**Problem:** Form had hardcoded `<input type="hidden" name="change_showcase" value="0">`. When checkbox changed, JavaScript tried to add the field but found it already existed and didn't update it.

**Solution:**
```javascript
addHiddenInput: function (inputName, new_value = 1) {
    var frmID = this.formID;
    var existingInput = $("form" + frmID + " > input[type='hidden'][name='" + inputName + "']");
    
    if (existingInput.length > 0) {
        // UPDATE existing hidden input value
        existingInput.val(new_value);
    } else if ($('form' + frmID).length) {
        // Add new hidden input
        $('<input>').attr({
            type: 'hidden',
            name: inputName,
            value: new_value
        }).appendTo('form' + frmID);
    }
}
```

**Also added checkbox handling:**
```javascript
// In change event handler (lines 344-347)
if ($(this).attr('type') === 'checkbox') {
    new_val = $(this).is(':checked') ? '1' : '0';
}
```

---

## 🎨 **ITEM (COLORLINE) LEVEL IMPLEMENTATION**

### **Location:** `application/views/item/form/view.php` (lines 592-689)

### **Parent Visibility Indicator**

```php
// Show parent product web visibility status
$parent_vis_status = (isset($info['parent_product_visibility']) && $info['parent_product_visibility'] == 'Y');
$parent_vis_icon = $parent_vis_status ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
$parent_vis_text = $parent_vis_status ? 'Parent product web visibility: ON' : 'Parent product web visibility: OFF';
```

Displays: **"🟢 Parent product web visibility: ON"** or **"🔴 Parent product web visibility: OFF"**

### **Checkbox State Determination**

```php
$is_manual_override = (isset($info['web_vis_toggle']) && $info['web_vis_toggle'] === '1');

if ($is_manual_override) {
    // MANUAL MODE: Use stored database value
    $is_checked = (isset($info['web_vis']) && $info['web_vis'] === '1');
} else {
    // AUTO MODE: Calculate based on THREE conditions
    $has_item_images = (!empty($info['pic_big_url']) || !empty($info['pic_hd_url']));
    $has_valid_status = (isset($info['status']) && in_array($info['status'], ['RUN', 'LTDQTY', 'RKFISH']));
    $parent_has_beauty_shot = (isset($info['parent_product_visibility']) && $info['parent_product_visibility'] == "Y");
    
    $is_checked = ($has_item_images && $has_valid_status && $parent_has_beauty_shot);
}

$checked = $is_checked ? 'checked' : '';
```

### **THREE Conditions for Auto-Checked:**
1. ✅ Item has images (pic_big_url OR pic_hd_url)
2. ✅ Status is **RUN, LTDQTY, or RKFISH**
3. ✅ Parent product visibility = 'Y'

### **UI Controls**

```html
<!-- Web Visible Checkbox -->
<input type="checkbox" class="custom-control-input form-control" 
       id="web_vis" name="web_vis" <?php echo $checked; ?> 
       <?php echo $is_disabled ? 'disabled' : ''; ?>>
<label class="custom-control-label" for="web_vis">
    Web Visible
    <?php if (!$manual_override_enabled && $has_images) { ?>
        <i class="fas fa-lock text-muted" title="Auto-calculated"></i>
    <?php } ?>
</label>

<!-- Manual Override Toggle Switch -->
<label class="switch" title="Manual Override">
    <input type="checkbox" name="web_vis_toggle" id="web_vis_toggle" 
           <?php echo (isset($info['web_vis_toggle']) && $info['web_vis_toggle'] === '1' ? 'checked' : '') ?> 
           class="form-control" <?php echo !$has_images ? 'disabled' : ''; ?>>
    <span class="slider round"></span>
</label>
<small class="form-text text-muted d-block">Manual Override</small>
```

### **JavaScript Toggle Handler**

**Location:** `application/views/item/form/view.php` (lines 1261-1289)

```javascript
// Toggle between auto and manual mode
$(document).on('change', '#web_vis_toggle', function() {
    const isEnabled = $(this).is(':checked');
    const $webVisCheckbox = $('#web_vis');
    const $label = $webVisCheckbox.next('label');
    
    if (isEnabled) {
        // Manual mode - remove lock icon
        $label.find('.fa-lock').remove();
    } else {
        // Auto mode - add lock icon
        if ($label.find('.fa-lock').length === 0) {
            $label.append(' <i class="fas fa-lock text-muted" title="Auto-calculated"></i>');
        }
    }
});

// Prevent checkbox changes in auto mode
$(document).on('click', '#web_vis', function(e) {
    const $toggle = $('#web_vis_toggle');
    const manualOverrideOn = $toggle.is(':checked');
    
    if (!manualOverrideOn) {
        e.preventDefault();
        const currentState = $(this).is(':checked');
        $(this).prop('checked', currentState);
        return false;
    }
});
```

---

## 🔄 **CALCULATION LOGIC (ACTUAL IMPLEMENTATION)**

### **Product Controller**

**Location:** `application/controllers/Product.php` (lines 2074-2165)

```php
// Calculate web visibility for a single item
private function calculate_web_visibility_for_item($item)
{
    // Check if manual override is enabled
    if (!empty($item['web_vis_toggle']) && $item['web_vis_toggle'] == 1) {
        // MANUAL MODE: Return stored value
        return !empty($item['web_vis']) ? (bool)$item['web_vis'] : false;
    } else {
        // AUTO MODE: Calculate based on rules
        return $this->calculate_auto_visibility($item);
    }
}

private function calculate_auto_visibility($item)
{
    // Check parent product visibility (from SHOWCASE_PRODUCT.visible)
    $product_web_visibility = !empty($item['parent_product_visibility']) ? 
                             ($item['parent_product_visibility'] === 'Y') : false;
    
    if (!$product_web_visibility) {
        return false;  // Parent not visible = child not visible
    }
    
    // Check item status (only 3 valid statuses for web visibility)
    $valid_statuses = ['RUN', 'LTDQTY', 'RKFISH'];
    $has_valid_status = isset($item['status']) && in_array($item['status'], $valid_statuses);
    
    return $has_valid_status;
}
```

### **Item Controller**

**Location:** `application/controllers/Item.php` (lines 1821-1871)

**Same logic as Product controller** - both controllers have identical calculation methods.

---

## ⚡ **LAZY CALCULATION (PERFORMANCE OPTIMIZATION)**

### **When It Happens**

**Location:** `application/controllers/Item.php::index()` (lines 226-248)

```php
// During item listing display
foreach ($item_details_for_table as &$item) {
    if (is_null($item['web_vis'])) {
        // Calculate web visibility
        $calculated_visibility = $this->calculate_web_visibility_for_item($item);
        $item['web_vis'] = $calculated_visibility;
        
        // Prepare for batch update
        $items_to_update[] = [
            'id' => $item['item_id'],
            'web_vis' => $calculated_visibility ? 1 : 0,
            'date_modif' => date('Y-m-d H:i:s')
        ];
    }
}

// Batch update all items that were calculated
if (!empty($items_to_update)) {
    $this->db->update_batch('T_ITEM', $items_to_update, 'id');
}
```

**Performance Benefits:**
- Only calculates items where `web_vis IS NULL`
- Batch updates multiple items in single query
- Subsequent page loads use cached database value

---

## 🔍 **DATA FLOW**

### **Query Paths for Item Data**

#### **Path 1: Item_model::get_item()**
**Location:** `application/models/Item_model.php` (line 413)

```php
$this->db->select('SHOWCASE_PRODUCT.visible AS parent_product_visibility');
$this->db->join('SHOWCASE_PRODUCT', 'T_ITEM.product_id = SHOWCASE_PRODUCT.product_id', 'left');
```

#### **Path 2: RevisedQueries_model::get_item_details()**
**Location:** `application/models/RevisedQueries_model.php` (line 156)

```php
$this->db->select('showp.visible as parent_product_visibility');
$this->db->join('SHOWCASE_PRODUCT AS showp', 'showp.product_id = i.product_id', 'left');
```

**CRITICAL:** Both query paths MUST use the same alias: `parent_product_visibility`

---

## 🎨 **UI/UX BEHAVIOR**

### **Product Edit Form**

**Checkbox States:**
- ✅ **Enabled + Unchecked**: Has beauty shot, user unchecked it
- ✅ **Enabled + Checked**: Has beauty shot, user checked it
- ⛔ **Disabled + Unchecked**: No beauty shot, forced to unchecked
- Text: *"Upload a beauty shot to enable web visibility"*

### **Item Edit Form**

#### **Auto-Calculated Mode** (web_vis_toggle = 0, default)

**Visual:**
- Checkbox shows calculated state (checked or unchecked)
- Lock icon 🔒 appears next to label
- Text: *"Auto-calculated (toggle manual override to edit)"*
- Checkbox is **READ-ONLY** (clicking does nothing)

**Calculation:**
```
Checkbox is CHECKED when ALL THREE are true:
✅ Has images (pic_big_url OR pic_hd_url)
✅ Status is RUN, LTDQTY, or RKFISH
✅ Parent visible = 'Y'
```

#### **Manual Override Mode** (web_vis_toggle = 1)

**Visual:**
- No lock icon
- Checkbox is **EDITABLE**
- User can check/uncheck freely
- Text: *"Manual Override"* under toggle switch

**Behavior:**
- Toggle switch ON = manual mode
- User can override auto-calculation
- Value saved to database on form submit

#### **No Images State**

**Visual:**
- Both checkbox AND toggle are **DISABLED**
- Text: *"Upload images to enable"* (red)

---

## 🔧 **CRITICAL FIXES APPLIED (October 2025)**

### **1. JavaScript Form Validation Fix**

**Problem:** Hardcoded `change_showcase=0` hidden field wasn't being updated when checkbox changed.

**Fix:** Modified `addHiddenInput()` to UPDATE existing fields instead of only adding new ones.

**Location:** `assets/js/form_validation.js` (lines 207-221)

### **2. Checkbox Value Handling**

**Problem:** Checkboxes use `val()` which returns attribute value, not checked state.

**Fix:** Added special handling for checkboxes.

**Location:** `assets/js/form_validation.js` (lines 344-347)

```javascript
if ($(this).attr('type') === 'checkbox') {
    new_val = $(this).is(':checked') ? '1' : '0';
}
```

### **3. Product Save Condition**

**Problem:** Product data only saved when `change_product=1`, but checkbox only triggers `change_showcase`.

**Fix:** Added `change_showcase` to save condition.

**Location:** `application/controllers/Product.php` (line 891)

```php
} else if ($this->input->post('change_product') === '1' || $this->input->post('change_showcase') === '1') {
```

### **4. Column Name Typos**

**Problem:** Queries used `parent_product_visiblity` (missing 'i'), causing data to not load.

**Fix:** Corrected to `parent_product_visibility` in 4 locations:
- `application/models/Item_model.php` (line 413)
- `application/models/RevisedQueries_model.php` (line 156)
- `application/views/item/form/view.php` (line 598, 624)
- `application/controllers/Item.php` (line 1850)

### **5. Column Name in Product Query**

**Problem:** Query used `sp.web_vis` but SHOWCASE_PRODUCT table has `visible` column.

**Fix:** Changed to `sp.visible`.

**Location:** `application/controllers/Product.php` (line 2077)

---

## 📝 **VALID ITEM STATUSES FOR WEB VISIBILITY**

**Only 3 statuses allow auto-calculated visibility:**

1. **RUN** - Active product
2. **LTDQTY** - Limited quantity available
3. **RKFISH** - Roadkit Fishbowl

**All other statuses** (discontinued, special order, etc.) → web_vis = 0 (hidden)

---

## 🔄 **CASCADE UPDATE FLOW**

**When parent product visibility changes:**

1. User checks/unchecks "Web Visible" in Product Edit Form
2. Form submits to `Product::submit_form()`
3. Product data saved (requires `change_product=1` OR `change_showcase=1`)
4. Showcase data saved to `SHOWCASE_PRODUCT.visible` ('Y' or 'N')
5. **Cascade:** `update_child_items_web_visibility($product_id)` is called
6. All active child items are queried with parent visibility
7. Each child item's visibility is recalculated (respects manual override)
8. Batch update to `T_ITEM.web_vis` for all children

**Location:** `application/controllers/Product.php` (lines 2074-2109)

---

## 👁️ **EYE ICON VISUAL INDICATORS**

### **Location:** `assets/js/init_datatables.js` (lines ~860-880)

**How It Works:**

```javascript
// Eye icon column in DataTables
{
    "title": "Web Visible",
    "data": "web_vis",
    "render": function(data, type, row, meta) {
        if (data === '1' || data === 1) {
            return '<i class="fas fa-eye text-primary"></i>';  // Blue eye
        } else {
            return '<i class="fas fa-eye-slash text-muted"></i>';  // Slashed eye (gray)
        }
    }
}
```

**Key Points:**
- Icons are **ALWAYS** database-driven from `T_ITEM.web_vis`
- Blue eye = `web_vis = 1` (visible)
- Gray slashed eye = `web_vis = 0` or NULL (hidden)
- Icons update after lazy calculation runs

---

## 🚨 **CRITICAL IMPLEMENTATION NOTES**

### **1. Data Type Conversions**

```php
// SHOWCASE_PRODUCT.visible (CHAR)
'Y' = visible
'N' = hidden
// Comparison: === 'Y' (not boolean cast)

// T_ITEM.web_vis (TINYINT)
1 = visible
0 = hidden  
NULL = needs calculation
// Comparison: === '1' or === 1 (depends on context)

// T_ITEM.web_vis_toggle (TINYINT)
1 = manual override ON
0 = auto-calculated mode
// Comparison: === '1' or === 1
```

### **2. Form Checkbox Submission**

```php
// Unchecked checkbox = NOT submitted (is_null() returns true)
// Checked checkbox = value submitted ('1' or 'on')
// Disabled checkbox = NOT submitted (even if checked in HTML)

// Solution: Always check for null
$value = $this->input->post('checkbox_name');
if (is_null($value)) {
    $value = '0';  // Default to unchecked
}
```

### **3. JavaScript Form Validation**

**Change tracking uses hidden fields:**
- `change_product` = Product data changed
- `change_showcase` = Showcase data changed
- `change_item` = Item data changed

**These fields start at '0' and JavaScript updates to '1' when fields change.**

**CRITICAL:** Controller only saves data when change field = '1':
```php
if ($this->input->post('change_product') === '1' || $this->input->post('change_showcase') === '1') {
    // Save product data
}
```

---

## 📋 **QUICK REFERENCE**

### **Product Form**
- **Field:** `showcase_visible` (checkbox)
- **Saves to:** `SHOWCASE_PRODUCT.visible` (CHAR: 'Y'/'N')
- **Enabled when:** `pic_big_url IS NOT NULL`
- **Triggers:** Child item recalculation on save

### **Item Form**  
- **Fields:** `web_vis` (checkbox), `web_vis_toggle` (switch)
- **Saves to:** `T_ITEM.web_vis`, `T_ITEM.web_vis_toggle` (TINYINT: 0/1)
- **Auto mode:** Checkbox locked, shows calculated state
- **Manual mode:** Checkbox editable, user controls state

### **Listing Display**
- **Shows:** Blue eye icons from `T_ITEM.web_vis`
- **Lazy calc:** NULL values calculated and saved on display
- **Performance:** Only calculates once, then cached

---

## ✅ **TESTING CHECKLIST**

### **Product Form Testing**
- [ ] Checkbox disabled when no beauty shot
- [ ] Checkbox enabled when beauty shot exists
- [ ] Checked state persists on save
- [ ] Unchecked state persists on save
- [ ] Child items recalculate when parent changes

### **Item Form Testing**
- [ ] Parent visibility indicator shows correct state
- [ ] Auto mode: Checkbox shows calculated state with lock icon
- [ ] Auto mode: Clicking checkbox does nothing
- [ ] Manual mode: Toggle enables checkbox editing
- [ ] Manual mode: Lock icon disappears
- [ ] No images: Both controls disabled

### **DataTables Testing**
- [ ] Blue eye icons reflect database values
- [ ] Lazy calculation updates NULL items
- [ ] Icons update after form submissions

---

## 🐛 **KNOWN ISSUES / FUTURE ENHANCEMENTS**

### **Not Implemented (From Original Spec)**
- ❌ Audit logging table
- ❌ S3/local file verification methods
- ❌ Complex validation helper methods
- ❌ Batch processing service
- ❌ `T_PRODUCT.web_visibility` column (uses `SHOWCASE_PRODUCT.visible` instead)

### **Legacy Columns (Unused)**
- `T_ITEM.web_vis2` - Not used in current implementation
- `T_ITEM.web_vis_checkbox` - Not used in current implementation

---

## 📚 **VERSION HISTORY**

### **v2.0.0 - Current Implementation Documentation** *(October 8, 2025)*
- Updated to reflect actual production code
- Documented real database schema (SHOWCASE_PRODUCT.visible vs T_PRODUCT.web_visibility)
- Documented actual calculation logic and UI behavior
- Added JavaScript fixes for form validation
- Corrected data type comparisons (CHAR vs TINYINT vs BOOLEAN)
- Removed theoretical/unimplemented features

### **v1.1.0 - Original Specification** *(January 15, 2025)*
- Theoretical implementation with complex helper methods
- Proposed audit logging and file verification
- Some features not implemented in actual code

---

## 🎓 **KEY LEARNINGS FOR AI ASSISTANTS**

1. **Always examine actual code** - Don't assume database column names or types
2. **Check ALL query paths** - Item data can load from multiple models (Item_model, RevisedQueries_model)
3. **Understand CodeIgniter form submission** - Unchecked checkboxes don't submit
4. **JavaScript change tracking** - Hidden fields with hardcoded values need updating, not just adding
5. **Data type matters** - CHAR(1) 'Y'/'N' vs TINYINT 0/1 vs BOOLEAN require different comparisons
6. **Docker caching** - Restart containers after code changes, clear opcache
7. **Typos are deadly** - `parent_product_visiblity` vs `parent_product_visibility` breaks everything

---

## 📞 **SUPPORT CONTACTS**

For questions about this implementation:
- **Developer:** Paul Leasure
- **Date Last Updated:** October 8, 2025
- **Git Branch:** `aiWebVis`
- **Commits:** 
  - `2182931c` - Fix blue eye visual indicators
  - `80fc7975` - Fix Product web visibility checkbox persistence
  - `56f79390` - Fix column name in SHOWCASE_PRODUCT query
  - `2d47896e` - Merge Item features and fix parent visibility typos



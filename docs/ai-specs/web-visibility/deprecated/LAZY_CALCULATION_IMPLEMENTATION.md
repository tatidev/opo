# Lazy Calculation Implementation Summary

**Date:** January 15, 2025  
**Feature:** Web Visibility Lazy Calculation  
**Status:** ✅ IMPLEMENTED - Ready for Testing  

---

## 🎯 **WHAT WAS IMPLEMENTED**

Successfully implemented lazy calculation for `T_ITEM.web_vis` values in the colorline listing functionality.

---

## 📝 **CHANGES MADE**

### **1. RevisedQueries_model.php** (application/models/)

**File:** `application/models/RevisedQueries_model.php`  
**Line:** 150-156  
**Change:** Added `web_vis` and `web_vis_toggle` columns to the SELECT query

```php
$this->db->select("
  i.*, i.code, i.id AS item_id, 
  i.product_id,
  i.product_type AS product_type,
  i.archived,
  i.web_vis,              // ← ADDED
  i.web_vis_toggle,       // ← ADDED
  sales_stock.id AS sales_id,
  // ... rest of query
```

**Why:** These columns were missing from the query, so `web_vis` was never being retrieved from the database.

---

### **2. Item.php Controller** (application/controllers/)

**File:** `application/controllers/Item.php`

#### **A. Lazy Calculation Logic** (Lines 222-246)

Added lazy calculation immediately after data retrieval in the `get_product_items()` method:

```php
// ============================================================================
// LAZY CALCULATION: Calculate web_vis for items with NULL values
// ============================================================================
$items_to_update = [];
foreach ($item_details_for_table as &$item) {
    if (is_null($item['web_vis'])) {
        // Calculate web visibility using business logic
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

// Batch update calculated values to database
if (!empty($items_to_update)) {
    $this->db->update_batch('T_ITEM', $items_to_update, 'id');
    log_message('info', 'Lazy calculation updated ' . count($items_to_update) . ' items for product_id: ' . $product_id);
}
// ============================================================================
```

**Location:** Right after line 221, before `$this->data['tableData'] = $item_details_for_table;`

**Why:** This triggers calculation for any items with `NULL web_vis` values during colorline list display.

---

#### **B. Calculation Methods** (Lines 1773-1829)

Added two new private methods at the end of the Item controller:

**1. `calculate_web_visibility_for_item($item)` - Main calculation logic**
```php
private function calculate_web_visibility_for_item($item)
{
    // Validate item data
    if (empty($item['item_id']) || empty($item['product_id'])) {
        return false;
    }
    
    // Check manual override state
    $manual_override = !empty($item['web_vis_toggle']) ? (bool)$item['web_vis_toggle'] : false;
    
    if ($manual_override) {
        // Manual override logic
        return false;
    } else {
        // Auto-determination logic
        return $this->calculate_auto_visibility($item);
    }
}
```

**2. `calculate_auto_visibility($item)` - Auto-determination logic**
```php
private function calculate_auto_visibility($item)
{
    // Check if product has beauty shot (web_visible comes from SHOWCASE_PRODUCT)
    $product_web_visibility = !empty($item['parent_product_visiblity']) ? (bool)$item['parent_product_visiblity'] : false;
    
    if (!$product_web_visibility) {
        return false; // No beauty shot = not visible
    }
    
    // Check if status is valid for auto-visibility
    $valid_statuses = ['RUN', 'LTDQTY', 'RKFISH'];
    $status = !empty($item['status']) ? strtoupper(trim($item['status'])) : '';
    
    if (!in_array($status, $valid_statuses)) {
        return false; // Invalid status = not visible
    }
    
    // All conditions met for auto-visibility
    return true;
}
```

**Why:** Implements the Web Visibility Logic Flow specification with:
- Beauty shot dependency check
- Status validation (RUN, LTDQTY, RKFISH)
- Manual override support

---

## 🔍 **HOW IT WORKS**

### **Lazy Calculation Flow:**

1. **User loads colorline list** for product "ACDC" (product_id)
2. **Query retrieves all items** including `web_vis` and `web_vis_toggle` columns
3. **Lazy calculation triggers** for any items where `web_vis IS NULL`
4. **For each NULL item:**
   - Check if manual override is enabled (`web_vis_toggle`)
   - If no manual override:
     - Check if parent product has beauty shot (`parent_product_visiblity`)
     - Check if item status is RUN, LTDQTY, or RKFISH
     - Calculate visibility: `true` if all conditions met, `false` otherwise
5. **Batch update database** with calculated values
6. **Return data to UI** with calculated `web_vis` values

### **Blue Eye Icon Updates:**

The blue eye icons in the colorline list will now reflect the calculated `web_vis` values from the database.

---

## 🚨 **PREREQUISITES - RUN DATABASE MIGRATION FIRST!**

**CRITICAL:** Before testing, you **MUST** add the database columns!

### **Run this SQL migration:**

**File:** `docs/database-migrations/add_web_vis_columns_to_t_item.sql`

```sql
-- Step 1: Add web_vis_toggle column (manual override state)
ALTER TABLE T_ITEM 
ADD COLUMN web_vis_toggle BOOLEAN NOT NULL DEFAULT FALSE 
COMMENT 'Manual override toggle state for web visibility';

-- Step 2: Add web_vis column (computed visibility state)
ALTER TABLE T_ITEM 
ADD COLUMN web_vis BOOLEAN NULL DEFAULT NULL 
COMMENT 'Final computed visibility state (NULL = needs calculation)';

-- Step 3: Add indexes for performance optimization
ALTER TABLE T_ITEM ADD INDEX idx_web_vis (web_vis);
ALTER TABLE T_ITEM ADD INDEX idx_web_vis_toggle (web_vis_toggle);
```

**To run the migration:**
1. Open your MySQL client (phpMyAdmin, Sequel Pro, etc.)
2. Select your OPMS database
3. Run the SQL file: `docs/database-migrations/add_web_vis_columns_to_t_item.sql`
4. Verify columns exist: `DESCRIBE T_ITEM;`

**Expected Result:**
- `web_vis` column: BOOLEAN NULL DEFAULT NULL
- `web_vis_toggle` column: BOOLEAN NOT NULL DEFAULT FALSE
- All existing items will have `web_vis = NULL` (triggers lazy calculation)
- All existing items will have `web_vis_toggle = FALSE` (auto-mode)

---

## ✅ **TESTING CHECKLIST**

- [ ] **RUN DATABASE MIGRATION** (see above - REQUIRED FIRST!)
- [ ] Verify columns exist in T_ITEM table
- [ ] Load a colorline list with items that have `NULL web_vis` values
- [ ] Verify database is updated with calculated values
- [ ] Check application logs for lazy calculation messages
- [ ] Confirm blue eye icons reflect calculated visibility states
- [ ] Test with different statuses (RUN, LTDQTY, RKFISH, others)
- [ ] Test with products that have/don't have beauty shots
- [ ] Verify manual override items (web_vis_toggle = 1) are handled correctly
- [ ] Check performance with large lists (100+ items)

---

## 📊 **EXPECTED RESULTS**

### **For Product: ACDC**

Based on your screenshot, all 12 items should calculate as follows:

| Item | Status | Has Beauty Shot? | web_vis_toggle | Expected Result |
|------|--------|------------------|----------------|-----------------|
| All ACDC items | RUN | Yes (assumed) | 0 | **TRUE (visible)** |

**After first load:**
- Database: All `T_ITEM.web_vis` values updated from `NULL` → `1` (or `0`)
- UI: Blue eye icons show visibility state
- Logs: Entry showing "Lazy calculation updated 12 items for product_id: [product_id]"

**After subsequent loads:**
- No lazy calculation needed (values already calculated)
- UI shows stored database values immediately

---

## 🐛 **TROUBLESHOOTING**

### **If lazy calculation doesn't trigger:**

1. **Check if `web_vis` columns exist:**
   ```sql
   DESCRIBE T_ITEM;
   -- Should show: web_vis (BOOLEAN NULL), web_vis_toggle (BOOLEAN NOT NULL)
   ```

2. **Check if values are actually NULL:**
   ```sql
   SELECT id, code, web_vis, web_vis_toggle FROM T_ITEM WHERE product_id = [ACDC_product_id];
   ```

3. **Check application logs:**
   ```
   tail -f application/logs/log-[date].php
   -- Look for: "Lazy calculation updated X items"
   ```

4. **Enable debug output:**
   Uncomment debug lines in `Item.php` line 217-220 to see data structure

---

## 🔗 **RELATED SPECIFICATIONS**

- **Web Visibility AI Model Specification:** `docs/ai-specs/Web-Visibility-AI-Model-Specification.md`
- **Web Visibility Logic Requirements:** `docs/Web-Visibility_Logic-Requirements.md`
- **OPMS AI Model Specification:** `docs/ai-specs/OPMS-AI-Model-Specification.md`

---

## 📝 **NEXT STEPS**

1. **Test the implementation** with current NULL data
2. **Monitor logs** for lazy calculation performance
3. **Verify UI updates** for blue eye icons
4. **Implement explicit editing** (Product/Item edit forms) if not already done
5. **Add manual override toggle** UI elements (future enhancement)

---

**End of Lazy Calculation Implementation Summary**

*This implementation follows the Web Visibility Logic Flow specification and integrates seamlessly with the existing OPMS codebase.*


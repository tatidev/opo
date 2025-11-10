# 🚀 Quick Start: Lazy Calculation Implementation

**Status:** ✅ Code Complete - Database Migration Required  
**Date:** January 15, 2025

---

## ⚡ **TL;DR - What You Need to Do**

1. **Run SQL migration** to add database columns
2. **Refresh your colorline list** - lazy calculation will trigger automatically
3. **Verify blue eye icons** reflect calculated visibility

---

## 📋 **STEP-BY-STEP GUIDE**

### **Step 1: Run Database Migration** ⚠️ **REQUIRED FIRST!**

**Open your MySQL client and run:**

```sql
-- Add the two required columns to T_ITEM table
ALTER TABLE T_ITEM 
ADD COLUMN web_vis_toggle BOOLEAN NOT NULL DEFAULT FALSE 
COMMENT 'Manual override toggle state';

ALTER TABLE T_ITEM 
ADD COLUMN web_vis BOOLEAN NULL DEFAULT NULL 
COMMENT 'Computed visibility state (NULL = needs calculation)';

-- Add performance indexes
ALTER TABLE T_ITEM ADD INDEX idx_web_vis (web_vis);
ALTER TABLE T_ITEM ADD INDEX idx_web_vis_toggle (web_vis_toggle);
```

**Verify columns exist:**
```sql
DESCRIBE T_ITEM;
-- Should show web_vis and web_vis_toggle columns
```

**Full migration file:** `docs/database-migrations/add_web_vis_columns_to_t_item.sql`

---

### **Step 2: Test Lazy Calculation**

1. **Load any colorline list** (e.g., ACDC product)
2. **Watch for:**
   - Database updates happening
   - Blue eye icons reflecting calculated states
   - Log entries: "Lazy calculation updated X items"

---

### **Step 3: Verify Results**

**Check the database:**
```sql
SELECT 
    id, code, status_id, web_vis, web_vis_toggle
FROM T_ITEM 
WHERE product_id = [YOUR_PRODUCT_ID]
LIMIT 10;
```

**Expected:**
- Items with `RUN`, `LTDQTY`, or `RKFISH` status → `web_vis = 1` (if product has beauty shot)
- Items with other statuses → `web_vis = 0`
- All items → `web_vis_toggle = 0` (auto-mode)

---

## 🎯 **What Happens When You Load Colorline List**

```
1. User clicks "View Colorlines" for ACDC
   ↓
2. System retrieves all items (including web_vis, web_vis_toggle)
   ↓
3. Lazy calculation detects items with web_vis = NULL
   ↓
4. For each NULL item:
   - Check if product has beauty shot ✓
   - Check if status is RUN/LTDQTY/RKFISH ✓
   - Calculate: visible = (has_beauty_shot AND valid_status) ✓
   ↓
5. Batch update database with calculated values ✓
   ↓
6. Return data to UI with blue eye icons ✓
```

---

## 📊 **Business Logic**

### **Item is VISIBLE when:**
✅ Product has beauty shot  
✅ Item status is: RUN, LTDQTY, or RKFISH  
✅ Manual override is OFF (web_vis_toggle = 0)

### **Item is HIDDEN when:**
❌ Product has NO beauty shot  
❌ Item status is: HOLD, TBD, or any other status  
❌ Manual override forces it hidden

---

## 🔍 **Troubleshooting**

### **Error: "Unknown column 'i.web_vis_toggle'"**
**Solution:** Run the database migration (Step 1)

### **Blue eye icons don't update**
**Check:**
1. Database columns exist: `DESCRIBE T_ITEM;`
2. Application logs: `tail -f application/logs/log-*.php`
3. Browser console for JavaScript errors

### **All items show hidden**
**Check:**
1. Does product have beauty shot? (SHOWCASE_PRODUCT.visible = 1)
2. Are items in valid status? (Run this query:)
   ```sql
   SELECT id, code, ps.name as status 
   FROM T_ITEM i
   LEFT JOIN P_PRODUCT_STATUS ps ON i.status_id = ps.id
   WHERE i.product_id = [YOUR_PRODUCT_ID];
   ```

---

## 📁 **Files Modified**

1. **`application/models/RevisedQueries_model.php`**
   - Added `web_vis` and `web_vis_toggle` to SELECT query

2. **`application/controllers/Item.php`**
   - Added lazy calculation logic (lines 222-246)
   - Added calculation methods (lines 1773-1829)

3. **`T_ITEM` database table**
   - New column: `web_vis` (BOOLEAN NULL)
   - New column: `web_vis_toggle` (BOOLEAN NOT NULL DEFAULT FALSE)
   - New indexes for performance

---

## 📚 **Documentation**

- **Full Implementation:** `docs/ai-specs/LAZY_CALCULATION_IMPLEMENTATION.md`
- **Database Migration:** `docs/database-migrations/add_web_vis_columns_to_t_item.sql`
- **Web Visibility Spec:** `docs/ai-specs/Web-Visibility-AI-Model-Specification.md`
- **Business Requirements:** `docs/Web-Visibility_Logic-Requirements.md`

---

## ✅ **Success Criteria**

You'll know it's working when:
- ✅ Colorline list loads without errors
- ✅ Application log shows: "Lazy calculation updated X items"
- ✅ Database query shows `web_vis` values are no longer NULL
- ✅ Blue eye icons reflect proper visibility states
- ✅ Second page load is faster (no recalculation needed)

---

**🎉 Ready to test? Run the database migration and load a colorline list!**


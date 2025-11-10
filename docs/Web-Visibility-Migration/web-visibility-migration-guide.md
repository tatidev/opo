# Web Visibility Migration Guide

**Version:** 2.0.0  
**Date:** October 8, 2025  
**Author:** Paul Leasure  
**Status:** Ready for Testing

---

## 🎯 **PURPOSE**

This migration script initializes web visibility for all products and items in OPMS using a **conservative approach** that respects existing user decisions and requires explicit approval for web visibility.

---

## 📋 **WHAT THIS MIGRATION DOES**

### **Products (Parent Level)**
- ✅ Preserves existing 'Y' values (user previously approved)
- ✅ Preserves existing 'N' values (user previously declined)
- ✅ Initializes NULL values to 'N' (conservative default)
- ✅ Fixes data errors ('Y' without beauty shot → 'N')
- ⚠️ **Does NOT auto-enable visibility** - users must manually check the box

### **Items (Child Level)**
- ✅ Calculates visibility based on THREE conditions:
  1. Parent product visibility = 'Y'
  2. Status is RUN, LTDQTY, or RKFISH
  3. Has item images (pic_big_url OR pic_hd_url)
- ✅ Preserves manual overrides (never changes items with `web_vis_toggle = 1`)
- ✅ Updates only NULL or auto-calculated items
- ✅ Respects parent-child cascade relationship

---

## 🗄️ **DATABASE SCHEMA MIGRATION (REQUIRED FIRST)**

### **Prerequisites**

Before running the migration script, ensure the T_ITEM table has the required columns. If you've already added these columns, skip to the Usage section below.

### **Step 0: Add Web Visibility Columns to T_ITEM**

**Run these SQL commands on your database:**

```sql
-- ============================================================================
-- Web Visibility Columns Migration for T_ITEM Table
-- ============================================================================
-- Purpose: Add web_vis and web_vis_toggle columns to support lazy calculation
-- Reference: docs/ai-specs/Web-Visibility-ACTUAL-IMPLEMENTATION.md
-- ============================================================================

-- Step 1: Add web_vis_toggle column (manual override state)
ALTER TABLE T_ITEM 
ADD COLUMN web_vis_toggle TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Manual override toggle state for web visibility';

-- Step 2: Add web_vis column (computed visibility state)
ALTER TABLE T_ITEM 
ADD COLUMN web_vis TINYINT(1) NULL DEFAULT NULL 
COMMENT 'Final computed visibility state (NULL = needs calculation)';

-- Step 3: Add indexes for performance optimization
ALTER TABLE T_ITEM ADD INDEX idx_web_vis (web_vis);
ALTER TABLE T_ITEM ADD INDEX idx_web_vis_toggle (web_vis_toggle);

-- Step 4: Verify columns were added successfully
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'T_ITEM'
  AND COLUMN_NAME IN ('web_vis', 'web_vis_toggle')
ORDER BY COLUMN_NAME;
```

### **Verify Schema Changes**

**Check how many items have NULL web_vis (should be all after adding columns):**

```sql
SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN web_vis IS NULL THEN 1 ELSE 0 END) as null_web_vis_count,
    SUM(CASE WHEN web_vis = 1 THEN 1 ELSE 0 END) as visible_count,
    SUM(CASE WHEN web_vis = 0 THEN 1 ELSE 0 END) as hidden_count
FROM T_ITEM
WHERE archived = 'N';
```

**Sample query to see the data:**

```sql
SELECT 
    id as item_id,
    code,
    product_id,
    status_id,
    web_vis,
    web_vis_toggle,
    archived
FROM T_ITEM
WHERE archived = 'N'
LIMIT 10;
```

### **Rollback Script (If Needed)**

⚠️ **Only use if you need to undo the schema changes:**

```sql
-- Remove indexes
ALTER TABLE T_ITEM DROP INDEX idx_web_vis;
ALTER TABLE T_ITEM DROP INDEX idx_web_vis_toggle;

-- Remove columns
ALTER TABLE T_ITEM DROP COLUMN web_vis;
ALTER TABLE T_ITEM DROP COLUMN web_vis_toggle;

-- Verify removal
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'T_ITEM'
  AND COLUMN_NAME IN ('web_vis', 'web_vis_toggle');
```

### **Expected State After Schema Migration**
- ✅ All existing items will have `web_vis = NULL`
- ✅ All existing items will have `web_vis_toggle = 0`
- ✅ First colorline list load will trigger lazy calculation
- ✅ Database will be updated with calculated web_vis values
- ✅ Subsequent loads will use stored values (no recalculation needed)

---

## 🚀 **USAGE**

### **Step 1: Generate Report (REQUIRED)**

```bash
cd /path/to/opuzen-opms
php index.php cli/migrate_web_visibility report
```

**What it does:**
- Analyzes all products and items
- Categorizes by current state
- Generates CSV reports in `reports/` directory
- Shows summary of actions that will be taken

**Output Files:**
- `reports/product_visibility_analysis_YYYY-MM-DD_HHMMSS.csv`
- `reports/item_visibility_analysis_YYYY-MM-DD_HHMMSS.csv`

**Review these reports before proceeding!**

---

### **Step 2: Test Migration (Dry Run)**

```bash
php index.php cli/migrate_web_visibility run --dry-run
```

**What it does:**
- Shows exactly what would change WITHOUT modifying data
- Displays detailed changes for each product/item
- Verifies data integrity
- Shows migration summary

**Review the output to ensure expected behavior!**

---

### **Step 3: Run Actual Migration**

```bash
# Default batch size (100 records)
php index.php cli/migrate_web_visibility run

# Custom batch size for large datasets
php index.php cli/migrate_web_visibility run --batch-size=500
```

**What it does:**
- Processes products first (parents)
- Then processes items (children)
- Verifies data integrity
- Shows migration summary
- Provides next steps

---

## 📊 **REPORT CATEGORIES**

### **Product Categories**

| Category | Description | Action |
|----------|-------------|--------|
| Has beauty shot + Visible | Already configured correctly | NO ACTION |
| Has beauty shot + Not Visible | User chose to keep hidden | NO ACTION (respect decision) |
| Has beauty shot + NULL | No visibility setting | INITIALIZE TO 'N' |
| No beauty shot + Visible | DATA ERROR | FIX: SET TO 'N' |
| No beauty shot + Not Visible | Correctly set | NO ACTION |
| No beauty shot + NULL | No beauty shot | INITIALIZE TO 'N' |

### **Item Categories**

| Category | Description | Action |
|----------|-------------|--------|
| Manual Override Active | User manually set visibility | NO ACTION (preserve) |
| Auto-Calculated Visible | web_vis = 1 | NO ACTION |
| Auto-Calculated Hidden | web_vis = 0 | NO ACTION |
| NULL (Needs Calculation) | web_vis IS NULL | CALCULATE AND SAVE |

---

## 🔍 **EXAMPLE OUTPUT**

### **Report Mode**
```
╔═══════════════════════════════════════════════════════════════╗
║   OPMS Web Visibility Migration Script                       ║
║   REPORT MODE                                                 ║
║   2025-10-08 14:30:25                                         ║
╚═══════════════════════════════════════════════════════════════╝

[INFO] Analyzing products...
[INFO] Found 1,247 active products to analyze
[INFO] 
Analyzing items...
[INFO] Found 8,542 active items to analyze
[INFO] Product report generated: reports/product_visibility_analysis_2025-10-08_143025.csv
[INFO] Item report generated: reports/item_visibility_analysis_2025-10-08_143025.csv

╔═══════════════════════════════════════════════════════════════╗
║   REPORT SUMMARY                                              ║
╚═══════════════════════════════════════════════════════════════╝

Products:
  Has beauty shot + Visible:        234 (OK)
  Has beauty shot + Not Visible:    89 (User choice)
  Has beauty shot + NULL:           156 (WILL INITIALIZE)
  No beauty shot + Visible:         3 (DATA ERROR - WILL FIX)
  No beauty shot + Not Visible:     421 (OK)
  No beauty shot + NULL:            344 (WILL INITIALIZE)

Items:
  Manual Override Active:           45 (WILL PRESERVE)
  Auto-Calculated Visible:          1,234 (OK)
  Auto-Calculated Hidden:           5,678 (OK)
  NULL (Needs Calculation):         1,585 (WILL CALCULATE)

Actions Required:
  Products to update: 503
  Items to update:    1,585

✅ REPORT GENERATION COMPLETED
```

### **Migration Mode (Dry Run)**
```
╔═══════════════════════════════════════════════════════════════╗
║   OPMS Web Visibility Migration Script                       ║
║   MIGRATION MODE                                              ║
║   2025-10-08 14:35:12                                         ║
╚═══════════════════════════════════════════════════════════════╝

Batch size: 100
Dry run: YES

[INFO] STEP 1: Migrating product-level web visibility...
[INFO] Found 1,247 active products to process
  Product 2345 (Tranquil): NULL → N (initializing NULL)
  Product 2346 (Berba): Y → N (data fix)
  Product 2347 (Motley): NULL → N (initializing NULL)
  Progress: 100/1247 products (8.0%)
  ...
  Progress: 1247/1247 products (100.0%)

[INFO] 
STEP 2: Migrating item-level web visibility...
[INFO] Found 8,542 active items to process
  Item 12345: SKIPPED (manual override active)
  Item 12346 (TRANS-ASH): NULL → 0
  Item 12347 (BERB-FIESTA): NULL → 1
  Progress: 100/8542 items (1.2%)
  ...
  Progress: 8542/8542 items (100.0%)

[INFO] 
STEP 3: Verifying data integrity...
[INFO] Items with NULL web_vis: 0 (will be calculated on display via lazy calculation)

╔═══════════════════════════════════════════════════════════════╗
║   MIGRATION SUMMARY                                           ║
╚═══════════════════════════════════════════════════════════════╝

Products:
  Total Processed:        1247
  Updated:                503
    - Initialized (NULL):  500
    - Data Errors Fixed:   3
  Already Configured:     744

Items:
  Total Processed:        8542
  Updated:                1585
  Already Set:            6912
  Skipped (Manual):       45

🔍 DRY RUN COMPLETED - No data was modified
```

---

## ⚠️ **IMPORTANT NOTES**

### **Conservative Approach**
- **Products with beauty shots are NOT automatically enabled**
- All NULL visibility values default to 'N' (not visible)
- Users must manually check "Web Visible" checkbox in Product Edit Forms
- This prevents accidental exposure of products on the website

### **User Intent Preserved**
- Existing 'Y' and 'N' values are NEVER changed (except data errors)
- Manual overrides on items are NEVER changed
- Script respects all previous user decisions

### **Data Integrity**
- Products cannot be visible without a beauty shot
- Items cannot be visible if parent is not visible
- All data errors are automatically fixed

### **Safe Execution**
- Report mode shows changes before execution
- Dry run mode tests without modifying data
- Batch processing prevents memory exhaustion
- Progress tracking for large datasets

---

## 🔧 **TECHNICAL DETAILS**

### **Database Changes**

**SHOWCASE_PRODUCT Table:**
- Creates records for products without showcase entries
- Updates `visible` column ('Y' or 'N')
- Sets `date_modif` to current timestamp
- Uses `user_id = 0` (system user)

**T_ITEM Table:**
- Updates `web_vis` column (0 or 1)
- Sets `date_modif` to current timestamp
- NEVER touches `web_vis_toggle` column
- Uses batch updates for performance

### **Query Patterns**

**Products:**
```sql
-- Fetch products with showcase data
SELECT 
    p.id, p.type, p.name,
    sp.visible, sp.pic_big_url, sp.url_title, sp.descr
FROM T_PRODUCT p
LEFT JOIN SHOWCASE_PRODUCT sp ON p.id = sp.product_id
WHERE p.archived = 'N'
LIMIT 100 OFFSET 0;
```

**Items:**
```sql
-- Fetch items with parent visibility
SELECT 
    i.id, i.product_id, i.code,
    i.web_vis, i.web_vis_toggle,
    ps.abrev as status,
    sp.visible as parent_product_visibility,
    i.pic_big_url, i.pic_hd_url
FROM T_ITEM i
LEFT JOIN P_PRODUCT_STATUS ps ON i.status_id = ps.id
LEFT JOIN SHOWCASE_PRODUCT sp ON i.product_id = sp.product_id
WHERE i.archived = 'N'
LIMIT 100 OFFSET 0;
```

### **Performance**
- **Batch Size:** 100 records per batch (configurable)
- **Memory:** Minimal (processes in batches)
- **Database Load:** Moderate (SELECT → UPDATE pattern)
- **Estimated Time:** ~1-2 minutes per 1,000 records

---

## 🎯 **POST-MIGRATION ACTIONS**

### **For Users**
1. Review CSV reports to understand current state
2. Go to Product Edit Forms for products with beauty shots
3. Manually check "Web Visible" checkbox for products to enable
4. Save the form to trigger child item recalculation
5. Review item listings to verify eye icons are correct

### **For Developers**
1. Verify migration logs for any warnings or errors
2. Check database integrity queries in Step 3 output
3. Monitor application logs for any issues
4. Test product and item edit forms
5. Verify DataTables listings show correct eye icons

---

## 🐛 **TROUBLESHOOTING**

### **Script Won't Run**
```bash
# Error: No direct script access allowed
# Solution: Ensure running from CLI
php index.php cli/migrate_web_visibility run
```

### **Permission Denied on Reports**
```bash
# Create reports directory
mkdir -p reports
chmod 755 reports
```

### **Database Connection Error**
```bash
# Check database configuration
cat application/config/database.php
```

### **Batch Size Too Large**
```bash
# Reduce batch size for large datasets
php index.php cli/migrate_web_visibility run --batch-size=50
```

---

## 📋 **CHECKLIST**

### **Before Migration**
- [ ] Backup database (CRITICAL!)
- [ ] Run schema migration (Step 0) - Add web_vis columns to T_ITEM
- [ ] Verify columns were added successfully
- [ ] Run report mode (Step 1)
- [ ] Review CSV reports
- [ ] Run dry-run mode (Step 2)
- [ ] Review dry-run output
- [ ] Notify users of maintenance window

### **During Migration**
- [ ] Run migration script
- [ ] Monitor output for errors
- [ ] Verify summary statistics
- [ ] Check data integrity results

### **After Migration**
- [ ] Test product edit forms
- [ ] Test item edit forms
- [ ] Verify DataTables listings
- [ ] Check application logs
- [ ] Notify users to enable products

---

## 🔒 **SECURITY**

### **CLI-Only Access**
- Script is NOT accessible via web
- Must be run from terminal/SSH
- Follows security prime directives from `.cursorrules.mdc`

### **SQL Injection Prevention**
- Uses CodeIgniter Query Builder (parameterized queries)
- No string concatenation in SQL
- All user input sanitized

### **Data Integrity**
- Validates all changes before applying
- Prevents invalid states (visible without beauty shot)
- Respects foreign key relationships

---

## 📚 **REFERENCES**

- **Implementation Doc:** `docs/ai-specs/Web-Visibility-ACTUAL-IMPLEMENTATION.md`
- **Database Spec:** `docs/ai-specs/opms-database-spec.md`
- **Security Rules:** `.cursorrules.mdc`
- **Controller:** `application/controllers/cli/Migrate_web_visibility.php`

---

## 📞 **SUPPORT**

For questions or issues:
- **Developer:** Paul Leasure
- **Git Branch:** `aiWebVis`
- **Date:** October 8, 2025

---

## ✅ **NEXT STEPS AFTER APPROVAL**

1. **Apply Schema Changes (Development):**
   ```sql
   -- Run the ALTER TABLE statements from Step 0 above
   -- Verify columns were added with verification queries
   ```

2. **Test on Development:**
   ```bash
   # Development environment
   php index.php cli/migrate_web_visibility report
   php index.php cli/migrate_web_visibility run --dry-run
   php index.php cli/migrate_web_visibility run
   ```

3. **Verify Results:**
   - Check DataTables listings
   - Test product edit forms
   - Test item edit forms
   - Verify parent-child cascade

4. **Production Deployment:**
   - Backup production database (CRITICAL!)
   - Schedule maintenance window
   - Apply schema changes (Step 0 SQL)
   - Verify schema with verification queries
   - Run report mode
   - Run dry-run mode
   - Run actual migration
   - Verify results

5. **User Training:**
   - Notify users about changes
   - Provide instructions for enabling products
   - Explain new eye icon indicators
   - Document manual override feature

---

**END OF MIGRATION GUIDE**


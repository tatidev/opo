# Web Visibility Migration - Documentation

**Version:** 2.0.0  
**Date:** October 8, 2025  
**Status:** ✅ Ready for Testing  
**Branch:** `aiWebVis`

---

## 📚 **Documentation Overview**

This folder contains complete documentation for the OPMS Web Visibility Migration batch process.

### **Files in This Folder**

| File | Purpose | When to Use |
|------|---------|-------------|
| **web-visibility-migration-guide.md** | Complete step-by-step guide | Read first - comprehensive instructions |
| **QUICK-REFERENCE.md** | One-page command reference | Quick lookup during execution |
| **MIGRATION-SUMMARY.md** | Implementation details | For technical review and approval |
| **Web-Visibility-Migration-Implementation-Notes.md** | Developer notes | Background and decision rationale |

---

## 🚀 **Quick Start**

### **Step 1: Test Environment**
```bash
php index.php cli/test_web_visibility run
```

### **Step 2: Generate Reports**
```bash
php index.php cli/migrate_web_visibility report
```

### **Step 3: Review CSVs**
```bash
open reports/product_visibility_analysis_*.csv
open reports/item_visibility_analysis_*.csv
```

### **Step 4: Dry Run**
```bash
php index.php cli/migrate_web_visibility run --dry-run
```

### **Step 5: Actual Migration**
```bash
php index.php cli/migrate_web_visibility run
```

---

## 📁 **Related Files**

### **Migration Scripts** (not in this folder)
- `application/controllers/cli/Migrate_web_visibility.php` - Main migration script
- `application/controllers/cli/Test_web_visibility.php` - Test suite

### **Implementation Files**
- `docs/ai-specs/Web-Visibility-ACTUAL-IMPLEMENTATION.md` - Current production implementation
- `docs/ai-specs/opms-database-spec.md` - Database schema reference

---

## 🎯 **What This Migration Does**

### **Products (Parent Level)**
- ✅ Preserves existing 'Y' values (user approved)
- ✅ Preserves existing 'N' values (user declined)
- ✅ Initializes NULL values to 'N' (conservative)
- ✅ Fixes data errors ('Y' without beauty shot → 'N')

### **Items (Child Level)**
- ✅ Calculates visibility based on THREE conditions:
  1. Parent product visibility = 'Y'
  2. Status is RUN, LTDQTY, or RKFISH
  3. Has item images (pic_big_url OR pic_hd_url)
- ✅ NEVER changes manual overrides (`web_vis_toggle = 1`)
- ✅ Updates NULL or auto-calculated items only

---

## ⚠️ **Critical Business Rule**

**Product visibility ≠ Has beauty shot**

**Product visibility = Has beauty shot AND user checked checkbox**

The migration uses a **conservative approach**:
- Products with beauty shots default to NOT visible
- Users must manually enable products after migration
- This prevents accidental exposure of products on website

---

## 📖 **Recommended Reading Order**

1. **Start here:** `web-visibility-migration-guide.md`
   - Complete instructions
   - Business rules
   - Troubleshooting

2. **During execution:** `QUICK-REFERENCE.md`
   - Command reference
   - Quick troubleshooting

3. **For approval:** `MIGRATION-SUMMARY.md`
   - Technical details
   - Testing checklist
   - Success criteria

4. **For context:** `Web-Visibility-Migration-Implementation-Notes.md`
   - Implementation decisions
   - Why things work this way

---

## 🔒 **Safety Features**

- ✅ **Report mode** - Analyze before changing anything
- ✅ **Dry run mode** - Test without modifications
- ✅ **Preserves user decisions** - Never changes existing Y/N
- ✅ **Respects manual overrides** - Skips items with toggle=1
- ✅ **Batch processing** - Handles large datasets
- ✅ **Comprehensive logging** - Detailed output for debugging

---

## 📊 **Expected Results**

### **Typical Product Migration:**
```
Products:
  Total Processed:        ~1,247
  Updated:                ~503
    - Initialized (NULL):  ~500
    - Data Errors Fixed:   ~3
  Already Configured:     ~744
```

### **Typical Item Migration:**
```
Items:
  Total Processed:        ~8,542
  Updated:                ~1,585
  Already Set:            ~6,912
  Skipped (Manual):       ~45
```

### **Execution Time:**
- Report generation: ~30 seconds
- Dry run: ~1 minute
- Actual migration: ~1-2 minutes

---

## 🐛 **Troubleshooting**

### **Common Issues:**

**Issue:** "Test suite fails"  
**Solution:** Check database connectivity and table structure

**Issue:** "Permission denied on reports/"  
**Solution:** `mkdir -p reports && chmod 755 reports`

**Issue:** "Memory issues"  
**Solution:** Reduce batch size with `--batch-size=50`

**Full troubleshooting guide:** See `web-visibility-migration-guide.md` section "TROUBLESHOOTING"

---

## ✅ **Post-Migration Actions**

1. Verify results in DataTables (eye icons should reflect database)
2. Test Product Edit Forms (checkbox states correct)
3. Test Item Edit Forms (auto-calculated vs manual)
4. Notify users to manually enable products with beauty shots
5. Monitor application logs for any issues

---

## 📞 **Support**

**Developer:** Paul Leasure  
**Date:** October 8, 2025  
**Git Branch:** `aiWebVis`  
**Documentation Location:** `/docs/Web-Visibility-Migration/`

---

## 🎓 **Key Learnings**

1. Beauty shot **enables** checkbox, doesn't **auto-check** it
2. User approval is required for web visibility
3. Conservative defaults prevent accidental exposure
4. Manual overrides are sacred and never changed
5. Report mode is essential before running migration

---

**For detailed information, start with `web-visibility-migration-guide.md`**


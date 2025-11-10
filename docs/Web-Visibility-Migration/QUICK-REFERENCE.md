# Web Visibility Migration - Quick Reference Card

**Version:** 2.0.0 | **Date:** October 8, 2025

---

## ⚡ **QUICK COMMANDS**

```bash
# 1. Test Suite (validate environment)
php index.php cli/test_web_visibility run

# 2. Generate Reports (analyze current state)
php index.php cli/migrate_web_visibility report

# 3. Dry Run (test without changes)
php index.php cli/migrate_web_visibility run --dry-run

# 4. Actual Migration (requires approval)
php index.php cli/migrate_web_visibility run

# 5. Custom batch size (for large datasets)
php index.php cli/migrate_web_visibility run --batch-size=500
```

---

## 🎯 **BUSINESS RULES**

### **Product Visibility**
```
Visible = Has Beauty Shot AND User Checked Box
Default = 'N' (not visible) for NULL values
```

### **Item Visibility**
```
Visible = Parent Visible AND Valid Status AND Has Images
Valid Statuses: RUN, LTDQTY, RKFISH
Manual Overrides: NEVER CHANGED
```

---

## 📊 **EXPECTED OUTCOMES**

### **Products**
| Current State | Has Beauty Shot | Action | New Value |
|---------------|----------------|--------|-----------|
| 'Y' | ✅ | Keep | 'Y' |
| 'N' | ✅ | Keep | 'N' |
| NULL | ✅ | Initialize | 'N' |
| 'Y' | ❌ | **FIX** | 'N' |
| 'N' | ❌ | Keep | 'N' |
| NULL | ❌ | Initialize | 'N' |

### **Items**
| Current | Manual Override | Action | Behavior |
|---------|----------------|--------|----------|
| NULL | No | Calculate | Set 0 or 1 |
| 0 | No | Keep | No change |
| 1 | No | Keep | No change |
| Any | **Yes** | **SKIP** | Preserve |

---

## 🔍 **REPORT OUTPUTS**

### **Files Generated**
```
reports/
├── product_visibility_analysis_YYYY-MM-DD_HHMMSS.csv
└── item_visibility_analysis_YYYY-MM-DD_HHMMSS.csv
```

### **CSV Columns**

**Products:**
- Product ID, Name, Type
- Has Beauty Shot (YES/NO)
- Current Visible (Y/N/NULL)
- Recommended Action
- Action Reason

**Items:**
- Item ID, Code, Product ID
- Status, Has Images, Parent Visible
- Current web_vis, Manual Override
- Recommended Action

---

## ⚠️ **CRITICAL WARNINGS**

1. **Backup Database First**
   ```bash
   mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
   ```

2. **Products Default to NOT Visible**
   - All NULL values → 'N'
   - Users must manually enable
   - Beauty shot alone doesn't enable

3. **Manual Overrides Preserved**
   - Items with `web_vis_toggle = 1` are NEVER changed
   - Script skips these entirely

4. **Test Suite Must Pass**
   - Run test suite BEFORE migration
   - All tests must pass
   - Fix any failures first

---

## 🚦 **EXECUTION SEQUENCE**

### **Standard Workflow**
```
┌─────────────────┐
│  1. Test Suite  │  Validate environment
└────────┬────────┘
         ↓
┌─────────────────┐
│  2. Report      │  Analyze current state
└────────┬────────┘
         ↓
┌─────────────────┐
│  3. Review CSVs │  Understand changes
└────────┬────────┘
         ↓
┌─────────────────┐
│  4. Dry Run     │  Test without changes
└────────┬────────┘
         ↓
┌─────────────────┐
│  5. Review      │  Verify output
└────────┬────────┘
         ↓
┌─────────────────┐
│  6. Migration   │  Execute actual changes
└────────┬────────┘
         ↓
┌─────────────────┐
│  7. Verify      │  Check results in UI
└─────────────────┘
```

---

## 🔧 **TROUBLESHOOTING**

### **Test Suite Fails**
```bash
# Check database connection
cat application/config/database.php

# Verify tables exist
mysql -u user -p database -e "SHOW TABLES;"
```

### **Permission Errors**
```bash
# Create reports directory
mkdir -p reports
chmod 755 reports
```

### **Memory Issues**
```bash
# Reduce batch size
php index.php cli/migrate_web_visibility run --batch-size=50
```

### **Rollback Migration**
```bash
# Restore from backup
mysql -u user -p database < backup_YYYYMMDD.sql
```

---

## 📈 **SUCCESS CRITERIA**

### **Migration Complete When:**
- ✅ All products processed
- ✅ All items processed
- ✅ No errors in output
- ✅ Data integrity checks pass
- ✅ Eye icons in DataTables reflect database values
- ✅ Product Edit Forms show correct states
- ✅ Item Edit Forms show correct states
- ✅ Parent-child cascade works correctly

### **Post-Migration Actions:**
1. Notify users about changes
2. Provide instructions for enabling products
3. Monitor application logs
4. Test product/item forms
5. Verify DataTables listings

---

## 📞 **QUICK SUPPORT**

**Files:**
- Migration Script: `application/controllers/cli/Migrate_web_visibility.php`
- Test Suite: `application/controllers/cli/Test_web_visibility.php`
- Full Documentation: `docs/database-migrations/web-visibility-migration-guide.md`
- Summary: `docs/database-migrations/MIGRATION-SUMMARY.md`

**Key Points:**
- Conservative approach (defaults to NOT visible)
- Respects user decisions (preserves Y/N values)
- Manual overrides never changed
- Beauty shot enables checkbox (doesn't auto-check)

---

## 🎯 **ONE-MINUTE SUMMARY**

**What:** Initialize web visibility for all products and items  
**Why:** Conservative approach that respects user intent  
**How:** Three-phase execution (report → dry-run → migrate)  
**Risk:** Low (preserves existing data, defaults to safe)  
**Time:** ~1-2 minutes per 1,000 records  
**Rollback:** Restore from backup  

**Key Rule:** Beauty shot ENABLES checkbox, user must CHECK it.

---

**END OF QUICK REFERENCE**


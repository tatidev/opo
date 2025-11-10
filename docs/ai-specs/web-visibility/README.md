# Web Visibility Feature Specifications

This directory contains all documentation related to the Web Visibility feature in OPMS.

## 📚 Documentation Files

### **Current Implementation (October 2025)**
- **[Web-Visibility-ACTUAL-IMPLEMENTATION.md](./Web-Visibility-ACTUAL-IMPLEMENTATION.md)** - Complete specification of the current working implementation, including business logic, database schema, form patterns, lazy calculation, and cascade behavior.

### **Deprecated (Previous Attempts)**
- **[deprecated/Web-Visibility-AI-Model-Specification.md](./deprecated/Web-Visibility-AI-Model-Specification.md)** - Original AI model specification from January 2025 (superseded by ACTUAL-IMPLEMENTATION.md).
- **[deprecated/LAZY_CALCULATION_FINAL_SUMMARY.md](./deprecated/LAZY_CALCULATION_FINAL_SUMMARY.md)** - Theoretical lazy calculation summary from January 2025.
- **[deprecated/LAZY_CALCULATION_IMPLEMENTATION.md](./deprecated/LAZY_CALCULATION_IMPLEMENTATION.md)** - Theoretical implementation notes from January 2025.
- **[deprecated/QUICK_START_LAZY_CALCULATION.md](./deprecated/QUICK_START_LAZY_CALCULATION.md)** - Theoretical quick start guide from January 2025.

---

## 🎯 Feature Overview

The Web Visibility feature controls which products and items (colorlines) are visible on the public-facing website. It consists of two levels:

### **Product Level (Parent)**
- Controlled by `SHOWCASE_PRODUCT.visible` (CHAR(1): 'Y' or 'N')
- Requires beauty shot (`pic_big_url` IS NOT NULL)
- User must manually check "Web Visible" checkbox in product form
- Beauty shot enables the checkbox but doesn't auto-check it

### **Item Level (Child)**
- Controlled by `T_ITEM.web_vis` (TINYINT(1): 1, 0, or NULL)
- Auto-calculated based on three conditions:
  1. Parent product is visible (`SHOWCASE_PRODUCT.visible = 'Y'`)
  2. Item has valid status (RUN, LTDQTY, RKFISH)
  3. Item has images (`pic_big_url` OR `pic_hd_url` in SHOWCASE_ITEM)
- Supports manual override via `T_ITEM.web_vis_toggle` flag
- Uses lazy calculation: NULL values calculated on-demand and cached

### **Lazy Calculation**
- NULL values trigger calculation on first display
- Calculated values stored in database for performance
- Manual overrides (`web_vis_toggle = 1`) never recalculated
- Parent changes cascade to child items in auto mode
- Eye icons in DataTables reflect database-calculated values

---

## 📖 Reading Order

For AI models or developers learning this feature:

1. **Read this:** [Web-Visibility-ACTUAL-IMPLEMENTATION.md](./Web-Visibility-ACTUAL-IMPLEMENTATION.md) - This is the complete, current implementation that includes lazy calculation as part of the working system.

---

## 🔗 Related Documentation

- **Database Schema:** `../opms-database-spec.md`
- **Codebase Guide:** `../OPMS-Codebase-AI-Specification.md`
- **Migration Docs:** `../../Web-Visibility-Migration/`

---

**Last Updated:** October 9, 2025


# Sales App Link Removal - AI Model Specification

**Version:** 1.0.0  
**Date:** October 9, 2025  
**Status:** 📋 Pending Implementation  
**Complexity:** Low (UI changes only, no data model changes)

---

## 🎯 **OBJECTIVE**

Remove clickable links to the Sales Manager application at `sales.opuzen-service.com` from the OPMS user interface while **preserving all data integration and sync functionality**.

---

## 📊 **CURRENT STATE - SALES APP INTEGRATION**

### **What the Sales App Is**

The Sales App (`sales.opuzen-service.com`) is a **separate inventory/sales management system** that:
- Tracks stock levels (yardsInStock, yardsOnHold, yardsAvailable)
- Manages purchase orders (yardsOnOrder)
- Handles backorders (yardsBackorder)
- Manages bolt/roll inventory

### **Database Architecture**

**Separate Database:** Each environment has its own Sales database:

| Environment | Sales Database Name |
|-------------|---------------------|
| Production | `opuzen_prod_sales` |
| Development | `opuzen_dev_sales` |
| QA/Stage | `opuzen_qa_sales` |
| Local | `opuzen_loc_sales` |

**Defined in:** `application/core/MY_Model.php` lines 67-106

```php
// Example for localdev environment
$this->db_sales = "opuzen_loc_sales";
$this->t_product_stock = $this->db_sales . ".op_products_stock";
$this->t_product_stock_bolts = $this->db_sales . ".op_products_bolts";
```

### **Data Sync Pattern**

**OPMS ↔ Sales DB Linkage:**
```
T_ITEM.id (OPMS)
    ↕
op_products.master_item_id (Sales DB)
    ↕
op_products.id = sales_id
```

**How Sync Works:**
1. User selects Sales product in OPMS item form
2. `save_item_sales_id()` updates Sales DB
3. Sets `master_item_id = item_id` in `op_products` table
4. Calls `proc_update_products_stock()` to refresh aggregated stock
5. Stock data flows back to OPMS via LEFT JOIN queries

---

## 🔍 **LINK LOCATIONS (7 Total)**

### **Active Clickable Links (5 instances):**

#### **1. DataTables: Item Listings Stock Column**
**File:** `assets/js/init_datatables.js`  
**Line:** 801  
**Context:** "In Stock" column render function

```javascript
if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
    txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
}
```

**User Experience:**
- Displays stock yardage (e.g., "5.5")
- Adds clickable external link icon
- Opens Sales App bolt page in new tab

---

#### **2. Restock Email Notification**
**File:** `application/controllers/Restock.php`  
**Line:** 2498  
**Context:** Email table generation for restock notifications

```php
$d['sales_link'] = !is_null($d['sales_id']) ? 
    "<a href='https://sales.opuzen-service.com/index.php/bolt/index/" . $d['sales_id'] . "' target='_blank'>link</a>" 
    : 'N/A';
```

**User Experience:**
- Email sent to purchasing staff
- Table includes "Sales Link" column
- Click opens Sales App to order page

---

#### **3. Items Filterer Report**
**File:** `application/views/reports/items_filterer_view.php`  
**Line:** 254  
**Context:** DataTable "In Stock" column

```javascript
if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
    txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
}
```

**User Experience:** Same as #1 (DataTables pattern)

---

#### **4. Lists Form View**
**File:** `application/views/lists/form/view.php`  
**Line:** 329  
**Context:** List item display with stock column

```javascript
if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
    txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
}
```

**User Experience:** Same as #1 (DataTables pattern)

---

### **Commented-Out Link (1 instance):**

#### **5. Price Changes Report**
**File:** `application/views/reports/price_changes_view.php`  
**Line:** 250  
**Status:** Already commented out (inactive)

```javascript
// if (typeof(row.sales_id) !== 'undefined' && row.sales_id !== null) {
//     txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
// }
```

**Action:** Can be deleted entirely (already inactive)

---

### **Documentation References (2 instances):**

#### **6. Email Notifications Documentation**
**File:** `docs/restock/email-notifications.md`  
**Lines:** 74-84, 257-262  
**Context:** Explains Sales system links feature

**Action:** Update to reflect link removal

#### **7. Code References Documentation**
**File:** `docs/restock/code-references.md`  
**Line:** 308  
**Context:** Example of sales link generation

**Action:** Update to reflect link removal

---

## 🔐 **FUNCTIONALITY TO PRESERVE (CRITICAL)**

### **DO NOT REMOVE - Data Integration Features:**

#### **1. Sales Management Sync UI** (Item Form)
**File:** `application/views/item/form/view.php`  
**Lines:** 299-314

```php
<label for="sales_id">
    Sales Management Sync <i class="far fa-toggle-on"></i>
</label>
<input type="text" id="sales_m_searchbox" placeholder="Search Sales Management items">
<input type="hidden" id="sales_id" name="sales_id" value="<?php echo $info['sales_id'] ?>">
```

**Why Keep:** Allows users to link OPMS items to Sales DB products

---

#### **2. Sales Typeahead Search**
**File:** `application/controllers/Item.php`  
**Lines:** 1766-1786  
**Method:** `typeahead_sales_sync()`

```php
function typeahead_sales_sync()
{
    $search = $this->input->post("query");
    $t = $this->model->db_sales . ".op_products";
    
    $this->model->db->select("Stock.id, CONCAT(...) as label")
        ->from("$t Stock")
        ->where("Stock.master_item_id IS NULL")
        ...
}
```

**Why Keep:** Required for search functionality

---

#### **3. Save Sales ID Method**
**File:** `application/models/Item_model.php`  
**Lines:** 653-669  
**Method:** `save_item_sales_id($item_id, $sales_id)`

```php
function save_item_sales_id($item_id, $sales_id){
    // Clean previous connection
    $this->db->set("master_item_id", NULL)
        ->where("master_item_id", $item_id)
        ->update($this->db_sales . ".op_products");
    
    // Set new connection
    $this->db->set("master_item_id", $item_id)
        ->where("id", $sales_id)
        ->update($this->db_sales . ".op_products");
    
    // Update stock
    $this->db->query("call $this->db_sales.proc_update_products_stock();");
}
```

**Why Keep:** Core sync functionality

---

#### **4. Stock Data Queries**
**Files:** Multiple models  
**Pattern:** JOIN to `{db_sales}.op_products_stock`

```php
$this->db->join(
    $this->db_sales . '.op_products_stock Stock',
    'T_ITEM.id = Stock.master_item_id',
    'left'
);
```

**Why Keep:** Displays stock levels in OPMS

---

#### **5. Database Connection Variables**
**File:** `application/core/MY_Model.php`  
**Lines:** 67-106

```php
$this->db_sales = "opuzen_loc_sales";
$this->t_product_stock = $this->db_sales . ".op_products_stock";
```

**Why Keep:** Required for all Sales DB access

---

#### **6. Sales Inbound Link Handler**
**File:** `application/controllers/Item.php`  
**Lines:** 45-48

```php
// Condition for link back from sales:
if (empty($_POST) && $arg !== null && is_numeric($arg) && $arg > 0) {
    $this->is_incomming_item_id_link = true;
    // echo '<h3>Incomming link from Sales App</h3>';
}
```

**Why Keep:** Allows Sales App to deep-link INTO OPMS (reverse direction)

---

## ✂️ **REMOVAL STRATEGY**

### **Option A: Complete Removal** (Recommended)

**Remove the link HTML entirely:**
```javascript
// BEFORE
if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
    txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
}

// AFTER
// (nothing - just show stock yardage without link)
```

**Result:** Stock data shows without clickable icon

---

### **Option B: Comment Out** (Easy Rollback)

**Keep code but disable:**
```javascript
// BEFORE
if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
    txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
}

// AFTER
// Sales App link removed 2025-10-09 - can be restored if needed
// if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
//     txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
// }
```

**Result:** Same visual result, easier to restore

---

### **Option C: Configuration Flag** (Most Flexible)

**Add config variable to enable/disable:**
```javascript
// In config
const ENABLE_SALES_APP_LINKS = false;

// In code
if (ENABLE_SALES_APP_LINKS && typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
    txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
}
```

**Result:** Can toggle without code changes

---

## 📋 **IMPLEMENTATION CHECKLIST**

### **Phase 1: Remove Links**
- [ ] Remove link from `assets/js/init_datatables.js:801`
- [ ] Remove link from `application/controllers/Restock.php:2498`
- [ ] Remove link from `application/views/reports/items_filterer_view.php:254`
- [ ] Remove link from `application/views/lists/form/view.php:329`
- [ ] Delete commented code from `application/views/reports/price_changes_view.php:250`

### **Phase 2: Update Documentation**
- [ ] Update `docs/restock/email-notifications.md`
- [ ] Update `docs/restock/code-references.md`
- [ ] Add note about link removal and date

### **Phase 3: Testing**
- [ ] Test item DataTables (stock column shows without link)
- [ ] Test restock emails (no sales link in table)
- [ ] Test items filterer report
- [ ] Test list views
- [ ] Verify stock data still displays correctly
- [ ] Verify Sales sync UI still works in item form

### **Phase 4: Verify Preserved Functionality**
- [ ] Sales Management Sync toggle works
- [ ] Typeahead search finds Sales products
- [ ] Linking items to Sales DB still saves
- [ ] Stock levels still display in tables
- [ ] Inbound links from Sales App still work

---

## 🧪 **TESTING REQUIREMENTS**

### **Visual Testing:**
1. Open item listing
2. Check "In Stock" column
3. **Expected:** Shows yardage (e.g., "5.5") WITHOUT external link icon
4. **Verify:** Stock data still visible

### **Email Testing:**
1. Trigger restock email notification
2. Open email in client
3. **Expected:** Table shows items WITHOUT "Sales Link" column OR column shows "N/A" for all
4. **Verify:** Email still sends correctly

### **Report Testing:**
1. Open Items Filterer Report
2. Check stock column
3. **Expected:** Stock data without clickable link
4. **Verify:** Report still functions

### **Sync Testing:**
1. Edit an item
2. Use "Sales Management Sync" search
3. Link to a Sales product
4. Save item
5. **Expected:** Sync still works, just no clickable link in listings

---

## 🔄 **DATA FLOW (PRESERVED)**

### **Current Flow (Keep This):**

```
OPMS Item Form
    ↓ (User selects Sales product)
Item.save_item()
    ↓ (change_item_sales_id === '1')
Item_model.save_item_sales_id(item_id, sales_id)
    ↓ (Updates Sales DB)
op_products.master_item_id = item_id
    ↓ (Stored procedure)
proc_update_products_stock()
    ↓ (Aggregates to view)
op_products_stock (stock levels)
    ↓ (Query in OPMS)
LEFT JOIN {db_sales}.op_products_stock
    ↓ (Display in UI)
Shows: yardsInStock, yardsAvailable, etc.
    ↓ (REMOVE THIS PART)
❌ Clickable link icon to Sales App
```

**What Changes:** Only the final step (clickable link) is removed

---

## 📝 **CODE CHANGES REQUIRED**

### **Change 1: DataTables Stock Column**

**File:** `assets/js/init_datatables.js`  
**Lines:** 792-804

**Current Code:**
```javascript
{
    "title": "In Stock", "data": "yardsInStock", "defaultContent": '-', "searchable": false,
    "render": function (data, type, row, meta) {
        var txt = '';
        if (row.yardsInStock !== null && typeof (row.yardsInStock) !== 'undefined') {
            txt += row.yardsInStock;
        } else {
            txt += '-';
        }
        if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
            txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
        }
        return txt;
    }
}
```

**New Code (Option A - Complete Removal):**
```javascript
{
    "title": "In Stock", "data": "yardsInStock", "defaultContent": '-', "searchable": false,
    "render": function (data, type, row, meta) {
        var txt = '';
        if (row.yardsInStock !== null && typeof (row.yardsInStock) !== 'undefined') {
            txt += row.yardsInStock;
        } else {
            txt += '-';
        }
        // Sales App link removed 2025-10-09
        return txt;
    }
}
```

**New Code (Option B - Commented):**
```javascript
{
    "title": "In Stock", "data": "yardsInStock", "defaultContent": '-', "searchable": false,
    "render": function (data, type, row, meta) {
        var txt = '';
        if (row.yardsInStock !== null && typeof (row.yardsInStock) !== 'undefined') {
            txt += row.yardsInStock;
        } else {
            txt += '-';
        }
        // Sales App link removed 2025-10-09 - can be restored if needed
        // if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
        //     txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
        // }
        return txt;
    }
}
```

---

### **Change 2: Restock Email Link**

**File:** `application/controllers/Restock.php`  
**Line:** 2498

**Current Code:**
```php
$d['sales_link'] = !is_null($d['sales_id']) ? 
    "<a href='https://sales.opuzen-service.com/index.php/bolt/index/" . $d['sales_id'] . "' target='_blank'>link</a>" 
    : 'N/A';
```

**New Code (Option A - Remove Column):**
```php
// Sales App link removed 2025-10-09
// $d['sales_link'] = !is_null($d['sales_id']) ? 
//     "<a href='https://sales.opuzen-service.com/index.php/bolt/index/" . $d['sales_id'] . "' target='_blank'>link</a>" 
//     : 'N/A';
```

**Also remove from column headers** (search for `$_COL_NAMES` nearby)

**New Code (Option B - Show N/A):**
```php
// Sales App link removed 2025-10-09 - showing N/A for all items
$d['sales_link'] = 'N/A';
```

---

### **Change 3: Items Filterer Report**

**File:** `application/views/reports/items_filterer_view.php`  
**Lines:** 249-257

**Same pattern as Change 1** - Remove or comment out the link addition

---

### **Change 4: Lists Form View**

**File:** `application/views/lists/form/view.php`  
**Lines:** 324-332

**Same pattern as Change 1** - Remove or comment out the link addition

---

### **Change 5: Price Changes Report (Cleanup)**

**File:** `application/views/reports/price_changes_view.php`  
**Lines:** 245-255

**Current:** Already commented out

**New:** Delete the commented code entirely (cleanup)

---

## 📚 **DOCUMENTATION UPDATES**

### **Update 1: Email Notifications Doc**

**File:** `docs/restock/email-notifications.md`  
**Lines:** 74-84, 257-262

**Add Note:**
```markdown
### Sales System Links (REMOVED 2025-10-09)

**Previous Behavior:**
Email notifications included clickable links to Sales App for ordering items.

**Current Behavior:**
Links removed. Stock data still displayed but not clickable.
Users must access Sales App directly via URL: https://sales.opuzen-service.com

**Data Sync:** Sales Management sync functionality remains intact in OPMS item forms.
```

---

### **Update 2: Code References Doc**

**File:** `docs/restock/code-references.md`  
**Line:** 303-313

**Update Example:**
```markdown
// Lines 331-338: Build table rows
foreach ($orders_data as $d) {
    // Sales App link removed 2025-10-09
    // Previously generated: <a href='https://sales.opuzen-service.com/...'>link</a>
    // Now: N/A or column removed entirely
    
    // Add row to table
    $this->table->add_row($data);
}
```

---

## ⚠️ **IMPACT ANALYSIS**

### **User Impact: LOW**

| User Type | Current Behavior | After Removal | Workaround |
|-----------|------------------|---------------|------------|
| Admin Users | Click link to Sales App | See stock data only | Access Sales App directly |
| Purchasing Staff | Click email link to order | See item info only | Access Sales App directly |
| Report Users | Click link in reports | See stock data only | Access Sales App directly |

### **Technical Impact: NONE**

| Component | Status | Reason |
|-----------|--------|--------|
| Data Sync | ✅ Preserved | No changes to sync logic |
| Stock Display | ✅ Preserved | Still queries Sales DB |
| Item Linking | ✅ Preserved | Form UI unchanged |
| Database | ✅ Preserved | No schema changes |
| Backend Logic | ✅ Preserved | No controller/model changes (except email link) |

### **Business Impact: LOW**

- ✅ Stock data still visible in all locations
- ✅ Sales sync still functional
- ✅ Data accuracy maintained
- ⚠️ Staff must access Sales App via bookmark/URL (not one-click)

---

## 🔄 **ROLLBACK PLAN**

### **If Links Need to be Restored:**

**Option A - Commented Code:**
- Uncomment the link generation code
- Commit and deploy
- **Time:** 5 minutes

**Option B - Git Revert:**
```bash
git revert {commit_hash}
```
- **Time:** 2 minutes

**Option C - Manual Restoration:**
- Refer to this spec for original code
- Copy/paste link generation back
- **Time:** 10 minutes

---

## 📦 **FILES AFFECTED SUMMARY**

| File Type | File Path | Lines | Change Type |
|-----------|-----------|-------|-------------|
| JavaScript | `assets/js/init_datatables.js` | 801 | Remove link |
| PHP Controller | `application/controllers/Restock.php` | 2498 | Remove link |
| PHP View | `application/views/reports/items_filterer_view.php` | 254 | Remove link |
| PHP View | `application/views/lists/form/view.php` | 329 | Remove link |
| PHP View | `application/views/reports/price_changes_view.php` | 250 | Delete commented code |
| Markdown | `docs/restock/email-notifications.md` | 74-84, 257-262 | Update docs |
| Markdown | `docs/restock/code-references.md` | 303-313 | Update docs |

**Total:** 7 files, ~10 lines of code changes

---

## 🎯 **SUCCESS CRITERIA**

Implementation is successful when:

1. ✅ **No clickable links** to `sales.opuzen-service.com` in UI
2. ✅ **Stock data still displays** in all locations
3. ✅ **Sales sync UI** still works in item form
4. ✅ **Typeahead search** for Sales products still works
5. ✅ **Data integration** preserved (JOIN queries work)
6. ✅ **No console errors** in browser
7. ✅ **No PHP errors** in application logs
8. ✅ **Documentation updated** to reflect changes

---

## 🔍 **VERIFICATION STEPS**

### **After Implementation:**

1. **Check DataTables:**
   - Open item listing
   - Verify "In Stock" column shows yardage
   - Verify NO external link icon appears

2. **Check Reports:**
   - Open Items Filterer Report
   - Verify stock column shows data
   - Verify NO external link icon

3. **Check Lists:**
   - Open a list
   - Verify stock displays
   - Verify NO external link icon

4. **Check Emails:**
   - Trigger restock notification
   - Verify email sends
   - Verify table shows stock data
   - Verify NO sales link column OR all show N/A

5. **Check Sales Sync:**
   - Edit an item
   - Toggle Sales Management Sync
   - Search for Sales product
   - Link and save
   - Verify sync still works

---

## 🚨 **CRITICAL WARNINGS**

### **DO NOT Remove:**
- ❌ `$this->db_sales` variable declarations
- ❌ JOIN clauses to Sales DB tables
- ❌ `save_item_sales_id()` method
- ❌ `typeahead_sales_sync()` method
- ❌ Sales Management Sync UI in item form
- ❌ `sales_id` form field
- ❌ Stock data SELECT clauses
- ❌ Inbound link handler from Sales App

### **Safe to Remove:**
- ✅ HTML anchor tags to `sales.opuzen-service.com`
- ✅ External link icons (`fa-external-link-alt`)
- ✅ Commented-out link code (cleanup)

---

## 📞 **RELATED SYSTEMS**

### **Sales App Side (No Changes Needed)**

The Sales App may have links TO OPMS:
- These are handled by OPMS inbound link handler (Item.php:45-48)
- No changes needed in Sales App codebase
- Sales App can still deep-link to OPMS items

### **Email System**

Restock emails sent via:
- SendGrid (configured in .env)
- Email templates built in Restock controller
- Link removal only affects table content

---

## 🎓 **KEY LEARNINGS FOR AI MODELS**

### **1. Distinguish UI Links from Data Integration**
- **UI Links:** Can be removed safely (user convenience)
- **Data Integration:** Must be preserved (business functionality)

### **2. Sales App is Separate System**
- Different database (`opuzen_*_sales`)
- JOIN queries cross databases
- Sync is bidirectional (OPMS ↔ Sales)

### **3. Multiple Link Instances**
- Same pattern repeated in 4 different views
- All use same URL format
- All conditionally check for `sales_id !== null`

### **4. External Link Pattern**
```javascript
txt += " <a href='URL' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
```
- This exact pattern appears in all instances
- Easy to identify and remove

---

## 📋 **IMPLEMENTATION RECOMMENDATIONS**

### **Recommended Approach:**

**Option B (Comment Out) for Code**
- Easy to restore if needed
- Clear documentation of change
- Minimal risk

**Option A (Complete Removal) for Already-Commented Code**
- Delete lines 245-255 in price_changes_view.php
- Code is already inactive, safe to remove

**Update Documentation**
- Add removal notes with date
- Explain why removed
- Document how to restore if needed

---

## 🔖 **VERSION CONTROL**

### **Commit Message Template:**

```
Remove Sales App external links from UI

Removed clickable links to sales.opuzen-service.com while preserving
all data integration and sync functionality.

CHANGES:
- Removed external link icons from DataTables stock columns
- Removed sales link from restock email notifications  
- Updated documentation to reflect removal
- Deleted commented-out link code (cleanup)

PRESERVED:
- Sales Management Sync UI in item form
- Sales DB data queries (stock levels)
- save_item_sales_id() method
- typeahead_sales_sync() search
- Inbound link handler from Sales App
- All data integration functionality

FILES MODIFIED:
- assets/js/init_datatables.js (line 801)
- application/controllers/Restock.php (line 2498)
- application/views/reports/items_filterer_view.php (line 254)
- application/views/lists/form/view.php (line 329)
- application/views/reports/price_changes_view.php (deleted lines 245-255)
- docs/restock/email-notifications.md
- docs/restock/code-references.md

IMPACT: Low - UI convenience feature removed, no functional changes

ROLLBACK: Uncomment code or git revert commit

Approved-by: [NAME]
```

---

## 🎯 **DECISION POINTS FOR IMPLEMENTATION**

When implementing, decide:

1. **Removal Method:**
   - [ ] Option A: Complete removal
   - [ ] Option B: Comment out (recommended)
   - [ ] Option C: Configuration flag

2. **Email Table:**
   - [ ] Remove sales_link column entirely
   - [ ] Keep column but show 'N/A' for all

3. **Documentation:**
   - [ ] Update with removal notes
   - [ ] Add restoration instructions
   - [ ] Include business rationale

---

## ✅ **READY FOR IMPLEMENTATION**

This specification provides complete guidance for safely removing Sales App links from OPMS while preserving all business-critical data integration functionality.

**Estimated Time:** 30-45 minutes  
**Risk Level:** Low  
**Complexity:** Low  
**Testing Required:** Manual UI testing + Email testing

---

**END OF SPECIFICATION**

**Author:** AI Assistant  
**Approved:** Pending  
**Date:** October 9, 2025  
**Related Tag:** v2.6


# Exportable Toggle Implementation Summary

## Overview
Added an "exportable" toggle column to the colorline list (`/item/index`) to mark items as exportable for NetSuite integration.

## Changes Made

### 1. Database Migration
**File**: `db-data/migrations/add_exportable_to_t_item.sql`

```sql
ALTER TABLE `T_ITEM` 
ADD COLUMN `exportable` int NOT NULL DEFAULT '1' COMMENT '0 no / 1 yes' AFTER `in_master`;

CREATE INDEX `idx_exportable` ON `T_ITEM` (`exportable`);
```

- Column added after `in_master`
- Default value: `1` (ON/exportable by default)
- Index added for query performance

### 2. Model Updates
**File**: `application/models/RevisedQueries_model.php`

- Added `i.exportable` to the SELECT statement in `get_item_details()` method (line 148)
- Ensures exportable field is always fetched with item data

### 3. Controller Updates
**File**: `application/controllers/Item.php`

Added new method `toggle_exportable()` (lines 1660-1674):
- Accepts batch updates (same pattern as `toggle_ringset()`)
- Updates T_ITEM table via `save_item()` method
- Returns success JSON response
- Route: `/item/toggle_exportable`

### 4. View Configuration
**File**: `application/views/_header.php`

Added JavaScript variable (line 66):
```javascript
var toggleExportableUrl = '<?php echo site_url('item/toggle_exportable')?>';
```

### 5. DataTable Column
**File**: `assets/js/init_datatables.js`

Added exportable column at position 1 (lines 929-945):
- Located between Edit icon and Shelf column
- Column title: "Exportable"
- Shows standard HTML toggle switch element
- Only editable for users with `hasEditPermission`
- Non-editable users see checkmark (✓) or dash (-)
- Adjusted subsequent column positions (Bin to position 4, Roll to position 5)

### 6. JavaScript Handlers
**File**: `assets/js/commons.js`

Added three functions (lines 265-317):
1. **Event Handler**: `$(document).on('change', 'input.btnToggleExportable', ...)`
   - Listens for checkbox changes
   - Triggers AJAX update

2. **Toggle Function**: `toggleExportable(given_data, target_table, checkbox)`
   - Sends POST request to controller
   - Handles errors (reverts checkbox on failure)
   - Shows success notification

3. **View Update**: `toggleExportableView(item, target_table)`
   - Updates datatable row data
   - Refreshes display without full page reload

### 7. CSS Styles
**File**: `assets/css/style.css`

Added toggle switch styles (lines 315-368):
- Modern iOS-style toggle switch
- Green color when ON (#28a745)
- Gray when OFF (#ccc)
- Smooth transitions (0.3s)
- Disabled state support

## Features

### Toggle Switch Behavior
- **Default State**: ON (exportable = 1)
- **Visual States**:
  - ON: Green switch, slider on right
  - OFF: Gray switch, slider on left
- **Permissions**: Only users with edit permission can toggle
- **Error Handling**: Checkbox reverts on AJAX failure
- **Success Feedback**: Shows success notification (SweetAlert)

### Column Position
```
| Edit | Exportable | Checkbox | Shelf | Bin | Roll | ... |
```

### Database Structure
```
T_ITEM table:
- exportable: int NOT NULL DEFAULT '1'
- Values: 0 (not exportable) / 1 (exportable)
- Index: idx_exportable for performance
```

## Testing Checklist

1. **Database Migration**
   - [ ] Run ALTER TABLE query on development database
   - [ ] Verify column exists with correct default value
   - [ ] Verify index is created

2. **UI Display**
   - [ ] Navigate to `/item/index`
   - [ ] Verify "Exportable" column appears between Edit icon and Shelf
   - [ ] Verify toggle switch displays correctly
   - [ ] Verify existing items show ON by default

3. **Toggle Functionality**
   - [ ] Click toggle switch
   - [ ] Verify it changes visually
   - [ ] Verify success notification appears
   - [ ] Refresh page and verify state persists
   - [ ] Toggle OFF and verify state saves

4. **Permissions**
   - [ ] Login as user WITHOUT edit permission
   - [ ] Verify toggle shows as checkmark/dash (read-only)
   - [ ] Login as user WITH edit permission
   - [ ] Verify toggle is clickable

5. **Error Handling**
   - [ ] Simulate network error (disconnect)
   - [ ] Click toggle
   - [ ] Verify checkbox reverts to previous state

6. **Batch Operations** (if applicable)
   - [ ] Select multiple items
   - [ ] Test if batch toggle works (if implemented)

## Rollback Instructions

If needed, to rollback the changes:

1. **Database**: 
   ```sql
   ALTER TABLE `T_ITEM` DROP COLUMN `exportable`;
   DROP INDEX `idx_exportable` ON `T_ITEM`;
   ```

2. **Code**: Revert all file changes using git:
   ```bash
   git checkout HEAD -- application/models/RevisedQueries_model.php
   git checkout HEAD -- application/controllers/Item.php
   git checkout HEAD -- application/views/_header.php
   git checkout HEAD -- assets/js/init_datatables.js
   git checkout HEAD -- assets/js/commons.js
   git checkout HEAD -- assets/css/style.css
   ```

## Files Modified

1. `db-data/migrations/add_exportable_to_t_item.sql` (NEW)
2. `application/models/RevisedQueries_model.php`
3. `application/controllers/Item.php`
4. `application/views/_header.php`
5. `assets/js/init_datatables.js`
6. `assets/js/commons.js`
7. `assets/css/style.css`

## Security Considerations

- ✅ AJAX-only endpoint (no direct access)
- ✅ User authentication required
- ✅ Edit permission checked on backend
- ✅ SQL injection protected (using CodeIgniter's Query Builder)
- ✅ XSS protected (proper output escaping)

## Performance Considerations

- ✅ Index added on `exportable` column
- ✅ AJAX updates without page reload
- ✅ Minimal DOM manipulation
- ✅ CSS transitions for smooth UX

## Future Enhancements

Potential improvements for future iterations:
- Batch toggle for multiple selected items
- Filter items by exportable status
- Export history tracking
- Integration with NetSuite export process

---

**Implementation Date**: October 15, 2025  
**Branch**: itemExportToggle  
**Developer**: AI Assistant (Claude)


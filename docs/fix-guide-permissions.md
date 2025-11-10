# 🔧 Fix Guide: Enable Guides Menu with Admin Permissions

## 📋 **OVERVIEW**

This guide explains how to uncomment the Guides menu in the OPMS system and ensure it only appears for users with admin privileges. The Guides menu provides access to important documentation files including FAQ, Cheat Sheet, and User Manual.

## 🎯 **CURRENT STATE**

The Guides menu is currently **commented out** in the menu system. It was designed to provide access to three key documentation files:
- **FAQ** - `OPMS - FAQ.pdf`
- **Cheat Sheet** - `OPMS - Cheat Sheet.pdf` 
- **User Manual** - `OPMS-User-Manual-Nov2023.pdf`

## 🔍 **LOCATION OF COMMENTED CODE**

**File:** `application/models/Menu_model.php`  
**Lines:** 168-199

```php
/*array('text'=>'<i class="fal fa-question"></i> GUIDES',
      'class'=> 'bg-primary text-white',
      'url'=>'', 
      'module'=>'',
      'action'=>'',
      'sub'=> array(
        array(
          'text'=>'FAQ',
          'class'=>'',
          'url' => '',
          'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS - FAQ.pdf" ',
          'module'=>'',
          'action'=>''
        ),
        array(
          'text'=>'Cheat Sheet',
          'class'=>'',
          'url' => '',
          'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS - Cheat Sheet.pdf" ',
          'module'=>'',
          'action'=>''
        ),
        array(
          'text'=>'User Manual',
          'class'=>'',
          'url' => '',
          'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS-User-Manual-Nov2023.pdf" ',
          'module'=>'',
          'action'=>''
        )
      )
) */
```

## 🛠️ **IMPLEMENTATION STEPS**

### **Step 1: Verify Production Files**

The guide files **already exist in production** but are excluded from git via `.gitignore`. The following files should be present in production:
- `files/guides/OPMS - FAQ.pdf`
- `files/guides/OPMS - Cheat Sheet.pdf`
- `files/guides/OPMS-User-Manual-Nov2023.pdf`

### **Step 2: Create Local Mock Files for Testing**

For local development and testing, create mock PDF files:

```bash
# Create the guides directory
mkdir -p /path/to/opuzen-opms/files/guides

# Create mock PDF files for testing
touch files/guides/"OPMS - FAQ.pdf"
touch files/guides/"OPMS - Cheat Sheet.pdf"
touch files/guides/"OPMS-User-Manual-Nov2023.pdf"
```

**⚠️ IMPORTANT:** These mock files are already git-ignored via `.gitignore` (line 19: `assets/guides/`), so they won't be committed to the repository.

**Note:** The `.gitignore` pattern `assets/guides/` covers the `files/guides/` directory as well, ensuring all guide files remain excluded from version control.

### **Step 3: Uncomment and Modify Menu Code**

**File:** `application/models/Menu_model.php`

**Replace lines 168-199 with:**

```php
array('text'=>'<i class="fal fa-question"></i> GUIDES',
      'class'=> 'bg-primary text-white',
      'url'=>'', 
      'module'=>'guides',
      'action'=>'view',
      'sub'=> array(
        array(
          'text'=>'FAQ',
          'class'=>'',
          'url' => '',
          'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS - FAQ.pdf" ',
          'module'=>'guides',
          'action'=>'view'
        ),
        array(
          'text'=>'Cheat Sheet',
          'class'=>'',
          'url' => '',
          'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS - Cheat Sheet.pdf" ',
          'module'=>'guides',
          'action'=>'view'
        ),
        array(
          'text'=>'User Manual',
          'class'=>'',
          'url' => '',
          'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS-User-Manual-Nov2023.pdf" ',
          'module'=>'guides',
          'action'=>'view'
        )
      )
)
```

## 🔐 **PERMISSION SYSTEM EXPLANATION**

### **How Admin Permissions Work**

The OPMS system uses a permission-based menu system with the following key components:

1. **Admin Check:** User ID `1` is automatically considered admin
2. **Permission System:** Uses `module` and `action` fields for access control
3. **Menu Filtering:** The `hasPermissions()` function in `utility_helper.php` controls menu visibility

### **Permission Flow:**

```php
// In utility_helper.php - hasPermissions() function
function hasPermissions($permissionsList, $arr, $user_id = null)
{
    if ($user_id === '1') {
        return true;  // User ID 1 is always admin
    }
    if (array_key_exists('module', $arr)) {
        if ($arr['module'] === '' && $arr['action'] === '') {
            return true;  // Empty module/action = public access
        }
        foreach ($permissionsList as $row) {
            if ($row['module'] == $arr['module'] && $row['action'] == $arr['action'])
                return true;  // User has specific permission
        }
        return false;  // No permission found
    }
    return true;  // No module/action = public access
}
```

### **Admin-Only Access Implementation**

To make the Guides menu **admin-only**, we set:
- `'module'=>'guides'` 
- `'action'=>'view'`

This requires users to have explicit `guides` module with `view` action permission, which should only be granted to admins.

## 🗄️ **DATABASE PERMISSION SETUP**

### **Option 1: Database Permission Entry**

Add a permission entry in the database for admin users:

```sql
-- Add guides permission for admin group (assuming admin group ID is 1)
INSERT INTO `permissions` (`module`, `action`, `description`) 
VALUES ('guides', 'view', 'View Guides Menu');

-- Grant permission to admin group
INSERT INTO `group_permissions` (`group_id`, `permission_id`) 
VALUES (1, LAST_INSERT_ID());
```

### **Option 2: Admin-Only Check (Simpler)**

If you prefer to keep it simple and only show to user ID 1 (super admin), modify the menu item:

```php
array('text'=>'<i class="fal fa-question"></i> GUIDES',
      'class'=> 'bg-primary text-white',
      'url'=>'', 
      'module'=>'',  // Empty = public access
      'action'=>'',  // Empty = public access
      'admin_only'=> true,  // Custom flag for admin-only
      'sub'=> array(
        // ... submenu items
      )
)
```

Then modify the `hasPermissions()` function to check for the `admin_only` flag:

```php
function hasPermissions($permissionsList, $arr, $user_id = null)
{
    // Check for admin_only flag
    if (isset($arr['admin_only']) && $arr['admin_only'] === true) {
        return ($user_id === '1');
    }
    
    if ($user_id === '1') {
        return true;
    }
    // ... rest of existing logic
}
```

## 🧪 **TESTING**

### **Test Cases:**

1. **Admin User (ID = 1):**
   - Should see Guides menu
   - Should be able to access all three PDF files
   - Links should open in new tabs

2. **Regular User:**
   - Should NOT see Guides menu
   - Should not have access to guide files

3. **Guest User:**
   - Should NOT see Guides menu
   - Should not have access to guide files

### **Testing Steps:**

1. Login as admin user
2. Verify Guides menu appears in navigation
3. Click each guide link to ensure PDFs open correctly
4. Login as regular user
5. Verify Guides menu does NOT appear
6. Test direct URL access to PDFs (should be blocked or require authentication)

## 📁 **FILE STRUCTURE AFTER IMPLEMENTATION**

### **Production Environment:**
```
opuzen-opms/
├── files/
│   └── guides/
│       ├── OPMS - FAQ.pdf                    (exists in prod)
│       ├── OPMS - Cheat Sheet.pdf            (exists in prod)
│       └── OPMS-User-Manual-Nov2023.pdf      (exists in prod)
├── application/
│   └── models/
│       └── Menu_model.php                    (modified)
└── .gitignore                                (excludes guides/)
```

### **Local Development Environment:**
```
opuzen-opms/
├── files/
│   └── guides/
│       ├── OPMS - FAQ.pdf                    (mock file for testing)
│       ├── OPMS - Cheat Sheet.pdf            (mock file for testing)
│       └── OPMS-User-Manual-Nov2023.pdf      (mock file for testing)
├── application/
│   └── models/
│       └── Menu_model.php                    (modified)
└── .gitignore                                (excludes guides/)
```

**Note:** The `files/guides/` directory and its contents are git-ignored, so they won't appear in version control.

## ⚠️ **IMPORTANT NOTES**

1. **File Security:** Ensure PDF files are properly secured and only accessible to authorized users
2. **Path Validation:** The `base_url()` function should resolve correctly to your domain
3. **Permission Granularity:** Consider if you want different permission levels (e.g., some users can view FAQ but not User Manual)
4. **Mobile Responsiveness:** Test the menu on mobile devices to ensure proper display
5. **Browser Compatibility:** Test PDF opening in different browsers

## 🔧 **TROUBLESHOOTING**

### **Common Issues:**

1. **Menu Not Appearing:**
   - Check if user has proper permissions
   - Verify `hasPermissions()` function is working
   - Check browser console for JavaScript errors

2. **PDFs Not Opening:**
   - Verify file paths are correct
   - Check file permissions on server
   - Ensure PDF files exist in correct directory

3. **Permission Errors:**
   - Verify database permission entries
   - Check user group assignments
   - Test with different user accounts

## 📚 **REFERENCE FILES**

- **Menu Model:** `application/models/Menu_model.php`
- **Permission Helper:** `application/helpers/utility_helper.php`
- **Menu View:** `application/views/_header_menu.php`
- **Base Controller:** `application/core/MY_Controller.php`

---

**End of Guide**

*This implementation ensures the Guides menu is properly secured and only accessible to admin users while maintaining the existing permission system architecture.*

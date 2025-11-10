# OPMS Database Schema - AI Model Specification

## 🎯 **OVERVIEW**

The OPMS (Order Processing Management System) database is a **legacy MySQL database** containing 20+ years of textile/fabric business data. This specification provides complete schema understanding for AI models working with OPMS data integration.

### **Critical Context**
- **Legacy System**: 20+ year old database with established business processes
- **Production Data**: Contains live business-critical data - handle with extreme care
- **No Schema Changes**: AI models must work with existing schema - no modifications allowed
- **Direct Integration**: API reads directly from OPMS tables without abstraction layers

## 📊 **DATABASE ARCHITECTURE**

### **Schema Statistics**
- **Total Tables**: 150+ tables
- **Core T_* Tables**: 35 primary business tables
- **P_* Tables**: 25+ parameter/lookup tables  
- **Z_* Tables**: 5 vendor/contact tables
- **Support Tables**: 90+ history, cache, and utility tables

### **Naming Conventions**
```sql
-- Core business entities
T_PRODUCT, T_ITEM, T_ITEM_COLOR, T_PRODUCT_VENDOR

-- Parameter/lookup tables
P_COLOR, P_CONTENT, P_ABRASION_TEST, P_FIRECODE_TEST

-- Vendor/contact tables
Z_VENDOR, Z_CONTACT, Z_SHOWROOM

-- History tracking
S_HISTORY_*, BK_*, RESTOCK_*

-- Cache/views
cached_product_spec_view, PROC_*
```

## 🏗️ **CORE ENTITY TABLES**

### **1. T_PRODUCT - Product Master Data**
**Purpose**: Product families and specifications
```sql
CREATE TABLE `T_PRODUCT` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `name` varchar(50) NOT NULL,                -- Product name (e.g., "Tranquil", "Berba")
  `type` char(1) NOT NULL DEFAULT 'R',        -- R=Regular, D=Digital, S=ScreenPrint
  `width` decimal(11,2) DEFAULT NULL,         -- Fabric width in inches
  `vrepeat` decimal(5,2) DEFAULT NULL,        -- Vertical repeat in inches
  `hrepeat` decimal(5,2) DEFAULT NULL,        -- Horizontal repeat in inches
  `lightfastness` varchar(128) DEFAULT NULL,  -- Light fastness rating
  `seam_slippage` varchar(128) NOT NULL,      -- Seam slippage test results
  `outdoor` char(1) NOT NULL DEFAULT 'N',    -- Y/N outdoor suitable
  `dig_product_name` varchar(50) DEFAULT NULL, -- Digital product name
  `dig_width` decimal(11,2) DEFAULT NULL,     -- Digital width
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `in_master` tinyint(1) NOT NULL DEFAULT '0', -- In master catalog
  `archived` char(1) NOT NULL DEFAULT 'N',    -- N=Active, Y=Archived
  `log_vers_id` int NOT NULL DEFAULT '1',     -- Version tracking
  `user_id` int NOT NULL,                     -- User who created
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7593;
```

**NetSuite Mapping**:
- `id` → `custitem_opms_prod_id` (custom field)
- `name` → Part of display name: `"${name}: ${colors}"`
- `width` → `custitem_opms_fabric_width` (custom field)
- `type` → Product type classification

### **2. T_ITEM - Product Item Variations (SKUs)**
**Purpose**: Individual items/SKUs within product families
```sql
CREATE TABLE `T_ITEM` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT
  `product_type` varchar(2) NOT NULL,         -- R, D, S
  `in_ringset` int NOT NULL DEFAULT '0',      -- 0=No, 1=Yes in ringset
  `code` varchar(9) DEFAULT NULL,             -- Item code (NetSuite itemid)
  `status_id` int NOT NULL DEFAULT '1',       -- FK to P_PRODUCT_STATUS
  `stock_status_id` int NOT NULL DEFAULT '1', -- FK to P_STOCK_STATUS
  `vendor_color` varchar(50) DEFAULT NULL,    -- Vendor's color code
  `vendor_code` varchar(50) DEFAULT NULL,     -- Vendor's item code
  `roll_location_id` varchar(11) DEFAULT NULL, -- Physical location
  `roll_yardage` decimal(11,2) DEFAULT NULL,  -- Yards in stock
  `bin_location_id` varchar(11) DEFAULT NULL, -- Bin location
  `bin_quantity` int DEFAULT NULL,            -- Quantity in bin
  `min_order_qty` varchar(20) DEFAULT NULL,   -- Minimum order quantity
  `reselections_ids` varchar(1024) DEFAULT NULL, -- Related items
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `in_master` tinyint(1) NOT NULL DEFAULT '0', -- In master catalog
  `archived` char(11) NOT NULL DEFAULT 'N',   -- N=Active, Y=Archived
  `user_id` int NOT NULL,                     -- User who created
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `product_id` (`product_id`,`id`,`product_type`)
) ENGINE=InnoDB AUTO_INCREMENT=42966;
```

**NetSuite Mapping**:
- `id` → `custitem_opms_item_id` (custom field)
- `code` → `itemid` (NetSuite item ID)
- `vendor_color` → `custitem_opms_vendor_color` (custom field)
- `vendor_code` → `vendorcode` (itemvendor sublist)

### **3. T_ITEM_COLOR - Item-Color Relationships**
**Purpose**: Links items to their colors (many-to-many)
```sql
CREATE TABLE `T_ITEM_COLOR` (
  `item_id` int NOT NULL,                     -- FK to T_ITEM
  `color_id` int NOT NULL,                    -- FK to P_COLOR
  `n_order` int NOT NULL,                     -- Display order
  `date_add` timestamp NOT NULL,              -- Creation date
  `user_id` int NOT NULL DEFAULT '0',         -- User who created
  PRIMARY KEY (`item_id`,`color_id`),
  KEY `idx_item_id` (`item_id`)
) ENGINE=InnoDB;
```

**Usage Pattern**:
```sql
-- Get colors for an item
SELECT c.name 
FROM T_ITEM_COLOR ic 
JOIN P_COLOR c ON ic.color_id = c.id 
WHERE ic.item_id = ?
ORDER BY ic.n_order;
```

### **4. P_COLOR - Color Master Data**
**Purpose**: Master list of all colors
```sql
CREATE TABLE `P_COLOR` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `name` varchar(30) NOT NULL,                -- Color name (e.g., "Ash", "Fiesta")
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who created
  `active` char(1) NOT NULL DEFAULT 'Y',      -- Y=Active, N=Inactive
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7585;
```

## 🏢 **VENDOR & RELATIONSHIP TABLES**

### **5. Z_VENDOR - Vendor Master Data**
**Purpose**: Vendor/supplier information
```sql
CREATE TABLE `Z_VENDOR` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `abrev` varchar(15) DEFAULT NULL,           -- Vendor abbreviation
  `name` varchar(40) DEFAULT NULL,            -- Vendor business name
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '1',         -- User who created
  `active` char(1) NOT NULL DEFAULT 'Y',      -- Y=Active, N=Inactive
  `archived` varchar(1) NOT NULL DEFAULT 'N', -- N=Active, Y=Archived
  PRIMARY KEY (`id`),
  KEY `Z_VENDOR_id_name_index` (`id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=269;
```

**Critical Integration**: Requires `opms_netsuite_vendor_mapping` table for NetSuite sync.

### **6. T_PRODUCT_VENDOR - Product-Vendor Relationships**
**Purpose**: Links products to their vendors
```sql
CREATE TABLE `T_PRODUCT_VENDOR` (
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT
  `vendor_id` int NOT NULL,                   -- FK to Z_VENDOR
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',         -- User who created
  PRIMARY KEY (`product_id`),                 -- One vendor per product
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB;
```

### **7. T_PRODUCT_VARIOUS - Extended Product Attributes**
**Purpose**: Additional product specifications
```sql
CREATE TABLE `T_PRODUCT_VARIOUS` (
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT (unique)
  `vendor_product_name` varchar(50) NOT NULL, -- Vendor's product name
  `yards_per_roll` varchar(50) NOT NULL,     -- Yards per roll
  `lead_time` varchar(50) NOT NULL,          -- Lead time
  `min_order_qty` varchar(50) NOT NULL,      -- Minimum order quantity
  `tariff_code` varchar(50) NOT NULL,        -- Tariff classification
  `tariff_surcharge` varchar(50) DEFAULT NULL, -- Tariff surcharge
  `duty_perc` varchar(50) DEFAULT NULL,       -- Duty percentage
  `freight_surcharge` varchar(64) DEFAULT NULL, -- Freight surcharge
  `vendor_notes` text,                        -- Vendor notes
  `railroaded` char(1) NOT NULL DEFAULT 'N', -- Y/N railroaded
  `prop_65` char(1) DEFAULT NULL,             -- CA Prop 65 compliance
  `ab_2998_compliant` char(1) DEFAULT NULL,   -- AB 2998 compliance
  `dyed_options` char(1) DEFAULT NULL,        -- Dyed options available
  `weight_n` decimal(5,2) DEFAULT NULL,       -- Weight value
  `weight_unit_id` int DEFAULT NULL,          -- FK to P_WEIGHT_UNIT
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',         -- User who created
  UNIQUE KEY `product_id` (`product_id`)      -- One record per product
) ENGINE=InnoDB;
```

**NetSuite Mapping**:
- `vendor_product_name` → `custitem_opms_vendor_prod_name` (custom field)

## 📝 **MINI-FORMS TABLES (Rich Content)**

### **8. T_PRODUCT_CONTENT_FRONT - Front Content Specifications**
**Purpose**: Front fabric content percentages
```sql
CREATE TABLE `T_PRODUCT_CONTENT_FRONT` (
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT
  `perc` decimal(5,2) NOT NULL,               -- Percentage (e.g., 55.00)
  `content_id` int NOT NULL,                  -- FK to P_CONTENT
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',         -- User who created
  PRIMARY KEY (`product_id`,`perc`,`content_id`)
) ENGINE=InnoDB;
```

### **9. T_PRODUCT_CONTENT_FRONT_DESCR - Front Content Description**
**Purpose**: Rich text description of front content
```sql
CREATE TABLE `T_PRODUCT_CONTENT_FRONT_DESCR` (
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT (unique)
  `content` text NOT NULL,                    -- Rich text content
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB;
```

### **10. T_PRODUCT_CONTENT_BACK - Back Content Specifications**
**Purpose**: Back fabric content percentages
```sql
CREATE TABLE `T_PRODUCT_CONTENT_BACK` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT
  `perc` decimal(5,2) NOT NULL,               -- Percentage
  `content_id` int NOT NULL,                  -- FK to P_CONTENT
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL DEFAULT '0',         -- User who created
  PRIMARY KEY (`product_id`,`perc`,`content_id`),
  UNIQUE KEY `id` (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=523;
```

### **11. T_PRODUCT_CONTENT_BACK_DESCR - Back Content Description**
**Purpose**: Rich text description of back content
```sql
CREATE TABLE `T_PRODUCT_CONTENT_BACK_DESCR` (
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT (unique)
  `content` text NOT NULL,                    -- Rich text content
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB;
```

### **12. T_PRODUCT_ABRASION - Abrasion Test Results**
**Purpose**: Abrasion test data and results
```sql
CREATE TABLE `T_PRODUCT_ABRASION` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT
  `n_rubs` int NOT NULL,                      -- Number of rubs
  `abrasion_test_id` int NOT NULL,            -- FK to P_ABRASION_TEST
  `abrasion_limit_id` int NOT NULL,           -- FK to P_ABRASION_LIMIT
  `visible` char(1) NOT NULL DEFAULT 'Y',     -- Y/N visible
  `data_in_vendor_specsheet` char(1) NOT NULL DEFAULT 'N', -- Y/N in vendor spec
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,                 -- User who created
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `T_PRODUCT_ABRASION_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `T_PRODUCT` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4945;
```

### **13. T_PRODUCT_ABRASION_FILES - Abrasion Test Files**
**Purpose**: File attachments for abrasion tests
```sql
CREATE TABLE `T_PRODUCT_ABRASION_FILES` (
  `abrasion_id` int NOT NULL,                 -- FK to T_PRODUCT_ABRASION
  `url_dir` varchar(100) NOT NULL,            -- File path/URL
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who uploaded
  KEY `abrasion_id` (`abrasion_id`)
) ENGINE=InnoDB;
```

### **14. T_PRODUCT_FIRECODE - Fire Code Certifications**
**Purpose**: Fire safety test results and certifications
```sql
CREATE TABLE `T_PRODUCT_FIRECODE` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `product_id` int NOT NULL,                  -- FK to T_PRODUCT
  `firecode_test_id` int NOT NULL,            -- FK to P_FIRECODE_TEST
  `visible` char(1) NOT NULL DEFAULT 'Y',     -- Y/N visible
  `data_in_vendor_specsheet` char(1) NOT NULL DEFAULT 'N', -- Y/N in vendor spec
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who created
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10729;
```

### **15. T_PRODUCT_FIRECODE_FILES - Fire Code Test Files**
**Purpose**: File attachments for fire code tests
```sql
CREATE TABLE `T_PRODUCT_FIRECODE_FILES` (
  `firecode_id` int NOT NULL,                 -- FK to T_PRODUCT_FIRECODE
  `url_dir` varchar(200) NOT NULL,            -- File path/URL
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who uploaded
  KEY `firecode_id` (`firecode_id`)
) ENGINE=InnoDB;
```

## 🔍 **LOOKUP/PARAMETER TABLES**

### **16. P_CONTENT - Content Types**
**Purpose**: Fabric content types (Cotton, Polyester, etc.)
```sql
CREATE TABLE `P_CONTENT` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `name` varchar(35) NOT NULL,                -- Content name (e.g., "Cotton", "Polyester")
  `descr` varchar(50) DEFAULT NULL,           -- Description
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who created
  `active` char(1) NOT NULL DEFAULT 'Y',      -- Y=Active, N=Inactive
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=182;
```

### **17. P_ABRASION_TEST - Abrasion Test Types**
**Purpose**: Types of abrasion tests (Wyzenbeek, Martindale, etc.)
```sql
CREATE TABLE `P_ABRASION_TEST` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `name` varchar(35) NOT NULL,                -- Test name
  `descr` text,                               -- Test description
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who created
  `active` char(1) NOT NULL DEFAULT 'Y',      -- Y=Active, N=Inactive
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9;
```

### **18. P_FIRECODE_TEST - Fire Code Test Types**
**Purpose**: Types of fire safety tests (NFPA, CAL 117, etc.)
```sql
CREATE TABLE `P_FIRECODE_TEST` (
  `id` int NOT NULL AUTO_INCREMENT,           -- Primary key
  `name` varchar(50) NOT NULL,                -- Test name
  `descr` text,                               -- Test description
  `date_add` timestamp NOT NULL,              -- Creation date
  `date_modif` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,                     -- User who created
  `active` char(1) NOT NULL DEFAULT 'Y',      -- Y=Active, N=Inactive
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71;
```

## 📊 **CRITICAL QUERY PATTERNS**

### **Master Export Query (NetSuite Integration)**
```sql
SELECT DISTINCT
    i.id as item_id,
    i.code as item_code,
    i.vendor_code,                    -- T_ITEM.vendor_code
    i.vendor_color,                   -- T_ITEM.vendor_color
    p.id as product_id,
    p.name as product_name,
    p.width,
    v.id as vendor_id,
    v.name as vendor_name,
    m.netsuite_vendor_id,
    m.netsuite_vendor_name,
    pvar.vendor_product_name,         -- T_PRODUCT_VARIOUS.vendor_product_name
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as color_name
FROM T_ITEM i
JOIN T_PRODUCT p ON i.product_id = p.id
JOIN T_PRODUCT_VENDOR pv ON p.id = pv.product_id
JOIN Z_VENDOR v ON pv.vendor_id = v.id
JOIN opms_netsuite_vendor_mapping m ON v.id = m.opms_vendor_id
LEFT JOIN T_PRODUCT_VARIOUS pvar ON p.id = pvar.product_id
LEFT JOIN T_ITEM_COLOR ic ON i.id = ic.item_id
LEFT JOIN P_COLOR c ON ic.color_id = c.id
WHERE i.code IS NOT NULL
  AND p.name IS NOT NULL
  AND v.name IS NOT NULL
  AND i.archived = 'N'
  AND p.archived = 'N'
  AND v.active = 'Y'
  AND v.archived = 'N'
  AND m.opms_vendor_name = m.netsuite_vendor_name
GROUP BY i.id, i.code, i.vendor_code, i.vendor_color, p.id, p.name, p.width, v.id, v.name, m.netsuite_vendor_id, m.netsuite_vendor_name, pvar.vendor_product_name
HAVING color_name IS NOT NULL;
```

### **Mini-Forms Data Extraction**
```sql
-- Front Content with percentages
SELECT 
    pcf.perc,
    pc.name as content_name,
    pcfd.content as front_description
FROM T_PRODUCT_CONTENT_FRONT pcf
JOIN P_CONTENT pc ON pcf.content_id = pc.id
LEFT JOIN T_PRODUCT_CONTENT_FRONT_DESCR pcfd ON pcf.product_id = pcfd.product_id
WHERE pcf.product_id = ?
ORDER BY pcf.perc DESC;

-- Back Content with percentages
SELECT 
    pcb.perc,
    pc.name as content_name,
    pcbd.content as back_description
FROM T_PRODUCT_CONTENT_BACK pcb
JOIN P_CONTENT pc ON pcb.content_id = pc.id
LEFT JOIN T_PRODUCT_CONTENT_BACK_DESCR pcbd ON pcb.product_id = pcbd.product_id
WHERE pcb.product_id = ?
ORDER BY pcb.perc DESC;

-- Abrasion Tests with files
SELECT 
    pa.n_rubs,
    pat.name as test_name,
    pal.name as limit_name,
    GROUP_CONCAT(paf.url_dir) as file_urls
FROM T_PRODUCT_ABRASION pa
JOIN P_ABRASION_TEST pat ON pa.abrasion_test_id = pat.id
JOIN P_ABRASION_LIMIT pal ON pa.abrasion_limit_id = pal.id
LEFT JOIN T_PRODUCT_ABRASION_FILES paf ON pa.id = paf.abrasion_id
WHERE pa.product_id = ? AND pa.visible = 'Y'
GROUP BY pa.id;

-- Fire Codes with files
SELECT 
    pft.name as test_name,
    GROUP_CONCAT(pff.url_dir) as file_urls
FROM T_PRODUCT_FIRECODE pf
JOIN P_FIRECODE_TEST pft ON pf.firecode_test_id = pft.id
LEFT JOIN T_PRODUCT_FIRECODE_FILES pff ON pf.id = pff.firecode_id
WHERE pf.product_id = ? AND pf.visible = 'Y'
GROUP BY pf.id;
```

## ⚠️ **DATA QUALITY CONSTRAINTS**

### **Mandatory Filters for All Queries**
```sql
-- ALWAYS include these WHERE conditions
WHERE i.archived = 'N'           -- Active items only
  AND p.archived = 'N'           -- Active products only
  AND v.active = 'Y'             -- Active vendors only
  AND v.archived = 'N'           -- Non-archived vendors
  AND i.code IS NOT NULL         -- Valid item codes required
  AND p.name IS NOT NULL         -- Valid product names required
  AND v.name IS NOT NULL         -- Valid vendor names required
```

### **Field Validation Rules**
- **Item Codes**: Must be unique, non-null, exclude patterns like 'Digital%', 'D', ''
- **Product Names**: Required for all products
- **Vendor Mapping**: Must exist in `opms_netsuite_vendor_mapping` table
- **Colors**: At least one color required per item via T_ITEM_COLOR
- **Archive Status**: Only process active (non-archived) records

## 🔄 **REQUIRED INTEGRATION TABLES**

### **opms_netsuite_vendor_mapping (CRITICAL)**
**Purpose**: Maps OPMS vendors to NetSuite vendor IDs
```sql
CREATE TABLE opms_netsuite_vendor_mapping (
    id int NOT NULL AUTO_INCREMENT,
    opms_vendor_id int NOT NULL,              -- FK to Z_VENDOR.id
    opms_vendor_name varchar(100) NOT NULL,   -- Z_VENDOR.name
    netsuite_vendor_id int NOT NULL,          -- NetSuite internal ID
    netsuite_vendor_name varchar(100) NOT NULL, -- NetSuite vendor name
    active char(1) NOT NULL DEFAULT 'Y',      -- Y=Active, N=Inactive
    date_created timestamp DEFAULT CURRENT_TIMESTAMP,
    date_modified timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by varchar(50) DEFAULT 'system',
    notes text,                               -- Mapping notes
    PRIMARY KEY (id),
    UNIQUE KEY unique_opms_vendor (opms_vendor_id),
    UNIQUE KEY unique_netsuite_vendor (netsuite_vendor_id),
    KEY idx_opms_vendor_name (opms_vendor_name),
    KEY idx_netsuite_vendor_name (netsuite_vendor_name)
) ENGINE=InnoDB;
```

**Critical Rule**: Only process vendors where `opms_vendor_name = netsuite_vendor_name` for data accuracy.

## 🎨 **HTML GENERATION PATTERNS**

### **Mini-Forms HTML Output**
The API transforms mini-forms data into beautiful HTML for NetSuite rich text fields:

```javascript
// Front Content HTML Generation
const generateFrontContentHtml = (frontData) => {
    return `
    <div style="font-family: Arial, sans-serif; margin: 10px 0;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    color: white; padding: 8px 12px; border-radius: 4px; 
                    font-weight: bold; margin-bottom: 8px;">
            Front Content
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            ${frontData.map(item => `
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 4px 8px; font-weight: bold;">${item.perc}%</td>
                    <td style="padding: 4px 8px;">${item.content_name}</td>
                </tr>
            `).join('')}
        </table>
        ${frontData.description ? `
            <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; 
                        border-left: 3px solid #667eea; margin-top: 8px;">
                ${frontData.description}
            </div>
        ` : ''}
    </div>`;
};
```

## 📋 **AI MODEL INTEGRATION GUIDELINES**

### **For AI Models Working with OPMS Data**

#### **1. Data Access Patterns**
- **Always use prepared statements** to prevent SQL injection
- **Include mandatory filters** in all queries (archived, active, non-null)
- **Use LEFT JOINs** for optional relationships (colors, mini-forms)
- **Group by core fields** when aggregating data

#### **2. Field Validation Requirements**
```javascript
// MANDATORY validation for all OPMS fields
const validateOpmsField = (fieldName, fieldData) => {
    if (fieldData === undefined) {
        return 'query_failed';  // Field not accessible
    } else if (fieldData === null || fieldData === '' || 
               (Array.isArray(fieldData) && fieldData.length === 0)) {
        return 'src_empty_data';  // Empty but accessible
    } else {
        return 'has_data';  // Contains actual data
    }
};
```

#### **3. NetSuite Integration Rules**
- **Display Name Format**: Always use `"${product_name}: ${color_name}"` with colon separator
- **Vendor Mapping**: Verify mapping exists before processing
- **Custom Fields**: All 20+ custom fields must be populated or marked as "src empty data"
- **ItemVendor Sublist**: Must be populated for vendor relationships

#### **4. Error Handling Requirements**
- **Continue processing** despite individual field failures
- **Log all query failures** with structured error data
- **Track failure patterns** to detect systematic issues
- **Provide transparent data status** to end users

#### **5. Performance Considerations**
- **Use indexes** on frequently queried fields (product_id, item_id, vendor_id)
- **Limit result sets** for large queries
- **Cache lookup table data** (colors, content types, test types)
- **Use connection pooling** for database connections

### **Critical Success Factors**
1. **Respect Legacy Schema**: Never modify existing tables or constraints
2. **Handle Missing Data**: Use "src empty data" pattern for transparency
3. **Maintain Data Quality**: Apply all mandatory filters consistently
4. **Follow Naming Conventions**: Use established OPMS field names
5. **Preserve Relationships**: Maintain all foreign key relationships
6. **Support Rich Content**: Generate proper HTML for mini-forms data

This specification provides the complete foundation for AI models to safely and effectively work with the OPMS legacy database while maintaining data integrity and business continuity.

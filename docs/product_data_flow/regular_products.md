# Regular Products Data Flow Documentation

## Overview
Regular products in the system are the standard fabric products that form the core of the product catalog. This document outlines their data structure, relationships, and flow through the system.

## Database Structure

### Core Tables
1. `T_PRODUCT` - Main product table
   - Primary key: `id`
   - Key fields:
     - `name` - Product name
     - `width` - Product width
     - `vrepeat` - Vertical repeat
     - `hrepeat` - Horizontal repeat
     - `outdoor` - Outdoor flag
     - `archived` - Archive status

2. `T_ITEM` - Product variations/colorways
   - Primary key: `id`
   - Key fields:
     - `product_id` - Links to T_PRODUCT
     - `code` - Item code
     - `status_id` - Product status
     - `stock_status_id` - Stock status
     - `archived` - Archive status

### Supporting Tables
1. `T_PRODUCT_VENDOR` - Vendor relationships
2. `T_PRODUCT_PRICE` - Pricing information
3. `T_ITEM_COLOR` - Color associations
4. `T_PRODUCT_CONTENT_FRONT` - Front content specifications
5. `T_PRODUCT_CONTENT_BACK` - Back content specifications
6. `T_PRODUCT_FINISH` - Finish specifications
7. `T_PRODUCT_FIRECODE` - Fire code specifications
8. `T_PRODUCT_VARIOUS` - Additional product details

## Data Flow

### Creation Flow
1. Product Creation:
   ```php
   // Basic product data
   $data = array(
     'name' => $product_name,
     'width' => $width,
     'vrepeat' => $vrepeat,
     'hrepeat' => $hrepeat,
     'outdoor' => $outdoor
   );
   $product_id = $this->model->save_product($data);
   ```

2. Item Creation:
   ```php
   $data = array(
     'product_id' => $product_id,
     'code' => $code,
     'status_id' => $status,
     'stock_status_id' => $stock_status
   );
   $item_id = $this->model->save_item($data);
   ```

### Update Flow
1. Product Updates:
   - Updates can be made to any product attribute
   - Changes are tracked in history tables
   - Price updates follow a separate flow

2. Item Updates:
   - Status changes
   - Stock status updates
   - Color associations
   - Code updates

### Archive Flow
1. Product Archiving:
   ```php
   $this->model->archive_product($product_id);
   ```

2. Item Archiving:
   - Items can be archived independently
   - Archived items are hidden from standard views
   - Can be retrieved if needed

## UI Representation

### Product Display
1. Basic Information:
   - Product name
   - Width
   - Repeat patterns
   - Outdoor status

2. Item Display:
   - Item code
   - Colors
   - Status
   - Stock status
   - Price information

### Search and Filtering
1. Product Search:
   - By name
   - By vendor
   - By attributes

2. Item Search:
   - By code
   - By color
   - By status
   - By stock status

## Relationships

### Product-Item Relationship
- One-to-many relationship
- Products can have multiple items
- Items represent colorways/variations

### Vendor Relationship
- Products can be associated with multiple vendors
- Vendor information affects pricing and availability

### Color Relationship
- Items can have multiple colors
- Colors are stored in `P_COLOR` table
- Linked through `T_ITEM_COLOR`

## Status Management

### Product Status
1. Active
2. Discontinued
3. Archived

### Stock Status
1. In Stock
2. Out of Stock
3. On Order
4. Discontinued

## Price Management

### Price Types
1. Regular Price
2. Volume Price
3. Special Price

### Price Updates
1. Manual updates
2. Bulk updates
3. Price list imports

## History Tracking

### Change History
1. Product changes
2. Price changes
3. Status changes

### Audit Trail
1. Who made changes
2. When changes were made
3. What was changed

## Best Practices

### Data Entry
1. Always include required fields
2. Validate measurements
3. Check for duplicates

### Updates
1. Verify status changes
2. Update related items
3. Maintain history

### Archiving
1. Archive items before products
2. Check for dependencies
3. Maintain relationships

## Common Operations

### Product Creation
1. Enter basic information
2. Add specifications
3. Set pricing
4. Create items

### Item Management
1. Add colors
2. Set status
3. Update stock
4. Manage pricing

### Product Updates
1. Update specifications
2. Modify pricing
3. Change status
4. Update items

## Error Handling

### Common Issues
1. Duplicate codes
2. Invalid measurements
3. Missing relationships
4. Status conflicts

### Resolution Steps
1. Validate input
2. Check relationships
3. Verify status
4. Update history

## Integration Points

### External Systems
1. Inventory management
2. Order processing
3. Customer management
4. Reporting systems

### Internal Systems
1. Price management
2. Stock tracking
3. Order processing
4. Customer service 
# Digital Products Data Flow Documentation

## Overview
Digital products represent a specialized category of products that combine digital printing styles with base materials. This document outlines their unique structure and flow through the system.

## Database Structure

### Core Tables
1. `T_PRODUCT_X_DIGITAL` - Main digital product table
   - Primary key: `id`
   - Key fields:
     - `style_id` - Links to digital style
     - `reverse_ground` - Reverse printing flag
     - `in_master` - Master list inclusion
     - `item_id` - Links to base item

2. `U_DIGITAL_STYLE` - Digital style definitions
   - Primary key: `id`
   - Key fields:
     - `name` - Style name
     - `vrepeat` - Vertical repeat
     - `hrepeat` - Horizontal repeat
     - `active` - Active status

3. `T_ITEM` - Product variations
   - Primary key: `id`
   - Key fields:
     - `product_id` - Links to T_PRODUCT_X_DIGITAL
     - `code` - Item code
     - `status_id` - Product status
     - `stock_status_id` - Stock status

### Supporting Tables
1. `T_PRODUCT` - Base product information
2. `T_ITEM_COLOR` - Color associations
3. `T_PRODUCT_PRICE` - Pricing information
4. `T_SHOWCASE_STYLE_ITEMS` - Showcase relationships

## Data Flow

### Creation Flow
1. Digital Product Creation:
   ```php
   $data = array(
     'style_id' => $style_id,
     'reverse_ground' => $reverse_ground,
     'item_id' => $item_id,
     'in_master' => $in_master
   );
   $digital_product_id = $this->model->save_digital_product($data);
   ```

2. Item Creation:
   ```php
   $data = array(
     'product_id' => $digital_product_id,
     'product_type' => 'D',
     'code' => 'Digital',
     'status_id' => $status,
     'stock_status_id' => $stock_status
   );
   $item_id = $this->model->save_item($data);
   ```

### Update Flow
1. Digital Product Updates:
   - Style changes
   - Ground changes
   - Master list status
   - Price updates

2. Item Updates:
   - Status changes
   - Stock status
   - Color associations

### Archive Flow
1. Digital Product Archiving:
   ```php
   $this->model->archive_product($digital_product_id, 'D');
   ```

2. Item Archiving:
   - Items can be archived independently
   - Archived items are hidden from standard views
   - Can be retrieved if needed

## UI Representation

### Product Display
1. Basic Information:
   ```
   [Style Name] on [Reverse] [Product Name] / [Color1] / [Color2]
   ```

2. Item Display:
   - Digital designation
   - Colors
   - Status
   - Stock status
   - Price information

### Search and Filtering
1. Digital Product Search:
   - By style
   - By ground
   - By attributes

2. Item Search:
   - By code
   - By color
   - By status
   - By stock status

## Relationships

### Style-Ground Relationship
- Digital style applied to base material
- Reverse ground option
- Multiple color possibilities

### Product-Item Relationship
- One-to-many relationship
- Products can have multiple items
- Items represent colorways/variations

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
2. Validate style and ground combinations
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
1. Select digital style
2. Choose ground material
3. Set pricing
4. Create items

### Item Management
1. Add colors
2. Set status
3. Update stock
4. Manage pricing

### Product Updates
1. Update style
2. Modify ground
3. Change status
4. Update items

## Error Handling

### Common Issues
1. Invalid style-ground combinations
2. Missing relationships
3. Status conflicts
4. Price inconsistencies

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

## Special Considerations

### Style Management
1. Style activation/deactivation
2. Style version control
3. Style-ground compatibility

### Ground Management
1. Ground availability
2. Ground specifications
3. Ground pricing

### Digital Printing
1. Print specifications
2. Color management
3. Quality control

## Performance Considerations

### Database Optimization
1. Index usage
2. Query optimization
3. Relationship management

### UI Performance
1. Lazy loading
2. Caching
3. Search optimization 
# Inventory Data Flow Documentation

## Overview
The inventory system manages fabric stock in yards, tracking both physical inventory and orders. The system maintains several key metrics:
- Yards in Stock
- Yards on Hold
- Yards Available
- Yards on Order
- Yards Backorder

## Core Tables

### 1. Sales Database Tables
1. `op_products_bolts` - Individual bolt inventory
   - Tracks individual bolts of fabric
   - Contains fields:
     - `yardsInStock`
     - `yardsOnHold`
     - `yardsAvailable`

2. `op_products` - Product inventory summary
   - Links to master items
   - Contains `master_item_id` linking to `T_ITEM`

3. `op_products_stock` - Aggregated stock view
   - Created via stored procedure
   - Updated daily
   - Contains aggregated stock metrics

### 2. Master Database Tables
1. `T_ITEM` - Master item records
   - Links to products
   - Contains stock status
   - Links to sales inventory

2. `T_ITEM_SHELF` - Physical location tracking
   - Maps items to shelf locations
   - Used for physical inventory management

## Database Integration

### 1. Sales Database Integration (opuzen_prod_sales)
The OPMS system integrates with the `opuzen_prod_sales` database to maintain real-time inventory and order information. This integration is crucial for accurate stock management and order processing.

1. **Stock Management Integration**:
   - Direct queries to `op_products_bolts` for bolt-level inventory
   - Access to `op_products` for product-level information
   - Utilization of `v_products_stock` view for aggregated data
   - Example integration:
     ```sql
     SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsInStock)
     FROM opuzen_prod_sales.op_products_bolts
     JOIN opuzen_prod_sales.op_products 
     ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
     WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
     ```

2. **Order Processing Integration**:
   - Integration with `op_orders_header` for order information
   - Access to `op_orders_products` for order line items
   - Connection to `op_purchase_order` for purchase order data
   - Real-time order status updates

3. **Views and Procedures**:
   - Database views that join with sales tables for comprehensive reporting
   - Stored procedures for daily stock updates
   - Views like `v_item_stock` and `v_item_full` that aggregate sales data
   - Example procedure:
     ```sql
     CREATE PROCEDURE proc_update_products_stock()
     BEGIN
       TRUNCATE TABLE opuzen_prod_sales.op_products_stock;
       INSERT INTO opuzen_prod_sales.op_products_stock
       SELECT 
         P.id,
         P.master_item_id,
         P.name,
         P.color,
         C.name as catalogue,
         SUM(PB.yardsInStock) as yardsInStock,
         SUM(PB.yardsOnHold) as yardsOnHold,
         SUM(PB.yardsAvailable) as yardsAvailable
       FROM opuzen_prod_sales.op_products P
       LEFT OUTER JOIN opuzen_prod_sales.op_products_bolts PB 
       ON P.id = PB.idProduct
       JOIN opuzen_prod_sales.op_catalogue C 
       ON P.idCatalogue = C.id
     ```

4. **Integration Points**:
   - Stock level synchronization
   - Order status updates
   - Purchase order tracking
   - Catalog management
   - Real-time inventory updates

5. **Data Flow Considerations**:
   - Read-focused integration with sales database
   - Real-time data access for inventory management
   - Daily batch updates for aggregated data
   - Transaction management for data consistency

## Data Flow

### 1. Stock Updates
1. Daily Update Process:
   ```sql
   -- Stored procedure updates stock daily
   CREATE PROCEDURE proc_update_products_stock()
   BEGIN
     TRUNCATE TABLE op_products_stock;
     INSERT INTO op_products_stock
     SELECT 
       P.id,
       P.master_item_id,
       P.name,
       P.color,
       C.name as catalogue,
       SUM(PB.yardsInStock) as yardsInStock,
       SUM(PB.yardsOnHold) as yardsOnHold,
       SUM(PB.yardsAvailable) as yardsAvailable,
       -- Additional calculations for orders and backorders
   ```

### 2. Stock Status Tracking
1. Stock Metrics:
   - `yardsInStock`: Total physical inventory
   - `yardsOnHold`: Reserved inventory
   - `yardsAvailable`: Available for sale
   - `yardsOnOrder`: On order from suppliers
   - `yardsBackorder`: Backordered by customers

2. Status Updates:
   - Stock status changes tracked in `P_STOCK_STATUS`
   - Product status changes tracked in `P_PRODUCT_STATUS`

### 3. Physical Location Management
1. Shelf Management:
   - Items can be assigned to multiple shelves
   - Shelf locations tracked in `T_ITEM_SHELF`
   - Shelf names stored in `P_SHELF`

2. Sampling Locations:
   - Roll locations tracked
   - Bin locations tracked
   - Location changes logged

## Integration Points

### 1. Sales System Integration
1. Stock Synchronization:
   - Daily updates via stored procedure
   - Real-time stock checks
   - Order processing integration

2. Order Management:
   - Backorder tracking
   - Order fulfillment
   - Stock allocation

### 2. Master Data Integration
1. Product-Item Relationship:
   - Products link to items
   - Items link to sales inventory
   - Stock status propagation

2. Location Management:
   - Physical location tracking
   - Shelf management
   - Sampling location tracking

## UI Representation

### 1. Stock Display
1. Stock Metrics:
   ```javascript
   {
     "title": "In Stock",
     "data": "yardsInStock",
     "render": function(data, type, row) {
       // Display logic for in-stock quantity
     }
   }
   ```

2. Availability Display:
   ```javascript
   {
     "title": "Available",
     "data": "yardsAvailable",
     "render": function(data, type, row) {
       // Display logic for available quantity
     }
   }
   ```

### 2. Stock Filtering
1. Search Capabilities:
   - Filter by stock levels
   - Filter by location
   - Filter by status

2. Reporting:
   - Stock level reports
   - Location reports
   - Status reports

## Best Practices

### 1. Stock Management
1. Regular Updates:
   - Daily stock synchronization
   - Real-time order processing
   - Location updates

2. Data Validation:
   - Stock level validation
   - Location validation
   - Status validation

### 2. Error Handling
1. Common Issues:
   - Stock discrepancies
   - Location mismatches
   - Status conflicts

2. Resolution Steps:
   - Stock reconciliation
   - Location verification
   - Status updates

## Performance Considerations

### 1. Database Optimization
1. Index Usage:
   - Stock level queries
   - Location queries
   - Status queries

2. Query Optimization:
   - Aggregated views
   - Stored procedures
   - Efficient joins

### 2. UI Performance
1. Data Loading:
   - Lazy loading
   - Caching
   - Efficient filtering

2. Display Optimization:
   - Pagination
   - Dynamic updates
   - Efficient rendering 
# Database Schema

## Overview

The Restock system uses multiple database tables to manage orders, shipments, and related data. The schema is designed to separate active orders from completed ones for performance optimization.

## Core Tables

### `restock_orders` (Active Orders)
Primary table for pending/active restock orders.

| Column | Type | Purpose | Notes |
|--------|------|---------|-------|
| `id` | INT PRIMARY KEY | Unique order identifier | Auto-increment |
| `item_id` | INT | Foreign key to items table | Links to fabric item |
| `destination_id` | INT | Foreign key to destinations | Where samples go |
| `size` | INT | Sample size | 6, 12, or 18 (inches) |
| `quantity_total` | INT | Total samples requested | Regular + Priority |
| `quantity_priority` | INT | Priority samples | Urgent/rush samples |
| `quantity_ringsets` | INT | Ringset samples | Ring-bound sample books |
| `quantity_shipped` | INT | Total samples shipped | Running total |
| `quantity_ringsets_shipped` | INT | Ringsets shipped | Running total |
| `restock_status_id` | INT | Current order status | Foreign key to status table |
| `user_id` | INT | User who created order | Foreign key to users |
| `user_id_modif` | INT | User who last modified | Foreign key to users |
| `date_add` | DATETIME | Order creation date | Timestamp |
| `date_modif` | DATETIME | Last modification date | Timestamp |

**Indexes**:
- Primary: `id`
- Foreign keys: `item_id`, `destination_id`, `restock_status_id`
- Performance: `(item_id, size, destination_id)` for duplicate detection

### `restock_completed` (Historical Orders)  
Archive table for completed/cancelled orders.

**Schema**: Same as `restock_orders` table
**Purpose**: Keeps completed orders separate for performance
**Migration**: Orders moved here when status = completed or cancelled

### `restock_shipments` (Shipment Records)
Tracks individual shipments for each order.

| Column | Type | Purpose | Notes |
|--------|------|---------|-------|
| `id` | INT PRIMARY KEY | Unique shipment ID | Auto-increment |
| `order_id` | INT | Foreign key to restock_orders | Links to order |
| `quantity` | INT | Regular samples shipped | In this shipment |
| `quantity_ringsets` | INT | Ringsets shipped | In this shipment |
| `date_shipped` | DATETIME | Shipment date | Auto-set to current time |
| `user_id` | INT | User who recorded shipment | Foreign key to users |

**Purpose**: 
- Detailed shipment history
- Allows multiple partial shipments per order
- Audit trail for fulfillment

### `restock_destinations` (Destination Master)
Lookup table for shipping destinations.

| Column | Type | Purpose | Notes |
|--------|------|---------|-------|
| `id` | INT PRIMARY KEY | Unique destination ID | |
| `name` | VARCHAR | Destination name | "Chicago Office", "NY Warehouse" |
| `address` | TEXT | Shipping address | Optional detailed address |
| `active` | ENUM('Y','N') | Active status | Hide inactive destinations |

### `restock_status` (Status Master)
Lookup table for order statuses.

| Column | Type | Purpose | Notes |
|--------|------|---------|-------|
| `id` | INT PRIMARY KEY | Unique status ID | |
| `name` | VARCHAR | Status name | "Pending", "Backorder", etc. |
| `abrev` | VARCHAR | Short abbreviation | "PEND", "BACK" |
| `active` | ENUM('Y','N') | Active status | Hide inactive statuses |

**Standard Status IDs** (from controller constants):
- `5` - Completed
- `6` - Cancelled  
- `7` - Backorder

## Related Tables (From Other Systems)

### `items` (Item Master)
Contains fabric/product item details.

**Key Columns Used**:
- `item_id` - Primary key
- `product_id` - Foreign key to products table
- `code` - Item code/SKU
- `color` - Color name
- `sales_id` - Link to sales system

### `products` (Product Master)
Contains fabric product information.

**Key Columns Used**:
- `product_id` - Primary key
- `product_name` - Fabric name
- `vendor_id` - Supplier information

### `users` (User System)
User authentication and permissions.

**Key Columns Used**:
- `user_id` - Primary key
- `username` - Display name
- `email` - Contact information

## Data Relationships

### Entity Relationship Diagram

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│  restock_orders │    │ restock_shipments│    │     items       │
│                 │    │                  │    │                 │
│ id (PK)         │◄───┤ order_id (FK)    │    │ item_id (PK)    │
│ item_id (FK)    ├───►│ quantity         │    │ code            │
│ destination_id  │    │ quantity_ringsets│    │ color           │
│ quantity_total  │    │ date_shipped     │    │ product_id (FK) │
│ quantity_shipped│    │ user_id (FK)     │    │ sales_id        │
│ status_id (FK)  │    └──────────────────┘    └─────────────────┘
│ user_id (FK)    │                                      │
└─────────────────┘                                      │
         │                                              │
         │         ┌─────────────────┐                  │
         └────────►│    products     │◄─────────────────┘
                   │                 │
                   │ product_id (PK) │
                   │ product_name    │
                   │ vendor_id (FK)  │
                   └─────────────────┘
```

### Foreign Key Relationships

**restock_orders**:
- `item_id` → `items.item_id`
- `destination_id` → `restock_destinations.id`
- `restock_status_id` → `restock_status.id`
- `user_id` → `users.user_id`
- `user_id_modif` → `users.user_id`

**restock_shipments**:
- `order_id` → `restock_orders.id`
- `user_id` → `users.user_id`

**items** (from other system):
- `product_id` → `products.product_id`

## Data Flow Patterns

### Order Creation Flow
```sql
-- 1. Check for duplicates
SELECT * FROM restock_orders 
WHERE item_id IN (?) AND size IN (?) AND destination_id = ?
  AND restock_status_id NOT IN (5, 6);  -- Not completed/cancelled

-- 2. Insert new orders or update existing
INSERT INTO restock_orders (item_id, destination_id, quantity_total, ...)
VALUES (?, ?, ?, ...);

-- OR update existing
UPDATE restock_orders 
SET quantity_total = quantity_total + ?, 
    date_modif = NOW(), 
    user_id_modif = ?
WHERE id = ?;
```

### Shipment Recording Flow
```sql
-- 1. Insert shipment record
INSERT INTO restock_shipments (order_id, quantity, quantity_ringsets, user_id)
VALUES (?, ?, ?, ?);

-- 2. Update order totals
UPDATE restock_orders 
SET quantity_shipped = quantity_shipped + ?,
    quantity_ringsets_shipped = quantity_ringsets_shipped + ?
WHERE id = ?;

-- 3. Check if order complete, move to completed table if so
INSERT INTO restock_completed SELECT * FROM restock_orders WHERE id = ?;
DELETE FROM restock_orders WHERE id = ?;
```

### Status Change Flow
```sql
-- Update order status
UPDATE restock_orders 
SET restock_status_id = ?, 
    date_modif = NOW(), 
    user_id_modif = ?
WHERE id = ?;

-- If cancelled, move to completed
INSERT INTO restock_completed SELECT * FROM restock_orders WHERE id = ?;
DELETE FROM restock_orders WHERE id = ?;
```

## Query Patterns

### Common Queries Used by System

**Get Active Orders with Filters**:
```sql
SELECT ro.*, rd.name as destination, rs.name as status,
       i.code, i.color, p.product_name, u.username
FROM restock_orders ro
JOIN restock_destinations rd ON ro.destination_id = rd.id
JOIN restock_status rs ON ro.restock_status_id = rs.id  
JOIN items i ON ro.item_id = i.item_id
JOIN products p ON i.product_id = p.product_id
JOIN users u ON ro.user_id = u.user_id
WHERE ro.date_add BETWEEN ? AND ?
  AND (? = 0 OR ro.destination_id = ?)
  AND (? = 0 OR ro.restock_status_id = ?)
ORDER BY ro.date_add DESC;
```

**Get Order Shipment History**:
```sql
SELECT rs.*, u.username, ro.quantity_total, ro.quantity_ringsets
FROM restock_shipments rs
JOIN users u ON rs.user_id = u.user_id
JOIN restock_orders ro ON rs.order_id = ro.id
WHERE rs.order_id = ?
ORDER BY rs.date_shipped DESC;
```

**Duplicate Detection Query**:
```sql
SELECT ro.*, i.code, i.color, p.product_name, u.username, rs.name as status
FROM restock_orders ro
JOIN items i ON ro.item_id = i.item_id
JOIN products p ON i.product_id = p.product_id
JOIN users u ON ro.user_id = u.user_id
JOIN restock_status rs ON ro.restock_status_id = rs.id
WHERE ro.item_id IN (?, ?, ?) 
  AND ro.size IN (?, ?, ?)
  AND ro.destination_id = ?
  AND ro.restock_status_id NOT IN (5, 6);  -- Exclude completed/cancelled
```

## Performance Optimizations

### Indexes
- **Primary Keys**: Fast lookups by ID
- **Foreign Keys**: Efficient joins between tables
- **Composite Index**: `(item_id, size, destination_id)` for duplicate detection
- **Date Index**: `date_add` for time-based filtering
- **Status Index**: `restock_status_id` for status filtering

### Table Partitioning
- **Active vs Completed**: Separate tables improve query performance
- **Smaller Result Sets**: Active orders table stays small
- **Historical Archive**: Completed orders preserved but separate

### Query Optimization
- **Limited Joins**: Only join tables when data needed
- **Selective WHERE**: Filter early in query processing
- **Batch Operations**: Multiple inserts/updates in single transaction

## Data Integrity Rules

### Constraints
- **NOT NULL**: Required fields cannot be empty
- **Foreign Keys**: Referential integrity enforced
- **Check Constraints**: Quantities must be >= 0
- **Unique Constraints**: Prevent exact duplicates when needed

### Business Rules Enforced
- Shipped quantities cannot exceed ordered quantities
- Orders cannot be modified after completion
- Status transitions must follow valid patterns
- User permissions enforced at application level

### Audit Trail
- All modifications record user and timestamp
- Shipment history preserved permanently  
- Status change history maintained
- User activity tracked for compliance

## Backup and Maintenance

### Backup Strategy
- **Regular Backups**: All tables included in standard backup
- **Point-in-Time Recovery**: Transaction log backup for recovery
- **Archive Strategy**: Old completed orders can be archived separately

### Maintenance Tasks
- **Index Rebuilding**: Periodic index maintenance for performance
- **Statistics Updates**: Keep query optimizer statistics current
- **Archive Old Data**: Move very old completed orders to archive tables
- **Clean Temp Data**: Remove any temporary processing tables
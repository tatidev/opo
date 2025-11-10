# Product Spec View Optimization & Caching Strategy

This document describes the process of optimizing the original UNION-based product specification query and replacing it with a high-performance cache-based implementation for scalable, search-friendly behavior in the OPMS application.

---

## ⚙️ Overview

The original `get_products_spec_view()` function built a massive `UNION ALL` query joining many related tables for both regular and digital products. The resulting query was taxing the RDS database, with page loads taking **6+ seconds** due to:

* Many `JOIN`s over large tables
* Unindexed text filtering with `LIKE`
* Complex aggregations executed live for every request

---

## ✅ Optimization Goals

* **Improve response time** from 6+ seconds to under 1 second
* **Avoid redundant processing** on each page load
* **Preserve compatibility** with existing DataTables frontend
* **Allow text search on key fields** like name, content, uses, firecodes, vendor

---

## 🧱 Step-by-Step Optimizations

### 1. Full Query Extraction

The full unfiltered UNION query was extracted into a reusable function:

```
Product_model::get_products_spec_full_unfiltered_query()
```

---

### 2. Cached Table Design

A new MySQL table `cached_product_spec_view` was created to store the fully joined and formatted output of the UNION query.

#### ✅ Table Schema

```
CREATE TABLE `cached_product_spec_view` (
    `product_name` TEXT,
    `vrepeat` VARCHAR(50) DEFAULT NULL,
    `hrepeat` VARCHAR(50) DEFAULT NULL,
    `width` VARCHAR(50) DEFAULT NULL,
    `product_id` INT NOT NULL,
    `outdoor` VARCHAR(5) DEFAULT NULL,
    `product_type` CHAR(1) NOT NULL,
    `archived` CHAR(1) DEFAULT NULL,
    `in_master` CHAR(1) DEFAULT NULL,
    `abrasions` TEXT,
    `count_abrasion_files` INT DEFAULT NULL,
    `content_front` TEXT,
    `firecodes` TEXT,
    `count_firecode_files` INT DEFAULT NULL,
    `uses` TEXT,
    `uses_id` TEXT,
    `vendor_product_name` VARCHAR(255) DEFAULT NULL,
    `tariff_surcharge` DECIMAL(10,2) DEFAULT NULL,
    `freight_surcharge` DECIMAL(10,2) DEFAULT NULL,
    `p_hosp_cut` DECIMAL(10,2) DEFAULT NULL,
    `p_hosp_roll` DECIMAL(10,2) DEFAULT NULL,
    `p_res_cut` DECIMAL(10,2) DEFAULT NULL,
    `p_dig_res` DECIMAL(10,2) DEFAULT NULL,
    `p_dig_hosp` DECIMAL(10,2) DEFAULT NULL,
    `price_date` VARCHAR(20) DEFAULT NULL,
    `fob` DECIMAL(10,2) DEFAULT NULL,
    `cost_cut` TEXT,
    `cost_half_roll` TEXT,
    `cost_roll` TEXT,
    `cost_roll_landed` TEXT,
    `cost_roll_ex_mill` TEXT,
    `cost_date` VARCHAR(20) DEFAULT NULL,
    `vendors_name` VARCHAR(255) DEFAULT NULL,
    `vendors_abrev` VARCHAR(50) DEFAULT NULL,
    `weaves` TEXT,
    `weaves_id` TEXT,
    `colors` TEXT,
    `color_ids` TEXT,
    `searchable_colors` TEXT,
    `searchable_uses` TEXT,
    `searchable_firecodes` TEXT,
    `searchable_content_front` TEXT,
    PRIMARY KEY (`product_id`, `product_type`),
    FULLTEXT KEY `ft_match` (
        `product_name`,
        `vendor_product_name`,
        `abrasions`,
        `content_front`,
        `firecodes`,
        `uses`,
        `vendors_name`,
        `weaves`,
        `searchable_colors`,
        `searchable_uses`,
        `searchable_firecodes`,
        `searchable_content_front`
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

---

### 3. Cache Builder Function

The full cache builder in `Product_model.php`:

```
public function build_cached_product_spec_view()
{
    $sql = "REPLACE INTO cached_product_spec_view (...) " . $this->get_products_spec_full_unfiltered_query();
    return $this->db->query($sql);
}
```

Uses `REPLACE INTO` to avoid primary key conflicts by upserting rows.

---

### 4. Manual Refresh Endpoint

A controller endpoint was added to allow cache refresh manually or via cron:

```
public function refresh_spec_cache()
{
    // Secret key protection and logging included
    ...
    $this->Product_model->build_cached_product_spec_view();
}
```

Logs a timestamp and enforces a secure access key.

---

### 5. Search Query Update

The search method now reads from `cached_product_spec_view` instead of rebuilding joins live. It uses:

* `MATCH (...) AGAINST (...)` for fulltext search
* `GROUP BY product_id, product_type` to eliminate duplicates

---

### 6. Auto-Refresh on Product Save

When a product or item is saved, its cached row is refreshed:

**controller/Item.php:**

```
function save_item()
{
    ...
    // Refresh the cached product spec view by inserting the product row into the cache table
    $this->load->model('Product_model', 'product_model');
    $this->product_model->refresh_cached_product_row($product_id, $product_type);
    ...
}
```

**Product\_model.php:**

```
public function refresh_cached_product_row($product_id, $product_type)
{
    ...
    // Runs the full query for a single product, then REPLACE INTO cache
}
```

---

### 7. Scheduled Background Refresh

A cron job runs every 20 minutes to rebuild the full cache:

```
*/20 * * * * curl -s "https://opms.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN" > /dev/null 2>&1
```

---

## 🚀 Deployment Steps

For all environments:

1. Deploy Git branch (`deployDev`, `deployQA`, `deployProd`)
2. Apply the updated `CREATE TABLE` SQL to the corresponding RDS
3. Add the `cron` job
4. Test by visiting:

   ```
   https://<env>.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN
   ```

---

## ✅ Benefits

* ⚡ **Speed**: Query time reduced from 6+ seconds to sub-second
* 🔍 **Searchability**: FULLTEXT on cleaned, concatenated values
* 🔄 **Maintained**: Auto-refresh logic on save + scheduled cron
* 🧹 **Compatible**: No changes to DataTables or frontend logic
* 🧱 **Stable**: Safe and extendable caching structure

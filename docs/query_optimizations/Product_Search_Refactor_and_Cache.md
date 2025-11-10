# Product Search Refactor

### Refactor: Implement cached product spec view for optimized search and datatable performance

- Introduced new `cached_product_spec_view` table with full schema designed to mirror the output of the original UNION query
- Built `build_cached_product_spec_view[]` function to populate the cache with full unfiltered dataset
- Ensured proper column alignment and order to support existing DataTable consumption without breaking frontend behavior
- Added searchable-only fields []`searchable_colors`, `searchable_uses`, `searchable_firecodes`, `searchable_content_front`] for efficient fulltext matching while preserving display structure
- Applied FULLTEXT indexes on appropriate fields for fast BOOLEAN MODE search
- Updated `get_products_spec_view` to query the cache table instead of generating live UNION queries
  - Added GROUP BY `product_id, product_type` to prevent duplicate results
  - Flattened aliased where clauses for compatibility with denormalized cached structure
- Reverted `search_by_name[$q, $filters]` to previous version to avoid breaking dependent functionality

### Performance and compatibility validated. 

#### This sets the foundation for scalable search and filter operations across large product datasets.

## How to implement


### 1. Make sure table is created with the following schema:

```sql
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


2) Push the new Controller/Product.php, model/Product_model.php, and the new table to the server.

3) set up cron job to run the refresh_spec_cache function every 20 minutes.
    ```
    # Silent mode
    */20 * * * * curl -s "https://opms.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN" > /dev/null 2>&1
    ```
    ```
    # Log mode
     */20 * * * * curl -s "https://opms.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN" >> /var/log/spec_cache_refresh.log 2>&1
    ```

   Test the cron job by running the following command:
      ```
      curl -s "https://opms.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN" > /dev/null 2>&1
      ```

    // Cache build function for cron job
    ```
      https://opms.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN
      https://opms-dev.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN
      https://opms-qa.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN
      https://localhost:8445.opuzen-service.com/product/refresh_spec_cache?key=T8h9ve2QzLm3WxP0rBdN
    ```

# Vendor Abbreviation Search Fix

## Issue Summary
The search functionality in the product specifications view was not returning results when searching for vendor abbreviations (e.g., "DLTX"). Despite the records existing in the database, searches for vendor abbreviations returned empty results.

## Investigation Process

1. **Confirmed the existence of records** - Direct database queries confirmed that records with vendor abbreviation "DLTX" existed in the `cached_product_spec_view` table.

2. **Examined the search functionality** - The search functionality was using a combination of FULLTEXT search and LIKE clauses, but was not properly handling vendor abbreviation searches.

3. **Tested direct queries** - Direct SQL queries against the database confirmed that the data was accessible and could be retrieved with a simple LIKE query.

## Solution Implemented

We implemented a special case handler in the controller for vendor abbreviation searches. Since vendor abbreviations are typically 4 characters or less, we added logic to detect these short search terms and use a direct query approach.

### Code Changes

#### 1. Modified the Product Controller

In `application/controllers/Product.php`, we added special handling for short search terms:

```php
public function get_products()
{
    $list = array();
    $list['arr'] = array();
    $search = $this->input->post('search');
    
    if (strlen($search['value']) > 0) {
        // Check if it might be a vendor abbreviation search (4 characters or less)
        $search_term = $search['value'];
        if (strlen($search_term) <= 4) {
            // Try a direct query for vendor abbreviation
            $sql = "SELECT * FROM cached_product_spec_view WHERE vendors_abrev LIKE '%" . $this->db->escape_str($search_term) . "%'";
            $query = $this->db->query($sql);
            
            if ($query->num_rows() > 0) {
                // Found vendor abbreviation matches, use them
                $list['arr'] = $query->result_array();
                $list['recordsFiltered'] = $query->num_rows();
                $list['recordsTotal'] = $this->db->count_all('cached_product_spec_view');
                $list['query'] = $sql;
                
                // Return the results directly
                echo json_encode($this->return_datatables_data($list['arr'], $list));
                return;
            }
        }
        
        // If we get here, either it's not a vendor abbreviation search or no matches were found
        // Use the regular search method
        $this->model->searchText = $search['value'];
        $list = $this->model->get_products_spec_view($this->data['is_showroom']);
    }

    echo json_encode($this->return_datatables_data($list['arr'], $list));
}
```

#### 2. Updated the Product Model

In `application/models/Product_model.php`, we improved the search query to include both `vendors_abrev` and `searchable_vendors_abrev`:

```php
public function get_products_spec_view()
{
    // Manual refresh of the cache for development purposes
    // Manual refresh of the cache for development purposes
    //$this->build_cached_product_spec_view(); // optional: rebuild only if needed

    $this->set_datatables_variables();
    $search = $this->searchText;
    
    $mappedClause = $this->flatten_cached_spec_where_clause(
        $this->remap_where_clause_aliases($this->whereClause)
    );

    $sql = "SELECT * FROM cached_product_spec_view";

    $whereParts = [];

    if (!empty($search)) {
        $search = $this->db->escape_str($search);
        
        // Use LIKE for vendor abbreviation to ensure it works
        $whereParts[] = "(
            vendors_abrev LIKE '%" . $search . "%'
            OR searchable_vendors_abrev LIKE '%" . $search . "%'
            OR product_name LIKE '%" . $search . "%'
            OR vendor_product_name LIKE '%" . $search . "%'
        )";
    }

    if (!empty($mappedClause)) {
        $whereParts[] = $mappedClause;
    }

    if (!empty($whereParts)) {
        $sql .= " WHERE " . implode(" AND ", $whereParts);
    }

    // ✅ De-duplicate results based on primary identifiers
    $sql .= " GROUP BY product_id, product_type";
    $sql .= " ORDER BY product_name ASC";

    return $this->apply_datatables_processing($sql);
}
```

#### 3. Ensured Proper Column Mapping

In the `flatten_cached_spec_where_clause` method, we confirmed that vendor abbreviation is properly mapped:

```php
private function flatten_cached_spec_where_clause($clause)
{
    $replacements = [
        'P.name' => 'product_name',
        'P.dig_product_name' => 'product_name', // dig names were merged into product_name
        'PV.vendor_product_name' => 'vendor_product_name',
        'V.abrev' => 'vendors_abrev', // Add mapping for vendor abbreviation
        'C.name' => 'searchable_colors',
        'U.name' => 'searchable_uses',
        'FT.name' => 'searchable_firecodes',
        'PC.name' => 'searchable_content_front',
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $clause);
}
```

## Results

The fix successfully enables searching for vendor abbreviations like "DLTX" in the product specifications view. The search now returns all products associated with the specified vendor abbreviation.

## Technical Details

1. **Direct Query Approach** - For short search terms (≤4 characters), we use a direct SQL query with a LIKE clause on the `vendors_abrev` column.

2. **Fallback Mechanism** - If no matches are found with the direct query, or if the search term is longer than 4 characters, we fall back to the regular search method.

3. **Performance Considerations** - The direct query approach is efficient for vendor abbreviation searches and avoids the complexity of the full search mechanism for these specific cases.

## Future Improvements

While the current solution addresses the immediate issue, future improvements could include:

1. **Refining the FULLTEXT search** - Investigate why the FULLTEXT search was not properly handling vendor abbreviation searches and improve it.

2. **Adding vendor abbreviation filters** - Consider adding explicit vendor filters to the UI to make searching by vendor more intuitive.

3. **Optimizing the cached view** - Review the structure and indexes of the `cached_product_spec_view` table to ensure optimal performance for all search types. 
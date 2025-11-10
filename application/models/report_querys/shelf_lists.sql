/*
SELECT VI.product_name, VI.code, VI.color, VI.yardsInStock, VI.yardsAvailable 
FROM opuzen_prod_master_app.V_ITEM_FULL VI
WHERE VI.shelfs LIKE '%F%'
*/

SELECT  DISTINCT
        V.name as 'Vendor',
        I.product_type as 'Type',
        I.product_name as 'Product',
        I.code as 'Item #',
        I.color as 'Color',
        I.stock_status as 'Stock Status',
        I.status as 'Status',
        GROUP_CONCAT(SH.name) as 'Shelfs',
        SI.visible as 'Web Visible',
        PS.yardsInStock as 'Yards in Stock'
FROM opuzen_prod_master_app.V_ITEM I
LEFT JOIN opuzen_prod_master_app.Q_LIST_ITEMS LI ON I.item_id = LI.item_id
LEFT JOIN opuzen_prod_master_app.Q_LIST_SHOWROOMS LS ON LI.list_id = LS.list_id
JOIN opuzen_prod_master_app.T_PRODUCT_VENDOR PV ON I.product_id = PV.product_id
JOIN opuzen_prod_master_app.Z_VENDOR V ON PV.vendor_id = V.id
LEFT JOIN opuzen_prod_master_app.SHOWCASE_ITEM SI ON I.item_id = SI.item_id
LEFT JOIN opuzen_prod_sales.v_products_stock PS ON I.item_id = PS.master_item_id
LEFT JOIN opuzen_prod_master_app.T_PRODUCT_SHELF PSH ON binary I.product_id = binary PSH.product_id AND binary I.product_type = binary PSH.product_type
LEFT JOIN opuzen_prod_master_app.P_SHELF SH ON PSH.shelf_id = SH.id

WHERE
-- Item not deleted
I.archived = 'N'

-- Website Visible
-- AND SI.visible = 'Y'

-- Item Status: dont include 1:TBD, 2:DISCO, 3:NOTRUN, 18:MSO
AND I.status_id NOT IN (1,2,3,18)

-- Filter by showroom: 24:Anthony
AND LS.showroom_id IN (24)

-- Filter by Shelf name: 6:F
-- AND PSH.shelf_id IN (6)

-- Check stock
-- AND PS.yardsInStock > 0

GROUP BY I.item_id
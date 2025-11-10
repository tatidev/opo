SELECT I.*
FROM opuzen_prod_sales.op_products P
JOIN opuzen_prod_master_app.V_ITEM I ON P.master_item_id = I.item_id
WHERE
P.idCatalogue IN (33, 35)
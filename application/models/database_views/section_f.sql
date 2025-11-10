-- Get all Outdoor Fabrics from Sales Management
SELECT VPS.name, VPS.color, VPS.catalogue, VPS.yardsInStock, VPS.yardsOnHold, VPS.yardsAvailable
FROM opuzen_prod_sales.v_products_stock VPS
WHERE 
-- Sales Catalogues that indicate Outdoors
VPS.idCatalogue IN (35, 47, 14)
AND VPS.yardsInStock > 0
-- Except the Section F items
AND VPS.master_item_id NOT IN (
SELECT I.item_id
FROM opuzen_prod_master_app.V_ITEM_FULL I
WHERE I.shelfs LIKE '%F%'
  )
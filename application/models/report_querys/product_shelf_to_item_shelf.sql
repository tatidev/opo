INSERT INTO T_ITEM_SHELF (item_id, shelf_id)
SELECT I.id, PS.shelf_id
FROM T_PRODUCT_SHELF PS
JOIN T_ITEM I ON PS.product_id = I.product_id AND PS.product_type = I.product_type
WHERE I.status_id IN (4, 5, 6, 15, 20)
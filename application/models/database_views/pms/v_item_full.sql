CREATE OR REPLACE VIEW V_ITEM_FULL AS

SELECT 
`T_PRODUCT`.`name` as `product_name`, 
`T_PRODUCT`.`id` as `product_id`, 
'R' as product_type, 
CONCAT(T_PRODUCT.width, '"') as width, 
GROUP_CONCAT( DISTINCT 'V: ', T_PRODUCT.vrepeat, ' / H: ', T_PRODUCT.hrepeat ) as repeats,
-- GROUP_CONCAT(DISTINCT REPLACE(T_PRODUCT_CONTENT_FRONT.perc, '.00', ''), '% ', P_CONTENT.name ORDER BY T_PRODUCT_CONTENT_FRONT.perc DESC SEPARATOR ', ' ) as content_front, 
`T_PRODUCT`.`outdoor`, 
`T_ITEM`.`id` as `item_id`, 
`T_ITEM`.`code`, 
`T_ITEM`.`in_ringset`, 
GROUP_CONCAT(DISTINCT P_COLOR.NAME ORDER BY T_ITEM_COLOR.n_order SEPARATOR '/') AS color, 
`P_STOCK_STATUS`.`name` as `stock_status`, 
`P_STOCK_STATUS`.`id` as `stock_status_id`, 
`P_PRODUCT_STATUS`.`name` as `status`, 
`P_PRODUCT_STATUS`.`id` as `status_id`,
GROUP_CONCAT(DISTINCT P_FIRECODE_TEST.name SEPARATOR ' / ') as firecode,
GROUP_CONCAT(DISTINCT T_PRODUCT_ABRASION.n_rubs, ' ', P_ABRASION_TEST.name SEPARATOR ' / ') as abrasion,
P_ORIGIN.name as origin,
GROUP_CONCAT(DISTINCT P_SHELF.name SEPARATOR ' / ') as shelfs
/*
        (
					SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsInStock)
					FROM opuzen_prod_sales.op_products_bolts
					JOIN opuzen_prod_sales.op_products ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
					WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsInStock, 
        (
					SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsOnHold)
					FROM opuzen_prod_sales.op_products_bolts
					JOIN opuzen_prod_sales.op_products ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
					WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsOnHold, 
        (
					SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsAvailable)
					FROM opuzen_prod_sales.op_products_bolts
					JOIN opuzen_prod_sales.op_products ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
					WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsAvailable
        /*
        (
					SELECT SUM(PO.yardsOrdered)
          FROM opuzen_prod_sales.op_purchase_order PO
					JOIN opuzen_prod_sales.op_products ON PO.idProduct = opuzen_prod_sales.op_products.id
					WHERE PO.lastStage IN (1, 2)
          AND opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsOnOrder, 
        (
					SELECT SUM(OPB.yards)
          FROM opuzen_prod_sales.op_orders_products_bolts OPB
          JOIN opuzen_prod_sales.op_orders_header OH ON OPB.idOrder = OH.id
					JOIN opuzen_prod_sales.op_products ON OPB.idProduct = opuzen_prod_sales.op_products.id
					WHERE 
          OH.stage = 'BACKORDER'
          AND opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsBackorder, 
        */
-- COALESCE(T_PRODUCT_PRICE.p_res_cut, '-') as p_res_cut, 
-- COALESCE(T_PRODUCT_PRICE.p_hosp_cut, '-') as p_hosp_cut, 
-- COALESCE(T_PRODUCT_PRICE.p_hosp_roll, '-') as p_hosp_roll, 
-- COALESCE(T_PRODUCT_PRICE.p_dig_res, '-') as p_dig_res, 
-- COALESCE(T_PRODUCT_PRICE.p_dig_hosp, '-') as p_dig_hosp
-- `T_PRODUCT_PRICE_COST`.`fob`, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_cut != '0.00' THEN GROUP_CONCAT(DISTINCT P1.name, ' ', T_PRODUCT_PRICE_COST.cost_cut) ELSE '-' END as cost_cut, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_half_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P2.name, ' ', T_PRODUCT_PRICE_COST.cost_half_roll) ELSE '-' END as cost_half_roll, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P3.name, ' ', T_PRODUCT_PRICE_COST.cost_roll) ELSE '-' END as cost_roll, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_roll_landed != '0.00' THEN GROUP_CONCAT(DISTINCT P4.name, ' ', T_PRODUCT_PRICE_COST.cost_roll_landed) ELSE '-' END as cost_roll_landed, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_roll_ex_mill != '0.00' THEN GROUP_CONCAT(DISTINCT P5.name, ' ', T_PRODUCT_PRICE_COST.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill, 
-- `T_PRODUCT_PRICE_COST`.`cost_roll_ex_mill_text`, SUBSTRING(T_PRODUCT_PRICE_COST.date, 1, 10) as cost_date
FROM `T_ITEM`
JOIN `T_PRODUCT` ON `T_ITEM`.`product_id` = `T_PRODUCT`.`id`
-- LEFT OUTER JOIN `T_PRODUCT_CONTENT_FRONT` ON `T_PRODUCT_CONTENT_FRONT`.`product_id` = `T_PRODUCT`.`id`
-- LEFT OUTER JOIN `P_CONTENT` ON `T_PRODUCT_CONTENT_FRONT`.`content_id` = `P_CONTENT`.`id`
JOIN `P_STOCK_STATUS` ON `T_ITEM`.`stock_status_id` = `P_STOCK_STATUS`.`id`
JOIN `P_PRODUCT_STATUS` ON `T_ITEM`.`status_id` = `P_PRODUCT_STATUS`.`id`
JOIN `T_ITEM_COLOR` ON `T_ITEM`.`id` = `T_ITEM_COLOR`.`item_id`
JOIN `P_COLOR` ON `T_ITEM_COLOR`.`color_id` = `P_COLOR`.`id`
-- LEFT OUTER JOIN `T_PRODUCT_PRICE` ON `T_PRODUCT`.`id` = `T_PRODUCT_PRICE`.`product_id` AND `T_PRODUCT_PRICE`.`product_type` = 'R' 
-- LEFT OUTER JOIN `T_PRODUCT_PRICE_COST` ON `T_PRODUCT`.`id` = `T_PRODUCT_PRICE_COST`.`product_id`
-- LEFT OUTER JOIN `P_PRICE_TYPE` `P1` ON `T_PRODUCT_PRICE_COST`.`cost_cut_type_id` = `P1`.`id`
LEFT OUTER JOIN T_PRODUCT_FIRECODE ON T_PRODUCT.id = T_PRODUCT_FIRECODE.product_id AND T_PRODUCT_FIRECODE.visible = 'Y'
LEFT OUTER JOIN P_FIRECODE_TEST ON T_PRODUCT_FIRECODE.firecode_test_id = P_FIRECODE_TEST.id
LEFT OUTER JOIN T_PRODUCT_ABRASION ON T_PRODUCT.id = T_PRODUCT_ABRASION.product_id AND T_PRODUCT_ABRASION.visible = 'Y'
LEFT OUTER JOIN P_ABRASION_TEST ON T_PRODUCT_ABRASION.abrasion_test_id = P_ABRASION_TEST.id
LEFT OUTER JOIN T_PRODUCT_ORIGIN ON T_PRODUCT.id = T_PRODUCT_ORIGIN.product_id
LEFT OUTER JOIN P_ORIGIN ON T_PRODUCT_ORIGIN.origin_id = P_ORIGIN.id
LEFT OUTER JOIN T_ITEM_SHELF ON T_ITEM.id = T_ITEM_SHELF.item_id
LEFT OUTER JOIN P_SHELF ON T_ITEM_SHELF.shelf_id = P_SHELF.id
WHERE `T_ITEM`.`product_type` = 'R'
GROUP BY `T_ITEM`.`id` 

UNION ALL 

SELECT 
CONCAT(U_DIGITAL_STYLE.name, ' on ', CASE WHEN T_PRODUCT_X_DIGITAL.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, COALESCE(T_PRODUCT.dig_product_name, T_PRODUCT.name), ' ', GROUP_CONCAT(DISTINCT PC.name ORDER BY PC.name SEPARATOR ' / ') ) as product_name, 
`T_PRODUCT_X_DIGITAL`.`id` as `product_id`, 
'D' as product_type, 
CONCAT( COALESCE(T_PRODUCT.dig_width, T_PRODUCT.width), '"') as width, 
GROUP_CONCAT( DISTINCT 'V: ', U_DIGITAL_STYLE.vrepeat, ' / H: ', U_DIGITAL_STYLE.hrepeat ) as repeats,
-- GROUP_CONCAT(DISTINCT REPLACE(T_PRODUCT_CONTENT_FRONT.perc, '.00', ''), '% ', P_CONTENT.name ORDER BY T_PRODUCT_CONTENT_FRONT.perc DESC SEPARATOR ', ' ) as content_front, 
`T_PRODUCT`.`outdoor`, 
`T_ITEM`.`id` as `item_id`, 
`T_ITEM`.`code`, 
`T_ITEM`.`in_ringset`, 
GROUP_CONCAT(DISTINCT P_COLOR.NAME ORDER BY T_ITEM_COLOR.n_order SEPARATOR '/') AS color, 
`P_STOCK_STATUS`.`name` as `stock_status`, 
`P_STOCK_STATUS`.`id` as `stock_status_id`, 
`P_PRODUCT_STATUS`.`name` as `status`, 
`P_PRODUCT_STATUS`.`id` as `status_id`,
GROUP_CONCAT(DISTINCT P_FIRECODE_TEST.name SEPARATOR ' / ') as firecode,
GROUP_CONCAT(DISTINCT T_PRODUCT_ABRASION.n_rubs, ' ', P_ABRASION_TEST.name SEPARATOR ' / ') as abrasion,
P_ORIGIN.name as origin,
GROUP_CONCAT(DISTINCT P_SHELF.name SEPARATOR ' / ') as shelfs
/*
0 as yardsInStock,
0 as yardsOnHold,
0 as yardsAvailable
/*
        (
					SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsInStock)
					FROM opuzen_prod_sales.op_products_bolts
					JOIN opuzen_prod_sales.op_products ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
					WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsInStock, 
        (
					SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsOnHold)
					FROM opuzen_prod_sales.op_products_bolts
					JOIN opuzen_prod_sales.op_products ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
					WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsOnHold,
        (
					SELECT SUM(opuzen_prod_sales.op_products_bolts.yardsAvailable)
					FROM opuzen_prod_sales.op_products_bolts
					JOIN opuzen_prod_sales.op_products ON opuzen_prod_sales.op_products.id = opuzen_prod_sales.op_products_bolts.idProduct
					WHERE opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsAvailable, 
        /*
        (
					SELECT SUM(PO.yardsOrdered)
          FROM opuzen_prod_sales.op_purchase_order PO
					JOIN opuzen_prod_sales.op_products ON PO.idProduct = opuzen_prod_sales.op_products.id
					WHERE PO.lastStage IN (1, 2)
          AND opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsOnOrder, 
        (
					SELECT SUM(OPB.yards)
          FROM opuzen_prod_sales.op_orders_products_bolts OPB
          JOIN opuzen_prod_sales.op_orders_header OH ON OPB.idOrder = OH.id
					JOIN opuzen_prod_sales.op_products ON OPB.idProduct = opuzen_prod_sales.op_products.id
					WHERE 
          OH.stage = 'BACKORDER'
          AND opuzen_prod_sales.op_products.master_item_id = T_ITEM.id
					GROUP BY opuzen_prod_sales.op_products.master_item_id
				) as yardsBackorder, 
        */
-- COALESCE(T_PRODUCT_PRICE.p_res_cut, '-') as p_res_cut, 
-- COALESCE(T_PRODUCT_PRICE.p_hosp_cut, '-') as p_hosp_cut, 
-- COALESCE(T_PRODUCT_PRICE.p_hosp_roll, '-') as p_hosp_roll, 
-- COALESCE(T_PRODUCT_PRICE.p_dig_res, '-') as p_dig_res, 
-- COALESCE(T_PRODUCT_PRICE.p_dig_hosp, '-') as p_dig_hosp 
-- `T_PRODUCT_PRICE_COST`.`fob`, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_cut != '0.00' THEN GROUP_CONCAT(DISTINCT P1.name, ' ', T_PRODUCT_PRICE_COST.cost_cut) ELSE '-' END as cost_cut, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_half_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P2.name, ' ', T_PRODUCT_PRICE_COST.cost_half_roll) ELSE '-' END as cost_half_roll, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_roll != '0.00' THEN GROUP_CONCAT(DISTINCT P3.name, ' ', T_PRODUCT_PRICE_COST.cost_roll) ELSE '-' END as cost_roll, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_roll_landed != '0.00' THEN GROUP_CONCAT(DISTINCT P4.name, ' ', T_PRODUCT_PRICE_COST.cost_roll_landed) ELSE '-' END as cost_roll_landed, 
-- CASE WHEN T_PRODUCT_PRICE_COST.cost_roll_ex_mill != '0.00' THEN GROUP_CONCAT(DISTINCT P5.name, ' ', T_PRODUCT_PRICE_COST.cost_roll_ex_mill) ELSE '-' END as cost_roll_ex_mill, 
-- `T_PRODUCT_PRICE_COST`.`cost_roll_ex_mill_text`, SUBSTRING(T_PRODUCT_PRICE_COST.date, 1, 10) as cost_date
FROM `T_ITEM`
JOIN `T_PRODUCT_X_DIGITAL` ON `T_ITEM`.`product_id` = `T_PRODUCT_X_DIGITAL`.`id`
JOIN `T_ITEM` `TT` ON `T_PRODUCT_X_DIGITAL`.`item_id` = `TT`.`id`
LEFT OUTER JOIN `T_ITEM_COLOR` `TC` ON `TT`.`id` = `TC`.`item_id`
LEFT OUTER JOIN `P_COLOR` `PC` ON `TC`.`color_id` = `PC`.`id`
JOIN `T_PRODUCT` ON `TT`.`product_id` = `T_PRODUCT`.`id`
JOIN `U_DIGITAL_STYLE` ON `T_PRODUCT_X_DIGITAL`.`style_id` = `U_DIGITAL_STYLE`.`id`
-- LEFT OUTER JOIN `T_PRODUCT_CONTENT_FRONT` ON `T_PRODUCT_CONTENT_FRONT`.`product_id` = `T_PRODUCT`.`id`
-- LEFT OUTER JOIN `P_CONTENT` ON `T_PRODUCT_CONTENT_FRONT`.`content_id` = `P_CONTENT`.`id`
JOIN `P_STOCK_STATUS` ON `T_ITEM`.`stock_status_id` = `P_STOCK_STATUS`.`id`
JOIN `P_PRODUCT_STATUS` ON `T_ITEM`.`status_id` = `P_PRODUCT_STATUS`.`id`
JOIN `T_ITEM_COLOR` ON `T_ITEM`.`id` = `T_ITEM_COLOR`.`item_id`
JOIN `P_COLOR` ON `T_ITEM_COLOR`.`color_id` = `P_COLOR`.`id`
-- LEFT OUTER JOIN `T_PRODUCT_PRICE` ON `T_PRODUCT_X_DIGITAL`.`id` = `T_PRODUCT_PRICE`.`product_id` AND `T_PRODUCT_PRICE`.`product_type` = 'D' 
-- LEFT OUTER JOIN `T_PRODUCT_PRICE_COST` ON `T_PRODUCT`.`id` = `T_PRODUCT_PRICE_COST`.`product_id`
-- LEFT OUTER JOIN `P_PRICE_TYPE` `P1` ON `T_PRODUCT_PRICE_COST`.`cost_cut_type_id` = `P1`.`id`
LEFT OUTER JOIN T_PRODUCT_FIRECODE ON T_PRODUCT.id = T_PRODUCT_FIRECODE.product_id AND T_PRODUCT_FIRECODE.visible = 'Y'
LEFT OUTER JOIN P_FIRECODE_TEST ON T_PRODUCT_FIRECODE.firecode_test_id = P_FIRECODE_TEST.id
LEFT OUTER JOIN T_PRODUCT_ABRASION ON T_PRODUCT.id = T_PRODUCT_ABRASION.product_id AND T_PRODUCT_ABRASION.visible = 'Y'
LEFT OUTER JOIN P_ABRASION_TEST ON T_PRODUCT_ABRASION.abrasion_test_id = P_ABRASION_TEST.id
LEFT OUTER JOIN T_PRODUCT_ORIGIN ON T_PRODUCT.id = T_PRODUCT_ORIGIN.product_id
LEFT OUTER JOIN P_ORIGIN ON T_PRODUCT_ORIGIN.origin_id = P_ORIGIN.id
LEFT OUTER JOIN T_ITEM_SHELF ON T_ITEM.id = T_ITEM_SHELF.item_id
LEFT OUTER JOIN P_SHELF ON T_ITEM_SHELF.shelf_id = P_SHELF.id
WHERE `T_ITEM`.`product_type` = 'D'
GROUP BY `T_ITEM`.`id`
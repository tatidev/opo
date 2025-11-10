
DELIMITER $$
  DROP PROCEDURE IF EXISTS `proc_update_products_stock` $$
  CREATE PROCEDURE `proc_update_products_stock`()
  BEGIN

    TRUNCATE TABLE opuzen_prod_sales.op_products_stock;
    INSERT INTO opuzen_prod_sales.op_products_stock
    SELECT  P.id, 
            P.master_item_id, 
            P.name, 
            P.color, 
            C.name as catalogue,
            SUM(PB.yardsInStock) as yardsInStock, 
            SUM(PB.yardsOnHold) as yardsOnHold, 
            SUM(PB.yardsAvailable) as yardsAvailable,
            (
              SELECT SUM(PO.yardsOrdered)
              FROM opuzen_prod_sales.op_purchase_order PO
              WHERE PO.lastStage IN (1, 2)
              AND PO.idProduct = P.id
              GROUP BY P.master_item_id
            ) as yardsOnOrder,
            (
              SELECT SUM(OPB.yardsTotal)
              FROM opuzen_prod_sales.op_orders_products OPB
              JOIN opuzen_prod_sales.op_orders_header OH ON OPB.idOrder = OH.id
              WHERE 
              OH.stage = 'BACKORDER'
              AND OPB.idProduct = P.id
              GROUP BY P.master_item_id
            ) as yardsBackorder
        
    FROM opuzen_prod_sales.op_products P
    LEFT OUTER JOIN opuzen_prod_sales.op_products_bolts PB ON P.id = PB.idProduct
    JOIN opuzen_prod_sales.op_catalogue C ON P.idCatalogue = C.id
    GROUP BY P.id;
  END $$
DELIMITER ;

CREATE EVENT IF NOT EXISTS event_update_products_stock
ON SCHEDULE
  EVERY 1 DAY
  STARTS '2018-11-15 00:00:00' ON COMPLETION PRESERVE ENABLE
DO
  CALL proc_update_products_stock;

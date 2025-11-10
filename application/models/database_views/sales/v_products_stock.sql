CREATE OR REPLACE VIEW v_products_stock AS

SELECT P.id, 
  P.master_item_id, 
  P.name, 
  P.color, 
  C.name as catalogue, 
  SUM(PB.yardsInStock) as yardsInStock, 
  SUM(PB.yardsOnHold) as yardsOnHold, 
  SUM(PB.yardsAvailable) as yardsAvailable,
  

FROM opuzen_prod_sales.op_products P
LEFT OUTER JOIN opuzen_prod_sales.op_products_bolts PB ON P.id = PB.idProduct
JOIN opuzen_prod_sales.op_catalogue C ON P.idCatalogue = C.id
GROUP BY P.id 

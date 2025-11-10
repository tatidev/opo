CREATE OR REPLACE VIEW V_MASTER_IDS_TO_UPDATE_DUE_TO_DELETION AS

SELECT SP.*
FROM op_products SP
JOIN opuzen_prod_master_app.T_ITEM I ON SP.master_item_id = I.id
JOIN opuzen_prod_master_app.T_PRODUCT P ON I.product_id = P.id
WHERE I.archived = 'Y' OR P.archived = 'Y'

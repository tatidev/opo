CREATE ALGORITHM=UNDEFINED 
DEFINER=`pklopuzen`@`%` 
SQL SECURITY DEFINER 
VIEW `V_ITEM` AS

-- REGULAR PRODUCTS
SELECT
    IF(
        `T_ITEM`.`code` IS NULL,
        CONCAT_WS(' ', `Z_VENDOR`.`abrev`, `T_PRODUCT`.`name`),
        `T_PRODUCT`.`name`
    ) AS `product_name`,
    `T_PRODUCT`.`id` AS `product_id`,
    'R' AS `product_type`,
    `T_PRODUCT`.`outdoor` AS `outdoor`,
    `T_ITEM`.`id` AS `item_id`,
    `T_ITEM`.`code` AS `code`,
    `T_ITEM`.`in_ringset` AS `in_ringset`,
    GROUP_CONCAT(DISTINCT `P_COLOR`.`name` ORDER BY `T_ITEM_COLOR`.`n_order` ASC SEPARATOR '/') AS `color`,
    `P_STOCK_STATUS`.`name` AS `stock_status`,
    `P_STOCK_STATUS`.`id` AS `stock_status_id`,
    `P_PRODUCT_STATUS`.`name` AS `status`,
    `P_PRODUCT_STATUS`.`id` AS `status_id`,
    `T_ITEM`.`archived` AS `archived`,
    `T_PRODUCT`.`archived` AS `archived_product`,
    `T_ITEM`.`date_add` AS `date_add`,
    `T_ITEM`.`date_modif` AS `date_modif`
FROM
    `T_ITEM`
    JOIN `T_PRODUCT` ON `T_ITEM`.`product_id` = `T_PRODUCT`.`id`
    JOIN `P_STOCK_STATUS` ON `T_ITEM`.`stock_status_id` = `P_STOCK_STATUS`.`id`
    JOIN `P_PRODUCT_STATUS` ON `T_ITEM`.`status_id` = `P_PRODUCT_STATUS`.`id`
    JOIN `T_ITEM_COLOR` ON `T_ITEM`.`id` = `T_ITEM_COLOR`.`item_id`
    JOIN `P_COLOR` ON `T_ITEM_COLOR`.`color_id` = `P_COLOR`.`id`
    JOIN `T_PRODUCT_VENDOR` ON `T_ITEM`.`product_id` = `T_PRODUCT_VENDOR`.`product_id`
    JOIN `Z_VENDOR` ON `T_PRODUCT_VENDOR`.`vendor_id` = `Z_VENDOR`.`id`
    LEFT JOIN `T_PRODUCT_PRICE` ON (
        `T_PRODUCT`.`id` = `T_PRODUCT_PRICE`.`product_id`
        AND `T_PRODUCT_PRICE`.`product_type` = 'R'
    )
WHERE
    `T_ITEM`.`product_type` = 'R'
GROUP BY
    `T_ITEM`.`id`

UNION ALL

-- DIGITAL PRODUCTS
SELECT
    CONCAT(
        `U_DIGITAL_STYLE`.`name`,
        ' on ',
        CONVERT(
            CASE
                WHEN `T_PRODUCT_X_DIGITAL`.`reverse_ground` = 'Y'
                THEN 'Reverse '
                ELSE ''
            END USING latin1
        ),
        COALESCE(`T_PRODUCT`.`dig_product_name`, `T_PRODUCT`.`name`),
        ' ',
        GROUP_CONCAT(DISTINCT `PC`.`name` ORDER BY `PC`.`name` ASC SEPARATOR ' / ')
    ) AS `product_name`,
    `T_PRODUCT_X_DIGITAL`.`id` AS `product_id`,
    'D' AS `product_type`,
    `T_PRODUCT`.`outdoor` AS `outdoor`,
    `T_ITEM`.`id` AS `item_id`,
    `T_ITEM`.`code` AS `code`,
    `T_ITEM`.`in_ringset` AS `in_ringset`,
    GROUP_CONCAT(DISTINCT `P_COLOR`.`name` ORDER BY `T_ITEM_COLOR`.`n_order` ASC SEPARATOR '/') AS `color`,
    `P_STOCK_STATUS`.`name` AS `stock_status`,
    `P_STOCK_STATUS`.`id` AS `stock_status_id`,
    `P_PRODUCT_STATUS`.`name` AS `status`,
    `P_PRODUCT_STATUS`.`id` AS `status_id`,
    `T_ITEM`.`archived` AS `archived`,
    `T_PRODUCT_X_DIGITAL`.`archived` AS `archived_product`,
    `T_ITEM`.`date_add` AS `date_add`,
    `T_ITEM`.`date_modif` AS `date_modif`
FROM
    `T_ITEM`
    JOIN `T_PRODUCT_X_DIGITAL` ON `T_ITEM`.`product_id` = `T_PRODUCT_X_DIGITAL`.`id`
    JOIN `T_ITEM` AS `TT` ON `T_PRODUCT_X_DIGITAL`.`item_id` = `TT`.`id`
    LEFT JOIN `T_ITEM_COLOR` AS `TC` ON `TT`.`id` = `TC`.`item_id`
    LEFT JOIN `P_COLOR` AS `PC` ON `TC`.`color_id` = `PC`.`id`
    JOIN `T_PRODUCT` ON `TT`.`product_id` = `T_PRODUCT`.`id`
    JOIN `U_DIGITAL_STYLE` ON `T_PRODUCT_X_DIGITAL`.`style_id` = `U_DIGITAL_STYLE`.`id`
    JOIN `P_STOCK_STATUS` ON `T_ITEM`.`stock_status_id` = `P_STOCK_STATUS`.`id`
    JOIN `P_PRODUCT_STATUS` ON `T_ITEM`.`status_id` = `P_PRODUCT_STATUS`.`id`
    JOIN `T_ITEM_COLOR` ON `T_ITEM`.`id` = `T_ITEM_COLOR`.`item_id`
    JOIN `P_COLOR` ON `T_ITEM_COLOR`.`color_id` = `P_COLOR`.`id`
    LEFT JOIN `T_PRODUCT_PRICE` ON (
        `T_PRODUCT_X_DIGITAL`.`id` = `T_PRODUCT_PRICE`.`product_id`
        AND `T_PRODUCT_PRICE`.`product_type` = 'D'
    )
WHERE
    `T_ITEM`.`product_type` = 'D'
GROUP BY
    `T_ITEM`.`id`;
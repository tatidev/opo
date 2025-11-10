SELECT
    V.name as vendor,
    A.shelf_id,
    I.status,
    I.stock_status,
    I.product_name,
    I.code,
    I.color,
    A.add_date as change_date,
    U.username as change_by,
    A.change_type

FROM (

    SELECT
           A.item_id,
           GROUP_CONCAT(A.shelf_id) as shelf_id,
           A.date_add as add_date,
           A.user_id as add_by,
           B.last_shelf_ids,
           IF(B.removal_date IS NULL, "add", "moved") as change_type

    FROM T_ITEM_SHELF A

    LEFT OUTER JOIN (

        SELECT A.item_id,
               GROUP_CONCAT(DISTINCT A.shelf_id) as last_shelf_ids,
               A.ts as removal_date
        FROM S_HISTORY_ITEM_SHELF A
         JOIN (

            SELECT item_id, max(ts) as ts
            FROM S_HISTORY_ITEM_SHELF
            GROUP BY item_id

        ) B ON A.item_id = B.item_id AND A.ts = B.ts

    --     WHERE A.item_id = 18351

        GROUP BY A.item_id

    ) B ON A.item_id = B.item_id

    WHERE A.date_add BETWEEN '{date_from} 00:00:00' AND '{date_to} 23:59:59'
        {filter_shelf_id}
--         WHERE A.date_add BETWEEN '2022-06-01 00:00:00' AND '2022-08-01 23:59:59'

    -- WHERE A.item_id = 18351

    GROUP BY A.item_id

) A

JOIN V_ITEM I ON A.item_id = I.item_id
JOIN V_PRODUCT_VENDOR V ON I.product_id = V.product_id
JOIN V_USER U ON A.add_by = U.id

{optional_where}
{optional_group_by}

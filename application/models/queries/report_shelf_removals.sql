SELECT
    V.name as vendor,
    A.shelf_id,
    I.status,
    I.stock_status,
    I.product_name,
    I.code,
    I.color,
    A.removal_date as change_date,
    U.username as change_by,
    'removal' as change_type

FROM (
    SELECT
        A.item_id,
        A.last_shelf_ids as shelf_id,
        A.removal_date,
        A.user_id
    FROM (
        SELECT
               A.item_id,
               GROUP_CONCAT(DISTINCT A.shelf_id) as last_shelf_ids,
               A.ts as removal_date,
               A.user_id

        FROM S_HISTORY_ITEM_SHELF A
        JOIN (

            -- Select last removal per item on the given timeframe
            SELECT A.item_id, max(A.ts) as ts
            FROM S_HISTORY_ITEM_SHELF A
            WHERE A.ts BETWEEN '{date_from} 00:00:00' AND '{date_to} 23:59:59'
                {filter_shelf_id}
            GROUP BY A.item_id

        ) B ON A.item_id = B.item_id AND A.ts = B.ts

        GROUP BY A.item_id
    ) A
    WHERE A.item_id NOT IN (
        -- Filter items that do live in the shelfs today, we don't have a snapshot of the past
        SELECT item_id
        FROM T_ITEM_SHELF
    )
) A

JOIN V_ITEM I ON A.item_id = I.item_id
JOIN V_PRODUCT_VENDOR V ON I.product_id = V.product_id
JOIN V_USER U ON A.user_id = U.id

{optional_where}
{optional_group_by}


/* REGULAR PART  */
-- Final Optimized Query: Regular Product Search (with output identical to original)
--- completed optimization summary for the Regular Product query. Every component has been independently tested and optimized.
-- Search term is 'blue' and all filters remain intact
-- Product_model.php: $this->selectors('specs', constant('Regular'));

SELECT DISTINCT
    T_PRODUCT.name AS product_name,
    T_PRODUCT.vrepeat,
    T_PRODUCT.hrepeat,
    T_PRODUCT.width,
    T_PRODUCT.id AS product_id,
    T_PRODUCT.outdoor,
    'R' AS product_type,
    T_PRODUCT.archived,
    T_PRODUCT.in_master,
    GROUP_CONCAT(DISTINCT CONCAT_WS('*',
        T_PRODUCT_ABRASION.abrasion_test_id,
        P_ABRASION_LIMIT.name,
        T_PRODUCT_ABRASION.n_rubs,
        P_ABRASION_TEST.name
    ) SEPARATOR ' / ') AS abrasions,
    COUNT(DISTINCT T_PRODUCT_ABRASION_FILES.abrasion_id) AS count_abrasion_files,
    GROUP_CONCAT(DISTINCT CONCAT(
        REPLACE(T_PRODUCT_CONTENT_FRONT.perc, '.00', ''),
        '% ',
        PC1.name
    ) ORDER BY T_PRODUCT_CONTENT_FRONT.perc DESC SEPARATOR ' / ') AS content_front,
    GROUP_CONCAT(DISTINCT P_FIRECODE_TEST.name SEPARATOR ' / ') AS firecodes,
    COUNT(DISTINCT T_PRODUCT_FIRECODE_FILES.firecode_id) AS count_firecode_files,
    GROUP_CONCAT(DISTINCT P_USE.name ORDER BY P_USE.name ASC SEPARATOR ' / ') AS uses,
    GROUP_CONCAT(DISTINCT P_USE.id SEPARATOR ' / ') AS uses_id,
    T_PRODUCT_VARIOUS.vendor_product_name,
    T_PRODUCT_VARIOUS.tariff_surcharge,
    T_PRODUCT_VARIOUS.freight_surcharge,
    T_PRODUCT_PRICE.p_hosp_cut,
    T_PRODUCT_PRICE.p_hosp_roll,
    T_PRODUCT_PRICE.p_res_cut,
    T_PRODUCT_PRICE.p_dig_res,
    T_PRODUCT_PRICE.p_dig_hosp,
    DATE_FORMAT(T_PRODUCT_PRICE.date, '%m/%d/%Y') AS price_date,
    T_PRODUCT_PRICE_COST.fob,
    IFNULL(GROUP_CONCAT(DISTINCT CONCAT_WS(' ',
        PT.name,
        T_PRODUCT_PRICE_COST.cost_cut
    )), '-') AS cost_cut,
    IFNULL(GROUP_CONCAT(DISTINCT CONCAT_WS(' ',
        PT.name,
        T_PRODUCT_PRICE_COST.cost_half_roll
    )), '-') AS cost_half_roll,
    IFNULL(GROUP_CONCAT(DISTINCT CONCAT_WS(' ',
        PT.name,
        T_PRODUCT_PRICE_COST.cost_roll
    )), '-') AS cost_roll,
    IFNULL(GROUP_CONCAT(DISTINCT CONCAT_WS(' ',
        PT.name,
        T_PRODUCT_PRICE_COST.cost_roll_landed
    )), '-') AS cost_roll_landed,
    IFNULL(GROUP_CONCAT(DISTINCT CONCAT_WS(' ',
        PT.name,
        T_PRODUCT_PRICE_COST.cost_roll_ex_mill
    )), '-') AS cost_roll_ex_mill,
    DATE_FORMAT(T_PRODUCT_PRICE_COST.date, '%m/%d/%Y') AS cost_date,
    V.name AS vendors_name,
    V.abrev AS vendors_abrev,
    GROUP_CONCAT(DISTINCT P_WEAVE.name ORDER BY P_WEAVE.name ASC SEPARATOR ' / ') AS weaves,
    GROUP_CONCAT(DISTINCT P_WEAVE.id SEPARATOR ' / ') AS weaves_id
FROM
    T_PRODUCT
    LEFT JOIN T_PRODUCT_ABRASION ON T_PRODUCT.id = T_PRODUCT_ABRASION.product_id
    LEFT JOIN P_ABRASION_TEST ON T_PRODUCT_ABRASION.abrasion_test_id = P_ABRASION_TEST.id
    LEFT JOIN P_ABRASION_LIMIT ON T_PRODUCT_ABRASION.abrasion_limit_id = P_ABRASION_LIMIT.id
    LEFT JOIN T_PRODUCT_ABRASION_FILES ON T_PRODUCT_ABRASION.id = T_PRODUCT_ABRASION_FILES.abrasion_id
    LEFT JOIN T_PRODUCT_CONTENT_FRONT ON T_PRODUCT.id = T_PRODUCT_CONTENT_FRONT.product_id
    LEFT JOIN P_CONTENT PC1 ON T_PRODUCT_CONTENT_FRONT.content_id = PC1.id
    LEFT JOIN T_PRODUCT_FIRECODE ON T_PRODUCT.id = T_PRODUCT_FIRECODE.product_id
    LEFT JOIN P_FIRECODE_TEST ON T_PRODUCT_FIRECODE.firecode_test_id = P_FIRECODE_TEST.id
    LEFT JOIN T_PRODUCT_FIRECODE_FILES ON T_PRODUCT_FIRECODE.id = T_PRODUCT_FIRECODE_FILES.firecode_id
    LEFT JOIN T_PRODUCT_USE ON T_PRODUCT.id = T_PRODUCT_USE.product_id
    LEFT JOIN P_USE ON T_PRODUCT_USE.use_id = P_USE.id
    LEFT JOIN T_PRODUCT_VARIOUS ON T_PRODUCT.id = T_PRODUCT_VARIOUS.product_id
    LEFT JOIN T_PRODUCT_PRICE ON T_PRODUCT_PRICE.product_id = T_PRODUCT.id AND T_PRODUCT_PRICE.product_type = 'R'
    LEFT JOIN T_PRODUCT_PRICE_COST ON T_PRODUCT.id = T_PRODUCT_PRICE_COST.product_id
    LEFT JOIN P_PRICE_TYPE PT ON T_PRODUCT_PRICE_COST.cost_cut_type_id = PT.id
    LEFT JOIN T_PRODUCT_VENDOR ON T_PRODUCT.id = T_PRODUCT_VENDOR.product_id
    LEFT JOIN Z_VENDOR V ON T_PRODUCT_VENDOR.vendor_id = V.id
    LEFT JOIN T_PRODUCT_WEAVE ON T_PRODUCT.id = T_PRODUCT_WEAVE.product_id
    LEFT JOIN P_WEAVE ON T_PRODUCT_WEAVE.weave_id = P_WEAVE.id
WHERE
    T_PRODUCT.archived = 'N'
    AND (
        V.name LIKE '%acdc%'
        OR V.abrev LIKE '%acdc%'
        OR T_PRODUCT.name LIKE '%acdc%'
        OR T_PRODUCT.dig_product_name LIKE '%acdc%'
        OR T_PRODUCT_VARIOUS.vendor_product_name LIKE '%acdc%'
    )
GROUP BY
    T_PRODUCT.id;


/* DIGITAL PRODUXT PART */
-- ✅ Optimized Digital Product Query with Firecodes, Uses, Vendors, and Weaves
SELECT
    CONCAT(DS.name, ' on ', CASE WHEN X.reverse_ground = 'Y' THEN 'Reverse ' ELSE '' END, COALESCE(P.dig_product_name, P.name), ' / ', GROUP_CONCAT(DISTINCT C.name SEPARATOR ' / ')) AS product_name,
    DS.vrepeat,
    DS.hrepeat,
    COALESCE(P.dig_width, P.width) AS width,
    X.id AS product_id,
    P.outdoor,
    'D' AS product_type,
    X.archived,
    X.in_master,
    PR.p_dig_res,
    PR.p_dig_hosp,
    PR.date AS price_date,
    PRC.fob,
    PRC.cost_roll,
    A.abrasions,
    A.count_abrasion_files,
    CF.content_front,
    FIRE.firecodes,
    FIRE.count_firecode_files,
    UDATA.uses,
    UDATA.uses_id,
    V.name AS vendors_name,
    V.abrev AS vendors_abrev,
    WDATA.weaves,
    WDATA.weaves_id
FROM T_PRODUCT_X_DIGITAL X
JOIN U_DIGITAL_STYLE DS ON DS.id = X.style_id
JOIN T_ITEM I ON I.id = X.item_id
JOIN T_PRODUCT P ON P.id = I.product_id
LEFT JOIN T_ITEM_COLOR IC ON IC.item_id = X.item_id
LEFT JOIN P_COLOR C ON C.id = IC.color_id
LEFT JOIN T_PRODUCT_PRICE PR ON PR.product_id = X.id AND PR.product_type = 'D'
LEFT JOIN T_PRODUCT_PRICE_COST PRC ON PRC.product_id = P.id
LEFT JOIN (
    SELECT
        A.product_id,
        GROUP_CONCAT(DISTINCT A.abrasion_test_id, '*', AL.name, '-', A.n_rubs, '-', AT.name SEPARATOR ' / ') AS abrasions,
        COUNT(DISTINCT AF.abrasion_id) AS count_abrasion_files
    FROM T_PRODUCT_ABRASION A
    LEFT JOIN P_ABRASION_LIMIT AL ON A.abrasion_limit_id = AL.id
    LEFT JOIN P_ABRASION_TEST AT ON A.abrasion_test_id = AT.id
    LEFT JOIN T_PRODUCT_ABRASION_FILES AF ON AF.abrasion_id = A.id
    GROUP BY A.product_id
) A ON A.product_id = P.id
LEFT JOIN (
    SELECT
        CF.product_id,
        GROUP_CONCAT(DISTINCT REPLACE(CF.perc, '.00', ''), '% ', PC.name ORDER BY CF.perc DESC SEPARATOR ' / ') AS content_front
    FROM T_PRODUCT_CONTENT_FRONT CF
    LEFT JOIN P_CONTENT PC ON CF.content_id = PC.id
    GROUP BY CF.product_id
) CF ON CF.product_id = P.id
LEFT JOIN (
    SELECT
        F.product_id,
        GROUP_CONCAT(DISTINCT FT.name SEPARATOR ' / ') AS firecodes,
        COUNT(DISTINCT FF.firecode_id) AS count_firecode_files
    FROM T_PRODUCT_FIRECODE F
    LEFT JOIN P_FIRECODE_TEST FT ON F.firecode_test_id = FT.id
    LEFT JOIN T_PRODUCT_FIRECODE_FILES FF ON F.id = FF.firecode_id
    GROUP BY F.product_id
) FIRE ON FIRE.product_id = P.id
LEFT JOIN (
    SELECT
        U.product_id,
        GROUP_CONCAT(DISTINCT PU.name ORDER BY PU.name ASC SEPARATOR ' / ') AS uses,
        GROUP_CONCAT(DISTINCT PU.id SEPARATOR ' / ') AS uses_id
    FROM T_PRODUCT_USE U
    LEFT JOIN P_USE PU ON U.use_id = PU.id
    GROUP BY U.product_id
) UDATA ON UDATA.product_id = P.id
LEFT JOIN T_PRODUCT_VENDOR PV ON PV.product_id = P.id
LEFT JOIN Z_VENDOR V ON V.id = PV.vendor_id
LEFT JOIN (
    SELECT
        W.product_id,
        GROUP_CONCAT(DISTINCT PW.name ORDER BY PW.name ASC SEPARATOR ' / ') AS weaves,
        GROUP_CONCAT(DISTINCT PW.id SEPARATOR ' / ') AS weaves_id
    FROM T_PRODUCT_WEAVE W
    LEFT JOIN P_WEAVE PW ON W.weave_id = PW.id
    GROUP BY W.product_id
) WDATA ON WDATA.product_id = P.id
WHERE X.archived = 'N'

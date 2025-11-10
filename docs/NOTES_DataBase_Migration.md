
# Procedure for Database Migration

## In each database:

 - opuzen_[env]_master_app   
 - opuzen_[env]_roadkit      
 - opuzen_[env]_roadkit_int  
 - opuzen_[env]_sales        
 - opuzen_[env]_showcase     
 - opuzen_[env]_user


## IMPORTING DB:
    
1. ### Add a `USE` statement at the beging of dump file (.sql)
        
   ```
       USE opuzen_dev_master_app;
    /* USE opuzen_qa_master_app; */
    /* USE opuzen_prod_master_app; */
   ```

1. ### User conversion

   __To prevent the _ERROR_:__  "_Access denied for user 'pklopuzen'@'%' (using password: YES)_"
      
    ```
     SEARCH:  `root`@                     AND  REPLACE:  `< new user >`@
     SEARCH:  `<...pu..en>`@`localhost`   AND  REPLACE:  `< new user >`@`%`
     SEARCH:  `<...pu..en>`@`%`           AND  REPLACE:  `< new user >`@`%`
    ```

## Dealing with ERRORS

### __The error:__ "_Illegal mix of collations ..." 

... is related to a mismatch of the character sets and collations and often occurs when comparing strings or performing operations between columns that have different collations.

__To resolve this__, explicitly convert the collations to be consistent across your query. You can use the COLLATE clause to ensure that all string comparisons use the same collation.

Here’s how you can modify your query:

    CREATE ALGORITHM=UNDEFINED DEFINER=`pklopuzen`@`%` SQL SECURITY DEFINER VIEW `R_ITEMS_WHERE_TPRODUCT_INMASTER`
    AS SELECT
       `V`.`name` AS `vendor_name`,
       `I`.`status` AS `status`,
       `I`.`stock_status` AS `stock_status`,
       `P`.`name` AS `product_name`,
       COALESCE(`I`.`code` COLLATE utf8mb4_unicode_ci, '') AS `code`,
       `I`.`color` COLLATE utf8mb4_unicode_ci AS `color`
    FROM ((`V_ITEM` `I` 
      JOIN `T_PRODUCT` `P` ON ((`I`.`product_id` = `P`.`id` COLLATE utf8mb4_unicode_ci) 
      AND (`I`.`product_type` = 'R' COLLATE utf8mb4_unicode_ci)))
    LEFT JOIN `V_PRODUCT_VENDOR` `V` ON (`P`.`id` = `V`.`product_id` COLLATE utf8mb4_unicode_ci))
    WHERE ((`P`.`in_master` = 1) AND (`I`.`product_type` = 'R' COLLATE utf8mb4_unicode_ci))
    ORDER BY `P`.`name` COLLATE utf8mb4_unicode_ci, `I`.`color` COLLATE utf8mb4_unicode_ci;


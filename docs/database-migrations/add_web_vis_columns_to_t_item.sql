-- ============================================================================
-- Web Visibility Columns Migration for T_ITEM Table
-- ============================================================================
-- Date: January 15, 2025
-- Purpose: Add web_vis and web_vis_toggle columns to support lazy calculation
-- Reference: docs/ai-specs/Web-Visibility-AI-Model-Specification.md
-- ============================================================================

-- Step 1: Add web_vis_toggle column (manual override state)
ALTER TABLE T_ITEM 
ADD COLUMN web_vis_toggle BOOLEAN NOT NULL DEFAULT FALSE 
COMMENT 'Manual override toggle state for web visibility';

-- Step 2: Add web_vis column (computed visibility state)
ALTER TABLE T_ITEM 
ADD COLUMN web_vis BOOLEAN NULL DEFAULT NULL 
COMMENT 'Final computed visibility state (NULL = needs calculation)';

-- Step 3: Add indexes for performance optimization
ALTER TABLE T_ITEM ADD INDEX idx_web_vis (web_vis);
ALTER TABLE T_ITEM ADD INDEX idx_web_vis_toggle (web_vis_toggle);

-- Step 4: Verify columns were added successfully
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'T_ITEM'
  AND COLUMN_NAME IN ('web_vis', 'web_vis_toggle')
ORDER BY COLUMN_NAME;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Check how many items have NULL web_vis (should be all of them initially)
SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN web_vis IS NULL THEN 1 ELSE 0 END) as null_web_vis_count,
    SUM(CASE WHEN web_vis = 1 THEN 1 ELSE 0 END) as visible_count,
    SUM(CASE WHEN web_vis = 0 THEN 1 ELSE 0 END) as hidden_count
FROM T_ITEM
WHERE archived = 'N';

-- Sample query to see the data
SELECT 
    id as item_id,
    code,
    product_id,
    status_id,
    web_vis,
    web_vis_toggle,
    archived
FROM T_ITEM
WHERE archived = 'N'
LIMIT 10;

-- ============================================================================
-- ROLLBACK SCRIPT (if needed)
-- ============================================================================
/*
-- Uncomment to rollback changes:

-- Remove indexes
ALTER TABLE T_ITEM DROP INDEX idx_web_vis;
ALTER TABLE T_ITEM DROP INDEX idx_web_vis_toggle;

-- Remove columns
ALTER TABLE T_ITEM DROP COLUMN web_vis;
ALTER TABLE T_ITEM DROP COLUMN web_vis_toggle;

-- Verify removal
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'T_ITEM'
  AND COLUMN_NAME IN ('web_vis', 'web_vis_toggle');

*/

-- ============================================================================
-- NOTES
-- ============================================================================
/*

IMPORTANT NOTES:
1. web_vis_toggle defaults to FALSE (auto-determination mode)
2. web_vis starts as NULL to trigger lazy calculation
3. Lazy calculation will populate web_vis values as colorline lists are viewed
4. Manual override (web_vis_toggle = TRUE) will be implemented in future phase

EXPECTED BEHAVIOR AFTER MIGRATION:
- All existing items will have web_vis = NULL
- All existing items will have web_vis_toggle = FALSE
- First colorline list load will trigger lazy calculation
- Database will be updated with calculated web_vis values
- Subsequent loads will use stored values (no recalculation needed)

*/


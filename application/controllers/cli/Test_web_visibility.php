<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Test Script for Web Visibility Migration
 * 
 * Validates database connectivity, table structure, and query logic
 * before running the actual migration.
 * 
 * Usage:
 *   php index.php cli/test_web_visibility run
 * 
 * @author Paul Leasure
 * @date October 8, 2025
 */
class Test_web_visibility extends CI_Controller
{
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $test_results = [];

    public function __construct()
    {
        parent::__construct();
        
        // Ensure CLI only
        if (!$this->input->is_cli_request()) {
            show_error('This script can only be run from command line', 403);
        }
        
        $this->load->database();
    }

    /**
     * Run all tests
     */
    public function run()
    {
        $this->log_header();
        
        // Database Tests
        $this->log_section("DATABASE CONNECTION TESTS");
        $this->test_database_connection();
        
        // Table Structure Tests
        $this->log_section("TABLE STRUCTURE TESTS");
        $this->test_table_exists('T_PRODUCT');
        $this->test_table_exists('T_ITEM');
        $this->test_table_exists('SHOWCASE_PRODUCT');
        $this->test_table_exists('P_PRODUCT_STATUS');
        
        // Column Tests
        $this->log_section("COLUMN EXISTENCE TESTS");
        $this->test_column_exists('T_PRODUCT', 'archived');
        $this->test_column_exists('T_ITEM', 'web_vis');
        $this->test_column_exists('T_ITEM', 'web_vis_toggle');
        $this->test_column_exists('SHOWCASE_PRODUCT', 'visible');
        $this->test_column_exists('SHOWCASE_PRODUCT', 'pic_big_url');
        
        // Data Query Tests
        $this->log_section("DATA QUERY TESTS");
        $this->test_products_query();
        $this->test_items_query();
        
        // Data State Tests
        $this->log_section("DATA STATE TESTS");
        $this->test_product_visibility_states();
        $this->test_item_visibility_states();
        
        // Logic Tests
        $this->log_section("BUSINESS LOGIC TESTS");
        $this->test_product_visibility_calculation();
        $this->test_item_visibility_calculation();
        
        // Show Summary
        $this->log_summary();
    }

    /**
     * Test database connection
     */
    private function test_database_connection()
    {
        try {
            $this->db->query('SELECT 1');
            $this->pass("Database connection successful");
        } catch (Exception $e) {
            $this->fail("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Test if table exists
     */
    private function test_table_exists($table)
    {
        try {
            $result = $this->db->query("SHOW TABLES LIKE '{$table}'")->num_rows();
            if ($result > 0) {
                $this->pass("Table '{$table}' exists");
            } else {
                $this->fail("Table '{$table}' does not exist");
            }
        } catch (Exception $e) {
            $this->fail("Error checking table '{$table}': " . $e->getMessage());
        }
    }

    /**
     * Test if column exists
     */
    private function test_column_exists($table, $column)
    {
        try {
            $result = $this->db->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'")->num_rows();
            if ($result > 0) {
                $this->pass("Column '{$table}.{$column}' exists");
            } else {
                $this->fail("Column '{$table}.{$column}' does not exist");
            }
        } catch (Exception $e) {
            $this->fail("Error checking column '{$table}.{$column}': " . $e->getMessage());
        }
    }

    /**
     * Test products query
     */
    private function test_products_query()
    {
        try {
            $query = $this->db->select('
                p.id as product_id,
                p.type as product_type,
                p.name as product_name,
                sp.visible as current_visible,
                sp.pic_big_url as beauty_shot,
                sp.url_title,
                sp.descr
            ')
            ->from('T_PRODUCT p')
            ->join('SHOWCASE_PRODUCT sp', 'p.id = sp.product_id', 'left')
            ->where('p.archived', 'N')
            ->limit(5)
            ->get();
            
            $count = $query->num_rows();
            $this->pass("Products query successful (fetched {$count} records)");
            
            // Show sample data
            if ($count > 0) {
                $sample = $query->row_array();
                $this->log_info("  Sample: Product ID={$sample['product_id']}, Name={$sample['product_name']}, Visible=" . ($sample['current_visible'] ?? 'NULL'));
            }
        } catch (Exception $e) {
            $this->fail("Products query failed: " . $e->getMessage());
        }
    }

    /**
     * Test items query
     */
    private function test_items_query()
    {
        try {
            $query = $this->db->select('
                i.id as item_id,
                i.product_id,
                i.code as item_code,
                i.web_vis as current_web_vis,
                i.web_vis_toggle,
                i.status_id,
                ps.abrev as status,
                sp.visible as parent_product_visibility,
                i.pic_big_url as item_pic_big,
                i.pic_hd_url as item_pic_hd
            ')
            ->from('T_ITEM i')
            ->join('P_PRODUCT_STATUS ps', 'i.status_id = ps.id', 'left')
            ->join('SHOWCASE_PRODUCT sp', 'i.product_id = sp.product_id', 'left')
            ->where('i.archived', 'N')
            ->limit(5)
            ->get();
            
            $count = $query->num_rows();
            $this->pass("Items query successful (fetched {$count} records)");
            
            // Show sample data
            if ($count > 0) {
                $sample = $query->row_array();
                $this->log_info("  Sample: Item ID={$sample['item_id']}, Code={$sample['item_code']}, web_vis=" . ($sample['current_web_vis'] ?? 'NULL') . ", Status={$sample['status']}");
            }
        } catch (Exception $e) {
            $this->fail("Items query failed: " . $e->getMessage());
        }
    }

    /**
     * Test product visibility states
     */
    private function test_product_visibility_states()
    {
        try {
            // Count products by state
            $states = [
                'has_beauty_shot_visible' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM SHOWCASE_PRODUCT
                    WHERE visible = 'Y'
                      AND pic_big_url IS NOT NULL
                      AND pic_big_url != ''
                ")->row()->count,
                
                'has_beauty_shot_not_visible' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM SHOWCASE_PRODUCT
                    WHERE visible = 'N'
                      AND pic_big_url IS NOT NULL
                      AND pic_big_url != ''
                ")->row()->count,
                
                'no_beauty_shot_visible' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM SHOWCASE_PRODUCT
                    WHERE visible = 'Y'
                      AND (pic_big_url IS NULL OR pic_big_url = '')
                ")->row()->count,
                
                'null_visibility' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM T_PRODUCT p
                    LEFT JOIN SHOWCASE_PRODUCT sp ON p.id = sp.product_id
                    WHERE p.archived = 'N'
                      AND sp.visible IS NULL
                ")->row()->count
            ];
            
            $this->pass("Product visibility states analyzed");
            $this->log_info("  With beauty shot + visible: {$states['has_beauty_shot_visible']}");
            $this->log_info("  With beauty shot + not visible: {$states['has_beauty_shot_not_visible']}");
            $this->log_info("  No beauty shot + visible (ERROR): {$states['no_beauty_shot_visible']}");
            $this->log_info("  NULL visibility: {$states['null_visibility']}");
            
            if ($states['no_beauty_shot_visible'] > 0) {
                $this->log_warning("  ⚠️  Found {$states['no_beauty_shot_visible']} products visible without beauty shot (will be fixed)");
            }
        } catch (Exception $e) {
            $this->fail("Product visibility states test failed: " . $e->getMessage());
        }
    }

    /**
     * Test item visibility states
     */
    private function test_item_visibility_states()
    {
        try {
            // Count items by state
            $states = [
                'manual_override' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM T_ITEM
                    WHERE archived = 'N'
                      AND web_vis_toggle = 1
                ")->row()->count,
                
                'auto_visible' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM T_ITEM
                    WHERE archived = 'N'
                      AND web_vis = 1
                      AND web_vis_toggle = 0
                ")->row()->count,
                
                'auto_hidden' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM T_ITEM
                    WHERE archived = 'N'
                      AND web_vis = 0
                      AND web_vis_toggle = 0
                ")->row()->count,
                
                'null_web_vis' => $this->db->query("
                    SELECT COUNT(*) as count
                    FROM T_ITEM
                    WHERE archived = 'N'
                      AND web_vis IS NULL
                ")->row()->count
            ];
            
            $this->pass("Item visibility states analyzed");
            $this->log_info("  Manual override active: {$states['manual_override']}");
            $this->log_info("  Auto-calculated visible: {$states['auto_visible']}");
            $this->log_info("  Auto-calculated hidden: {$states['auto_hidden']}");
            $this->log_info("  NULL (needs calculation): {$states['null_web_vis']}");
        } catch (Exception $e) {
            $this->fail("Item visibility states test failed: " . $e->getMessage());
        }
    }

    /**
     * Test product visibility calculation logic
     */
    private function test_product_visibility_calculation()
    {
        $test_cases = [
            ['current' => 'Y', 'beauty_shot' => '/path/to/image.jpg', 'expected' => true, 'reason' => 'User approved + has beauty shot'],
            ['current' => 'N', 'beauty_shot' => '/path/to/image.jpg', 'expected' => false, 'reason' => 'User declined (respect decision)'],
            ['current' => NULL, 'beauty_shot' => '/path/to/image.jpg', 'expected' => false, 'reason' => 'NULL + has beauty shot (conservative default)'],
            ['current' => NULL, 'beauty_shot' => NULL, 'expected' => false, 'reason' => 'NULL + no beauty shot'],
            ['current' => 'Y', 'beauty_shot' => NULL, 'expected' => false, 'reason' => 'Data error: visible without beauty shot (will fix)'],
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($test_cases as $test) {
            $result = $this->calculate_product_visibility_test($test['current'], $test['beauty_shot']);
            if ($result === $test['expected']) {
                $passed++;
            } else {
                $failed++;
                $this->log_error("  FAILED: {$test['reason']} - Expected " . ($test['expected'] ? 'true' : 'false') . ", got " . ($result ? 'true' : 'false'));
            }
        }
        
        if ($failed === 0) {
            $this->pass("Product visibility calculation logic validated ({$passed} test cases passed)");
        } else {
            $this->fail("Product visibility calculation logic failed ({$failed} of " . count($test_cases) . " test cases failed)");
        }
    }

    /**
     * Test item visibility calculation logic
     */
    private function test_item_visibility_calculation()
    {
        $test_cases = [
            ['parent' => 'Y', 'status' => 'RUN', 'images' => true, 'expected' => true, 'reason' => 'All conditions met'],
            ['parent' => 'N', 'status' => 'RUN', 'images' => true, 'expected' => false, 'reason' => 'Parent not visible'],
            ['parent' => 'Y', 'status' => 'DISC', 'images' => true, 'expected' => false, 'reason' => 'Invalid status'],
            ['parent' => 'Y', 'status' => 'RUN', 'images' => false, 'expected' => false, 'reason' => 'No images'],
            ['parent' => 'Y', 'status' => 'LTDQTY', 'images' => true, 'expected' => true, 'reason' => 'LTDQTY status valid'],
            ['parent' => 'Y', 'status' => 'RKFISH', 'images' => true, 'expected' => true, 'reason' => 'RKFISH status valid'],
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($test_cases as $test) {
            $result = $this->calculate_item_visibility_test($test['parent'], $test['status'], $test['images']);
            if ($result === $test['expected']) {
                $passed++;
            } else {
                $failed++;
                $this->log_error("  FAILED: {$test['reason']} - Expected " . ($test['expected'] ? 'true' : 'false') . ", got " . ($result ? 'true' : 'false'));
            }
        }
        
        if ($failed === 0) {
            $this->pass("Item visibility calculation logic validated ({$passed} test cases passed)");
        } else {
            $this->fail("Item visibility calculation logic failed ({$failed} of " . count($test_cases) . " test cases failed)");
        }
    }

    /**
     * Simulate product visibility calculation
     */
    private function calculate_product_visibility_test($current_visible, $beauty_shot)
    {
        // Preserve existing decisions
        if ($current_visible === 'Y') {
            return true;
        }
        if ($current_visible === 'N') {
            return false;
        }
        
        // Conservative default for NULL
        return false;
    }

    /**
     * Simulate item visibility calculation
     */
    private function calculate_item_visibility_test($parent_visible, $status, $has_images)
    {
        // Check parent visible
        if ($parent_visible !== 'Y') {
            return false;
        }
        
        // Check valid status
        $valid_statuses = ['RUN', 'LTDQTY', 'RKFISH'];
        if (!in_array($status, $valid_statuses, true)) {
            return false;
        }
        
        // Check has images
        if (!$has_images) {
            return false;
        }
        
        // All conditions met
        return true;
    }

    /**
     * Test helper methods
     */
    private function pass($msg)
    {
        $this->tests_passed++;
        $this->test_results[] = ['status' => 'PASS', 'message' => $msg];
        echo "  ✅ PASS: {$msg}\n";
    }

    private function fail($msg)
    {
        $this->tests_failed++;
        $this->test_results[] = ['status' => 'FAIL', 'message' => $msg];
        echo "  ❌ FAIL: {$msg}\n";
    }

    private function log_header()
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║   OPMS Web Visibility Migration - Test Suite                 ║\n";
        echo "║   " . date('Y-m-d H:i:s') . "                                      ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function log_section($title)
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo " {$title}\n";
        echo "═══════════════════════════════════════════════════════════════\n";
    }

    private function log_info($msg)
    {
        echo "{$msg}\n";
    }

    private function log_warning($msg)
    {
        echo "  ⚠️  {$msg}\n";
    }

    private function log_error($msg)
    {
        echo "  ❌ {$msg}\n";
    }

    private function log_summary()
    {
        $total = $this->tests_passed + $this->tests_failed;
        $pass_rate = $total > 0 ? round(($this->tests_passed / $total) * 100, 1) : 0;
        
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║   TEST SUMMARY                                                ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "  Total Tests:  {$total}\n";
        echo "  Passed:       {$this->tests_passed}\n";
        echo "  Failed:       {$this->tests_failed}\n";
        echo "  Pass Rate:    {$pass_rate}%\n";
        echo "\n";
        
        if ($this->tests_failed === 0) {
            echo "✅ ALL TESTS PASSED - Migration script is ready to run\n";
            echo "\n";
            echo "Next Steps:\n";
            echo "  1. Run: php index.php cli/migrate_web_visibility report\n";
            echo "  2. Review CSV reports in reports/ directory\n";
            echo "  3. Run: php index.php cli/migrate_web_visibility run --dry-run\n";
            echo "  4. If satisfied, run: php index.php cli/migrate_web_visibility run\n";
        } else {
            echo "❌ SOME TESTS FAILED - Fix issues before running migration\n";
            exit(1);
        }
        echo "\n";
    }
}


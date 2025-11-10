<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI Migration Script for Web Visibility Initialization
 * 
 * This script initializes web visibility for all products and items in OPMS.
 * It respects existing user decisions and uses a conservative approach.
 * 
 * BUSINESS RULES:
 * - Product visibility requires BOTH beauty shot AND user checkbox approval
 * - Item visibility requires parent visible + valid status + item images
 * - Manual overrides are preserved and never changed
 * 
 * Usage:
 *   php index.php cli/migrate_web_visibility report
 *   php index.php cli/migrate_web_visibility run [--batch-size=100] [--dry-run]
 * 
 * @author Paul Leasure
 * @date October 8, 2025
 * @version 2.0.0
 */
class Migrate_web_visibility extends CI_Controller
{
    private $batch_size = 100;
    private $dry_run = false;
    private $report_mode = false;
    private $report_data = [];
    private $stats = [
        'products_processed' => 0,
        'products_updated' => 0,
        'products_already_configured' => 0,
        'products_initialized' => 0,
        'products_data_errors_fixed' => 0,
        'items_processed' => 0,
        'items_updated' => 0,
        'items_skipped_manual' => 0,
        'items_already_set' => 0,
        'errors' => []
    ];

    public function __construct()
    {
        parent::__construct();
        
        // Ensure CLI only
        if (!$this->input->is_cli_request()) {
            show_error('This script can only be run from command line', 403);
        }
        
        $this->load->database();
        $this->load->model('Product_model', 'product_model');
        $this->load->model('Item_model', 'item_model');
        $this->load->helper('file');
        
        // Parse CLI arguments
        $this->parse_cli_args();
    }

    /**
     * Generate report of current state and recommended actions
     */
    public function report()
    {
        $this->report_mode = true;
        $this->log_header('REPORT MODE');
        
        try {
            // Analyze products
            $this->log_info("Analyzing products...");
            $this->analyze_products();
            
            // Analyze items
            $this->log_info("\nAnalyzing items...");
            $this->analyze_items();
            
            // Generate CSV reports
            $this->generate_reports();
            
            // Show summary
            $this->log_report_summary();
            
        } catch (Exception $e) {
            $this->log_error("FATAL ERROR: " . $e->getMessage());
            $this->log_error("Stack trace: " . $e->getTraceAsString());
            exit(1);
        }
        
        $this->log_info("\n✅ REPORT GENERATION COMPLETED");
    }

    /**
     * Main migration entry point
     */
    public function run()
    {
        $this->log_header('MIGRATION MODE');
        
        try {
            // Step 1: Migrate products first (parents)
            $this->log_info("STEP 1: Migrating product-level web visibility...");
            $this->migrate_products();
            
            // Step 2: Migrate items (children)
            $this->log_info("\nSTEP 2: Migrating item-level web visibility...");
            $this->migrate_items();
            
            // Step 3: Verify integrity
            $this->log_info("\nSTEP 3: Verifying data integrity...");
            $this->verify_migration();
            
            // Report results
            $this->log_summary();
            
        } catch (Exception $e) {
            $this->log_error("FATAL ERROR: " . $e->getMessage());
            $this->log_error("Stack trace: " . $e->getTraceAsString());
            exit(1);
        }
        
        if ($this->dry_run) {
            $this->log_info("\n🔍 DRY RUN COMPLETED - No data was modified");
        } else {
            $this->log_info("\n✅ MIGRATION COMPLETED SUCCESSFULLY");
            $this->log_info("\n⚠️  NEXT STEPS:");
            $this->log_info("   1. Review the migration results above");
            $this->log_info("   2. Products with beauty shots are initialized to 'N' (not visible)");
            $this->log_info("   3. Users must manually enable web visibility in Product Edit Forms");
            $this->log_info("   4. Item visibility will auto-calculate when parent is enabled");
        }
    }

    /**
     * Analyze products for report generation
     */
    private function analyze_products()
    {
        $offset = 0;
        $total = $this->get_products_count();
        $this->log_info("Found {$total} active products to analyze");
        
        $analysis = [
            'has_beauty_shot_and_visible' => [],
            'has_beauty_shot_not_visible' => [],
            'has_beauty_shot_null_visibility' => [],
            'no_beauty_shot_but_visible' => [],
            'no_beauty_shot_not_visible' => [],
            'no_beauty_shot_null_visibility' => []
        ];
        
        while ($offset < $total) {
            $products = $this->fetch_products_batch($offset);
            
            foreach ($products as $product) {
                $has_beauty_shot = !empty($product['beauty_shot']) && $product['beauty_shot'] !== '';
                $current_visible = $product['current_visible'];
                
                $product_info = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'product_type' => $product['product_type'],
                    'beauty_shot' => $product['beauty_shot'] ?? '',
                    'current_visible' => $current_visible ?? 'NULL',
                    'url_title' => $product['url_title'] ?? ''
                ];
                
                // Categorize
                if ($has_beauty_shot && $current_visible === 'Y') {
                    $analysis['has_beauty_shot_and_visible'][] = $product_info;
                } elseif ($has_beauty_shot && $current_visible === 'N') {
                    $analysis['has_beauty_shot_not_visible'][] = $product_info;
                } elseif ($has_beauty_shot && $current_visible === NULL) {
                    $analysis['has_beauty_shot_null_visibility'][] = $product_info;
                } elseif (!$has_beauty_shot && $current_visible === 'Y') {
                    $analysis['no_beauty_shot_but_visible'][] = $product_info;
                } elseif (!$has_beauty_shot && $current_visible === 'N') {
                    $analysis['no_beauty_shot_not_visible'][] = $product_info;
                } elseif (!$has_beauty_shot && $current_visible === NULL) {
                    $analysis['no_beauty_shot_null_visibility'][] = $product_info;
                }
            }
            
            $offset += $this->batch_size;
        }
        
        $this->report_data['products'] = $analysis;
    }

    /**
     * Analyze items for report generation
     */
    private function analyze_items()
    {
        $offset = 0;
        $total = $this->get_items_count();
        $this->log_info("Found {$total} active items to analyze");
        
        $analysis = [
            'manual_override_active' => [],
            'auto_calculated_visible' => [],
            'auto_calculated_hidden' => [],
            'null_needs_calculation' => []
        ];
        
        while ($offset < $total) {
            $items = $this->fetch_items_batch($offset);
            
            foreach ($items as $item) {
                $item_info = [
                    'item_id' => $item['item_id'],
                    'item_code' => $item['item_code'] ?? '',
                    'product_id' => $item['product_id'],
                    'status' => $item['status'] ?? '',
                    'web_vis' => $item['current_web_vis'] ?? 'NULL',
                    'web_vis_toggle' => $item['web_vis_toggle'] ?? '0',
                    'parent_visible' => $item['parent_product_visibility'] ?? 'NULL',
                    'has_images' => (!empty($item['item_pic_big']) || !empty($item['item_pic_hd'])) ? 'YES' : 'NO'
                ];
                
                // Categorize
                if (!empty($item['web_vis_toggle']) && $item['web_vis_toggle'] == 1) {
                    $analysis['manual_override_active'][] = $item_info;
                } elseif ($item['current_web_vis'] === '1' || $item['current_web_vis'] === 1) {
                    $analysis['auto_calculated_visible'][] = $item_info;
                } elseif ($item['current_web_vis'] === '0' || $item['current_web_vis'] === 0) {
                    $analysis['auto_calculated_hidden'][] = $item_info;
                } elseif ($item['current_web_vis'] === NULL || $item['current_web_vis'] === '') {
                    $analysis['null_needs_calculation'][] = $item_info;
                }
            }
            
            $offset += $this->batch_size;
        }
        
        $this->report_data['items'] = $analysis;
    }

    /**
     * Generate CSV reports
     */
    private function generate_reports()
    {
        $timestamp = date('Y-m-d_His');
        $report_dir = APPPATH . '../reports/';
        
        // Create reports directory if not exists
        if (!is_dir($report_dir)) {
            mkdir($report_dir, 0755, true);
        }
        
        // Generate product reports
        $this->generate_product_report($report_dir, $timestamp);
        
        // Generate item reports
        $this->generate_item_report($report_dir, $timestamp);
    }

    /**
     * Generate product CSV report
     */
    private function generate_product_report($report_dir, $timestamp)
    {
        $filename = $report_dir . "product_visibility_analysis_{$timestamp}.csv";
        $fp = fopen($filename, 'w');
        
        // CSV Header
        fputcsv($fp, ['Product ID', 'Product Name', 'Type', 'Has Beauty Shot', 'Current Visible', 'Recommended Action', 'Action Reason']);
        
        $products = $this->report_data['products'];
        
        // Products with beauty shots and visible (OK)
        foreach ($products['has_beauty_shot_and_visible'] as $p) {
            fputcsv($fp, [
                $p['product_id'],
                $p['product_name'],
                $p['product_type'],
                'YES',
                $p['current_visible'],
                'NO ACTION',
                'Already configured correctly'
            ]);
        }
        
        // Products with beauty shots but not visible (User choice)
        foreach ($products['has_beauty_shot_not_visible'] as $p) {
            fputcsv($fp, [
                $p['product_id'],
                $p['product_name'],
                $p['product_type'],
                'YES',
                $p['current_visible'],
                'NO ACTION',
                'User chose to keep hidden (respect decision)'
            ]);
        }
        
        // Products with beauty shots but NULL visibility (NEEDS REVIEW)
        foreach ($products['has_beauty_shot_null_visibility'] as $p) {
            fputcsv($fp, [
                $p['product_id'],
                $p['product_name'],
                $p['product_type'],
                'YES',
                $p['current_visible'],
                'INITIALIZE TO N',
                'Has beauty shot but no visibility setting - user must manually enable'
            ]);
        }
        
        // Products without beauty shots but visible (DATA ERROR)
        foreach ($products['no_beauty_shot_but_visible'] as $p) {
            fputcsv($fp, [
                $p['product_id'],
                $p['product_name'],
                $p['product_type'],
                'NO',
                $p['current_visible'],
                'FIX: SET TO N',
                'DATA ERROR: Cannot be visible without beauty shot'
            ]);
        }
        
        // Products without beauty shots and not visible (OK)
        foreach ($products['no_beauty_shot_not_visible'] as $p) {
            fputcsv($fp, [
                $p['product_id'],
                $p['product_name'],
                $p['product_type'],
                'NO',
                $p['current_visible'],
                'NO ACTION',
                'Correctly set to not visible'
            ]);
        }
        
        // Products without beauty shots and NULL visibility (OK)
        foreach ($products['no_beauty_shot_null_visibility'] as $p) {
            fputcsv($fp, [
                $p['product_id'],
                $p['product_name'],
                $p['product_type'],
                'NO',
                $p['current_visible'],
                'INITIALIZE TO N',
                'No beauty shot - correctly defaults to not visible'
            ]);
        }
        
        fclose($fp);
        $this->log_info("Product report generated: {$filename}");
    }

    /**
     * Generate item CSV report
     */
    private function generate_item_report($report_dir, $timestamp)
    {
        $filename = $report_dir . "item_visibility_analysis_{$timestamp}.csv";
        $fp = fopen($filename, 'w');
        
        // CSV Header
        fputcsv($fp, ['Item ID', 'Item Code', 'Product ID', 'Status', 'Has Images', 'Parent Visible', 'Current web_vis', 'Manual Override', 'Recommended Action']);
        
        $items = $this->report_data['items'];
        
        // Items with manual override (PRESERVE)
        foreach ($items['manual_override_active'] as $i) {
            fputcsv($fp, [
                $i['item_id'],
                $i['item_code'],
                $i['product_id'],
                $i['status'],
                $i['has_images'],
                $i['parent_visible'],
                $i['web_vis'],
                'YES',
                'NO ACTION - Manual override active (preserve user choice)'
            ]);
        }
        
        // Items auto-calculated as visible
        foreach ($items['auto_calculated_visible'] as $i) {
            fputcsv($fp, [
                $i['item_id'],
                $i['item_code'],
                $i['product_id'],
                $i['status'],
                $i['has_images'],
                $i['parent_visible'],
                $i['web_vis'],
                'NO',
                'NO ACTION - Already set to visible'
            ]);
        }
        
        // Items auto-calculated as hidden
        foreach ($items['auto_calculated_hidden'] as $i) {
            fputcsv($fp, [
                $i['item_id'],
                $i['item_code'],
                $i['product_id'],
                $i['status'],
                $i['has_images'],
                $i['parent_visible'],
                $i['web_vis'],
                'NO',
                'NO ACTION - Already set to hidden'
            ]);
        }
        
        // Items with NULL (needs calculation)
        foreach ($items['null_needs_calculation'] as $i) {
            fputcsv($fp, [
                $i['item_id'],
                $i['item_code'],
                $i['product_id'],
                $i['status'],
                $i['has_images'],
                $i['parent_visible'],
                $i['web_vis'],
                'NO',
                'CALCULATE - Will calculate and save web_vis value'
            ]);
        }
        
        fclose($fp);
        $this->log_info("Item report generated: {$filename}");
    }

    /**
     * Migrate all products (parent level)
     */
    private function migrate_products()
    {
        $total = $this->get_products_count();
        $this->log_info("Found {$total} active products to process");
        
        $offset = 0;
        while ($offset < $total) {
            $products = $this->fetch_products_batch($offset);
            
            foreach ($products as $product) {
                $this->process_product($product);
            }
            
            $offset += $this->batch_size;
            $this->log_progress($this->stats['products_processed'], $total, 'products');
        }
    }

    /**
     * Get count of active products
     */
    private function get_products_count()
    {
        $this->db->select('COUNT(*) as total')
                 ->from('T_PRODUCT')
                 ->where('archived', 'N');
        return $this->db->get()->row()->total;
    }

    /**
     * Fetch batch of products with showcase data
     */
    private function fetch_products_batch($offset)
    {
        $this->db->select('
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
        ->limit($this->batch_size, $offset);
        
        return $this->db->get()->result_array();
    }

    /**
     * Process single product with conservative approach
     */
    private function process_product($product)
    {
        $this->stats['products_processed']++;
        
        // Calculate visibility (respects existing values)
        $should_be_visible = $this->calculate_product_visibility($product);
        $new_visible = $should_be_visible ? 'Y' : 'N';
        
        // Special handling for data integrity issues
        $has_beauty_shot = !empty($product['beauty_shot']) && $product['beauty_shot'] !== '';
        
        // DATA INTEGRITY CHECK: Can't be visible without beauty shot
        if ($new_visible === 'Y' && !$has_beauty_shot) {
            $this->log_warning("  Product {$product['product_id']} ({$product['product_name']}): " .
                              "Visible='Y' but NO beauty shot - forcing to 'N'");
            $new_visible = 'N';
            $this->stats['products_data_errors_fixed']++;
        }
        
        // Determine if this is a change
        $is_new_record = ($product['current_visible'] === NULL);
        $is_update_needed = ($product['current_visible'] !== $new_visible);
        
        // Track statistics
        if (!$is_update_needed) {
            $this->stats['products_already_configured']++;
        } elseif ($is_new_record) {
            $this->stats['products_initialized']++;
        }
        
        // Update if needed
        if ($is_update_needed) {
            
            if (!$this->dry_run) {
                $showcase_data = [
                    'product_id' => $product['product_id'],
                    'product_type' => $product['product_type'],
                    'visible' => $new_visible,
                    'pic_big_url' => $product['beauty_shot'] ?? '',
                    'url_title' => $product['url_title'] ?? $product['product_name'],
                    'descr' => $product['descr'] ?? '',
                    'date_modif' => date('Y-m-d H:i:s'),
                    'user_id' => 0  // System user
                ];
                
                // Use replace for both insert and update
                $this->db->replace('SHOWCASE_PRODUCT', $showcase_data);
            }
            
            $this->stats['products_updated']++;
            
            $reason = $is_new_record ? '(initializing NULL)' : '(data fix)';
            $this->log_detail("  Product {$product['product_id']} ({$product['product_name']}): " .
                             ($product['current_visible'] ?? 'NULL') . " → {$new_visible} {$reason}");
        }
    }

    /**
     * Calculate product visibility - CONSERVATIVE APPROACH
     * 
     * BUSINESS RULE:
     * - Product web visibility requires BOTH beauty shot AND user approval
     * - Beauty shot only ENABLES checkbox, doesn't auto-check it
     * - Migration cannot assume user intent
     * 
     * STRATEGY:
     * - Preserve existing 'Y' values (user previously approved)
     * - Preserve existing 'N' values (user previously declined)
     * - Initialize NULL values to 'N' (conservative default - user must enable)
     */
    private function calculate_product_visibility($product)
    {
        // Preserve existing explicit decisions
        if ($product['current_visible'] === 'Y') {
            return true;  // User previously approved
        }
        
        if ($product['current_visible'] === 'N') {
            return false;  // User previously declined
        }
        
        // For NULL values (no showcase record exists)
        // CONSERVATIVE: Default to NOT visible
        // User must explicitly enable after migration
        return false;
    }

    /**
     * Migrate all items (child level)
     */
    private function migrate_items()
    {
        $total = $this->get_items_count();
        $this->log_info("Found {$total} active items to process");
        
        $offset = 0;
        while ($offset < $total) {
            $items = $this->fetch_items_batch($offset);
            $items_to_update = [];
            
            foreach ($items as $item) {
                $update_data = $this->process_item($item);
                if ($update_data) {
                    $items_to_update[] = $update_data;
                }
            }
            
            // Batch update all items
            if (!empty($items_to_update) && !$this->dry_run) {
                $this->db->update_batch('T_ITEM', $items_to_update, 'id');
            }
            
            $offset += $this->batch_size;
            $this->log_progress($this->stats['items_processed'], $total, 'items');
        }
    }

    /**
     * Get count of active items
     */
    private function get_items_count()
    {
        $this->db->select('COUNT(*) as total')
                 ->from('T_ITEM')
                 ->where('archived', 'N');
        return $this->db->get()->row()->total;
    }

    /**
     * Fetch batch of items with parent visibility
     */
    private function fetch_items_batch($offset)
    {
        $this->db->select('
            i.id as item_id,
            i.product_id,
            i.code as item_code,
            i.web_vis as current_web_vis,
            i.web_vis_toggle,
            i.status_id,
            ps.name as status,
            sp.visible as parent_product_visibility,
            sp.pic_big_url as parent_beauty_shot,
            si.pic_big_url as item_pic_big,
            si.pic_hd_url as item_pic_hd
        ')
        ->from('T_ITEM i')
        ->join('P_PRODUCT_STATUS ps', 'i.status_id = ps.id', 'left')
        ->join('SHOWCASE_PRODUCT sp', 'i.product_id = sp.product_id', 'left')
        ->join('SHOWCASE_ITEM si', 'i.id = si.item_id', 'left')
        ->where('i.archived', 'N')
        ->limit($this->batch_size, $offset);
        
        return $this->db->get()->result_array();
    }

    /**
     * Process single item
     */
    private function process_item($item)
    {
        $this->stats['items_processed']++;
        
        // CRITICAL: Skip items with manual override enabled
        if (!empty($item['web_vis_toggle']) && $item['web_vis_toggle'] == 1) {
            $this->stats['items_skipped_manual']++;
            $this->log_detail("  Item {$item['item_id']}: SKIPPED (manual override active)");
            return null;
        }
        
        // Calculate visibility
        $calculated_visibility = $this->calculate_item_visibility($item);
        $new_web_vis = $calculated_visibility ? 1 : 0;
        
        // Check if update needed
        $needs_update = false;
        
        // Update if NULL or different from calculated value
        if (is_null($item['current_web_vis']) || $item['current_web_vis'] === '') {
            $needs_update = true;
        } elseif ($item['current_web_vis'] != $new_web_vis) {
            $needs_update = true;
        } else {
            $this->stats['items_already_set']++;
        }
        
        if ($needs_update) {
            $this->stats['items_updated']++;
            $current_display = is_null($item['current_web_vis']) ? 'NULL' : $item['current_web_vis'];
            $this->log_detail("  Item {$item['item_id']} ({$item['item_code']}): " . 
                            "{$current_display} → {$new_web_vis}");
            
            return [
                'id' => $item['item_id'],
                'web_vis' => $new_web_vis,
                'date_modif' => date('Y-m-d H:i:s')
            ];
        }
        
        return null;
    }

    /**
     * Calculate item visibility based on business rules
     * 
     * Auto-calculated visibility requires ALL THREE:
     * 1. Parent product visibility = 'Y'
     * 2. Status is RUN, LTDQTY, or RKFISH
     * 3. Has item images (pic_big_url OR pic_hd_url)
     */
    private function calculate_item_visibility($item)
    {
        // Check manual override first (should be filtered earlier, but double-check)
        if (!empty($item['web_vis_toggle']) && $item['web_vis_toggle'] == 1) {
            // Manual mode - return stored value
            return !empty($item['current_web_vis']) ? (bool)$item['current_web_vis'] : false;
        }
        
        // Auto-calculated mode - THREE conditions (ALL required)
        
        // 1. Parent product must be visible
        $parent_visible = ($item['parent_product_visibility'] === 'Y');
        if (!$parent_visible) {
            return false;
        }
        
        // 2. Status must be valid
        $valid_statuses = ['RUN', 'LTDQTY', 'RKFISH'];
        $has_valid_status = in_array($item['status'], $valid_statuses, true);
        if (!$has_valid_status) {
            return false;
        }
        
        // 3. Must have item images
        $has_item_images = (!empty($item['item_pic_big']) || !empty($item['item_pic_hd']));
        if (!$has_item_images) {
            return false;
        }
        
        // All three conditions met
        return true;
    }

    /**
     * Verify migration integrity
     */
    private function verify_migration()
    {
        // Check for orphaned showcase records
        $orphaned_showcase = $this->db->query("
            SELECT COUNT(*) as count 
            FROM SHOWCASE_PRODUCT sp
            LEFT JOIN T_PRODUCT p ON sp.product_id = p.id
            WHERE p.id IS NULL
        ")->row()->count;
        
        if ($orphaned_showcase > 0) {
            $this->log_warning("Found {$orphaned_showcase} orphaned SHOWCASE_PRODUCT records");
        }
        
        // Check for items with web_vis=1 but parent not visible
        $inconsistent_items = $this->db->query("
            SELECT COUNT(*) as count
            FROM T_ITEM i
            JOIN SHOWCASE_PRODUCT sp ON i.product_id = sp.product_id
            WHERE i.web_vis = 1 
              AND sp.visible = 'N'
              AND i.web_vis_toggle = 0
              AND i.archived = 'N'
        ")->row()->count;
        
        if ($inconsistent_items > 0) {
            $this->log_warning("Found {$inconsistent_items} items visible but parent not visible");
        }
        
        // Check for items with web_vis=1 but parent is NULL
        $null_parent_items = $this->db->query("
            SELECT COUNT(*) as count
            FROM T_ITEM i
            LEFT JOIN SHOWCASE_PRODUCT sp ON i.product_id = sp.product_id
            WHERE i.web_vis = 1 
              AND sp.visible IS NULL
              AND i.archived = 'N'
        ")->row()->count;
        
        if ($null_parent_items > 0) {
            $this->log_warning("Found {$null_parent_items} items visible but parent has no SHOWCASE_PRODUCT record");
        }
        
        // Check for NULL web_vis values
        $null_items = $this->db->query("
            SELECT COUNT(*) as count
            FROM T_ITEM
            WHERE web_vis IS NULL
              AND archived = 'N'
        ")->row()->count;
        
        $this->log_info("Items with NULL web_vis: {$null_items} (will be calculated on display via lazy calculation)");
        
        // Check for products visible without beauty shot
        $invalid_products = $this->db->query("
            SELECT COUNT(*) as count
            FROM SHOWCASE_PRODUCT sp
            WHERE sp.visible = 'Y'
              AND (sp.pic_big_url IS NULL OR sp.pic_big_url = '')
        ")->row()->count;
        
        if ($invalid_products > 0) {
            $this->log_warning("Found {$invalid_products} products marked visible but have no beauty shot (DATA ERROR)");
        }
    }

    /**
     * Parse CLI arguments
     */
    private function parse_cli_args()
    {
        $args = $_SERVER['argv'] ?? [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--batch-size=') === 0) {
                $this->batch_size = (int) str_replace('--batch-size=', '', $arg);
            }
            if ($arg === '--dry-run') {
                $this->dry_run = true;
            }
        }
    }

    /**
     * Logging methods
     */
    private function log_header($mode = 'MIGRATION MODE')
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║   OPMS Web Visibility Migration Script                       ║\n";
        echo "║   " . $mode . "                                              ║\n";
        echo "║   " . date('Y-m-d H:i:s') . "                                      ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        if (!$this->report_mode) {
            echo "Batch size: {$this->batch_size}\n";
            echo "Dry run: " . ($this->dry_run ? 'YES' : 'NO') . "\n";
            echo "\n";
        }
    }

    private function log_info($msg)
    {
        echo "[INFO] {$msg}\n";
    }

    private function log_detail($msg)
    {
        // Only show in verbose mode or dry run
        if ($this->dry_run) {
            echo $msg . "\n";
        }
    }

    private function log_warning($msg)
    {
        echo "[WARN] {$msg}\n";
    }

    private function log_error($msg)
    {
        echo "[ERROR] {$msg}\n";
        $this->stats['errors'][] = $msg;
    }

    private function log_progress($current, $total, $entity)
    {
        $percent = $total > 0 ? round(($current / $total) * 100, 1) : 0;
        echo "  Progress: {$current}/{$total} {$entity} ({$percent}%)\n";
    }

    private function log_summary()
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║   MIGRATION SUMMARY                                           ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "Products:\n";
        echo "  Total Processed:        {$this->stats['products_processed']}\n";
        echo "  Updated:                {$this->stats['products_updated']}\n";
        echo "    - Initialized (NULL):  {$this->stats['products_initialized']}\n";
        echo "    - Data Errors Fixed:   {$this->stats['products_data_errors_fixed']}\n";
        echo "  Already Configured:     {$this->stats['products_already_configured']}\n";
        echo "\n";
        echo "Items:\n";
        echo "  Total Processed:        {$this->stats['items_processed']}\n";
        echo "  Updated:                {$this->stats['items_updated']}\n";
        echo "  Already Set:            {$this->stats['items_already_set']}\n";
        echo "  Skipped (Manual):       {$this->stats['items_skipped_manual']}\n";
        echo "\n";
        
        if (!empty($this->stats['errors'])) {
            echo "Errors: " . count($this->stats['errors']) . "\n";
            foreach ($this->stats['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
        echo "\n";
    }

    private function log_report_summary()
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║   REPORT SUMMARY                                              ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        $products = $this->report_data['products'];
        $items = $this->report_data['items'];
        
        echo "Products:\n";
        echo "  Has beauty shot + Visible:        " . count($products['has_beauty_shot_and_visible']) . " (OK)\n";
        echo "  Has beauty shot + Not Visible:    " . count($products['has_beauty_shot_not_visible']) . " (User choice)\n";
        echo "  Has beauty shot + NULL:           " . count($products['has_beauty_shot_null_visibility']) . " (WILL INITIALIZE)\n";
        echo "  No beauty shot + Visible:         " . count($products['no_beauty_shot_but_visible']) . " (DATA ERROR - WILL FIX)\n";
        echo "  No beauty shot + Not Visible:     " . count($products['no_beauty_shot_not_visible']) . " (OK)\n";
        echo "  No beauty shot + NULL:            " . count($products['no_beauty_shot_null_visibility']) . " (WILL INITIALIZE)\n";
        echo "\n";
        echo "Items:\n";
        echo "  Manual Override Active:           " . count($items['manual_override_active']) . " (WILL PRESERVE)\n";
        echo "  Auto-Calculated Visible:          " . count($items['auto_calculated_visible']) . " (OK)\n";
        echo "  Auto-Calculated Hidden:           " . count($items['auto_calculated_hidden']) . " (OK)\n";
        echo "  NULL (Needs Calculation):         " . count($items['null_needs_calculation']) . " (WILL CALCULATE)\n";
        echo "\n";
        
        $total_products_to_update = count($products['has_beauty_shot_null_visibility']) + 
                                   count($products['no_beauty_shot_but_visible']) + 
                                   count($products['no_beauty_shot_null_visibility']);
        $total_items_to_update = count($items['null_needs_calculation']);
        
        echo "Actions Required:\n";
        echo "  Products to update: {$total_products_to_update}\n";
        echo "  Items to update:    {$total_items_to_update}\n";
        echo "\n";
    }
}


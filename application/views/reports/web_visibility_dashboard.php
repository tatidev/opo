<?php
/**
 * Web Visibility Dashboard View
 * Professional admin dashboard for monitoring web visibility metrics
 */
?>
<html>
<head>
    <link rel="icon" type="image/ico" href="https://www.opuzen.com/favicon.ico">
	<?php echo asset_links($library_head) ?>
    <title><?php echo $title ?></title>
<style>
    body {
        background: #f5f7fa;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    .dashboard-header {
        background: white;
        border-bottom: 1px solid #e1e4e8;
        padding: 24px 0;
        margin-bottom: 32px;
    }
    
    .dashboard-header h1 {
        font-size: 28px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }
    
    .dashboard-header .subtitle {
        color: #6a737d;
        font-size: 14px;
        margin-top: 4px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e1e4e8;
        border-radius: 6px;
        padding: 24px;
        transition: box-shadow 0.2s;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .stat-card .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #6a737d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    
    .stat-card .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1;
        margin-bottom: 8px;
    }
    
    .stat-card .stat-change {
        font-size: 14px;
        color: #6a737d;
    }
    
    .stat-card.primary .stat-value { color: #0366d6; }
    .stat-card.success .stat-value { color: #28a745; }
    .stat-card.warning .stat-value { color: #ffc107; }
    .stat-card.secondary .stat-value { color: #6a737d; }
    
    .section-card {
        background: white;
        border: 1px solid #e1e4e8;
        border-radius: 6px;
        padding: 24px;
        margin-bottom: 24px;
    }
    
    .section-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 16px 0;
        padding-bottom: 16px;
        border-bottom: 1px solid #e1e4e8;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th {
        background: #f6f8fa;
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6a737d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e1e4e8;
    }
    
    .data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e1e4e8;
        font-size: 14px;
        color: #24292e;
    }
    
    .data-table tr:last-child td {
        border-bottom: none;
    }
    
    .data-table tr:hover {
        background: #f6f8fa;
    }
    
    .badge-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        background: #f6f8fa;
        color: #24292e;
        border: 1px solid #e1e4e8;
    }
    
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .status-dot.success { background: #28a745; }
    .status-dot.warning { background: #ffc107; }
    .status-dot.error { background: #dc3545; }
    .status-dot.info { background: #17a2b8; }
    
    .progress-bar-wrapper {
        height: 8px;
        background: #e1e4e8;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: #28a745;
        transition: width 0.3s;
    }
    
    .alert-custom {
        padding: 16px;
        border-radius: 6px;
        border: 1px solid;
        margin-bottom: 24px;
    }
    
    .alert-custom.success {
        background: #f0fdf4;
        border-color: #86efac;
        color: #166534;
    }
    
    .alert-custom.warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }
    
    .alert-custom.info {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1e40af;
    }
    
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 24px;
    }
    
    .quality-metric {
        text-align: center;
        padding: 24px;
        background: #f6f8fa;
        border-radius: 6px;
        border: 1px solid #e1e4e8;
    }
    
    .quality-metric .number {
        font-size: 48px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 8px;
    }
    
    .quality-metric .label {
        font-size: 12px;
        color: #6a737d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .quality-metric.ok .number { color: #28a745; }
    .quality-metric.warning .number { color: #ffc107; }
    .quality-metric.error .number { color: #dc3545; }
    
    .info-box {
        background: #f6f8fa;
        border-left: 4px solid #0366d6;
        padding: 16px;
        margin: 24px 0;
        border-radius: 4px;
    }
    
    .info-box h5 {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 12px 0;
    }
    
    .info-box ul {
        margin: 0;
        padding-left: 20px;
        color: #586069;
        font-size: 13px;
    }
    
    .info-box li {
        margin-bottom: 6px;
    }
</style>
</head>
<body>

<div class="full-loader hide">
    <div class="fa-3x mx-4">
        <i class="fas fa-circle-notch fa-spin"></i>
    </div>
</div>

<div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
    
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="fas fa-chart-bar" style="color: #0366d6;"></i> Web Visibility Dashboard</h1>
                <div class="subtitle">Monitor product and item showcase metrics</div>
            </div>
            <div class="col-md-6 text-right">
                <button class="btn btn-sm btn-outline-primary" onclick="window.location.reload();">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <!-- Product Statistics -->
    <div class="section-card">
        <h3>Product Statistics</h3>
        
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-label">Visible on Website</div>
                <div class="stat-value"><?php echo number_format($product_metrics['visible_on_web']); ?></div>
                <div class="stat-change">
                    <?php 
                    $pct = ($product_metrics['total_products'] > 0) ? 
                           round(($product_metrics['visible_on_web'] / $product_metrics['total_products']) * 100, 1) : 0;
                    echo $pct . '% of total products';
                    ?>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%; background: #28a745;"></div>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-label">Hidden</div>
                <div class="stat-value"><?php echo number_format($product_metrics['hidden']); ?></div>
                <div class="stat-change">
                    <?php 
                    $pct = ($product_metrics['total_products'] > 0) ? 
                           round(($product_metrics['hidden'] / $product_metrics['total_products']) * 100, 1) : 0;
                    echo $pct . '% of total products';
                    ?>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%; background: #ffc107;"></div>
                </div>
            </div>
            
            <div class="stat-card primary">
                <div class="stat-label">Not Configured</div>
                <div class="stat-value"><?php echo number_format($product_metrics['not_configured']); ?></div>
                <div class="stat-change">
                    <?php 
                    $pct = ($product_metrics['total_products'] > 0) ? 
                           round(($product_metrics['not_configured'] / $product_metrics['total_products']) * 100, 1) : 0;
                    echo $pct . '% of total products';
                    ?>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%; background: #0366d6;"></div>
                </div>
            </div>
            
            <div class="stat-card secondary">
                <div class="stat-label">Total Active Products</div>
                <div class="stat-value"><?php echo number_format($product_metrics['total_products']); ?></div>
                <div class="stat-change">All non-archived products</div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Beauty Shot Status</th>
                            <th class="text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot success"></span>
                                    Products with Beauty Shots
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($product_metrics['total_with_beauty_shot']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 32px;">
                                <span class="status-indicator">
                                    <i class="fas fa-check-circle" style="color: #28a745; width: 16px;"></i>
                                    Visible on Website
                                </span>
                            </td>
                            <td class="text-right" style="color: #28a745; font-weight: 600;"><?php echo number_format($product_metrics['visible_with_beauty_shot']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 32px;">
                                <span class="status-indicator">
                                    <i class="fas fa-eye-slash" style="color: #6a737d; width: 16px;"></i>
                                    Hidden by User
                                </span>
                            </td>
                            <td class="text-right"><?php echo number_format($product_metrics['total_with_beauty_shot'] - $product_metrics['visible_with_beauty_shot']); ?></td>
                        </tr>
                        <?php if ($product_metrics['visible_without_beauty_shot'] > 0): ?>
                        <tr style="background: #fff5f5;">
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot error"></span>
                                    Visible WITHOUT Beauty Shot
                                </span>
                            </td>
                            <td class="text-right" style="color: #dc3545; font-weight: 700;"><?php echo number_format($product_metrics['visible_without_beauty_shot']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="col-md-6">
                <div style="background: #f6f8fa; padding: 32px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 13px; color: #6a737d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px;">
                        Visibility Rate
                    </div>
                    <?php 
                    $visibility_rate = ($product_metrics['total_with_beauty_shot'] > 0) ? 
                                       round(($product_metrics['visible_with_beauty_shot'] / $product_metrics['total_with_beauty_shot']) * 100, 1) : 0;
                    ?>
                    <div style="font-size: 72px; font-weight: 700; color: #1a1a1a; line-height: 1;">
                        <?php echo $visibility_rate; ?><span style="font-size: 36px; color: #6a737d;">%</span>
                    </div>
                    <div style="font-size: 13px; color: #6a737d; margin-top: 12px;">
                        Products with beauty shots are visible
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Statistics -->
    <div class="section-card">
        <h3>Item Statistics (Colorlines)</h3>
        
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-label">Visible on Website</div>
                <div class="stat-value"><?php echo number_format($item_metrics['overall']['visible_on_web']); ?></div>
                <div class="stat-change">
                    <?php 
                    $pct = ($item_metrics['overall']['total_items'] > 0) ? 
                           round(($item_metrics['overall']['visible_on_web'] / $item_metrics['overall']['total_items']) * 100, 1) : 0;
                    echo $pct . '% of total items';
                    ?>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%; background: #28a745;"></div>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-label">Hidden</div>
                <div class="stat-value"><?php echo number_format($item_metrics['overall']['hidden']); ?></div>
                <div class="stat-change">
                    <?php 
                    $pct = ($item_metrics['overall']['total_items'] > 0) ? 
                           round(($item_metrics['overall']['hidden'] / $item_metrics['overall']['total_items']) * 100, 1) : 0;
                    echo $pct . '% of total items';
                    ?>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%; background: #ffc107;"></div>
                </div>
            </div>
            
            <div class="stat-card primary">
                <div class="stat-label">Pending Calculation</div>
                <div class="stat-value"><?php echo number_format($item_metrics['overall']['not_calculated']); ?></div>
                <div class="stat-change">
                    <?php 
                    $pct = ($item_metrics['overall']['total_items'] > 0) ? 
                           round(($item_metrics['overall']['not_calculated'] / $item_metrics['overall']['total_items']) * 100, 1) : 0;
                    echo $pct . '% will be calculated on display';
                    ?>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%; background: #0366d6;"></div>
                </div>
            </div>
            
            <div class="stat-card secondary">
                <div class="stat-label">Total Active Items</div>
                <div class="stat-value"><?php echo number_format($item_metrics['overall']['total_items']); ?></div>
                <div class="stat-change">All non-archived colorlines</div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h5 style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 16px;">Status Distribution (Visible Items)</h5>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="text-right">Count</th>
                            <th class="text-right">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($item_metrics['by_status'] as $status): ?>
                        <tr>
                            <td><span class="badge-status"><?php echo $status['status']; ?></span></td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($status['count']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php 
                                $pct = round(($status['count'] / $item_metrics['overall']['visible_on_web']) * 100, 1);
                                echo $pct . '%';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="border-top: 2px solid #e1e4e8; font-weight: 600;">
                            <td>TOTAL</td>
                            <td class="text-right"><?php echo number_format($item_metrics['overall']['visible_on_web']); ?></td>
                            <td class="text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="col-md-6">
                <h5 style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 16px;">Image Compliance</h5>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot success"></span>
                                    With Images
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600; color: #28a745;">
                                <?php echo number_format($item_metrics['image_compliance']['visible_with_images']); ?>
                            </td>
                        </tr>
                        <?php if ($item_metrics['image_compliance']['visible_without_images'] > 0): ?>
                        <tr style="background: #fff5f5;">
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot error"></span>
                                    Without Images
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 700; color: #dc3545;">
                                <?php echo number_format($item_metrics['image_compliance']['visible_without_images']); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <i class="fas fa-hand-paper" style="color: #6a737d; width: 16px;"></i>
                                    Manual Override Active
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;">
                                <?php echo number_format($item_metrics['overall']['manual_override_active']); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Data Quality -->
    <div class="section-card">
        <h3>Data Quality Analysis</h3>
        
        <?php 
        $total_issues = $data_quality['products_no_beauty_shot'] + 
                       $data_quality['items_parent_not_visible'] + 
                       $data_quality['items_no_images'];
        ?>
        
        <?php if ($total_issues === 0): ?>
            <div class="alert-custom success">
                <strong><i class="fas fa-check-circle"></i> Excellent Data Quality</strong><br>
                No data integrity issues detected. All visible products and items meet requirements.
            </div>
        <?php else: ?>
            <div class="alert-custom warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Data Quality Issues Detected</strong><br>
                <?php echo $total_issues; ?> issue(s) found that require attention.
            </div>
            
            <div class="metric-grid">
                <div class="quality-metric <?php echo $data_quality['products_no_beauty_shot'] > 0 ? 'error' : 'ok'; ?>">
                    <div class="number"><?php echo $data_quality['products_no_beauty_shot']; ?></div>
                    <div class="label">Products Visible<br>Without Beauty Shot</div>
                </div>
                
                <div class="quality-metric <?php echo $data_quality['items_parent_not_visible'] > 0 ? 'warning' : 'ok'; ?>">
                    <div class="number"><?php echo $data_quality['items_parent_not_visible']; ?></div>
                    <div class="label">Items Visible<br>Parent Not Visible</div>
                </div>
                
                <div class="quality-metric <?php echo $data_quality['items_no_images'] > 0 ? 'warning' : 'ok'; ?>">
                    <div class="number"><?php echo $data_quality['items_no_images']; ?></div>
                    <div class="label">Items Visible<br>Without Images</div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Summary Tables -->
    <div class="row">
        <div class="col-md-6">
            <div class="section-card">
                <h3>Product Summary</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Count</th>
                            <th class="text-right">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot success"></span>
                                    Visible on Website
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($product_metrics['visible_on_web']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($product_metrics['visible_on_web'] / $product_metrics['total_products']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot warning"></span>
                                    Hidden
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($product_metrics['hidden']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($product_metrics['hidden'] / $product_metrics['total_products']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot info"></span>
                                    Not Configured
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($product_metrics['not_configured']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($product_metrics['not_configured'] / $product_metrics['total_products']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr style="border-top: 2px solid #e1e4e8; font-weight: 700;">
                            <td>TOTAL</td>
                            <td class="text-right"><?php echo number_format($product_metrics['total_products']); ?></td>
                            <td class="text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="section-card">
                <h3>Item Summary</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Count</th>
                            <th class="text-right">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot success"></span>
                                    Visible on Website
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($item_metrics['overall']['visible_on_web']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($item_metrics['overall']['visible_on_web'] / $item_metrics['overall']['total_items']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot warning"></span>
                                    Hidden
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($item_metrics['overall']['hidden']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($item_metrics['overall']['hidden'] / $item_metrics['overall']['total_items']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <span class="status-dot info"></span>
                                    Pending Calculation
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($item_metrics['overall']['not_calculated']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($item_metrics['overall']['not_calculated'] / $item_metrics['overall']['total_items']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="status-indicator">
                                    <i class="fas fa-hand-paper" style="color: #6a737d; width: 16px;"></i>
                                    Manual Override
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600;"><?php echo number_format($item_metrics['overall']['manual_override_active']); ?></td>
                            <td class="text-right" style="color: #6a737d;">
                                <?php echo round(($item_metrics['overall']['manual_override_active'] / $item_metrics['overall']['total_items']) * 100, 1); ?>%
                            </td>
                        </tr>
                        <tr style="border-top: 2px solid #e1e4e8; font-weight: 700;">
                            <td>TOTAL</td>
                            <td class="text-right"><?php echo number_format($item_metrics['overall']['total_items']); ?></td>
                            <td class="text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Information Box -->
    <div class="info-box">
        <h5>Business Rules</h5>
        <ul>
            <li><strong>Product Visibility:</strong> Requires both beauty shot upload AND user approval via checkbox</li>
            <li><strong>Item Visibility:</strong> Auto-calculated when parent is visible + status is RUN/LTDQTY/RKFISH + item has images</li>
            <li><strong>Manual Override:</strong> Items with manual override enabled maintain user-specified visibility regardless of conditions</li>
            <li><strong>Lazy Calculation:</strong> Items with NULL web_vis are automatically calculated when displayed in colorline listings</li>
        </ul>
    </div>

    <!-- Migration Info -->
    <div class="section-card" style="background: #f6f8fa; border: none;">
        <h3 style="border-bottom: none; color: #6a737d;">Migration Tools</h3>
        <p style="color: #6a737d; font-size: 13px; margin-bottom: 16px;">
            Use the CLI migration script to initialize web visibility for all products and items.
        </p>
        <div style="background: #24292e; color: #e1e4e8; padding: 16px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 13px;">
            <div style="color: #6a737d;"># Generate analysis report</div>
            <div style="margin-bottom: 12px;">$ php index.php cli/migrate_web_visibility report</div>
            
            <div style="color: #6a737d;"># Test migration (no changes)</div>
            <div style="margin-bottom: 12px;">$ php index.php cli/migrate_web_visibility run --dry-run</div>
            
            <div style="color: #6a737d;"># Run actual migration</div>
            <div>$ php index.php cli/migrate_web_visibility run</div>
        </div>
    </div>

</div>

<?php echo asset_links($library_foot) ?>

</body>
</html>

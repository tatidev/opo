<?php echo $header?>

<style>


    @media print {
    
        body, html {
            margin: 0;
            padding: 0;
        }
    
        .print-content {
            margin: 0;
            padding: 0;
        }

        .hide-on-print {
            display: none;
        }
        
        .card-container {
            page-break-after: always;
        }
    
         /* Scale down the content to fit smaller with margins */
         body {
             transform: scale(0.95); 
             transform-origin: top left; 
         }
        
         .digital-cat-product-name {
            font-size: .70em;
            font-weight: bold;
        }

        .digital-book-row .spec-card {
            font-size: 65%;
        }

        span.specsheet_link {
            margin: .2em 0 0 0;
            padding: 0 0 0 0;
            display: block;
        }

    }

    .table thead th {
        border-bottom: 2px solid black !important;
        border-top: none !important;
    }
    td.col-30 {
        width: 30%;
    }
    td.col-20 {
        width: 30%;
    }
    td.col-10 {
        width: 30%;
    }
    img.img-thumbnails {
        width: 81px;
        margin: 0 5px;
    }
    .spec-col {
        /*width: 40% !important;*/
        height: 200px;
        padding-left: 40px !important;
    }
    .spec-img {
        /*width:85%!important;*/
        width: 220px;
        height: 220px;
        overflow: hidden;
    }
    a {
        text-decoration: none !important;
    }
    .digital-cat-product-name {
        font-size: 1em;
        font-weight: bold;
    }
    .digital-book-row .spec-card {
        font-size: 85%;
    }
    .digital_book-row .spec-col {
        height: auto;
        padding: 0 1em 1em;
    }
    span.specsheet_link {
        margin: .75em 0 0 0;
        padding: 0 0 0 0;
        display: block;
    }
</style>
<div id='print-content' class='hide'>

    <?php if( count($items) > 0 ){
    $N_ITEMS = count($items);
    $N_PAGES_PER_SINGLE_COLUMN = 5;
    $N_PER_PAGE = 6;
    $N_PAGES = intval($N_ITEMS / $N_PER_PAGE) + ( $N_ITEMS % $N_PER_PAGE > 0 ? 1 : 0 );
    //        var_dump($N_ITEMS, $N_PAGES); exit();
    $i = 0;
    ?>
    <div class="row mx-3">
        <div>
            <?php
                function ItemInfoHTML($item=null){
                    if(is_null($item)){
                        return <<<HTML
                        <div class="row digital_book-row">
                            <div class="col-5">
                                <div class="spec-img">
                                </div>
                            </div>
                            <div class="col spec-col">
                            </div>
                        </div>
HTML;
                    }

                    $productType = $item['product_type'];
                    $productId = $item['product_id'];
                    $productName ='<span class="digital-cat-product-name">'. $item['product_name'] . '</span>';
                    $aHref = site_url('reps/product/specsheet/'.$productType.'/'.$productId);
                    $imgHref = $item['pic_big_url'];
                    $code = (array_key_exists('code', $item) && strlen($item['code']) > 0 ? $item['code']."<br />" : '');
                    $color = (array_key_exists('color', $item) ? $item['color']."<br>" : "");
                    $width = (strlen($item['width']) > 0 ? "<b>Width:</b> ".$item['width']."<br />" : '');
                    $contentFront = (strlen($item['content_front']) > 0 ? "<b>Content:</b> ".$item['content_front']."<br />" : '');
                    $repeats = (strlen($item['repeats']) > 0 ? "<b>Repeats:</b> ".$item['repeats']."<br />" : '');
                    $abrasion = (strlen($item['abrasion']) > 0 ? "<span class='abrasion'><b>Abrasion:</b> ".$item['abrasion']."<br /></span>" : '');
                    $stock = (strlen($item['yardsAvailable']) > 0 ? "<span class='yardsAvailable '><b>Stock Available:</b> ".$item['yardsAvailable']." yard".(intval($item['yardsAvailable']) > 1 ? "s":"")."<br /></span>" : '');
                    // aplly similar log to $repears above
                    $price = (strlen($item['price']) > 0 ? "<span class='price'><b>Price:</b> $".$item['price']."<br /></span>" : '');
                    $volume_price = (strlen($item['volume_price']) > 0 ? "<span class='volume_price'><b>Volume Price:</b> $".$item['volume_price']."<br /></span>" : '');
                    $product_status = (strlen($item['product_status']) > 0 ? "<span class='product_status'><b>Product Status:</b> ".$item['product_status']."<br /></span>" : '');
                    $stock_status = (strlen($item['stock_status']) > 0 ? "<span class='stock_status hide'><b>Stock Status:</b> ".$item['stock_status']."<br /></span>" : '');
                    $use = (strlen($item['use']) > 0 ? "<span class='use '><b>Use:</b> ".$item['use']."<br /></span>" : '');

                    return <<<HTML
                        <div class="row digital-book-row">
                            <div class="col-5">
                                <div class="spec-img">
                                    <img class="img-fluid" src="{$imgHref}">
                                </div>
                            </div>
                            <div class="col spec-col">
                                <div class="spec-title">
                                    {$productName}<br>
                                    {$code}
                                    {$color}
                                </div>
                                <div class="spec-card mt-2">
                                    {$width}
                                    {$contentFront}
                                    {$repeats}
                                    {$use}
                                    {$abrasion}
                                    {$product_status}
                                    {$stock_status}
                                    {$stock}
                                    {$price}
                                    {$volume_price}
                                    <span class='specsheet_link'><a href="{$aHref}" target="_blank" >For more colors and specsheet</a></span>
                                </div>
                            </div>
                        </div>
HTML;
                }

                $page_num = 0;
                $item_ix = 0;
                $SINGLE_COL_PAGE = false;
                while($item_ix < $N_ITEMS){

                    # Is single or multi column page?
                    $N_PER_PAGE = 6;
                    $SINGLE_COL_PAGE = $page_num % $N_PAGES_PER_SINGLE_COLUMN == 0;
                    if($SINGLE_COL_PAGE){
                        $N_PER_PAGE = 3;
                    }
            ?>
                <div class="card-container d-flex flex-wrap flex-row mt-3" style="gap:35px;">
                <?php
                    $c = 0;
                    while($c < $N_PER_PAGE && $item_ix < $N_ITEMS){
                        $item = $items[$item_ix];
                    ?>
                        <div class="row w-100">
                        <?php
                            if($SINGLE_COL_PAGE){
                                ?>
                                    <div class="col-6">
                                        <?php echo ItemInfoHTML($item)?>
                                    </div>
                                    <div class="col-6">
                                        <?php echo ItemInfoHTML(null)?>
                                    </div>
                                <?php
                                $c++;
                                $item_ix++;
                            }
                            else {
                                // Column 1
                                ?>
                                    <div class="col-6">
                                        <?php echo ItemInfoHTML($item)?>
                                    </div>
                                <?php
                                $c++;
                                $item_ix++;

                                // Column 2
                                if($item_ix < $N_ITEMS){
                                    $item = $items[$item_ix];
                                    ?>
                                    <div class="col-6">
                                        <?php echo ItemInfoHTML($item)?>
                                    </div>
                                    <?php

                                }
                                else {
                                    ?>
                                    <div class="col-6">
                                        <?php echo ItemInfoHTML(null)?>
                                    </div>
                                    <?php
                                }
                                $c++;
                                $item_ix++;
                            }
                        ?>
                        </div>
                    <?php
                    }
                    $page_num++;
                ?>
                </div>
                <!-- <div class="pagebreak"></div> This breaks PDF see CSS above -->
            <?php

                }
            ?>
        </div>
    </div>
</div>

<?php } ?>

</div>
<?php echo asset_links($library_foot)?>

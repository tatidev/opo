<!-- <pre>--><?php//var_dump($showroom_data); var_dump($items); exit;?><!--</pre>-->
<?php echo $header?>

<style>
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
</style>
<div id='print-content' class='hide'>

    <!--    <div class='mx-2 mb-3'>-->
    <!--        <div class='row' style="font-family: 'EB Garamond', serif; display: flex; align-items: flex-start;">-->
    <!--            <div class='col'>-->
    <!--                <img src='https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png' class=''><br>-->
    <!--                5788 Venice Blvd.<br>-->
    <!--                Los Angeles, CA 90019<br>-->
    <!--                +1-323-549-3489 / www.opuzen.com-->
    <!--            </div>-->
    <!--            <div class='col'>-->
    <!--                <p class='float-right m-0' style="text-align:right;">-->
    <!--                    <span id="list-name"><b>--><?php//=$info['name']?><!--</b><br></span>-->
    <!--                    <span id="list-rep" class="hide">--><?php//=$showroom_data['tel']?><!--<br>--><?php//=$showroom_data['email']?><!--<br></span>-->
    <!--                    --><?php//=( $list_id != 0 ? "Last modified: " . date('m-d-Y', strtotime($info['date_modif']) ) . "<br>" : 'Print date: ' . date('m-d-Y') )?>
    <!--                    <small style='font-size:10px;'>SBO: Stocked by Opuzen / SBV: Stocked by Vendor / MBO: Manufactured by Opuzen / WTO: Weave to Order</small>-->
    <!--                </p>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->

    <?php if( count($items) > 0 ){
        $N_ITEMS = count($items);
        $N_PER_PAGE = 6;
        $N_PAGES = intval($N_ITEMS / $N_PER_PAGE) + ( $N_ITEMS % $N_PER_PAGE > 0 ? 1 : 0 );
//        var_dump($N_ITEMS, $N_PAGES); exit();
        $i = 0;
        ?>
        <div class="row mx-3">
            <div>
                    <?php
                    $page_num = 0;
                    while($page_num <= $N_PAGES){
                        ?>
                        <div class="card-container d-flex flex-wrap flex-row mt-3 <?php//=($page_num > 0?'mt-3':'')?>" style="gap:35px;<?php//=($page_num>0?'35px;':'10px;')?>">
                            <?php
                            $i = $page_num * $N_PER_PAGE;
                            $c = 0;
                            $incomplete_row = true;
                            while($c < $N_PER_PAGE){
                                if($i >= $N_ITEMS) break;
                                $item = $items[$i];

                                if($c % 2 == 0){
                                    ?>
                                        <div class="row w-100">
                                            <div class="col-6">
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="spec-img">
                                                            <img class="img-fluid" src="<?php echo $item['pic_big_url']?>">
                                                        </div>
                                                    </div>
                                                    <?php echo '(' . __LINE__. ")"__FILE__ ; ?>
                                                    <div class="col spec-col">
                                                        <div class="spec-title">
                                                            <?php echo $item['product_name']?><br>
                                                            <?php echo (array_key_exists('code', $item) && strlen($item['code']) > 0 ? $item['code']."<br />" : '')?>
                                                            <?php echo (array_key_exists('color', $item) ? $item['color']."<br>" : "")?>
                                                        </div>
                                                        <div class="spec-card mt-2">
                                                            <?php echo '(' . __LINE__. ")"__FILE__ ; ?>
                                                            <?php echo (strlen($item['width']) > 0 ? "<b>Width:</b> ".$item['width']."<br />" : '')?>
                                                            <?php echo (strlen($item['content_front']) > 0 ? "<b>Content:</b> ".$item['content_front']."<br />" : '')?>
                                                            <?php echo (strlen($item['repeats']) > 0 ? "<b>Repeats:</b> ".$item['repeats']."<br />" : '')?>
                                                            <?php echo (strlen($item['abrasion']) > 0 ? "<b>Abrasion:</b> ".$item['abrasion']."<br />" : '')?>
                                                            <br/>
                                                            <a href="<?php echo site_url('reps/product/specsheet/'.$item['product_type'].'/'.$item['product_id'])?>" target="_blank">Open specsheet</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
<!--                                        </div>-->
                                    <?php
                                    $incomplete_row = true;
                                    $i++;
                                    $c++;
                                }
                                else {
                                    ?>
<!--                                        <div class="row">-->
                                            <div class="col-6">
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="spec-img">
                                                            <img class="img-fluid" src="<?php echo $item['pic_big_url']?>">
                                                        </div>
                                                    </div>
                                                    <div class="col spec-col">
                                                    <div class="spec-title">
                                                        <?php echo $item['product_name']?><br />
                                                        <?php echo (array_key_exists('code', $item) && strlen($item['code']) > 0 ? $item['code']."<br />" : '')?>
                                                        <?php echo (array_key_exists('color', $item) ? $item['color']."<br />" : "")?>
                                                    </div>
                                                    <div class="spec-card mt-2">
                                                        <?php echo '(' . __LINE__. ")"__FILE__ ; ?>
                                                        <?php echo (strlen($item['width']) > 0 ? "<b>Width:</b> ".$item['width']."<br />" : '')?>
                                                        <?php echo (strlen($item['content_front']) > 0 ? "<b>Content:</b> ".$item['content_front']."<br />" : '')?>
                                                        <?php echo (strlen($item['repeats']) > 0 ? "<b>Repeats:</b> ".$item['repeats']."<br />" : '')?>
                                                        <?php echo (strlen($item['abrasion']) > 0 ? "<b>Abrasion:</b> ".$item['abrasion']."<br />" : '')?>
                                                        <br/>
                                                        <a href="<?php echo site_url('reps/product/specsheet/'.$item['product_type'].'/'.$item['product_id'])?>" target="_blank">Open specsheet</a>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    $incomplete_row = false;
                                    $i++;
                                    $c++;
                                }


                            }
                            // Check if we need an extra empty column
                            if($incomplete_row){
                                ?>
                                        <div class="col-6">
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="spec-img">

                                                    </div>
                                                </div>
                                                <div class="col spec-col">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            }

                            $page_num++;
                            if($i >= $N_ITEMS) break;
                            ?>
                        </div>
                        <div class="pagebreak"></div>
                        <?php
                    }?>
            </div>
        </div>

    <?php } ?>

    <!--    --><?php// if( $table['count'] > 0 ){ ?>
    <!--        <div id='items_table' class='row my-3'>-->
    <!--            <div class='col'>-->
    <!--                --><?php//=$table['html']?>
    <!--            </div>-->
    <!--        </div>-->
    <!--    --><?php// } ?>

    <!--    --><?php// if( $tableHidden['count'] > 0 ){ ?>
    <!--        <div id='items_missing_data' class='row my-3 hide'>-->
    <!--            <div class='col-12'>-->
    <!--                <h3>Items Missing Data</h3>-->
    <!--            </div>-->
    <!--            <div class='col'>-->
    <!--                --><?php//=$tableHidden['html']?>
    <!--            </div>-->
    <!--        </div>-->
    <!--    --><?php// } ?>

    <!--    <div id='items_table' class='my-3'>-->
    <!--        <table id='dt_table' class='row-border order-column hover compact' width='100%'>-->
    <!--        </table>-->
    <!--    </div>-->


</div>
<?php echo asset_links($library_foot)?>

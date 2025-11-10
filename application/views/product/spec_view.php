<?php echo asset_links($library_head)?>

<?php
// Check if the request uri contains `~web~` and echo a message
  $web_vis_check = false;
  if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '~web') !== false) {
      
      $web_vis_check = true;
  }

// echo "<pre> ". __FILE__ . " - " . __LINE__ ;
// print_r($_SERVER['REQUEST_URI']);
// echo "</pre>";

?>

<?php
	// 	echo "<pre>";
	// 	var_dump($specType);
	// 	var_dump($spec);
	// 	var_dump($info);
	// 	var_dump($colors_arr);
	// 	echo "</pre>";
	// 	exit;
	function get_product_name_font_size($name){
		$length = strlen($name);
		if( $length < 25 ) {
			$titleFontSize = 32;
		} else if ( $length >= 25 && $length <= 34 ) {
			$titleFontSize = 27;
		} else if ( $length > 34 && $length < 40 ) {
			$titleFontSize = 25;
		} else if ( $length >= 40 ) {
			$titleFontSize = 23;
		}
//		return intval($titleFontSize * 0.65);
		return 20;
	}
	function parse_name($name){
		if( strpos($name, " on ") !== false ){
			return str_replace(" on ", "<br>ON ", $name);
		}
		return $name;
	}
	$max_thumbnails = 20;
	$product_name_font_size = get_product_name_font_size($info['product_name']);
	$product_specification_subtitle_font_size = intval($product_name_font_size * 0.8);
	$info['product_name'] = parse_name($info['product_name']);
?>
<html>
<head>
    <title><?php echo $info['product_name']?> Specsheet</title>

    <style>
        /*#filters { position: fixed; top: 0; left: 0; }*/
        #btnPrint {color: white; background: rgb(149 133 121); border-radius: inherit; }
        .body { max-width: 980px!important; -webkit-print-color-adjust: exact !important; }
        /*.logo { margin: 22px 0; }*/
        /*.product-name { font-size: */<?php//=$product_name_font_size?>/*px; color: #bfac02; }*/
        .product-name { font-size: <?php echo $product_name_font_size?>px; color: black; font-weight:bold; }
        .product-subtitle { font-size: <?php echo $product_specification_subtitle_font_size?>px; }
        .beauty-shot { margin: 35px 0 25px 0; }
        .spec-list { font-size: 16px !important; margin-top: 15px;}
        .spec-name { line-height: 6px; }
        .spec-line { line-height: 6px; }
        dt { font-weight: normal!important; }
        .details { margin: 10px 0 0 0; }
        .thumbnail { border: none!important; width: 5rem!important; margin: 8px 0 8px 5px !important; }

        .my-footer { width: 980px!important; background: none!important;
            line-height: 20px!important;
            /*border-top: 1px solid #bfac02;*/
            padding: 10px 0 0 0;
            /*border-bottom: 8px solid #bfac02; */
        }
        b.title { font-weight: bold; color: #bfac02; }
        .footer-bar { text-align: center; background-color: #bfac02; color: white; padding: 6px; }

        @media print {
            .body { max-width: 980px!important; }
            .footer-print { width: 980px!important; position: fixed!important; bottom: 0!important; }
            .do-not-print { display: none!important; }
        }

        @page {
            size: A4 portrait;
        }
        span.prod-spec-name.product-name {
            font-size: 2em;
        }
    </style>
</head>
<body>




<div class='prod-spec-container container body mx-auto'>
    <div class='col-12 do-not-print' style="margin: 5px 0 20px 0;">
            <button id="btnPrint" class="btn pull-right" type="button" onclick="javascript:window.print()">
                <i class="fa fa-print" aria-hidden="true"></i> Print
            </button>
<!--        <div class='col-12'>-->
<!--            <ul id='filters' class="list-group list-group-flush">-->
<!--                <li class="list-group-item">-->
<!--                    <button id="btnPrint" class="btn pull-right" type="button" onclick="javascript:window.print()">-->
<!--                        <i class="fa fa-print" aria-hidden="true"></i> Print-->
<!--                    </button>-->
<!--                </li>-->
<!--            </ul>-->
<!--        </div>-->
    </div>
    <div class='col-12 header'>
        <div class='row header-title'>

            <div class="col d-flex justify-content-between">
                <span class="align-self-center" style="width:35%; padding: 0px 5% 0px 0px!important;">
                    <div class="d-flex flex-column">
                        <span class='prod-spec-name product-name'><?php echo strtoupper($info['product_name'])?></span>
                        <span class='product-subtitle'>Product Specification</span>
                    </div>
                </span>
                <span class="align-self-center flex-grow-1">
                    <div class="d-flex" style="margin-top: 8px;">
                        <img class='logo' src='https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png'>
                    </div>
                </span>
                <span class="align-self-center">
                    <div class="d-flex flex-column" style="font-size:1rem;">
                        <span class="align-self-end">323 549 3489</span>
                        <span class="align-self-end">info@opuzen.com</span>
                    </div>
                </span>
            </div>
<!--            <div class='col-4'>-->
<!--                <img class='logo' src='https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png'>-->
<!--            </div>-->
        </div>
		<?php if( strlen($img_url) > 0 ){ ?>
            <img class='img-fluid beauty-shot' style="width:980px;" src='<?php echo $img_url?>'>
		<?php } else { ?>
            <div class='row' style='border-bottom: 1px solid #bfac02; margin: 20px 0;'></div>
		<?php } ?>

    </div>
    <div class='col-12 details'>
        <div class='row'>
            <div class='col spec-list'>
                <dl class="row" style="line-height: 2px;">
					<?php
						$left_size = 4;
						foreach($spec as $spec_line){
                            //echo "<pre>";
                            //print_r($spec);
                            //echo "</pre>";
                            $row_style = array_key_exists("row_style", $spec_line) ? $spec_line["row_style"] : "";
							?>
                            <dt class='col-<?php echo $left_size?>' style="<?php echo $row_style?>"><?php echo strtoupper($spec_line['text'])?></dt>
                            <dd class='col-<?php echo (12-$left_size)?>' style="<?php echo $row_style?> padding-right: 5px !important">
								<?php
									foreach($spec_line['data'] as $d){
										?>
                                        <p class=''><?php echo $d?></p>
										<?php
									}
								?>
                            </dd>
							<?php
						}
					?>
                </dl>
            </div>
            <div class='col-5'>
                <div class="d-flex flex-wrap">
					<?php
						$count = 0;
						foreach($colors_arr as $item){
                            // echo "<pre>";
                            // print_r($item);
                            // echo "</pre>";
                            //if(!$item['web_vis']) continue;
                            if( $web_vis_check && isset($item['web_vis']) && !$item['web_vis'] ) continue;
							if($item['pic_big_url'] == '') continue;
							?>
                            <div class="thumbnail card">
                                <img src="<?php echo $item['pic_big_url']?>" class="card-img-top">
                                <ul class="list-group list-group-flush" style='list-style: none; line-height: 18px;'>
                                    <li class=""><?php echo $item['code']?></li>
                                    <li class=""><?php echo $item['color']?></li>
                                </ul>
                            </div>
							<?php
							$count++;
							if($count === $max_thumbnails) break;
						}
					?>
                </div>
				<?php
					if($count === $max_thumbnails){
						?>
                        <p><b class='title'>More available.<br>For a complete line please visit opuzen.com</b></p>
						<?php
					}
				?>
            </div>
        </div>
    </div>
    <!-- 	<hr style='border-color: #bfac02;'> -->
    <div class='col-12 my-footer footer-print'>
        <div class='row'>
            <div class='col-6 footer-text'>
                <p>
                    Images on this page may vary from actual colors.<br>
                    For 100% accuracy please order samples from:<br>
                    sampling@opuzen.com<br>
                    or call 323-549-3489
                </p>
            </div>
            <div class='col-6 footer-text d-flex flex-column-reverse'>
                <p class="align-self-end">
                    Print date: <?php echo date("M-d-Y")?>
<!--                    <b class='title'>HOW TO ORDER</b><br>To order samples please contact Opuzen at sampling@opuzen.com or call 323-549-3489-->
                </p>
            </div>
            <div class="col-12" style="background-clip: content-box; height:8px; background-color: #bfac02;">
            </div>
        </div>
    </div>
<!--    <div class='col-12'>-->
<!--        <div class="w-100 footer-bar"></div>-->
<!--    </div>-->
</div>

<?php echo asset_links($library_foot)?>
</body>
</html>
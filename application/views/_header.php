<!DOCTYPE html>
<html lang="en">
  
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
     <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=UA-28164702-2"></script> -->
    
    <script>
      /*
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-28164702-2');
      
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-XXXXX-Y', 'auto');
      ga('send', 'pageview');
      */
    </script>

  	<title><?php echo (ENVIRONMENT === 'development' ? "DEV " : "")?> OPMS</title>
    <!-- Insert Assets -->
    <?php echo asset_links($library_head)?>
  </head>
  
  <body class='the-background-color'>
  <div class='container-fluid'>
		
        <?php
          if (ENVIRONMENT == 'development') {
            echo "<div class='col-12 text-center sticky-top' style='background-color:black; color:white; border: 1px solid white;'>DEVELOPMENT STAGE @ ". constant('MT_SERVER') ." ". $_SERVER['SERVER_ADDR'] ." / ". $this->db->database ."</div>";
            echo "<script>
                    $(document).ready(function(){
                      $('.content-container').css('padding-top', '137px');
                    });
                  </script>";
          }
        ?>

      <?php
        if($is_showroom){
	        echo "<div class='col-12 text-center sticky-top' style='background-color:green; color:white; border: 1px solid white;'>SHOWROOM VIEW</div>";
	        echo "<script>
                    $(document).ready(function(){
                      $('.content-container').css('padding-top', '137px');
                    });
                  </script>";
        }
      ?>
		
    <script>
      var user_id = '<?php echo $user_id?>';
      var username = '<?php echo $username?>';
      
      var setCartUrl = '<?php echo site_url('cart/set_cart')?>';
      var printingCartUrl = '<?php echo site_url('cart/checkout')?>';
      
      var editProductUrl = '<?php echo $editProductUrl?>';
      var viewColorlinesUrl = '<?php echo $viewColorlinesUrl?>';
      var editItemModalUrl = '<?php echo site_url('item/edit_item')?>';
      var toggleRingsetUrl = '<?php echo site_url('item/toggle_ringset')?>';
      var toggleExportableUrl = '<?php echo site_url('item/toggle_exportable')?>';
      var multiEditItemModalUrl = '<?php echo site_url('item/edit_item_multi')?>';
      var editListUrl = '<?php echo $editListUrl?>';
			
      var howtosearchUrl = '';
      var ENVIRONMENT = '<?php echo ENVIRONMENT?>';
    </script>
    
    <div class="loader hide"></div>
    
    <div class="full-loader hide ">
      <div class="fa-3x mx-4">
        <i class="fas fa-circle-notch fa-spin"></i>
      </div>
      This may take a bit longer than expected..
    </div>
    
    <div id='nav-header'>
      
      <div class="collapse multi-collapse" id="multiCollapseExample1" style='background:black;'>
        <pre style='color:#00ff21;'>
          <?php //print_r($rawdata) ; print_r($_POST)?>
        </pre>
        <a class="btn btn-dark pull-right" data-toggle="collapse" href="#multiCollapseExample1" aria-expanded="false" aria-controls="multiCollapseExample1">^ hide</a>
      </div>

      <div id='nav-menu' class='row my-3 justify-content-center'>
        
        <div class='col text-center'>
          <img id='logo' class='img-fluid hover-pointer' style='max-width:80%;' src='<?php echo asset_url()?>images/opuzen_blackonwhite_272.png' onclick=" window.location = '<?php echo site_url()?>'; ">
        </div>
        <div class='col-9'>
          <?php
            if(ENVIRONMENT !== 'prod' && ENVIRONMENT !== 'production') {
              switch (ENVIRONMENT) {
                case 'dev':
                  $enviro_color = 'lightblue';
                  $enviro_background_color = "blue";
                  $enviro_text = "DEV";
                  break;
                case 'qa':
                  $enviro_color = 'lightgreen';
                  $enviro_background_color = "green";
                  $enviro_text = "QA";
                  break;
                default:
                  $enviro_color = 'black';
                  $enviro_background_color = "yellow";
                  $enviro_text = "LOCAL";
                  break;
              }
              $style = "background-color: $enviro_background_color; color:$enviro_color;";
              $style .= "font-weight: bold; font-family: sans-serif; font-size: 1.3em;";
              echo "<div class=\"d-flex\" style=\"$style\">
                      <span class=\"p-2\">ENV: $enviro_text </span>
                    </div>";  
            }
          ?>
          <?php
           echo $header_menu;
           echo $header_menu_mobile;
          ?>

        </div>
        <!--
        <div class='col hide'>
          
          <div class='row'>
            
            <div class='col'>
              <button class='btn'>
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                (<span id='cart_amount'>0</span>)
              </button>
            </div>
            <div class='col'>
              <div class="btn-group">
                <button type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <?php //=$username?>
                </button>
                <div class="dropdown-menu">
                  <?php // if( $username == 'admin' ) { ?>
                    <a class="dropdown-item" data-toggle="collapse" href="#multiCollapseExample1" aria-expanded="false" aria-controls="multiCollapseExample1"><i class="fas fa-code" aria-hidden="true"></i> Rawdata</a>
                  <?php // }?>
                  <a class="dropdown-item" href="<?php //=site_url('auth/change_password')?>"><i class="fas fa-key" aria-hidden="true"></i> Change password</a>
                  <a class="dropdown-item" href="<?php //=site_url('auth/logout')?>"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a>
                </div>
              </div>
            </div>
            
          </div>
          
        </div>
        -->
      </div>
      
    </div>
    
    <div id='error-alert' class='alert alert-danger error-alert w-50 mx-auto <?php echo (isset($error_msg)?'':'hide')?>'>
      <div class='d-flex justify-content-between'>
        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
        <h4>Oops!</h4>
        <i class="fas fa-times btnCloseAlert" aria-hidden="true"></i>
      </div>
      <div id='error-msg' class='my-1'>
        <?php echo (isset($error_msg)?$error_msg:'')?>
      </div>
    </div>

    <div id='nav-content'>
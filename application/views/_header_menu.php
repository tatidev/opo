<?php

?>

<!-- Main Menu -->
<nav role='navigation' class='the-background-color'>
	
	
  <ul id="main-menu" class="sm sm-blue sm-mytheme d-flex">
<?php  
  foreach($menu as $mitem){
    if( hasPermissions($permissionsList, $mitem, $user_id) ){
?>
    <li class='w-100' style='max-width:150px'>
      <a class='menu-item <?php echo $mitem['class']?> text-center' data-href="<?php echo $mitem['url']?>" <?php echo ( isset($mitem['extra']) ? $mitem['extra'] : '' )?> > <?php echo $mitem['text']?></a>
<?php
      if( !empty($mitem['sub']) ){
?>
        <ul>
<?php
          foreach ($mitem['sub'] as $subitem) {
            if( hasPermissions($permissionsList, $subitem, $user_id) && (!isset($subitem['mobileOnly']) || $subitem['mobileOnly'] == false) ){
?>
              <li><a class='menu-item-sub <?php echo ( isset($subitem['class']) ? $subitem['class'] : '')?>' data-href="<?php echo $subitem['url'];?>" <?php echo ( isset($subitem['extra']) ? $subitem['extra'] : '' )?> > <?php echo $subitem['text'];?></a>
<?php            if( !empty($subitem['sub-sub']) ) { ?>
                <ul>
<?php              foreach($subitem['sub-sub'] as $subsubitem) {?> 
                  <li>
                    <a class='menu-item-sub redir' href="#" data-id='<?php echo $subsubitem['id']?>' data-category='<?php echo $subsubitem['category']?>' data-name='<?php echo url_title( str_replace('/', ' ', $subsubitem['text']), '-', true)?>' data-contr='<?php echo $subsubitem['contr']?>'>
                      <span class='hidden-md-up'>- </span><?php echo $subsubitem['text'];?>
                    </a>
                  </li>
<?php              } 
?>
                </ul>
              </li>
<?php
              }
            }
          }
?>      </ul> 
<?php
      }
?>
    </li>        
<?php
    }
  }
?>
  </ul>
          
  <script>
    $(function() {
      $('#main-menu').smartmenus({
        showFunction: null,
        showDuration: 0,
        keepHighlighted: true,
        markCurrentTree: true,
        keepInViewport: true,
        subIndicators: true,
        subIndicatorsPos: 'append',
        subIndicatorsText: '  '
      });
    });
  </script>
</nav>

<!-- Sub Menu -->
<nav role='navigation' class='the-background-color '>
	<div class='row mt-2'>
							
            
            <div class='col'>
              <div class="btn-group">
                <a class='btnPrintMemotags btn btn-outline-primary no-border p-1' onclick="javascript: the_printing_cart.print();">
                  Print <i class="fal fa-tags" aria-hidden="true"></i>
                  (<span id='cart_amount'>0</span>)
                </a>
                <button type="button" class="btn btn-sm no-border p-1 text-dark btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="sr-only">Dropdown</span>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#" onclick="javascript: the_printing_cart.print('digital_ground');">Digital Grounds</a>
                </div>
              </div>
            </div>
						
							
	<div class='col'>
		<div class="btn-group small float-right"> 
			<button class="btn btn-outline-dark no-border p-0 ml-2 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Logged in as <?php echo $username?></button>
			<div class="dropdown-menu">
				<?php if( $username == 'admin' ) { ?>
				<a class="dropdown-item" data-toggle="collapse" href="#multiCollapseExample1" aria-expanded="false" aria-controls="multiCollapseExample1"><i class="fas fa-code" aria-hidden="true"></i> Rawdata</a>
				<?php }?>
				<a class="dropdown-item" href="<?php echo site_url('auth/change_password')?>"><i class="fas fa-key" aria-hidden="true"></i> Change password</a>
				<a class="dropdown-item" href="<?php echo site_url('auth/logout')?>"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a>
			</div>
		</div>
	</div>
            
	</div>
	<!--
  <ul id="sub-menu" class="sm sm-blue sm-mytheme invisible">
<?php  
	/*
  foreach($submenu as $mitem){
    if( hasPermissions($permissionsList, $mitem, $user_id) ){
?>
    <li>
      <a class='menu-item' href="<?php echo $mitem['url']?>" ><?php echo $mitem['text']?></a>
<?php
      if( !empty($mitem['sub']) ){
?>
        <ul>
<?php
          foreach ($mitem['sub'] as $subitem) {
            if( !isset($subitem['mobileOnly']) || $subitem['mobileOnly'] == false ){
?>
              <li><a class='menu-item-sub' href="<?php echo $subitem['url'];?>" ><?php echo $subitem['text'];?></a>
<?php            if( !empty($subitem['sub-sub']) ) { ?>
                <ul>
<?php              foreach($subitem['sub-sub'] as $subsubitem) {?> 
                  <li>
                    <a class='menu-item-sub redir' href="#"data-id='<?php echo $subsubitem['id']?>' data-category='<?php echo $subsubitem['category']?>' data-name='<?php echo url_title( str_replace('/', ' ', $subsubitem['text']), '-', true)?>' data-contr='<?php echo $subsubitem['contr']?>'>
                      <span class='hidden-md-up'>- </span><?php echo $subsubitem['text'];?>
                    </a>
                  </li>
<?php              } 
?>
                </ul>
              </li>
<?php
              }
            }
          }
?>      </ul> 
<?php
      }
?>
    </li>        
<?php
    }
  }
	*/
?>  <li>
			
		</li>
  </ul>
	-->
	
  <script>
    /*
		$(function() {
      $('#sub-menu').smartmenus({
        showFunction: null,
        showDuration: 0,
        keepHighlighted: true,
        markCurrentTree: true,
        keepInViewport: true,
        subIndicators: true,
        subIndicatorsPos: 'append',
        subIndicatorsText: '  '
      });
    });
		*/
  </script>
</nav>
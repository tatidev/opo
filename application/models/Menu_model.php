<?php

class Menu_model extends MY_Model {
  
  protected $menu;

  public function __construct(){
    parent::__construct();
  }
        
  public function get_menu($controller, $isShowroom=false){
    $activeCss = 'menu-selected-perm';
    // Create Menu
    $menu = array(
      array('text'=>'<i class="fal fa-book"></i> PRODUCTS',
            'class'=> ($controller == 'product' || $controller == 'item' ||  $controller == 'editing' ? $activeCss : '') . '',
            'url'=>site_url('product/'),
            'module'=>'product',
            'action'=>'view',
            'sub'=> array(
              array('text'=>'Create New', 'url'=>site_url('product/edit'), 'module'=>'product', 'action'=>'edit', 'sub'=>array() ),
              array('text'=>'Specs & Pricing', 'url'=>site_url(''), 'module'=>'product', 'action'=>'view', 'sub'=>array() ),
	            //array('text'=>'by Specifications', 'url'=>site_url('product/spec_list'), 'module'=>'product', 'action'=>'view', 'sub'=>array() ),
	            //array('text'=>'by Pricing', 'url'=>site_url('product/price_list'), 'module'=>'price_list', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Colorlines', 'url'=>site_url('item/index'), 'module'=>'product', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Patterns library', 'url'=>site_url('specs/style_libraries'), 'module'=>'specs', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Portfolio', 'url'=>site_url('portfolio/index'), 'module'=>'portfolio', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Editing', 'url'=>site_url('specs/index'), 'module'=>'specs', 'action'=>'edit', 'sub'=>array() ),
              array('text'=>'Calculator', 'url'=>site_url('product/calculator'), 'module'=>'', 'action'=>'', 'sub'=>array() )
            )
      ),
      
      array('text'=>'<i class="fal fa-list"></i> LISTS',
            'class'=> ($controller == 'lists' ? $activeCss : '') . '',
            'url'=>($isShowroom ? '' : site_url('lists/')),
            'module'=>'',
            'action'=>'',
            'sub' => array(
              array('text'=>'Create New', 'url'=>site_url('lists/edit'), 'module'=>'lists', 'action'=>'edit', 'sub'=>array() ),
              array('text'=>'View All', 'url'=>site_url('lists/index'), 'module'=>'lists', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Master Price List', 'url'=>site_url('lists/master'), 'module'=>'lists', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Vinyl Book',
	              'class'=>'',
	              'url' => '',
	              'extra'=>" target='_blank' href='".site_url('lists/sourcebook')."' ",
	              'module'=>'lists',
	              'action'=>'book')
            )
      ),

      array('text'=>'<i class="far fa-layer-group"></i> RESTOCKS',
	    'class'=> ($controller == 'restock' ? $activeCss : '') . '',
	    'url'=>site_url('restock/'),
	    'module'=>'restock',
	    'action'=>'view',
	    'sub'=> [
            // ['text'=>'On Order', 'url'=>site_url('restock/index'), 'module'=>'restock', 'action'=>'view', 'sub'=>[]],
            // ['text'=>'Completed', 'url'=>site_url('restock/index/completed'), 'module'=>'restock', 'action'=>'view', 'sub'=>[]]
	    ]
      ),

      /*
      array('text'=>'<i class="fas fa-briefcase"></i> ROADKITS', 
            'class'=>($controller == 'roadkit' ? $activeCss : ''),
            'url'=> '',
            'module'=>'roadkit',
            'action'=>'view',
            'sub'=>$this->submenu_roadkit()
      ),
      
      /*
      array('text'=>'<i class="fas fa-tags"></i> MEMOTAGS', 
            'class'=>($controller == 'memotag' ? $activeCss : ''),
            'url'=> '',
            'module'=>'memotag',
            'action'=>'view',
            'sub'=>$this->submenu_memotags()
      ), */
      /*array('text'=>'<i class="fas fa-warehouse"></i> STOCK',
            'class'=> " noajax ",
            'extra'=>" target='_blank' href='https://dev.opuzen.com/dev_sales2/' ",
            'url'=> '', 
            'module'=>'sales',
            'action'=>'access',
            'sub'=>array()
      ),
	*/

      array('text'=>'<i class="fal fa-address-book"></i> CONTACTS',
            'class'=> ($controller == 'contact' ? $activeCss : '') . '',
            'url'=>'',
            'module'=>'contact',
            'action'=>'view',
            'sub'=> array(
              array('text'=>'Showrooms', 'url'=>site_url('showroom'), 'module'=>'contact', 'action'=>'view', 'sub'=>array() ),
              array('text'=>'Vendors', 'url'=>site_url('vendors'), 'module'=>'contact', 'action'=>'view', 'sub'=>array() )
            )
      ),
			
      array('text'=>'<i class="fal fa-file-alt"></i> REPORTS',
            'class'=> ' noajax ',
            'extra'=>'', //" target='_blank' href='".site_url('reports/items')."' ",
            'url'=>'', //site_url('reports/items'), 
            'module'=>'reports',
            'action'=>'view',
            'sub'=> array(
              array(
	            'text'=>'ITEMS FILTERER',
	            'class'=>'',
	            'url' => '',
	            'extra'=>" target='_blank' href='".site_url('reports/items')."' ",
	            'module'=>'',
	            'action'=>''
              ),
              array(
	            'text'=>'DISCONTINUED',
	            'class'=>'',
	            'url' => '',
	            'extra'=>" target='_blank' href='".site_url('reports/disco')."' ",
	            'module'=>'',
	            'action'=>''
              ),
              array(
	            'text'=>'PRICE CHANGES',
	            'class'=>'',
	            'url' => '',
	            'extra'=>" target='_blank' href='".site_url('reports/pricing')."' ",
	            'module'=>'',
	            'action'=>''
              ),
              array(
                  'text'=>'SHELF CHANGES',
                  'class'=>'',
                  'url' => '',
                  'extra'=>" target='_blank' href='".site_url('reports/shelfs')."' ",
                  'module'=>'',
                  'action'=>''
              )
            )
      ),
      /*
      array('text'=>'<i class="far fa-images"></i> SHOWCASE',
            'class'=>($controller == 'showcase' ? $activeCss : ''),
            'url'=> '', 
            'module'=>'showcase',
            'action'=>'view',
            'sub'=>array()
      ),
      */
      array('text'=>'<i class="fal fa-users"></i> USERS',
            'class'=>($controller == 'users' ? $activeCss : '') . " noajax ",
            'extra'=>" href='".site_url('auth/index')."' ",
            'url'=>'',
            'module'=>'users',
            'action'=>'edit',
            'sub'=> array() //$this->submenu_users()
      ),
      /*
      array('text'=>'<i class="fa fa-cogs"></i> CONFIG',
            'class'=>($controller == 'config' ? $activeCss : ''),
            'url'=>site_url('config'), 
            'module'=>'config',
            'action'=>'edit',
            'sub'=>array()
      )
	*/
      
      /*array('text'=>'<i class="fal fa-question"></i> GUIDES',
            'class'=> 'bg-primary text-white',
            'url'=>'', 
            'module'=>'',
            'action'=>'',
            'sub'=> array(
              array(
	            'text'=>'FAQ',
	            'class'=>'',
	            'url' => '',
	            'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS - FAQ.pdf" ',
	            'module'=>'',
	            'action'=>''
              ),
              array(
	            'text'=>'Cheat Sheet',
	            'class'=>'',
	            'url' => '',
	            'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS - Cheat Sheet.pdf" ',
	            'module'=>'',
	            'action'=>''
              ),
              array(
	            'text'=>'User Manual',
	            'class'=>'',
	            'url' => '',
	            'extra'=>' target="_blank" href="'.base_url().'/files/guides/OPMS-User-Manual-Nov2023.pdf" ',
	            'module'=>'',
	            'action'=>''
              )
            )
      ) */
      
    );
    return $menu;
  }
}
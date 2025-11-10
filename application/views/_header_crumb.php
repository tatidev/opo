<?php $crumbs_total = count($crumbs); ?>
<div class='row <?php echo $crumbs_total==0?'hide':''?>'>
  <div class='col'>
    
    <nav aria-label="breadcrumb">
      
      <ol class="breadcrumb">
        
        <?php
        if( $crumbs_total > 0 ){
          $n = 0;
          foreach($crumbs as $c){
        ?>
            <li class="breadcrumb-item <?php echo ( $n===$crumbs_total-1 ? 'active' : '' )?>"><?php echo $c ?></li>
        <?php
            $n++;
          }
        }
        ?>
        
        <!-- Example of one with a Link
        <li class="breadcrumb-item"><a href="#">Library</a></li>
-->
        
      </ol>
    </nav>
    
  </div>
</div>
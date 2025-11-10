
<div class='text-center'>
  
  <div class='row'>
    <div class='col-xs-12'>
      <div class='section-header pull-left'>
        Hi <?php echo $user_name?>, these are your available roadkits
      </div>
    </div>
  </div>
  
  <form method="post" name="Form1">
    <?php
      foreach($rkArr as $rk){
    ?>
        <div class='roadkit-wrapper'>
          <div class='panel panel-default' onclick="redirect('<?php echo $js_controller['open_roadkit']?>', <?php echo $rk['id'];?>)">
            <div class='panel-heading'>
              <b><?php echo $rk['name']?></b><br>
            </div>
            <div class='panel-body'>
              <div class='row'>
                <div class='col-xs-6 col-sm-6 col-lg-6'>
                  <span class='badge'>
                    <?php 
                      $cc = $rk['total_items'];
                      echo $cc . " item" . ($cc > 1 ? 's' : '');
                    ?>
                  </span>
                </div>
                <div class='col-xs-6 col-sm-6 col-lg-6'>
                  <?php echo date("m/d/Y", strtotime($rk['date_created']));?>
                  <br>
                </div>
              </div>
            </div>
          </div>
        </div>
    <?php
      }
    ?>
  
    <input type="hidden" id="roadkit_id" name="roadkit_id">
  </form>  

<script>
  function redirect(controller, id){
    document.Form1.roadkit_id.value = id;
    document.Form1.action = controller;
    document.Form1.submit();
  }
</script>

</div>

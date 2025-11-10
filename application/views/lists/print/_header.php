<?php echo asset_links($library_head)?>
<style>
    .card {
        width: 100% !important;
    }
</style>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond&display=swap" rel="stylesheet">
<title>Price List - <?php echo $info['name']?></title>

<div class="full-loader text-center">
  <div class="fa-3x mx-4">
    <i class="fas fa-circle-notch fa-spin"></i>
  </div>
</div>

<div id='print_configs' class='d-flex justify-content-between'>
  <button id='btnCollapseConfig' class="btn btn-info pull-left" type="button" data-toggle="collapse" data-target="#collapseConfig" aria-expanded="false" aria-controls="collapseConfig">
    <i class="fa fa-eye" aria-hidden="true"></i>
  </button>
    <?php
        if(count($items_missing_data) > 0){
            $warningMessage = 'Be aware some items are being hidden because of missing information! Open filters on the left to see them.';
        }
    ?>
  <?php if( isset($warningMessage)){ ?>
  <u style='font-weight:bold;' class='text-warning'><?php echo $warningMessage?></u>
  <?php } ?>
  
  <button id='btnPrint' class="btn btn-success pull-right" type="button" onclick='javascript:window.print()'>
    <i class="fa fa-print" aria-hidden="true"></i>
  </button>
</div>

<div class="collapse" id="collapseConfig">
  <div class="card card-body">
    
    <div class='row'>
      
      <div class='col'>
        
        <ul id='filters' class="list-group list-group-flush">
          <?php
            $col = 0;
            // Iterate table_header to create the list of filters
            foreach($table_header as $h){ 
              $data_name = (isset($h['dataName']))? $h['dataName'] : "dummy";
              $fa_toggle_class = (isset($h['toggle_state']))? $h['toggle_state'] : '';
          ?>
              <li class="list-group-item">
                <?php echo $h['filter_name']?>
                <i class="fal <?php echo $fa_toggle_class; ?> btnToggleColumn pull-right"
                    data-col="<?php echo $col ?>"
                    data-name="<?php echo $data_name; ?>"
                    aria-hidden="true">
                </i>
                  <span class="<?php echo (isset($h['rename']) ? '' : 'hide')?>">
                      Rename column: <input type="text" class="col-rename" data-col-n="<?php echo $col?>" />
                  </span>
              </li>
          <?php 
              $col++;
              }  
          ?>
            <li class="list-group-item">
                Filter BIG PIECES only
                <i class="fa fa-toggle-off toggle-big-pieces pull-right"></i>
            </li>
            <li class="list-group-item">
                Show Rep email and tel on List header
                <i class="fa fa-toggle-off toggle-rep-data pull-right"></i>
            </li>
          <li class="list-group-item" style='color:red;'>
            Show filtered items because of missing information
            <i class="fa fa-toggle-off toggle-missing-data pull-right"></i>
          </li>  
          <li class="list-group-item">
            Font size
						<label class="radio-inline"><input type="radio" name="chkFontSize" value='16' checked>16px</label>
						<label class="radio-inline"><input type="radio" name="chkFontSize" value='17'>17px</label>
						<label class="radio-inline"><input type="radio" name="chkFontSize" value='18'>18px</label>
						<label class="radio-inline"><input type="radio" name="chkFontSize" value='19'>19px</label>
						<label class="radio-inline"><input type="radio" name="chkFontSize" value='20'>20px</label>
          </li>
        </ul>
        
      </div>
      
    </div>
    
  </div>
</div>

<script>
  $(document).ready(function(){
    
    $('div.full-loader').addClass('hide');
    $('#print-content').removeClass('hide');
    
    function toggle_classes(obj, classes_to_toggle=[]){
      $.each( classes_to_toggle, function(index, value){
          obj.toggleClass(value);
        }
      );
    }
    
    $('#filters').on('click', '.btnToggleColumn', function(){
      var column_to_toggle = $(this).attr('data-col');
      var data_name = $(this).attr('data-name');
      var visible = ( $(this).hasClass('fa-toggle-on') ? true : false );
      toggle_classes( $(this), ['fa-toggle-on', 'fa-toggle-off']);

      $('table').children().children().each(function(index, element){
        // Iterate through each table row
        var n = 0;
        $(this).children().each(function(index, element){
          if( n === parseInt(column_to_toggle) ){
            $(this).css('display', (visible ? 'none' : 'table-cell') );
          }
          n++;
        });

      });

      $('.'+ data_name).toggleClass('hide');
    })
    
    $('#filters').on('click', '.under30', function(){
      toggle_classes( $(this), ['fa-toggle-on', 'fa-toggle-off']);
      $.each( $('td.is_30under'), function(){
        $(this).parent().toggleClass('row-30under');
        $(this).children('span').toggleClass('hide');
      } );
    })
    
    $('#filters').on('click', '.toggle-missing-data', function(){
      toggle_classes( $(this), ['fa-toggle-on', 'fa-toggle-off']);
      $('#items_table').toggleClass('hide');
      $('#items_missing_data').toggleClass('hide');
    })

      $('#filters').on('click', '.toggle-rep-data', function(){
          toggle_classes( $(this), ['fa-toggle-on', 'fa-toggle-off']);
          $('#list-rep').toggleClass('hide');
      })

      $('#filters').on('click', '.toggle-big-pieces', function(){
          toggle_classes( $(this), ['fa-toggle-on', 'fa-toggle-off']);
          const isActive = $(this).hasClass('fa-toggle-on');
          console.log($("div#items_table").find("tbody").find("tr:has(:not(.big-piece))"));
          console.log($("div#items_table").find("tbody").find("tr:has(.big-piece)"));
          if(isActive){
              $("div#items_table").find("tr:has(.not-big-piece)").each((ix, el) => {
                  $(el).addClass("hide");
              });
          }
          else {
              $("div#items_table").find("tr:has(.not-big-piece)").each((ix, el) => {
                  $(el).removeClass("hide");
              });
          }
      })
    
    $('.outdoor').each(function(index, value){
      $(this).parent().addClass('row-outdoor');
    });
    $('.is_30under').each(function(index, value){
      $(this).parent().addClass('row-30under');
    });
    
		$('#filters').on('click', "input[type='radio'][name='chkFontSize']", function(){
			var size = $(this).val();
			$('table.table').css('font-size', size+"px");
		})

      $("input.col-rename").on('change', function(){
          const col_n = $(this).attr('data-col-n');
          const new_name = $(this).val();
          console.log(col_n, new_name)
          $('table.table-price-list > thead').first().children("tr").first().children("th").eq(col_n).html(new_name);
      })
  })

</script>


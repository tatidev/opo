<!-- <pre>--><?php//var_dump($showroom_data); exit;?><!--</pre>-->
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
</style>
<div id='print-content' class='hide'>
  
  <div class='mx-2'>

    <div class='row' style="font-family: 'EB Garamond', serif; display: flex; align-items: flex-start;">
      <div class='col'>
        <img src='https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png' class=''><br>
        5788 Venice Blvd.<br>
        Los Angeles, CA 90019<br>
        +1-323-549-3489 / www.opuzen.com
      </div>
      <div class='col'>
        <p class='float-right m-0' style="text-align:right;">
          <span id="list-name"><b><?php echo $info['name']?></b><br></span>
            <span id="list-rep" class="hide"><?php echo $showroom_data['tel']?><br><?php echo $showroom_data['email']?><br></span>
          <?php echo ( $list_id != 0 ? "Last modified: " . date('m-d-Y', strtotime($info['date_modif']) ) . "<br>" : 'Print date: ' . date('m-d-Y') )?>
            <small style='font-size:10px;'>SBO: Stocked by Opuzen / SBV: Stocked by Vendor / MBO: Manufactured by Opuzen / WTO: Weave to Order</small>
        </p>
      </div>
    </div>

<!--    <div class='row'>-->
<!--      <div class='col'>-->
<!--        <p class='float-right m-0' style='font-size:12px;'>-->
<!--          -->
<!--        </p>-->
<!--      </div>-->
<!--    </div>-->

  </div>

  <?php if( $table['count'] > 0 ){ ?>
  <div id='items_table' class='row my-3'>
    <div class='col'>
      <?php echo $table['html']?>
    </div>
  </div>
  <?php } ?>

  <?php if( $tableHidden['count'] > 0 ){ ?>
  <div id='items_missing_data' class='row my-3 hide'>
    <div class='col-12'>
      <h3>Items Missing Data</h3>
    </div>
    <div class='col'>
      <?php echo $tableHidden['html']?>
    </div>
  </div>
  <?php } ?>
  
  <div id='items_table' class='my-3'>
    <table id='dt_table' class='row-border order-column hover compact' width='100%'>
    </table>
  </div>


  
</div>
<?php echo asset_links($library_foot)?>
<!-- <script>
  
  var this_table;
  var group_by = '<?php//=$group_by?>';
  
$(document).ready(function() {
  
  this_table = $('#dt_table').DataTable({
    'dom': 'Btipr',
    'data': <?php//=$itemsjson?>,
    'paging': false,
    'columns': [
      { 'data': 'product_name', 'title': 'Product name' },
      { 'data': 'color', 'title': 'Color', 'visible': group_by == 'item', 'defaultContent': '' },
      { 'data': 'code', 'title': 'Item #', 'visible': group_by == 'item', 'defaultContent': '' },
      { 'data': 'stock_status', 'title': 'Stock Status' },
      { 'data': 'p_res_cut', 'title': 'Res Net/Yard',
        "render": function ( data, type, row, meta ) {
          return '$ ' + row.p_res_cut;
        }
      },
      { 'data': 'p_hosp_cut', 'title': 'Hosp Cut/Yard',
        "render": function ( data, type, row, meta ) {
          return '$ ' + row.p_hosp_cut;
        }
      },
      { 'data': 'p_hosp_roll', 'title': 'Hosp Roll/Yard',
        "render": function ( data, type, row, meta ) {
          return '$ ' + row.p_hosp_roll;
        }
      },
      { 'data': 'width', 'title': 'Width', 'visible': group_by == 'product' },
      { 'data': 'content_front', 'title': 'Content', 'visible': group_by == 'product' }
    ],
    'buttons': [{
        extend: 'colvis'
      }
    ]
  });
  
});

</script> -->
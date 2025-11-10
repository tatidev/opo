<html>
  <head>
    <link rel="icon" type="image/ico" href="https://www.opuzen.com/favicon.ico">
    <?php echo asset_links($library_head)?>
    <style>
      @media print {
        #filtersCollapse, #frmFilters, .input-group, .row-1, .row-2.d-flex { display: none!important;  }
        @page {size: landscape}
      }
      .filter-group { width:33%!important; margin:0.5rem 0rem !important;}
      input[type="number"] {width: 20%;}
      .h-10 { height: 10%!important;}
    </style>
    <title><?php echo $title?></title>
  </head>
  <body class='container-fluid'>
    <div class="full-loader hide ">
      <div class="fa-3x mx-4">
        <i class="fas fa-circle-notch fa-spin"></i>
      </div>
    </div>
    <?php  
      if( isset($filters) && !empty($filters) ){
    ?>
    <div class="collapse" id="filtersCollapse">
    <h3>Filters</h3>
    <form id='frmFilters'>
      
      <div id='filter_row' class='row d-flex flex-row mx-auto'>

    <?php
        foreach($filters as $f){
    ?>
        <div class='filter-group <?php echo ( isset($f['row_class']) ? $f['row_class'] : '' )?>'>
          <div class='row'>
              <label for="<?php echo url_title( $f['field_name'] )?>" class=" <?php echo ( isset($f['field_class']) ? $f['field_class'] : " col-xs-12 col-sm-4 col-form-label " )?> " tabindex="-1"><?php echo $f['field_name']?></label>
              <div class=" <?php echo ( isset($f['input_class']) ? $f['input_class'] : " col-xs-12 col-sm-8 px-4 " )?> ">
                <?php echo $f['input']?>
              </div>
          </div>
        </div>
    <?php
        }
    ?>

        <div class='col-12'>
          <a id='btnUpdateResults' class='btn btn-outline-success float-right'>Update Results</a>
          <a id='btnClearFilters' class='btn btn-outline-warning float-left hide'>Clear filters</a>
        </div>
      </div>
      <hr>

    </form>
    </div>
    <?php  
      } 
    ?>
    
    <table id='result' class='row-border order-column hover compact' width='100%'></table>
    
  </body>
  <script>
    var mtable_id = "table#result";
    var ajaxUrl = "<?php echo $ajaxUrl?>";
    var environment = '<?php echo ENVIRONMENT?>';
    var stamps = JSON.parse('<?php echo json_encode($stamps)?>');
    
    $(document).ready(function(){
        $("#filtersCollapse").collapse('toggle');
      this_table = $(mtable_id)
        .DataTable({
          dom: '< <"row-1 input-group" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"row-2 d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <i p> <t> i p >',
          "rowId": "item_id",
          "language": {
            "decimal": "",
            "emptyTable": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Show _MENU_ entries",
            "loadingRecords": "Loading...",
            "processing": "Processing...",
            "search": "",
            "zeroRecords": "No matching records found",
            "paginate": {
              "first": "First",
              "last": "Last",
              "next": "Next",
              "previous": "Previous"
            },
            "aria": {
              "sortAscending": ": activate to sort column ascending",
              "sortDescending": ": activate to sort column descending"
            }
          },
          "columns": [{
              "title": "Vendor",
              "data": "vendor",
              "defaultContent": "",
              "searchable": true,
              "orderable": true,
              "className": ''
            },
            {
              "title": "Shelf",
              "data": "shelf",
              "defaultContent": "",
              "visible": true,
              "searchable": true,
              "orderable": true,
              "className": ''
            },
            {
              "title": "Status",
              "data": "status",
              "defaultContent": "",
              "searchable": true,
              "render": function(data, type, row, meta) {
                return "<span data-toggle='tooltip' data-trigger='hover' data-title='" + row.status_descr + "' data-placement='top'>" + row.status + "</span>";
              }
            },
            {
              "title": "Stock Status",
              "data": "stock_status",
              "defaultContent": "",
              "searchable": true,
              "render": function(data, type, row, meta) {
                //return row.stock_status;
                return "<span data-toggle='tooltip' data-trigger='hover' data-title='" + row.stock_status_descr + "' data-placement='top'>" + row.stock_status + "</span>";
              }
            },
            {
              "title": "",
              "data": "btnInRingset",
              "defaultContent": "",
              "searchable": false,
              "orderable": false,
              "className": 'no-export noVis',
              "render": function(data, type, row, meta) {
                //console.log(row);
                return "<i class='fab fa-gg-circle rs-icon  " + (row.in_ringset === '1' ? ' rs-active ' : '') + " '></i>";
              }
            },
            {
              "title": "Product Name",
              "data": "product_name"
            },
              {
                  "title": "Code",
                  "data": "code"
              },
              {
                  "title": "Color",
                  "data": "color"
              },
            {
              "title": "",
              "data": "stamps",
              "searchable": false,
              "defaultContent": "",
              "orderable": false,
              "className": "noVis",
              "render": function(data, type, row, meta) {
                var add = '';
                switch (row.product_type) {
                  case 'R':
                    if (stamps.under30_ids.indexOf(row.item_id) >= 0) add += " <span class='is_30under' data-toggle='tooltip'data-title='$30 & Under'>$30</span>";
                    if (stamps.digital_ground_ids.indexOf(row.item_id) >= 0) add += " <span class='is_digitalground' data-toggle='tooltip' data-title='Digital Ground'>DG</span>";
                    break;
                }
                return add;
              }
            },
            {
                "title": "Date",
                "data": "change_date",
                "defaultContent": "",
                "visible": true,
                "searchable": true,
                "orderable": true,
            },
            {
                "title": "By",
                "data": "change_by",
                "defaultContent": "",
                "visible": true,
                "searchable": true,
                "orderable": true,
            },
            {
                "title": "Action",
                "data": "change_type",
                "defaultContent": "",
                "visible": true,
                "searchable": true,
                "orderable": true,
            }
          ],
          "order": [
            [0, "desc"]
          ],
          "buttons": [
              {
              extend: '',
              text: '<i class="fal fa-filter"></i> Show/Hide Filters',
              className: 'btn btn-outline-danger no-border',
              action: function(e, dt, node, config) {
                //           <a id='btnFiltersCollapse' data-toggle="collapse" href="#filtersCollapse">
                $("#filtersCollapse").collapse('toggle');
              }
            },
            custom_buttons.view(),
            custom_buttons.export()
          ]
        });
      
      $("a#btnClearFilters").on('click', function(){
        
      })
                                 
      $("a#btnUpdateResults").on("click", function() {

          date_from = $("input[name='date_from']")[0].value;
          date_to = $("input[name='date_to']")[0].value;
          validDates = date_from != '' && date_to != '';

        if( !validDates ){
            show_success_swal("Please enter some dates.", "warning");
            return;
        } else {

            date_from = new Date(date_from);
            date_to = new Date(date_to);
            date_diff = DateDiff.inDays(date_from, date_to);
            console.log(date_from);
            console.log(date_to);
            console.log(date_diff);

            // If no selection is made, restrict to 3 months report
            date_diff_thres = 365;
            isSelectAll = ($("select[name='vendor_id[]']").val().length == 0) & ($("select[name='shelf_id[]']").val().length == 0);
            if(isSelectAll){
                // date_diff_thres = 190;
                date_diff_thres = 365;
            }

            if(date_diff > date_diff_thres){
                show_success_swal("Valid range cannot exceed "+date_diff_thres+" days.", "warning");
                return;
            }
        }

        if (is_long_request()) {
          show_swal({
            f: function() {
              update_results(
                function() {
                  $(".full-loader").addClass('hide')
                }
              )
            }
          }, {
            title: "Are you sure you want to continue?",
            text: "This report could take longer than expected.",
            icon: 'info'
          }, {
            complete: function(obj) {
              environment !== 'development' ? $(".full-loader").removeClass('hide') : '';
              obj.f()
            }
          });
        } else {
          environment !== 'development' ? $(".full-loader").removeClass('hide') : '';
          update_results(function() {
            $(".full-loader").addClass('hide')
          })
        }
      })

      function is_long_request() {
        var ser = $("#frmFilters").serialize();
        var long_waits = [
          "stock_min=&stock_max=&web_visible=none&include_digital=Y",
          "stock_min=&stock_max=&web_visible=none&include_digital=N"
        ];
        return long_waits.indexOf(ser) >= 0 || ser.indexOf('shelf_id%5B%5D=none') >= 0;
      }

      function update_results(f) {
        $.post(ajaxUrl, $("#frmFilters").serialize(), function(data) {
          this_table.clear();
          this_table.search('');
          this_table.rows.add(data)
            .draw();
          f();
        }, 'json')
      }

    })
  </script>
  <footer>
    <?php echo asset_links($library_foot)?>
  </footer>
</html>
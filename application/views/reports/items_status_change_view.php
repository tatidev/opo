<html>
<head>
	<link rel="icon" type="image/ico" href="https://www.opuzen.com/favicon.ico">
	<?php echo asset_links($library_head) ?>
	<style>
        @media print {
            #filtersCollapse, #frmFilters, .input-group, .row-1, .row-2.d-flex {
                display: none !important;
            }

            @page {
                size: landscape
            }
        }

        .filter-group {
            width: 33% !important;
            margin: 0.5rem 0rem !important;
        }

        input[type="number"] {
            width: 20%;
        }

        .h-10 {
            height: 10% !important;
        }
	</style>
	<title><?php echo  $title ?></title>
</head>
<body class='container-fluid'>
<div class="full-loader hide ">
	<div class="fa-3x mx-4">
		<i class="fas fa-circle-notch fa-spin"></i>
	</div>
</div>

<?php if (isset($filters) && !empty($filters)) { ?>
	<div class="collapse" id="filtersCollapse">
        <h1><?php echo $title ?></h1>
		<h3>Filters</h3>
        
		<form id='frmFilters'>

			<div id='filter_row' class='row d-flex flex-row mx-auto'>

				<?php foreach ($filters as $f) { ?>
					<div class='filter-group <?php echo ( isset($f['row_class']) ? $f['row_class'] : '' )?>'>
						<div class='row'>
							<label for="<?php echo  url_title($f['field_name']) ?>" class="<?php echo ( isset($f['field_class']) ? $f['field_class'] : " col-xs-12 col-sm-4 col-form-label " )?>"
							       tabindex="-1"><?php echo  $f['field_name'] ?></label>
							<div class="<?php echo ( isset($f['input_class']) ? $f['input_class'] : " col-xs-12 col-sm-8 px-4 " )?>">
								<?php echo  $f['input'] ?>
							</div>
						</div>
					</div>
				<?php } ?>

			</div>
			<div class='row'>
				<div class=' filter-group col-6'>
					<div class='row'>
						<label for="print-title" class="col-xs-12 col-sm-4 col-form-label" tabindex="-1">Print page
							title</label>
						<div class="col-xs-12 col-sm-8 px-4">
							<input type='text' id='print-title' name='print-title' value=''>
						</div>
					</div>
				</div>
				<div class='col-6'>
					<a id='btnUpdateResults' class='btn btn-outline-success float-right'>Update Results</a>
					<a id='btnClearFilters' class='btn btn-outline-warning float-left hide'>Clear filters</a>
				</div>
			</div>

			<hr>
		</form>
	</div>
<?php } ?>

<table id='result' class='row-border order-column hover compact' width='100%'></table>

</body>
<script>
    var mtable_id = "table#result";
    var ajaxUrl = "<?php echo $ajaxUrl?>";
    var environment = '<?php echo ENVIRONMENT?>';
    var stamps = JSON.parse('<?php echo json_encode($stamps)?>');

    $(document).ready(function () {
        $("#filtersCollapse").collapse('toggle');
        this_table = $(mtable_id)
            .DataTable({
                dom: '< <"row-1 input-group h-10" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"row-2 d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" l> > <"items-filter"> <i p> <t> i p >',
                //           dom: '< <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <i p> <t> i p >',
                //           'serverSide': true,
                //           "processing": true,
                //           "ajax": {
                //             "url": ajaxUrl,
                //             "type": "POST",
                //             "dataSrc": "tableData"
                //           },
                //           "pageLength": 50,
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
                "columns": [
                    {
                        "title": "Product Name",
                        "data": "product_name"
                    },
                    {
                        "title": "Date Modified",
                        "data": "ModifiedOn",
                        "render": function (data, type, row, meta) {
                            if (data) {
                                const date = new Date(data);
                                return date.toISOString().split('T')[0]; // Formats to 'YYYY-MM-DD'
                            }
                            return ""; // Handle empty or null dates
                        }
                    },
                    {
                        "title": "Vendor",
                        "data": "vendor",
                        "defaultContent": "",
                        "searchable": false,
                        "orderable": true,
                        "className": ''
                    },
                    {
                        "title": "Prod Status",
                        "data": "status",
                        "defaultContent": "",
                        "searchable": false,
                        "render": function (data, type, row, meta) {
                            return "<span data-toggle='tooltip' data-trigger='hover' data-title='" + row.status_descr + "' data-placement='top'>" + row.status + "</span>";
                        }
                    },
                    {
                        "title": "Stock Status",
                        "data": "stock_status",
                        "defaultContent": "",
                        "searchable": false,
                        "render": function (data, type, row, meta) {
                            return "<span data-toggle='tooltip' data-trigger='hover'  data-placement='top'>" + row.stock_status + "</span>";
                        }
                    },
                    {
                        "title": "Item #",
                        "data": "code",
                        "defaultContent": "",
                    },
                    {
                        "title": "Color",
                        "data": "color",
                        "defaultContent": "",
                    },
                ],
                "order": [
                    [0, "desc"]
                ],
                "buttons": [{
                    extend: '',
                    text: '<i class="fal fa-filter"></i> Show/Hide Filters',
                    className: 'btn btn-outline-danger no-border',
                    action: function (e, dt, node, config) {
                        //           <a id='btnFiltersCollapse' data-toggle="collapse" href="#filtersCollapse">
                        $("#filtersCollapse").collapse('toggle');
                    }
                },
                    custom_buttons.view(),
                    custom_buttons.export({
                        title: function () {
                            var ptitle = $('#print-title').val();
                            if (ptitle.length > 0) {
                                return ptitle;
                            }
                            return 'Report';
//                 return $('#print-title').val();
                        },
                        messageTop: function () {
                            return $('.items-filter').html();
                        }
                    })
                ]
            });

        $("a#btnClearFilters").on('click', function () {

        })

        $("a#btnUpdateResults").on("click", function () {
            //           console.log( $("#frmFilters").serialize(), is_long_request() ); return;
            if (is_long_request()) {
                show_swal({
                    f: function () {
                        update_results(
                            function () {
                                $(".full-loader").addClass('hide')
                            }
                        )
                    }
                }, {
                    title: "Are you sure you want to continue?",
                    text: "This report could take longer than expected.",
                    icon: 'info'
                }, {
                    complete: function (obj) {
                        environment !== 'development' ? $(".full-loader").removeClass('hide') : '';
                        obj.f()
                    }
                });
            } else {
                environment !== 'development' ? $(".full-loader").removeClass('hide') : '';
                update_results(function () {
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
            $.post(ajaxUrl, $("#frmFilters").serialize(), function (data) {
                stamps = data.stamps;
                this_table.clear();
                this_table.search('');
                this_table.rows.add(data.tableData)
                    .draw();
                $('.items-filter').html(get_filters_summary());
                f();
            }, 'json')
        }

        function get_filters_summary() {
            var elem, tagName, selection, temp = '';
            var _return = [];
            for (var i = 0; i < $('.input-filter').length; i++) {
                temp = '';
                elem = $($('.input-filter')[i]);
                tagName = elem.prop('tagName');
                if (tagName === 'SELECT') {
                    selection = get_dropdown_selected_text("[name='" + elem.prop('name') + "']");
                    if (selection !== undefined) {
                        temp = elem.prop('name') + ':' + selection;
                    }
                } else if (tagName === 'INPUT' && elem.val().length > 0) {
                    temp = elem.prop('name') + ':' + elem.val();
                }

                if (temp.length > 0) {
                    _return.push(temp); //+ elem.val() + ':' + elem.prop('tagName');
                }
            }
            //                 console.log(_return.join(' / '));
            return _return.join(' / ');
        }

    })
</script>
<footer>
	<?php echo  asset_links($library_foot) ?>
</footer>
</html>
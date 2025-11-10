<!-- <div class='row my-4 hide'>
  <div class='input-group col-12 px-0' style='border-bottom: 1px dotted #bfac02; box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;'>
		<div class="input-group-prepend">
			<span class="input-group-text" style='background-color: transparent; border: none; font-size: 30px;'><i class="fas fa-search"></i></span>
		</div>
    <input id="input_search" type="text" placeholder="Search product or vendor name" class="form-control input_search" value="">
  </div>
</div> -->

<table id='lists_table' class='row-border order-column hover compact' width='100%'></table>

<script>
    var mtable_id = "#lists_table";
    var this_table;
    var ajaxUrl = '<?=$ajaxUrl?>';
    var printUrl = '<?=$printUrl?>';
    var hasEditPermission = '<?=$hasEditPermission?>' === '1';

    $(document).ready(function () {

        this_table = $(mtable_id)
            .on('preXhr.dt', function (e, settings, data) {
                if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
            })
            .DataTable({
                dom: '< <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
                'serverSide': true,
                "processing": true,
                "ajax": {
                    "url": ajaxUrl,
                    "type": "POST",
                    "dataSrc": "tableData"
                },
                "pageLength": 50,
                "language": {
                    "searchPlaceholder": "Search list name or category"
                },
                "columns": [
                    {
                        "title": '', "data": "btnEdit", "searchable": false, "orderable": false,
                        "render": function (data, type, row, meta) {
                            return (row.id !== '0' ? "<i class='fas fa-pen-square btn-action btnEditList' data-toggle='tooltip' data-trigger='hover' data-title='Edit List' data-placement='top' data-lid='" + row.id + "' ></i>" : '');
                        }
                    },
                    {"title": "Name", "data": "list_name"},
                    {"title": "Categories", "data": "category"},
                    {"title": "Showrooms", "data": "showrooms"},
                    {
                        "title": "Patterns (colors)", "data": "total_items", "searchable": false,
                        "render": function (data, type, row, meta) {
                            return (row.id !== '0' ? row.total_products + " (" + row.total_items + ")" : '');
                        }
                    },
                    {
                        "title": "Created on", "data": "date_add", "searchable": false,
                        "render": function (data, type, row, meta) {
                            return (row.id !== '0' ? row.date_add : '');
                        }
                    },
                    {
                        "title": "Last Modif.", "data": "date_modif", "searchable": false, "visible": false,
                        "render": function (data, type, row, meta) {
                            return (row.id !== '0' ? row.date_modif : '');
                        }
                    },
                    {
                        "title": "Print",
                        "data": "btnActions",
                        "searchable": false,
                        "orderable": false,
                        'className': 'd-flex justify-content-around',
                        "render": function (data, type, row, meta) {
                            // console.log(row.id, typeof (row.id));
                            var master_price_list_id = '0';
                            var txt = '';
                            txt += "<a href='" + printUrl + "/" + row.id + "/product' target='_blank' data-toggle='tooltip' data-trigger='hover' data-title='Print by Products' data-placement='top' ><i class='fas fa-print'></i></a>";
                            txt += "  <a href='" + printUrl + "/" + row.id + "/item' target='_blank' data-toggle='tooltip' data-trigger='hover' data-title='Print by Colors' data-placement='top'><i class='fas fa-print'></i></a>";
                            if (row.id !== master_price_list_id && parseInt(row.total_items) <= 1000) {
                                txt += "  <a id='addListToPrinter' data-lid='" + row.id + "' href='#' data-toggle='tooltip' data-trigger='hover' data-title='Add to memotag printer' data-placement='top'><i class='fas fa-tags'></i></a>";
                            } else {
                                txt += "<span></span>";
                            }
                            return txt;
                        }
                    }
                ],
                "order": [[5, "desc"]],
                "buttons": [
                    custom_buttons.back(),
                    custom_buttons.new('<?=site_url('lists/edit')?>'),
                    custom_buttons.view([0]),
                    custom_buttons.export()
                ]
            });


        $('#lists_table_wrapper').on('click', 'a#addListToPrinter', function () {
            // First get all items in this list
            var list_id = $(this).attr('data-lid');
            $.ajax({
                //async: false,
                method: "POST",
                url: "<?=site_url('lists/get_lists_items_for_memotag_printer')?>",
                dataType: 'json',
                data: {
                    'list_id': list_id
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                success: function (data, msg) {
                    // Thenn add them to the printer
                    $.each(data, function (index, value) {
                        the_printing_cart.add_item(value.product_type, value.product_id, value.item_id, 1);
                    });
                    show_success_swal('top');
                }
            });
        });

    });
</script>
var ti = jQuery.now();

//
// Datatables Custom General Buttons
//
var custom_buttons = {

    back: function (ft) {
        if (typeof (ft) === 'function') {
            return {
                extend: '',
                text: '<i class="far fa-arrow-alt-circle-left "></i> Back',
                className: 'btn btn-outline-info no-border',
                action: function (e, dt, node, config) {
                    ft();
                }
            };
        } else {
            return {
                extend: '',
                text: '<i class="far fa-arrow-alt-circle-left "></i> Back',
                className: 'btn btn-outline-info btnBack no-border'
            };
        }
    },

    view: function (cols_do_not_show) {
        var t = '';
        $.each(cols_do_not_show, function (index, value) {
            t += ':gt(' + value + ')';
        });
        return {
            extend: 'colvis',
            text: '<i class="fa fa-eye"></i> View <i class="fas fa-caret-down"></i>',
            className: 'btn btn-outline-primary no-border',
            columns: ':not(.noVis)' + t
            //columns: t
        };
    },

    new: function (given_url) {
        if (typeof (given_url) === 'string') {
            return {
                text: '<i class="fas fa-plus-circle"></i> New',
                className: 'btn btn-outline-success no-border btnNew ' + (typeof (hasEditPermission) !== 'undefined' && !hasEditPermission ? ' hide ' : ''),
                action: function (e, dt, node, config) {
                    get_ajax_view(given_url);
                }
            }
        } else if (typeof (given_url) === 'function') {
            return {
                text: '<i class="fas fa-plus-circle"></i> New',
                className: 'btn btn-outline-success no-border btnNew ' + (typeof (hasEditPermission) !== 'undefined' && !hasEditPermission ? ' hide ' : ''),
                action: function (e, dt, node, config) {
                    given_url();
                }
            }
        }

    },

    export: function (options = {}) {
        var defaults = {
            withSelection: false,
            title: document.title,
            autoClose: true,
            messageTop: '',
            extraButtons: [],
            printOrientation: 'landscape',
            replacers: {
                'print': function () {
                    var _title = settings.title;
                    var _messageTop = settings.messageTop;
                    var _printOrientation = settings.printOrientation;
                    return {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Raw Print',
                        title: _title,
                        autoClose: true,
                        messageTop: _messageTop,
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        },
                        customize: function (win) {
                            var last = null;
                            var current = null;
                            var bod = [];

                            var css = '@page { size: ' + _printOrientation + '; }',
                                head = win.document.head || win.document.getElementsByTagName('head')[0],
                                style = win.document.createElement('style');

                            style.type = 'text/css';
                            style.media = 'print';

                            if (style.styleSheet) {
                                style.styleSheet.cssText = css;
                            } else {
                                style.appendChild(win.document.createTextNode(css));
                            }
                            head.appendChild(style);
                        }
                    }
                },
                'excel': function () {
                    var _title = settings.title;
                    var _messageTop = settings.messageTop;
                    return {
                        extend: 'excel',
                        text: '<i class="far fa-file-excel"></i> Excel',
                        title: _title,
                        autoClose: true,
                        messageTop: _messageTop,
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        }
                    }
                },
                'pdf': function () {
                    var _title = settings.title;
                    var _messageTop = settings.messageTop;
                    return {
                        extend: 'pdf',
                        //download: 'open',
                        text: '<i class="far fa-file-pdf"></i> PDF',
                        title: _title,
                        autoClose: true,
                        messageTop: _messageTop,
                        exportOptions: {
                            columns: ':visible:not(.no-export)'
                        }
                    }
                }
            },
            exportOptions: {
                columns: ':visible:not(.no-export)',
                rows: {selected: true}
            }
        };
        var settings = $.extend(true, {}, defaults, options);
        if (settings.withSelection) {
            return {
                extend: 'collection',
                text: '<i class="fas fa-external-link-alt"></i> Export <i class="fas fa-caret-down"></i>',
                className: 'btn btn-outline-primary no-border',
                autoClose: true,
                buttons: [
                    {
                        extend: 'collection',
                        text: 'Export Selected <i class="fas fa-caret-down"></i>',
                        className: 'btn btn-outline-primary btn-dt-export-selected',
                        enabled: false,
                        autoClose: true,
                        buttons: [
                            {
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Print',
                                autoClose: true,
                                title: settings.title,
                                messageTop: settings.messageTop,
                                exportOptions: settings.exportOptions
                            },
                            {
                                extend: 'excel',
                                text: '<i class="far fa-file-excel"></i> Excel',
                                autoClose: true,
                                title: settings.title,
                                messageTop: settings.messageTop,
                                exportOptions: settings.exportOptions
                            },
                            {
                                extend: 'pdf',
                                //download: 'open',
                                text: '<i class="far fa-file-pdf"></i> PDF',
                                autoClose: true,
                                title: settings.title,
                                messageTop: settings.messageTop,
                                exportOptions: settings.exportOptions
                            }
                        ]
                    },
                    {
                        extend: 'collection',
                        text: 'Export All<i class="fas fa-caret-down"></i>',
                        className: 'btn btn-outline-primary btn-dt-export-all',
                        enabled: true,
                        autoClose: true,
                        buttons: [
                            {
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Print',
                                autoClose: true,
                                title: settings.title,
                                messageTop: settings.messageTop,
                                exportOptions: settings.exportOptions
                            },
                            {
                                extend: 'excel',
                                text: '<i class="far fa-file-excel"></i> Excel',
                                autoClose: true,
                                title: settings.title,
                                messageTop: settings.messageTop,
                                exportOptions: settings.exportOptions
                            },
                            {
                                extend: 'pdf',
                                //download: 'open',
                                text: '<i class="far fa-file-pdf"></i> PDF',
                                autoClose: true,
                                title: settings.title,
                                messageTop: settings.messageTop,
                                exportOptions: settings.exportOptions
                            }
                        ]
                    }
                ]
            };
        } else {
            return {
                extend: 'collection',
                text: '<i class="fas fa-external-link-alt"></i> Export <i class="fas fa-caret-down"></i>',
                className: 'btn btn-outline-primary no-border',
                autoClose: true,
                buttons:
                    $.merge(
                        settings.extraButtons,
                        [
                            settings.replacers.print,
                            settings.replacers.excel,
                            settings.replacers.pdf
                        ]
                    )
            };
        }
    },

    switch_view: function (options = {}) {
        return {
            text: '<i class="fab fa-nintendo-switch"></i> Switch Specifications/Prices View',
            className: 'btn btn-outline-danger no-border btnSwitchView',
            action: function (e, dt, node, config) {
            }
        };
    }

};

var custom_datatables = {

    defaults: {
        target: null, // required datatables object to replace
        table_id: null, // required
        serverSideUrl: null, // required
        search: '',
        //destroy: true,
        fullbuttons: true, // for special cases where all buttons are not needed
        buttons: [] // set separately
    },

    products: function (obj) {
        this.defaults.buttons = [
            custom_buttons.back(),
            custom_buttons.new(editProductUrl),
            custom_buttons.view(),
            custom_buttons.switch_view({}),
            custom_buttons.export()
        ];
        var params = $.extend({}, this.defaults, obj);
        let columns = [
                {
                    "title": "", "searchable": false, "orderable": false, "className": "no-export noVis",
                    "render": function (data, type, row, meta) {
                        return "<i class='fas fa-th btn-action btnColorline' data-product_id='" + row.product_id + "' data-product_type='" + row.product_type + "' data-toggle='tooltip' data-trigger='hover' data-title='View Colorline' data-placement='top'></i>";
                    }
                },
                {
                    "title": "Product Type", "data": "product_type", "searchable": false, "visible": false, "className": "noVis"
                },
                {
                    "title": "Product Name", "searchable": true, "data": "product_name",
                    "render": function (data, type, row, meta) {
                        var txt = row.product_name;
                        if (row.product_type === 'R' && row.vendor_product_name !== null /*row.vendor_product_name.length > 0*/) {
                            txt = "<span data-toggle='tooltip' data-trigger='hover' data-title='Vendor name: " + row.vendor_product_name + "' data-placement='top'>" + row.product_name + "</span>";
                        }
                        return txt;
                    }
                },
                {
                    "title": "", "data": "stamps", "searchable": false, "orderable": false, "className": "noVis",
                    "render": function (data, type, row, meta) {
                        var add = '';
                        switch (row.product_type) {
                            case 'R':
                                if (stamps.under30_ids.indexOf(row.product_id) >= 0) add += get_stamp_html('30U');
                                if (stamps.digital_ground_ids.indexOf(row.product_id) >= 0) add += get_stamp_html('DG');
                                if (stamps.fabricseen_ids.indexOf(row.product_id) >= 0) add += get_stamp_html('FS');
                                break;
                        }
                        if(row.in_master == '1'){
                            add += get_stamp_html("MPL");
                        }
                        return add;
                    }
                },
                {
                    "title": "Uses", "data": "uses", "searchable": false
                },
                // Specs view
                {
                    "title": "Weave", "data": "weaves", "searchable": false
                },
                {
                    "title": "Width", "data": "width", "searchable": false,
                    "render": function (data, type, row, meta) {
                        return (row.width !== '0' && row.width !== null ? row.width + '"' : '-');
                    }
                },
                {
                    "title": "Repeats", "data": "repeats", "searchable": false, "orderable": false,
                    "render": function (data, type, row, meta) {
                        var txt = '';
                        if (row.vrepeat === null && row.hrepeat === null) {
                            txt = 'No repeat';
                        } else {
                            if (row.vrepeat !== '0.00' && row.vrepeat !== null) {
                                txt += 'V: ' + row.vrepeat + '"';
                                if (row.hrepeat !== '0.00' && row.hrepeat !== null) {
                                    txt += ' / H: ' + row.hrepeat + '"';
                                }
                            } else if (row.hrepeat !== '0.00' && row.hrepeat !== null) {
                                txt += 'H: ' + row.hrepeat + '"';
                            }
                        }
                        return "<nobr>" + (txt === '' ? '-' : txt) + "</nobr>";
                    }
                },
                {
                    "title": "Content", "data": "content_front", "searchable": false,
                    "render": function (data, type, row, meta) {
                        return (row.content_front !== null ? row.content_front : '-');
                        //return ( row.content_front !== null ? '<nobr>'+row.content_front.replace(/\//g, '</nobr>/<nobr>')+'</nobr>' : '-' );
                    }
                },
                {
                    "title": "Firecode", "data": "firecodes", "searchable": false, "visible": false,
                    "render": function (data, type, row, meta) {
                        return (row.firecodes !== null ? row.firecodes : '-');
                        //return ( row.firecodes !== null ? '<nobr>'+row.firecodes.replace(/\//g, '</nobr>/<nobr>')+'</nobr>' : '-' );
                    }
                },
                {
                    "title": "Abrasion", "data": "abrasions", "searchable": false, "visible": false,
                    "render": function (data, type, row, meta) {
                        var b, a, abrasions;
                        var txt = '';
                        if (row.abrasions !== null) {
                            abrasions = row.abrasions.split('/');
                            var limit, rubs, test;
                            $.each(abrasions, function (index, value) {
                                b = value.split('*');
                                if ($.inArray(parseInt(b[0]), special_cases.abrasion) >= 0) {
                                    a = b[1].split('-');
                                    txt += a[2];
                                } else {
                                    a = b[1].split('-');
                                    limit = a[0];
                                    rubs = a[1];
                                    test = a[2];
                                    txt += limit + ' ' + numberWithCommas(rubs) + ' ' + test;
                                }
                                // Separate different abrasions
                                if (index < abrasions.length - 1) {
                                    txt += ' / ';
                                }
                            })
                        }
                        return (txt !== '' ? txt.replace(/Unknown/g, '').trim() : '-');
                    }
                },
                // Pricing view
                {
                    "title": "Cut Price", "data": "p_res_cut", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        return row.p_res_cut !== null ? "<nobr>$ " + row.p_res_cut + "</nobr>" : '-';
                    }
                },
                // {
                //     "title": "Roll Price", "data": "p_hosp_cut", 'searchable': false, 'visible': false,
                //     "render": function (data, type, row, meta) {
                //         // console.log('assets/js/init_datatables.js li: 391 row:', row);
                //         return row.p_hosp_cut !== null ? "<nobr>$ " + row.p_hosp_cut + "</nobr>" : '-';
                //     }
                // },
                {
                    "title": "Roll Price", "data": "p_hosp_roll", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        // console.log('assets/js/init_datatables.js li: 391 row:', row);
                        return row.p_hosp_roll !== null ? "<nobr>$ " + row.p_hosp_roll + "</nobr>" : '-';
                    }
                },
                // {
                //     "title": "Volume Price", "data": "p_hosp_roll", 'searchable': false, 'visible': false,
                //     "render": function (data, type, row, meta) {
                //         return row.p_hosp_roll !== null ? "<nobr>$ " + row.p_hosp_roll + "</nobr>" : '-';
                //     }
                // }
            ];
        if(!obj.is_showroom){
            columns.splice(0, 0, {
                "title": "", "searchable": false, "orderable": false, "className": "no-export noVis",
                "render": function (data, type, row, meta) {
                    return "<i class='fas fa-pen-square btn-action btnEdit' data-product_id='" + row.product_id + "' data-product_type='" + row.product_type + "' data-toggle='tooltip' data-trigger='hover' data-title='Edit Product' data-placement='top'></i>";
                }
            })
            columns.splice(3, 0, {
                "title": "Vendor", "searchable": true, "data": "vendors_name", "visible": true,
                "render": function (data, type, row, meta) {
                    var txt = '';
                    if (row.product_type === 'D' || row.product_type === 'SP') {
                        row.vendors_abrev = 'OZ';
                        row.vendors_name = 'Opuzen';
                    }
                    
                    // Handle null/empty vendor names
                    var vendorName = row.vendors_name || row.vendor_business_name || '';
                    var vendorAbrev = row.vendors_abrev;
                    
                    if (vendorAbrev === '' || vendorAbrev === null) {
                        txt = vendorName;
                    } else {
                        if (vendorName !== '') {
                            txt = "<span data-toggle='tooltip' data-trigger='hover' data-title='" + vendorName + "' data-placement='top'>" + vendorAbrev + "</span>";
                        } else {
                            txt = vendorAbrev;
                        }
                    }
                    return txt;
                }
            });
            columns = columns.concat([
                {"title": "Vendor ProdName", "data": "vendors_name", "visible": false, 'searchable': true},
                {"title": "Tariff+", "data": "tariff_surcharge", "visible": false, 'searchable': true},
                {"title": "Freight+", "data": "freight_surcharge", "visible": false, 'searchable': true},
                {
                    "title": "Cut", "data": "cost_cut", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        return row.cost_cut !== null ? "<nobr>" + row.cost_cut + "</nobr>" : '';
                    }
                },
                {
                    "title": "Half roll", "data": "cost_half_roll", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        return row.cost_half_roll !== null ? "<nobr>" + row.cost_half_roll + "</nobr>" : '';
                    }
                },
                {
                    "title": "Roll", "data": "cost_roll", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        return row.cost_roll !== null ? "<nobr>" + row.cost_roll + "</nobr>" : '';
                    }
                },
                {
                    "title": "Landed", "data": "cost_roll_landed", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        return row.cost_roll_landed !== null ? "<nobr>" + row.cost_roll_landed + "</nobr>" : '';
                    }
                },
                {
                    "title": "Exmill", "data": "cost_roll_ex_mill", 'searchable': false, 'visible': false,
                    "render": function (data, type, row, meta) {
                        return row.cost_roll_ex_mill !== null ? "<nobr>" + row.cost_roll_ex_mill + "</nobr>" : '';
                    }
                },
                {"title": "Costs Last Update", "data": "cost_date", 'searchable': false, 'visible': false},
                {"title": "FOB", "data": "fob", "searchable": false, "visible": false}
            ]);
            // console.log(columns);
        }

        return $(params.table_id)
            .on('preXhr.dt', function (e, settings, data) {
                if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
            })
            .DataTable({
                "dom": '< <"d-flex flex-row justify-content-between align-items-center my-4" B <"typeahead"> <"d-flex flex-column" l> > <t> i p >',
                'serverSide': true,
                "processing": true,
                "stateSave": true,
                "search": {
                    "search": params.search
                },
                "ajax": {
                    "url": params.serverSideUrl,
                    "type": "POST",
                    "dataSrc": function (json) {
                        return json.tableData;
                    },
                    "data": function (data) {
                        return data;
                    }
                },
                "language": {
                    "searchPlaceholder": "Search product or vendor name",
                    "emptyTable": "No results."
                },
                "initComplete": function (settings, json) {
                    $(".dataTables_filter").addClass('col');
                    myDefaultDatatablesInitComplete();
                    switch_viewer.update();
                },
                "createdRow": function (row, data, index) {
                    $(row).addClass('fs-small');
                },
                "columns": columns,
                "order": [[!obj.is_showroom ? 4 : 2, "ASC"]],
                "buttons": params.buttons
            });
    },

    items: function (obj = {}, given_settings = {}) {

        if (obj.isGeneralSearch) {
            this.defaults.buttons = [
                custom_buttons.back(),
                custom_buttons.new(editProductUrl),
                custom_buttons.view([1]),
                {
                    extend: 'collection',
                    text: '<i class="fas fa-bolt"></i> Actions <i class="fas fa-caret-down"></i>',
                    autoClose: true,
                    className: 'btn btn-outline-success no-border',
                    buttons: [
                        {
                            text: '<i class="fas fa-tags"></i> Add to Memotag Printer',
                            className: 'btn-dt-add-printer btn btn-outline-primary',
                            enabled: false,
                            action: function (e, dt, node, config) {
                                $.each(this_table.rows('.selected').data(), function (index, value) {
                                    //console.log(value.id);
                                    the_printing_cart.add_item(value.product_type, value.product_id, value.item_id, 1);
                                });
                                //the_printing_cart.add_item(product_type, id, qty); // assets/js/my_cart
                                show_success_swal('top');
                            }
                        },
                        {
                            text: '<i class="fa fa-list"></i> Add to List',
                            className: 'btn-dt-add-list btn btn-outline-primary' + (!hasEditPermission ? ' hide ' : ''),
                            enabled: false,
                            action: function (e, dt, node, config) {
                                $('#list_modal').modal('show');
                            }
                        }
                    ]
                },
                custom_buttons.export({
                    withSelection: true, title: function () {
                        return 'Search result: ' + $('div.dataTables_filter input').val();
                    }
                })
            ];
        } else {
            // More buttons here than the General Search menu
            this.defaults.buttons = [
                custom_buttons.back(),
                custom_buttons.view(),
                {
                    extend: 'collection',
                    text: '<i class="far fa-hand-pointer"></i> Select <i class="fas fa-caret-down"></i>',
                    className: 'btn btn-outline-primary no-border',
                    autoClose: true,
                    buttons: [
                        {
                            extend: 'selectAll',
                            text: '<i class="fas fa-circle"></i> All'
                            /*,className: 'btn btn-outline-primary',
                            action: function ( e, dt, node, config ) {
                              table.rows( { page: 'current' } ).select();
                            }*/
                        },
                        {
                            text: '<i class="fab fa-gg-circle"></i> Ringset',
                            className: 'btn btn-outline-primary',
                            action: function (e, dt, node, config) {
                                this_table.rows().deselect();
                                this_table.rows('.row-ringset').select();
                            }
                        },
                        {
                            extend: 'selectNone',
                            text: '<i class="far fa-circle"></i> None',
                            className: 'btn btn-outline-primary'
                        }
                    ]
                },
                {
                    extend: 'collection',
                    text: '<i class="fas fa-bolt"></i> Actions <i class="fas fa-caret-down"></i>',
                    autoClose: true,
                    className: 'btn btn-outline-success no-border',
                    buttons: [
                        {
                            text: '<i class="fas fa-plus-circle"></i> Create New',
                            className: 'btn-dt-new btn btn-outline-primary ' + (!hasEditPermission ? ' hide ' : ''),
                            columns: ':gt(0):gt(1)',
                            enabled: true,
                            action: function (e, dt, node, config, product_type) {
                                //console.log("Line 600 thisVALUES:: ", this );
                                //console.log("Line 600 THIS DATA TABLE VALUES:: ", this_table );
                                //console.log("Line 600 WINDPW VALUES:: ", window.product_type );
                                //the_printing_cart.add_item(value.product_type, value.product_id, value.item_id, 1);
                                open_item_modal('0', false, window.product_type, product_id, 'init_datatables.js');
                                // open_item_modal('0', false, product_type, product_id, 'init_datatables.js');
                            }
                        },
                        {
                            text: '<i class="fas fa-tags"></i> Add to Memotag Printer',
                            className: 'btn-dt-add-printer btn btn-outline-primary',
                            enabled: false,
                            action: function (e, dt, node, config) {
                                $.each(this_table.rows('.selected').data(), function (index, value) {
                                    //console.log(value.id);
                                    the_printing_cart.add_item(value.product_type, value.product_id, value.item_id, 1);
                                });
                                //the_printing_cart.add_item(product_type, id, qty); // assets/js/my_cart
                                show_success_swal('top');
                            }
                        },
                        {
                            text: '<i class="fab fa-gg-circle"></i> Add to Ringset',
                            className: 'btn-dt-add-ringset btn btn-outline-primary' + (!hasEditPermission ? ' hide ' : ''),
                            enabled: false,
                            action: function (e, dt, node, config) {
                                var batch = [];
                                var aux;
                                $.each(this_table.rows('.selected').data(), function (index, value) {
                                    aux = {
                                        item_id: value.item_id,
                                        in_ringset: true //value.in_ringset
                                    };
                                    batch.push(aux);
                                });
                                toggleRingset({
                                    batch: batch
                                }, this_table);
                            }
                        },
                        {
                            text: '<i class="fa fa-list"></i> Add to List',
                            className: 'btn-dt-add-list btn btn-outline-primary' + (!hasEditPermission ? ' hide ' : ''),
                            enabled: false,
                            action: function (e, dt, node, config) {
                                $('#list_modal').modal('show');
                            }
                        },
                        {
                            text: '<i class="far fa-layer-group"></i> Add Restock',
                            className: 'btn-dt-add-list btn btn-outline-primary' + (!hasEditPermission ? ' hide ' : ''),
                            enabled: false,
                            action: function (e, dt, node, config) {
                                $('#restock_modal').modal('show');
                            }
                        },
                        {
                            text: '<i class="fas fa-pen-square"></i> Edit selection',
                            autoClose: true,
                            className: 'btn-dt-edit btn btn-outline-primary ' + (!hasEditPermission ? ' hide ' : ''),
                            enabled: false,
                            action: function (e, dt, node, config) {
                                if (this_table.rows('.selected').count() > 0) {
                                    const item_ids = this_table.rows('.selected').data().map(i => i.item_id).toArray();
                                    open_item_modal(item_ids, true);
                                }
                            }
                        }
                    ]
                },
                {
                    text: '<i class="far fa-file-pdf"></i> Specsheet',
                    className: 'btn btn-outline-danger no-border',
                    action: function (e, dt, node, config) {
                        var auxarr = product_id.split('-');
                        console.log([product_id, product_type, item_id]);
                        const id = (product_type === 'item_id' ? item_id : product_id);
                        const validProductType = (product_type === 'item_id' ? 'R' : product_type);
                        window.open(printSpecUrl + '/' + validProductType + '/' + id);
                    }
                },
                custom_buttons.export({
                    withSelection: true, title: function () {
                        return $('#input_search').val();
                    }
                })
            ];
        }

        // We may bring different settings
        // Here we set defaults and override
        var params = $.extend({}, this.defaults, obj);
        const columns = [
            {
                "title": "",
                "defaultContent": "",
                "searchable": false,
                "orderable": false,
                "className": 'select-checkbox noVis'
            },
            {
                "title": "ID",
                "data": "item_id",
                'visible': false,
                "defaultContent": "",
                "searchable": false,
                "orderable": true,
                "className": ''
            },
            {
                "title": "Status", "data": "status", "searchable": false,
                "render": function (data, type, row, meta) {
                    return "<span data-toggle='tooltip' data-trigger='hover' data-title='" + row.status_descr + "' data-placement='top'>" + row.status + "</span>";
                }
            },
            {
                "title": "Stock Status", "data": "stock_status", "searchable": false,
                "render": function (data, type, row, meta) {
                    //return row.stock_status;
                    return "<span data-toggle='tooltip' data-trigger='hover' data-title='" + row.stock_status_descr + "' data-placement='top'>" + row.stock_status + "</span>";
                }
            },
            {
                "title": "",
                "data": "btnInRingset",
                "searchable": false,
                "orderable": false,
                "className": 'no-export noVis',
                "render": function (data, type, row, meta) {
                    //console.log(row);
                    return "<i class='fab fa-gg-circle rs-icon  " + (row.in_ringset === '1' ? ' rs-active ' : '') + (!obj.isGeneralSearch && hasEditPermission ? " btnToggleRingset " : '') + " ' data-id='" + row.item_id + "' " + (!obj.isGeneralSearch && hasEditPermission ? "data-toggle='tooltip' data-title='Toggle ringset' " : '') + "></i>";
                }
            },
            {
                "title": "Product Name", "data": "product_name",
                "render": function (data, type, row, meta) {
                    return "<a href='#' class='btnEdit' data-toggle='tooltip' data-trigger='hover' data-title='Edit Product' data-placement='top' data-product_id='" + row.product_id + "' data-product_type='" + row.product_type + "'>" + row.product_name + "</a>";
                }
            },
            {
                "title": "", "data": "stamps", "searchable": false, "orderable": false, "className": "noVis",
                "render": function (data, type, row, meta) {
                    var add = '';
                    if(row.in_master == '1' && row.in_master_product == '1'){
                        add += get_stamp_html("MPL");
                    }
                    switch (row.product_type) {
                        case 'R':
                            if (stamps.under30_ids.indexOf(row.item_id) >= 0) add += get_stamp_html('30U');
                            if (stamps.digital_ground_ids.indexOf(row.item_id) >= 0) add += get_stamp_html('DG');
                            if (stamps.fabricseen_ids.indexOf(row.item_id) >= 0) add += get_stamp_html('FS');
                            break;
                    }
                    return add;
                }
            },
            {"title": "Item #", "data": "code"},
            {"title": "Color", "data": "color", "searchable": true},
            {
                "title": "Cut Price", "data": "p_res_cut", "visible": true, "searchable": false,
                "render": function (data, type, row, meta) {
                    return typeof (row.p_res_cut) !== 'undefined' && row.p_res_cut !== null && row.p_res_cut !== '-' ? "<span class='text-primary'>" + row.p_res_cut + "</span>" : '-';
                }
            },
            {
                "title": "Roll Price", "data": "p_hosp_roll", "visible": true, "searchable": false,
                "render": function (data, type, row, meta) {
                    return typeof (row.p_hosp_roll) !== 'undefined' && row.p_hosp_roll !== null && row.p_hosp_roll !== '-' ? "<span class='text-primary'>" + row.p_hosp_roll + "</span>" : '-';
                }
            },
            // {
            //     "title": "Hosp/Cut", "data": "p_hosp_cut", "visible": true, "searchable": false,
            //     "render": function (data, type, row, meta) {
            //         return typeof (row.p_hosp_cut) !== 'undefined' && row.p_hosp_cut !== null && row.p_hosp_cut !== '-' ? "<span class='text-primary'>" + row.p_hosp_cut + "</span>" : '-';
            //     }
            // },
            // {
            //     "title": "Volume Price", "data": "p_hosp_roll", "visible": true, "searchable": false,
            //     "render": function (data, type, row, meta) {
            //         return typeof (row.p_hosp_roll) !== 'undefined' && row.p_hosp_roll !== null && row.p_hosp_roll !== '-' ? "<span class='text-primary'>" + row.p_hosp_roll + "</span>" : '-';
            //     }
            // },
            {
                "title": "In Stock", "data": "yardsInStock", "defaultContent": '-', "searchable": false, "visible": false,
                "render": function (data, type, row, meta) {
                    var txt = '';
                    if (row.yardsInStock !== null && typeof (row.yardsInStock) !== 'undefined' /*&& row.yardsInStock !== '0.00'*/) {
                        txt += row.yardsInStock;
                    } else {
                        txt += '-';
                    }
                    /* ============================================================
                       COMMENTED OUT: Sales App External Link in "In Stock" Column
                       Date: 2025-10-16
                       Reason: Removing Sales App links per aiRemoveSalesApLinks branch
                       ============================================================ */
                    /*
                    if (typeof (row.sales_id) !== 'undefined' && row.sales_id !== null) {
                        txt += " <a href='https://sales.opuzen-service.com/index.php/bolt/index/" + row.sales_id + "' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
                    }
                    */
                    return txt;
                }
            },
            {
                "title": "Available", "data": "yardsAvailable", "defaultContent": '-', "searchable": false, "visible": false,
                "render": function (data, type, row, meta) {
                    if (row.yardsAvailable === null || typeof (row.yardsAvailable) === 'undefined' || row.yardsAvailable === '0.00') {
                        return '-';
                    } else {
                        return "<span class='text-success'>" + row.yardsAvailable + "</span>";
                    }
                }
            },
            {
                "title": "On Hold", "data": "yardsOnHold", "defaultContent": '-', "searchable": false, "visible": false,
                "render": function (data, type, row, meta) {
                    if (row.yardsOnHold === null || typeof (row.yardsOnHold) === 'undefined' || row.yardsOnHold === '0.00') {
                        return '-';
                    } else {
                        return "<span class='text-danger'>" + row.yardsOnHold + "</span>";
                    }
                }
            },
            {
                "title": "Avail. On Order", "data": "", "defaultContent": '-', "searchable": false, "visible": false,
                "render": function (data, type, row, meta) {
                    var txt = 0;
                    if (row.yardsOnOrder === null || typeof (row.yardsOnOrder) === 'undefined' || row.yardsOnOrder === '0.00') {
                        txt = 0; //return '-';
                    } else {
                        txt += parseFloat(row.yardsOnOrder);
                    }
                    if (row.yardsBackorder === null || typeof (row.yardsBackorder) === 'undefined' || row.yardsBackorder === '0.00') {
                        //return '-';
                    } else {
                        txt -= parseFloat(row.yardsBackorder);
                    }
                    return txt === 0 ? '-' : "<span class='text-warning'>" + txt + "</span>";
                }
            },
            {
                "title": "Web Visible", "data": "web_visible", "defaultContent": "-", "searchable": false,
                "render": function (data, type, row, meta) {
                    var txt = "";
                    // ================================================================
                    // Use database-calculated web_vis value (from lazy calculation)
                    // ================================================================
                    let isVisible = (row.web_visibility === 'Y');
                    
                    if (isVisible) {
                        // Show open eye icon with link
                        if (row.url_title !== '') {
                            txt = " <a href='https://www.opuzen.com/product/" + row.url_title + "' target='_blank'><i class='far fa-eye'></i></a>";
                        } else if (stamps.digital_ground_ids.indexOf(row.item_id) >= 0) {
                            txt = " <a href='https://opuzen.com/digital/grounds/view-all' target='_blank'><i class='far fa-eye'></i></a>";
                        } else {
                            txt = " <i class='far fa-eye'></i>";
                        }
                    } else {
                        // Show crossed-out eye icon
                        txt = " <i class='far fa-eye-slash'></i>";
                    }
                    return txt;
                }
            },
            {
                "title": "Images", "data": "", "defaultContent": "", "searchable": false,
                "render": function (data, type, row, meta) {
                    var cls = "";
                    
                    // Function to check if URL is a valid uploaded image
                    function isValidUploadedImage(url) {
                        // Debug logging - remove this after fixing
                        if (url) {
                            console.log("Checking image URL:", url, "Type:", typeof url);
                        }
                        
                        if (!url || url === null || url === '') {
                            return false;
                        }
                        
                        // Exclude placeholder images
                        if (url.indexOf('placeholder') !== -1) {
                            console.log("Excluding placeholder:", url);
                            return false;
                        }
                        
                        // Exclude URLs that are just numbers (like "60", "123", etc.)
                        if (/^\d+$/.test(url)) {
                            console.log("Excluding numeric URL:", url);
                            return false;
                        }
                        
                        // Exclude URLs that don't look like proper URLs or file paths
                        // Must contain either http/https or a file path with extension
                        var isHttpUrl = /^https?:\/\//i.test(url);
                        var isFilePath = /\.(jpg|jpeg|png|gif|webp|bmp|tiff|svg)$/i.test(url);
                        
                        if (!isHttpUrl && !isFilePath) {
                            console.log("Excluding invalid URL format:", url);
                            return false;
                        }
                        
                        // Exclude generic S3 URLs that don't end with an image file
                        if (url.indexOf('opuzen-web-assets-public.s3.us-west-1.amazonaws.com') !== -1) {
                            // Check if URL ends with a valid image file extension
                            var hasImageExtension = /\.(jpg|jpeg|png|gif|webp|bmp|tiff|svg)$/i.test(url);
                            if (!hasImageExtension) {
                                console.log("Excluding generic S3 URL:", url);
                                return false;
                            }
                        }
                        
                        console.log("Accepting valid image URL:", url);
                        return true;
                    }
                    
                    var hasBigImage = isValidUploadedImage(row.pic_big_url);
                    var hasHdImage = isValidUploadedImage(row.pic_hd_url);
                    
                    if (hasBigImage && hasHdImage) {
                        cls = "far fa-check-double";
                    } else if (hasBigImage || hasHdImage) {
                        cls = "far fa-check";
                    }
                    
                    return '<i class="' + cls + '"></i>';
                }
            }
        ];
        if(!obj.is_showroom){
            columns.splice(0, 0, {
                "title": "", "searchable": false, "orderable": false, "className": "noVis",
                "render": function (data, type, row, meta) {
                    return "<i class='fas fa-pen-square btn-action btnEditItem' data-toggle='tooltip' data-trigger='hover' data-title='Edit Item' data-placement='top' data-item-id='" + row.item_id + "' data-product_id='" + row.product_id + "' data-product_type='" + row.product_type + "'></i>"
                }
            })
            columns.splice(1, 0, {
                "title": "Exportable",
                "data": "exportable",
                "searchable": false,
                "orderable": false,
                "className": 'text-center no-export noVis',
                "render": function (data, type, row, meta) {
                    if (!hasEditPermission) {
                        return row.exportable === '1' ? '<span class="text-success">✓</span>' : '<span class="text-muted">-</span>';
                    }
                    var isChecked = row.exportable === '1' || row.exportable === 1;
                    return '<label class="exportable-switch" style="margin:0;">' +
                           '<input type="checkbox" class="btnToggleExportable" data-item-id="' + row.item_id + '" ' + (isChecked ? 'checked' : '') + '>' +
                           '<span class="exportable-slider"></span>' +
                           '</label>';
                }
            })
            columns.splice(3, 0, {
                "title": "Shelf",
                "data": "shelf",
                "defaultContent": "",
                "searchable": false,
                "orderable": true,
                "className": ''
            },);
            columns.splice(4, 0, {
                "title": "Bin",
                "data": "bin_location",
                "defaultContent": "",
                "searchable": false,
                "orderable": true,
                "className": '',
                "render": function(data, type, row, meta){
                    // let cls = (row.bin_location == null ? 'hide' : (row.bin_quantity == null || parseInt(row.bin_quantity) == 0 ? 'text-danger' : 'text-success'));
                    let qty = parseInt(row.bin_quantity) || 0;
                    let cls = (row.bin_location == null && qty == 0 ? 'hide' : ( qty == 0 ? 'text-danger' : 'text-success' ));
                    return "<span class='"+cls+"'>"+(row.bin_location == null ? '' : row.bin_location)+" ("+qty+")</span>";
                }
            });
            columns.splice(5, 0, {
                "title": "Roll",
                "data": "roll_location",
                "defaultContent": "",
                "searchable": false,
                "orderable": true,
                "className": '',
                "render": function(data, type, row, meta){
                    // let cls = (row.roll_location == null ? 'hide' : (row.roll_yardage == null || parseFloat(row.roll_yardage) == 0 ? 'text-danger' : 'text-success'));
                    let qty = parseFloat(row.roll_yardage) || 0;
                    let cls = (row.roll_location == null && qty == 0 ? 'hide' : ( qty == 0 ? 'text-danger' : 'text-success' ));
                    return "<span class='"+cls+"'>"+(row.roll_location == null ? '' : row.roll_location)+" ("+qty+")</span>";
                }
            });
            columns.splice(16, 0, {
                "title": "Cut", "data": "cost_cut", 'searchable': false, 'visible': false,
                "render": function (data, type, row, meta) {
                    return row.cost_cut !== null ? "<nobr>" + row.cost_cut + "</nobr>" : '';
                }
            });
            columns.splice(17, 0, {
                "title": "Half roll", "data": "cost_half_roll", 'searchable': false, 'visible': false,
                "render": function (data, type, row, meta) {
                    return row.cost_half_roll !== null ? "<nobr>" + row.cost_half_roll + "</nobr>" : '';
                }
            });
            columns.splice(18, 0, {
                "title": "Roll", "data": "cost_roll", 'searchable': false, 'visible': false,
                "render": function (data, type, row, meta) {
                    return row.cost_roll !== null ? "<nobr>" + row.cost_roll + "</nobr>" : '';
                }
            });
            columns.splice(19, 0, {
                "title": "Tariff+", "data": "tariff_surcharge", 'searchable': false, 'visible': false,
                "render": function (data, type, row, meta) {
                    return row.tariff_surcharge !== null ? "<nobr>" + row.tariff_surcharge + "</nobr>" : '';
                }
            });
            columns.splice(20, 0, {
                "title": "Freight+", "data": "freight_surcharge", 'searchable': false, 'visible': false,
                "render": function (data, type, row, meta) {
                    return row.freight_surcharge !== null ? "<nobr>" + row.freight_surcharge + "</nobr>" : '';
                }
            });
        }

        this.defaults.settings = {
            //dom: '< <"d-flex flex-row justify-content-between align-items-center" B <"d-flex flex-column" f <"items-filter"> l> > <t> i p >',
            'serverSide': true,
            "processing": true,
            "ajax": {
                "url": params.serverSideUrl, //'<?//=site_url('item/get_product_items')?>',
                "type": "POST",
                // Set the data to send to Ajax
                "data": function (data) {
                    data.searchtype = 'It';//searchtype; // add data to post req
                    return data;
                },
                "dataSrc": function (json) {
                    return json.tableData;
                },
            },
            "rowId": "item_id",
            "pageLength": 200,
            "language": {
                "emptyTable": "<h4 class='m-0'>Use the searchbox on the top left corner to search for products.</h4>",
                //"emptyTable": "<h5 class='m-4' onclick='javascript: open_global_modal(this.href)' href='"+howtosearchUrl+"'>How to search</h4>",
                "sSearch": typeof get_custom_searchtype === 'function' ? get_custom_searchtype() : ''
            },
            "initComplete": function (settings, json) {
                //console.log(settings);
                myDefaultDatatablesInitComplete();
                $(settings.nTable).dataTable().fnFilterOnReturn();
                $('select#searchtype').val('It');//.multiselect('refresh');
            },
            "rowCallback": function (row, data) {
                let row_node = $(row);
                //console.log(row_node);
                //console.log(data);
                switch (data.status_id) {
                    case '3':
                        // Discontinued
                        row_node.addClass('row-discontinued');
                        break;
                    case '18':
                        // MSO
                        row_node.addClass('row-mso');
                        break;
                    default:
                        row_node.removeClass('row-mso row-discontinued')
                        break;
                }
                if (data.in_ringset === '1') {
                    row_node.addClass('row-ringset');
                } else {
                    row_node.removeClass('row-ringset');
                }
            },
            "columns": columns,
            'select': !obj.is_showroom ? {
                style: 'multi',
                selector: 'td:nth-child(3)'
            } : {style: 'multi', selector: 'td:nth-child(1)'},
            "order": [[!obj.is_showroom ? 12 : 8, "ASC"]],
            "buttons": params.buttons,
            "stateLoadParams": function (settings, data) {
                // Force specific columns to be hidden by removing their visibility from saved state
                // This ensures all users get the updated column visibility regardless of saved state
                if (data.columns) {
                    data.columns.forEach(function(col, index) {
                        // Find columns by title: "In Stock", "Available", "On Hold", "Avail. On Order"
                        var column = columns[index];
                        if (column && column.title) {
                            if (column.title === "In Stock" || 
                                column.title === "Available" || 
                                column.title === "On Hold" || 
                                column.title === "Avail. On Order") {
                                // Force these columns to be hidden by setting visible to false
                                col.visible = false;
                            }
                        }
                    });
                }
            }
        };
        var settings = $.extend({}, this.defaults.settings, given_settings);

        return $(params.table_id)
            .on('preXhr.dt', function (e, settings, data) {
                if (settings.jqXHR) settings.jqXHR.abort(); // Cancel multiple requests
            })
            .on('draw.dt select.dt deselect.dt', function (e, dt, type, indexes) {
                var selected = $(this).DataTable().rows({selected: true}).count();
                $(this).DataTable().buttons([".btn-dt-new"]).enable(selected === 0);
                $(this).DataTable().buttons([".btn-dt-export-selected", ".btn-dt-edit", ".btn-dt-add-printer", ".btn-dt-add-list", ".btn-dt-add-ringset"]).enable(selected > 0);
            })
            .DataTable(settings)
            ;

    }

};

$.extend(true, $.fn.dataTable.defaults, {
    pageLength: 50,
    lengthMenu: [[50, 200, 500, -1], [50, 200, 500, "All"]],
    dom: '< <"d-flex flex-row justify-content-between align-items-center" B <"typeahead"> <"d-flex flex-column" f l> > <t> i p >',
    deferRender: true,
    stateSave: true,
    scrollCollapse: true,
    //responsive: true,
    //scrollX: true,
    //fixedHeader: { header: true },
    "language": {
        "emptyTable": "<h4 class='m-0'>No results to display. Use the searchbox above to generate results.</h4>",
        //"emptyTable": "<h5 class='m-4' onclick='javascript: open_global_modal(this.href)' href='"+howtosearchUrl+"'>How to search</h4>",
        "sSearch": typeof get_custom_searchtype === 'function' ? get_custom_searchtype() : ''
    },
    searchDelay: 700,
    drawCallback: function (settings) {
        myDefaultDatatablesDrawCallback(settings);
    },
    initComplete: function (settings, json) {
        myDefaultDatatablesInitComplete();
    },
    "buttons": [
        custom_buttons.back(),
        custom_buttons.view([0, 1]),
        custom_buttons.export()
    ]
});

jQuery.fn.dataTableExt.oApi.fnFilterOnReturn = function (oSettings) {
    var _that = this;

    this.each(function (i) {
        $.fn.dataTableExt.iApiIndex = i;
        var $this = this;
        var anControl = $('input', _that.fnSettings().aanFeatures.f);
        anControl
            .unbind('keyup search input')
            .bind('keypress', function (e) {
                if (e.which == 13) {
                    $.fn.dataTableExt.iApiIndex = i;
                    _that.fnFilter(anControl.val());
                }
            });
        return this;
    });
    return this;
};

function myDefaultDatatablesDrawCallback(settings){
    //var api = new $.fn.dataTable.Api( settings );
    //console.log('drawCallback');
    init_tooltips();

    var tf = jQuery.now();
    if ((tf - ti) > 1000 && settings.aiDisplay.length === 0) {
        console.log(settings.oPreviousSearch.sSearch + ' not found');
    }
    $('div.internal-loader-spin').addClass('hide');
    $('table.dataTable').css('opacity', 1);
}

function myDefaultDatatablesInitComplete() {
    $(".dataTables_filter").addClass('col');
    // Call for ALL Datatables
    $("div.dt-buttons > button").each(function () {
        $(this).removeClass('dt-button');
    });
}

function init_tooltips(opt = undefined) {
    if ($('[data-toggle="tooltip"]').length > 0) {
        let targ = $('[data-toggle="tooltip"]');
        if (typeof (opt) !== 'undefined') {
            targ.tooltip(opt);
        } else {
            targ.tooltip({boundary: 'window', container: 'body', trigger: 'hover', delay: 500});
        }

    }
}

$(document).on('focus', "#input_search, input[type='search']", function () {
    $(this).select();
})

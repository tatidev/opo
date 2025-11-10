<?php $isNew = ($product_id == 0 ? true : false); ?>

<style>
    a.nav-link:hover {
        cursor: pointer;
    }
    .task-completed {
        font-size: 24px;
        color: limegreen;
    }
</style>

<div class='row mt-4'>

    <div class='col-12' id='form-content'>
        <div class='nav flex-row container mb-4'>
			<?php echo  $btnBack ?>
            <div class='mx-auto'>
                <div class="nav flex-row nav-pills <?php echo  !$isNew ? 'hide' : '' ?>" id="v-pills-tab" role="tablist">
                    <p class='my-auto px-4'>
                        Select Product Type:
                    </p>
                    <a data-product_type='<?php echo  constant('Regular') ?>'
                       class="nav-link <?php echo  $product_type == constant('Regular') ? 'active' : '' ?>" role="tab"
                       aria-expanded="true">Regular</a>
                    <a data-product_type='<?php echo  constant('Digital') ?>'
                       class="nav-link <?php echo  $product_type == constant('Digital') ? 'active' : '' ?>" role="tab"
                       aria-expanded="true">Digital</a>
                    <a data-product_type='<?php echo  constant('ScreenPrint') ?>'
                       class="nav-link <?php echo  $product_type == constant('ScreenPrint') ? 'active' : '' ?>" role="tab"
                       aria-expanded="true">Screen Prints</a>
                </div>
            </div>
			<?php echo  ($product_id !== 0 ? "<a href=" . site_url('product/specsheet/' . $product_type . '/' . $product_id) . " class='btn no-border btn-outline-danger pull-right mr-4' target='_blank'><i class='far fa-file-pdf' aria-hidden='true'></i> Specsheet</a>" : '') ?>
			<?php echo  ((!$is_showroom) && ($product_id !== 0) ? '<a href="#" class="btn no-border btn-outline-primary btnProductChecklist mr-4"><i class="fas fa-ballot check"></i> Checklist</a>' : '') ?>
			<?php echo  ($product_id !== 0 ? "<a href='#' class='btn no-border btn-outline-primary btnColorline pull-right mr-4' data-product_id='$product_id' data-product_type='$product_type'><i class='fas fa-th' aria-hidden='true'></i> Colorline</a>" : '') ?>
			<?php echo  ($hasEditPermission ? " <button class='btn btn-success btnSave btnFormValidator pull-right' data-for='product'><i class='far fa-square' aria-hidden='true'></i> Save</button> " : '') ?>
        </div>

        <div class='row py-2 bg-danger text-white <?php echo  (!$isNew && $info['archived'] === 'Y' ? '' : 'hide') ?>'>
            <div class='col text-center'>
                <i class="fas fa-box-open"></i> Product has been
                deleted. <?php if ($hasEditPermission) { ?>If you want to retrieve it, <u id='btnRetrieve'>click
                    here</u>.<?php } ?>
            </div>
        </div>

		<?php echo  $form ?>
    </div>

    <script>
        var product_id = <?php echo $product_id?>;
        var product_type = '<?php echo $product_type?>';
        // PKL Upload Trace
        var btnSpecsModalUrl = '<?php echo site_url('specs/get_specs_list')?>';
        var btnHistoryModalUrl = '<?php echo site_url('history/price')?>';
        var btnArchiveUrl = '<?php echo site_url('product/archive_product')?>';
        var btnRetrieveUrl = '<?php echo site_url('product/retrieve_product')?>';
        var btnProductChecklistUrl = '<?php echo site_url('product/checklist')?>';

        $(document).ready(function () {

            $(document).on('click', '#v-pills-tab > a.nav-link', function () {
                if ($(this).hasClass('active') == false) {
                    // Change active states
                    $('a.nav-link.active').removeClass('active');
                    $(this).addClass('active');
                    // Bring AJAX form
                    var product_type = $(this).attr('data-product_type');
                    var formData = {
                        'product_type': product_type
                    };
                    get_ajax_view("<?php echo site_url('product/edit')?>", formData);
                }
            });

            $('#form-content').on('click', '#btnArchive', function () {
                show_swal(
                    {},
                    {
                        title: 'Are you sure you want to delete this product?'
                    },
                    {
                        complete: function (t) {
                            $.ajax({
                                method: "POST",
                                url: btnArchiveUrl,
                                dataType: 'json',
                                data: {
                                    'product_id': product_id,
                                    'product_type': product_type
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    console.log(errorThrown);
                                },
                                success: function (data, msg) {
                                    get_ajax_view(data.continueUrl);
                                }
                            });
                        }
                    }
                );
            });

            $('#form-content').on('click', '#btnRetrieve', function () {

                show_swal(
                    {},
                    {
                        title: 'Are you sure you want to retrieve this product?'
                    },
                    {
                        complete: function (t) {
                            $.ajax({
                                method: "POST",
                                url: btnRetrieveUrl,
                                dataType: 'json',
                                data: {
                                    'product_id': product_id,
                                    'product_type': product_type
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    console.log(errorThrown);
                                },
                                success: function (data, msg) {
                                    get_ajax_view(data.continueUrl);
                                }
                            });
                        }
                    }
                );

            });

            //
            // Check list JS stuff
            //

            $('#form-content').on('click', '.btnProductChecklist', function(){
                alert('Product Button Pressed');
                // Get data and print in modal
                $.ajax({
                    method: "POST",
                    url: btnProductChecklistUrl,
                    dataType: 'json',
                    data: {
                        'product_id': product_id,
                        'product_type': product_type
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                    },
                    success: function (data, msg) {
                        $('#globalmodal > .modal-dialog > .modal-content').html(data.html);
                        $('#globalmodal > .modal-dialog').css('max-width', '1200px');
                        init_dropdowns();
                        $('#globalmodal').modal('show');
                    }
                });
            })

            $('#globalmodal > .modal-dialog').on('click', 'a.btnUpdateTask', function(e){
                // console.log('btnUpdateTask click')
                let task_id = $(this).attr('data-id');
                let url = $(this).attr('data-url');

                let task_data = collect_task_data(task_id);

                if(task_data == false){
                    return;
                }

                $.ajax({
                    method: "POST",
                    url: url,
                    dataType: 'json',
                    data: task_data,
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                    },
                    success: function (data, msg) {
                        update_task_view(data);
                    }
                });
            });

            function collect_task_data(task_id){
                let task_who = $("select[name='task_who_" + task_id + "[]']").val();
                if(task_who.length == 0){
                    show_swal(()=>{}, {title:"Need to select who completed the task.", buttons:true, icon:'error'});
                    return false;
                }
                return {
                    'product_type': product_type,
                    'product_id': product_id,
                    'task_id': task_id,
                    'task_who': task_who,
                    'task_when': $("input[name='task_date_" + task_id + "']").val(),
                    'task_notes': $("textarea[name='task_notes_" + task_id + "']").val()
                }
            }

            function update_task_view(data){
                let status_selector = "span#status_"+data.task_id;
                let status_icon_selector = 'span#status_icon_'+data.task_id
                if(data.completed){
                    // $(status_selector).addClass('task-completed');
                    $(status_icon_selector).removeClass('hide');
                } else {
                    // $(status_selector).removeClass('task-completed');
                    $(status_icon_selector).addClass('hide');
                }
                $("tr.task_row_need_update[name='task_row_"+data.task_id+"']").toggleClass('task_row_need_update');
            }

        });

    </script>
</div>
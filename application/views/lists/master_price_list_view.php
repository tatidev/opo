<?php
//    echo "<pre>"; var_dump($filters);
?>
<form id="frmMasterPriceList">
    <div class="row" style="margin-bottom: .5rem;">
        <div class="col-6">
            <div class="row">
                <div class="col-12"><h5>List Selection</h5></div>
                <div class="col-6"><?=$filters['multiselect']['list']?></div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-bottom: .5rem;">
        <div class="col-6">
            <div class="row">
                <div class="col-12"><h5>Columns</h5></div>
                <?php foreach($filters['single_checkboxes'] as $ix => $checkbox_data){
	                $checked = $checkbox_data['checked'];
	                $checkbox_name = $checkbox_data['name'];
                    $checkbox_value = $checkbox_data['value'];
                    ?>
                        <div class="col-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input form-control" id="<?=$checkbox_value?>" name="<?=$checkbox_value?>" value="on" <?=($checked ? 'checked' : '')?>>
                                <label class="custom-control-label" for="<?=$checkbox_value?>"><?=$checkbox_name?></label>
                            </div>
                        </div>
                <?php } ?>
            </div>
        </div>
        <div class="col-3">
            <div class="row">
                <div class="col-12"><h5>Product Types</h5></div>
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="product_type_regular" name="product_type" value="<?=Regular?>" checked>
                        <label class="custom-control-label" for="product_type_regular">Regular</label>
                    </div>
                </div>
                <div class="col-12 hide">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="product_type_digital" name="product_type" value="<?=Digital?>">
                        <label class="custom-control-label" for="product_type_digital">Digital</label>
                    </div>
                </div>
                <div class="col-12" style="margin-bottom: .5rem;"></div>
                <div class="col-12"><h5>Group By</h5></div>
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="group_by_product" name="group_by" value="<?=product?>" checked onchange="javascript:$('#group_by_item').prop('checked', !$('#group_by_item').prop('checked'))">
                        <label class="custom-control-label" for="group_by_product">Product</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="group_by_item" name="group_by" value="<?=item?>" onchange="javascript:$('#group_by_product').prop('checked', !$('#group_by_product').prop('checked'))">
                        <label class="custom-control-label" for="group_by_item">Item</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="row">
                <div class="col-12"><h5>Download Format</h5></div>
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="download_excel" name="download_format" value="excel" checked onchange="javascript:toggle_off_checkboxes(['download_json', 'download_pdf', 'download_dump'])">
                        <label class="custom-control-label" for="download_excel">Excel</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="download_pdf" name="download_format" value="pdf" onchange="javascript:toggle_off_checkboxes(['download_json', 'download_excel', 'download_dump'])">
                        <label class="custom-control-label" for="download_pdf">Print with header</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="download_json" name="download_format" value="json" onchange="javascript:toggle_off_checkboxes(['download_excel', 'download_pdf', 'download_dump'])">
                        <label class="custom-control-label" for="download_json">JSON</label>
                    </div>
                </div>
                <div class="col-12" style="margin-bottom: .5rem;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input form-control" id="download_dump" name="download_format" value="dump" onchange="javascript:toggle_off_checkboxes(['download_excel', 'download_pdf', 'download_json'])">
                        <label class="custom-control-label" for="download_dump">Dump</label>
                    </div>
                </div>
                <div class="col-12">
                    <input type="text" class="form-control" id="print_name" name='print_name' value='' placeholder="File name">
                </div>
            </div>
        </div>

    </div>
</form>

<br>

<button class="btn btn-success btnMasterPrint">Download</button>
<br>

<script>
    init_dropdowns();
    var master_filters = <?=json_encode($filters)?>;

    $(document).ready(function(){


        $("button.btnMasterPrint").on('click', function(){
            $.ajax({
                method: "POST",
                url: '<?=site_url('pricelist/set_master_filters')?>',
                dataType: 'json',
                data: $('#frmMasterPriceList').serialize(),
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                },
                success: function (data, msg) {
                    if(data.success){
                        window.open(data.url);
                    }
                }
            });
        })

    })

    function toggle_off_checkboxes(ids_to_toggle){
        ids_to_toggle.forEach(function(id_name){
                let el = $("#"+id_name);
                el.prop('checked', false);
            }
        )
    }
</script>
<? $isNew = $spec_id === '0'; ?>

<form id='frmEditSpec' action='<?php echo  site_url('specs/save_spec') ?>' class='row p-4'>

    <input type='hidden' id='spec_name' name='spec_name' value='<?php echo  $spec_name ?>'>
    <input type='hidden' id='spec_id' name='spec_id' value='<?php echo  $spec_id ?>'>

    <div class='col-12'>

        <div id='error-alert' class='alert alert-danger error-alert mx-auto hide'>
            <div class='d-flex justify-content-between'>
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                <h4>Oops!</h4>
                <i class="fa fa-times btnCloseAlert" aria-hidden="true"></i>
            </div>
            <div id='error-msg' class='my-1'>

            </div>
        </div>
    </div>

    <div class='col-12'>
        <div class='row'>
            <div class='col-6'>
                <a href='#' class="btn btn-secondary float-left" data-dismiss="modal"><i
                            class="far fa-window-close"></i> Close</a>
            </div>
            <div class='col-6'>
				<?php echo  ($hasPermission ? ' <a class="btn btn-success float-right btnSave btnSaveSpec"><i class="far fa-square" aria-hidden="true"></i> Save</a> ' : '') ?>
            </div>
        </div>
        <h3 class='mt-4'>
            Edit
        </h3>

    </div>


    <div class='col-12'>
        <div class='form-group'>
            <div class='col-form-label col-2'>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input form-control" id="active"
                           name="active" <?php echo  ($isNew || (!$isNew && $info['active'] === 'Y') ? 'checked' : '') ?> >
                    <label class="custom-control-label" for="active">Active</label>
                </div>
            </div>
        </div>
    </div>

    <div class='col-12'>
        <div class='form-group row'>
            <label for="info_name" class="col col-form-label">Name</label>
            <div class="col">
                <input type="text" class="form-control" id="info_name" name='info_name'
                       value='<?php echo  ($isNew ? '' : $info['name']) ?>' placeholder="">
            </div>
        </div>
    </div>

	<? if ($n_columns > 2) { ?>
        <div class='col-12'>
            <div class='form-group row'>
                <label for="info_descr" class="col col-form-label">Description</label>
                <div class="col">
                    <textarea class="form-control" id="info_descr" name="info_descr" value="" cols="40" rows="10"
                              style=""><?php echo  ($isNew ? '' : $info['descr']) ?></textarea>
                </div>
            </div>
        </div>
	<? } ?>

	<? if ($shared_files) { ?>
        <div class="col-12">
            <div class="form-group row">
                <label for='shared_files' class="col col-form-label">Files</label>
                <div class='col'>
                    <span class="btn btn-link" data-for="spec_file" onclick="javascript: upload_spec_file(this)">
                        Upload new file <i class="fa fa-plus" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group row">
                <label class="col col-form-label"></label>
                <div class="col">
                    <table id='list_files' class='table modal-spec-content m-auto table-sm table-responsiveX' style='' cellpadding='4' cellspacing='0'>
                        <tbody>
		                    <?php echo  $list_files['tbody'] ?>
                        </tbody>
                    </table>
                </div>
                <input type='hidden' class='form-control' id='files_encoded' name='files_encoded' value='<?php echo  $files_encoded ?>'>
            </div>
        </div>
	<? } ?>

</form>

<form id='fileupload_spec' action='<?php echo  site_url('fileupload/index') ?>' method='POST' enctype='multipart/form-data'>
    <input type='file' class='btn form-control' name='files[]' id='pfiles' style='display:none;' multiple>
</form>

<script>

    function upload_spec_file(me) {
        switch ($(me).attr('data-for')) {
            case 'spec_file':
                $('#pfiles').attr('multiple', 1).attr('name', 'files[]');
                var spec_id = $('#spec_id').val();
                break;
        }
        $('#pfiles').trigger('click');
    }

    $('#fileupload_spec').fileupload({
        dataType: 'json',
        dropZone: null,
        formData: function (e, data) {
            return []
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                if(file.size == 0){
                    console.log(file.error);
                } else {
                    add_file_list(file);
                }
            });
        }
    });

    function add_file_list(file) {
        var now = $.format.date(new Date(), "MM-dd-yyyy");

        var new_row = $("<tr><td><a href='" + file.url + "' target='_blank'><i class='fas fa-file' aria-hidden='true'></i></a></td><td>" + now + "</td> <td><i class='fas fa-times-circle delete_temp_url' aria-hidden='true'></td></tr>");
        $('table#list_files > tbody').prepend(new_row);

        var aux = [];
        if ($('#files_encoded').val() !== '') {
            aux = JSON.parse($('#files_encoded').val());
        }
        var ne = {
            url_dir: file.url,
            date: now,
            user_id: user_id
        };
        aux.push(ne);
        $('#files_encoded').val(JSON.stringify(aux)).trigger('change');
    }

    $('form#frmEditSpec').on('click', '.btnSaveSpec', function (e) {
        $.ajax({
            method: "POST",
            url: $('#frmEditSpec').attr('action'),
            dataType: 'json',
            data: $('#frmEditSpec').serialize(),
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            },
            success: function (data, msg) {
                // Update frontend!!
                if (data.success === true) {
                    add_row_to_view(data.row);
                    $('.modal#globalmodal').modal('hide');
                } else {
                    $('#frmEditSpec').children().find('#error-alert').removeClass('hide').children('#error-msg').html(data.message);
                }
            }
        });
    })

</script>
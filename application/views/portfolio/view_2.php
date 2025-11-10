<?php
//    var_dump($pictures_data);
?>
<style>
    div#frmUploadNewPicture {
        background-color: #e9ecef;
        padding: 20px 10px;
        margin: 10px 0;
        border-radius: .25rem;
    }
    .card {
        margin: 10px 20px;
        width: 12rem;
    }
    span.card-product-name {
        white-space: nowrap;
        margin: 5%;
        font-size: 12px;
    }
    .inactive-icon {
        position: absolute;
        font-size: 30px;
        margin: 10px;
        color: red;
    }
</style>

<a class="btn" data-toggle="collapse" href="#frmUploadNewPicture" role="button" aria-expanded="false" aria-controls="frmUploadNewPicture">
    Add <i class="fas fa-plus-circle"></i>
</a>

<div class="collapse" id="frmUploadNewPicture">
    <div class="row">
        <div class="col-6">
            <div class="form-group row">
                <div class="col-xs-12 col-sm-9">
                    <input type="file" id="new_product_image" name="new_product_image" accept="image/png, image/jpeg">
                </div>
            </div>
            <div class="form-group row">
                <label for="new_picture_notes" class="col-xs-12 col-sm-3 col-form-label">Picture notes</label>
                <div class="col-xs-12 col-sm-9">
                    <input type="text" class="form-control" id="new_picture_notes" name='new_picture_notes' value='' placeholder="Picture notes">
                </div>
            </div>
            <div class="form-group row">
                <label for="new_products_in_image" class="col-xs-12 col-sm-3 col-form-label">Products in image:</label>
                <div class="col-xs-12 col-sm-9">
                    <input type="text" class="form-control" id="new_search_product" name='new_search_product' value='' placeholder="Search products">
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group row">
                <div class="col">
                    <button class="btn btn-success float-right">
                        Save
                    </button>
                </div>
            </div>
            <div class="form-group row">
                <table id='new-colors-table' class="w-100">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<div class="pictures-container d-flex flex-wrap align-items-start">
</div>

<script>
    var pictures_data = JSON.parse('<?php echo $pictures_data?>');
    var new_pictures_data = [];

    function get_picture_template(data){
        console.log(data);
        let html_names = '';
        let product_names = '';
        let product_ids = '';
        if(data.products_assoc !== null){
            product_names = data.products_assoc.split('/').map((i) => i.trim())
            product_ids = data.products_assoc_id.split('/').map((i) => i.trim())

            for(let i = 0; i < product_ids.length; ++i){
                html_names += `<span class='card-product-name'>${product_names[i]} <i class="fas fa-window-close"></i></span>`
            }

            product_names = product_names.map((n) => `<span>${n}</span>`).join(' ')
            product_ids = product_ids.map((i) => `<span>${i.product_name}</span>`).join(' ')
        }

        return `
        <div id="${data.id}" class="card">
          <div class='card-img-container'>
            <i class="fad fa-do-not-enter inactive-icon ${isActive(data) ? 'hide' : ''}"></i>
            <img src="<?php echo base_url()?>${data.url}" class="card-img-top" style="${isActive(data) ? '' : 'opacity:0.5'}">

          </div>
          <div class="card-body">
            <p class="card-text">${data.notes}</p>
            <p class="card-text">${html_names}</p>
            <div class="d-flex justify-content-between">
                <span class="btnTogglePictureActive" data-id="${data.id}" onclick="toggle_active(this)"><i class="fal fa-${isActive(data) ? 'toggle-on' : 'toggle-off'}"></i></span>
                <span class="btnAddProductToPicture" data-id="${data.id}" onclick="open_add_product_to_picture(this)"><i class="fal fa-plus"></i></span>
                <span class="btnTrashPicture" data-id="${data.id}" onclick="delete_picture(this)"><i class="fas fa-trash"></i></span>
            </div>
          </div>
        </div>`;
    }

    function isActive(data){
        return data.active == '1'
    }

    function jx_delete_picture(picture_id){
        console.log('delete picture', picture_id);
        show_success_swal();
        // $.ajax({
        //     method: "POST",
        //     url: btnArchiveUrl,
        //     dataType: 'json',
        //     data: {
        //         'product_id': product_id,
        //         'picture_id': picture_id
        //     },
        //     error: function (jqXHR, textStatus, errorThrown) {
        //         console.log(errorThrown);
        //     },
        //     success: function (data, msg) {
        //         show_success_swal();
        //     }
        // });
    }

    function jx_activate_picture(picture_id, active){
        console.log("Activate", picture_id, active);
        show_success_swal();
        // $.ajax({
        //     method: "POST",
        //     url: btnArchiveUrl,
        //     dataType: 'json',
        //     data: {
        //         'product_id': product_id,
        //         'picture_id': picture_id
        //     },
        //     error: function (jqXHR, textStatus, errorThrown) {
        //         console.log(errorThrown);
        //     },
        //     success: function (data, msg) {
        //         show_success_swal();
        //     }
        // });
    }

    function jx_add_product_to_picture(product_id, picture_id){
        console.log("add product", product_id, 'to', picture_id);
        show_success_swal();
        // $.ajax({
        //     method: "POST",
        //     url: btnArchiveUrl,
        //     dataType: 'json',
        //     data: {
        //         'product_id': product_id,
        //         'picture_id': picture_id
        //     },
        //     error: function (jqXHR, textStatus, errorThrown) {
        //         console.log(errorThrown);
        //     },
        //     success: function (data, msg) {
        //         show_success_swal();
        //     }
        // });
    }

    function jx_delete_product_from_picture(product_id, picture_id){
        console.log('delete product', product_id, picture_id);
        show_success_swal();
        // $.ajax({
        //     method: "POST",
        //     url: btnArchiveUrl,
        //     dataType: 'json',
        //     data: {
        //         'product_id': product_id,
        //         'picture_id': picture_id
        //     },
        //     error: function (jqXHR, textStatus, errorThrown) {
        //         console.log(errorThrown);
        //     },
        //     success: function (data, msg) {
        //         show_success_swal();
        //     }
        // });
    }

    function update_view(data){
        $(".card#"+data.id).replaceWith(get_picture_template(data));
    }

    function get_data_ix(id){
        for(let index = 0; index < pictures_data.length; ++index){
            // console.log("Search", id, pictures_data[index].id);
            if(pictures_data[index].id == id){
                return index;
            }
        }
        return null;
    }

    $(document).ready(function(){
        console.log(pictures_data);
        let container = $(".pictures-container");
        for(let index = 0; index < pictures_data.length; ++index){
            let data = pictures_data[index];
            container.append(get_picture_template(data));
        }

    });

    function open_add_product_to_picture(obj){
        let data_id = $(obj).attr('data-id');
        let data_ix = get_data_ix(data_id);
        let data = pictures_data[data_ix];
        // Open search form
        console.log("Search products for picture ID", data.id)
    }

    function toggle_active(obj){
        let data_id = $(obj).attr('data-id');
        let data_ix = get_data_ix(data_id);
        let data = pictures_data[data_ix];
        data.active = isActive(data) ? '0' : '1';
        jx_activate_picture(data_id, data.active);
        pictures_data[data_ix] = data;
        update_view(data);
    };

    function delete_picture(obj){
        let data_id = $(obj).attr('data-id');
        let data = pictures_data.filter(d => d.id == data_id)[0];
        // console.log(data_id, data, data_ix);
        show_swal(
            {},
            {
                title: `Are you sure you want to delete the picture for ${data.notes}?`
            },
            {
                complete: function (t) {
                    jx_delete_picture(data_id);
                    pictures_data = pictures_data.filter(d => d.id !== data_id);
                    $(".card#"+data_id).remove();
                }
            }
        );
    };

    $('#frmUploadNewPicture').on('show.bs.collapse', function () {
        console.log("Open form, clean it maybe?");
    })

</script>
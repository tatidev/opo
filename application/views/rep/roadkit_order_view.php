<style>
td.details-control {
    background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
}
</style>

<div class='row'>
  <div class='col-xs-12 col-sm-6'>
    <div class='section-header pull-left'>
      Roadkit > <?php echo $roadkit_name?>
    </div>
  </div>
</div>

<table id="items-table" class="row-border hover" width="100%">
  <thead>
    <tr>
      <th>Spec</th>
      <th>Item #</th>
      <th>Name</th>
      <th>Color</th>
      <th>Samples</th>
      <th>Ringsets</th>
    </tr>
  </thead>
</table>

<div class="row table-overhead" style="border-bottom: 3px solid black;margin: 30px 0px;"></div>

    <!-- Shipping address Title -->
    <div id='shippingAddressWrap'>
      <div class='row' id='addressList'>
        <div class='col-xs-12'>
          <div class='section-header pull-left'>
            Shipping address
          </div>

          <div class='pull-right'>
            <a id='address-searching' role='button' class='btn btn-search-address pull-right' data-toggle="modal" data-target="#address-modal">
              <span class='hidden-xs'>Address Book  </span><span class='glyphicon glyphicon-list-alt address-gly'></span>
            </a>
          </div>

          <div class='col-xs-12 col-sm-7'>
            <div class='btn-address-containers'>
              <a role='button' class='newAddress btn btn-address-update' data-value='0'>
                Save New Address
              </a>
              <a role='button' class='updateAddress btn btn-address-update' data-value='0'>
                Update Address
              </a>
            </div>
          </div>
        </div>

      </div>
              
      <!-- Shipping address Form -->

      <div id='error_address' class="alert alert-danger alert-dismissible hide" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Please, complete all the required information
      </div>

      <div class='row'>
        <div class='col-sm-6 col-xs-12 form-group'>
          <input class="form-control input-address" id="company" name="company" placeholder="Company name" type="text" data-id=''>
        </div>
        <div class='col-sm-6 col-xs-12 form-group'>
          <input class="form-control input-address" id="attention" name="attention" placeholder="Attention" type="text" >
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-8 col-xs-12 form-group'>
          <input class='form-control input-address' id='address1' name='address1' placeholder='Address' type='text'>
        </div>
        <div class='col-sm-4 col-xs-12 form-group'>
          <input class='form-control' id='apt' name='apt' placeholder='Apt #' type='text' >
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-4 col-xs-12 form-group'>
          <input class='form-control input-address' id='city' name='city' placeholder='City' type='text' >
        </div>
        <div class='col-sm-4 col-xs-12 form-group'>
          <input class='form-control input-address' id='state' name='state' placeholder='State' type='text' >
        </div>
        <div class='col-sm-4 col-xs-12 form-group'>
          <input class='form-control input-address' id='zipcode' name='zipcode' placeholder='Zip Code' type='text' >
        </div>
      </div>
      <div class='row'>
        <div class='col-sm-4 col-xs-12 form-group'>
          <div class='row'>
            <div class='col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3 form-group shipping-service'  style='padding-bottom: 5px; border-bottom: #bfac02 solid 2px; '>
              Shipping Service
            </div>
            <div class='col-xs-12 form-group'>
              <select class='selectpicker' id='ship_via' name='ship_via'>
                <option value='G'>Ground</option>
                <option value='3D'>3 Days</option>
                <option value='2D'>2 Days</option>
                <option value='ND'>Next Day</option>
                <option value='NDA'>Next Day Air</option>
              </select>
            </div>
          </div>
        </div>
        <div class='col-sm-8 col-xs-12 form-group'>
          <textarea class="form-control" id="comments" name="comments" placeholder="Shipping and special instructions" rows="3"></textarea>
        </div>
      </div>

      <div class='row' style='margin-bottom: 20px;'>
        <div class='col-xs-4'>
          <a role='button' class='btn btn-default btn-block btn-back' data-toggle="modal" data-target="#confirmBack">
            <span class='glyphicon glyphicon-chevron-left'></span> <span class='hidden-xs'> Back</span>
          </a>
        </div>
        <div class='col-xs-8'>
          <a role='button' class='btn btn-info btn-block' onclick='proceed();' id='btnContinue'>
            Continue  <span class='glyphicon glyphicon-chevron-right'></span>
          </a>
        </div>
      </div>
    
  </div>

<script>
/* Formatting function for row details - modify as you need */
function format ( d ) {
    // `d` is the original data object for the row
    return '<table cellpadding="5" cellspacing="0" border="0" style="width:100%; padding-left:50px;">'+
        '<tr>'+
            '<td>Width:</td>'+
            '<td>'+d.width+'</td>'+
            '<td>Repeats:</td>'+
            '<td>H: '+d.hrepeat+'" / V: '+d.vrepeat+'"</td>'+
            '<td>Abrasion:</td>'+
            '<td>'+d.abrasion+'</td>'+
            '<td>Firecode:</td>'+
            '<td>'+d.firecode+'</td>'+
            '<td>Origin:</td>'+
            '<td>'+d.origin+'</td>'+
        '</tr>'+
    '</table>';
}
 
$(document).ready(function() {
    var table = $('#items-table').DataTable( {
        ajax: {
          'url': '<?php echo site_url('roadkit_order/get_roadkit_items')?>',
          'type': 'post',
          'data': {
            'roadkit_id': <?php echo $roadkit_id?>
          }
        },
        columns: [
            {
                "className":      'details-control text-center',
                "orderable":      false,
                "data":           null,
                "defaultContent": ''
            },
            { "data": "item_num" },
            { "data": "name" },
            { "data": "color" },
            { "data": "samples",
              "className": "text-center",
              "orderable": false,
              "searchable": false
            },
            { "data": "ringsets",
              "className": "text-center",
              "orderable": false,
              "searchable": false
            }
        ],
        order: [[2, 'asc']]
    } );
     
    // Add event listener for opening and closing details
    $('#items-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );
} );
</script>
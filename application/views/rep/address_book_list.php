<table id="list-table" class="row-border hover" width="100%">
  <thead>
    <tr>
      <th>Company</th>
      <th>Attention</th>
      <th>Address</th>
      <th>City/State/Zipcode</th>
      <th>Action</th>
    </tr>
  </thead>
</table>

<script>

$(document).ready(function(){
  
        var table = $('#list-table').DataTable( {
        ajax: {
          'url': '<?php echo site_url('address_book/get_addresses')?>',
          'type': 'post',
          'data': {
            
          }
        },
        columns: [
            { "data": "company" },
            { "data": "attention" },
            { "data": "address" },
            { "data": "combined" },
            {
                "className":      'details-control text-center',
                "orderable":      false,
                "data":           null,
                "defaultContent": ''
            }
        ],
        order: [[1, 'asc']]
    } );
          
});

</script>
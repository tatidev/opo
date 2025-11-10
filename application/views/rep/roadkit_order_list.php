<table id="list-table" class="row-border hover" width="100%">
  <thead>
    <tr>
      <th>Order #</th>
      <th>Date</th>
      <th>For</th>
      <th>Status</th>
      <th>Details</th>
    </tr>
  </thead>
</table>

<script>

$(document).ready(function(){
  
        var table = $('#list-table').DataTable( {
        ajax: {
          'url': '<?php echo site_url('roadkit_order/get_orders')?>',
          'type': 'post',
          'data': {
            
          }
        },
        columns: [
            { "data": "order_num",
              "className": "text-center"
            },
            { "data": "date_created",
              "className": "text-center" 
            },
            { "data": "company",
              "className": "text-center"
            },
            { "data": "status",
              "className": "text-center"
            },
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
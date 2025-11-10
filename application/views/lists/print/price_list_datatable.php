<?php echo asset_links($library_head)?>
<title>Price List - <?php echo $info['name']?></title>


<div class='row' style='display: flex; align-items: flex-end;'>
  <div class='col'>
    <img src='https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png' class=''><br>
    5788 Venice Blvd.<br>
    Los Angeles, CA 90019<br>
    +1-323-549-3489 / www.opuzen.com
  </div>
  <div class='col'>
    <p class='pull-right m-0'>
      <b><?php echo $info['name']?></b><br>
      Last modified: <?php echo $info['date_modif']?>
    </p>
      
  </div>
</div>

<div class='row my-4'>
  <div class='col'>
    <table></table>
  </div>
</div>

<script>
  
$(document).ready(function() {
    $('table').DataTable( {
        'dom': 'Bfrtip',
        'data': <?php echo json_encode($items)?>,
        'buttons': [
            {
              extend: 'colvis'
            },
            {
                extend: 'print',
                customize: function ( win ) {
                    $(win.document.body)
                        .css( 'font-size', '10pt' )
                        .prepend(
                            '<img src="http://datatables.net/media/images/logo-fade.png" style="position:absolute; top:0; left:0;" />'
                        );
 
                    $(win.document.body).find( 'table' )
                        .addClass( 'compact' )
                        .css( 'font-size', 'inherit' );
                }
            }
        ]
    } );
} );

</script>

<?php echo asset_links($library_foot)?>
<?php echo asset_links($library_head)?>
<title> Product Price History - <?php echo $product_name?></title>

<div class='row' style='display: flex; align-items: flex-end;'>
  <div class='col-10'>
    <h3><?php echo $product_name?> <small>(<?php echo $vendors_name?><?php echo (strlen($vendor_product_name) > 0 ? "'s '$vendor_product_name' " : '')?>)</small></h3>
  </div>
  <div class='col'>
    <small class='pull-right m-0'><?php echo date('Y-m-d')?></small>
  </div>
</div>

<br>
<h5>Price History</h5>
<?php echo $tablePrices?>

<h5>Costs History</h5>
<?php echo $tableCosts?>

<?php echo asset_links($library_foot)?>

<button id='btnCollapseConfig' class="btn btn-info pull-left" style='display:none;' type="button" data-toggle="collapse" data-target="#collapseConfig" aria-expanded="false" aria-controls="collapseConfig">
  <i class="fa fa-eye" aria-hidden="true"></i>
</button>
<br><br><br>
<div class="collapse" id="collapseConfig">
  <a href='http://www.chartjs.org/samples/latest/charts/bar/stacked-group.html' target='_blank'>example</a>
  <canvas id="myChart"></canvas>
  <script src="<?php echo asset_url()?>others/Chart.v2.7.2.bundle.min.js"></script>
  <script>

  var ctx = document.getElementById("myChart");
  var myChart = new Chart(ctx, {
      type: 'bar',
      data: {
          labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
          datasets: [{
              label: '# of Votes',
              data: [12, 19, 3, 5, 2, 3],
              backgroundColor: [
                  'rgba(255, 99, 132, 0.2)',
                  'rgba(54, 162, 235, 0.2)',
                  'rgba(255, 206, 86, 0.2)',
                  'rgba(75, 192, 192, 0.2)',
                  'rgba(153, 102, 255, 0.2)',
                  'rgba(255, 159, 64, 0.2)'
              ],
              borderColor: [
                  'rgba(255,99,132,1)',
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)'
              ],
              borderWidth: 1
          }]
      },
      options: {
          scales: {
              yAxes: [{
                  ticks: {
                      beginAtZero:true
                  }
              }]
          }
      }
  });

  </script>
</div>

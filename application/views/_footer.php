
    </div> <!-- div#nav-content end -->

    <!-- Shared Modal -->
    <div class="modal fade" id="globalmodal" style='z-index:9998;' tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
      <div class="modal-dialog" style='max-width:1000px;' role="document">
        <div class="modal-content p-4">
          <!--
              Data inserted via ajax
           -->
        </div>
      </div>
    </div>
    <script>
      function close_global_modal(){
        $('#globalmodal').modal('hide');
      }
    </script>
    </div> <!-- End Body container -->
    <div id='nav-foot' class='footer'>
      <small class='float-right' style='font-size: 0.6em; line-height:15px;'>
				OPMS <?php //echo constant('APP_VERSION')?><br>
				Branch: <?php //echo constant('APP_BRANCH')?><br>
				Stage: <?php //echo constant('ENVIRONMENT')?><br>
				Server: <?php //echo array_get($_SERVER, "SERVER_ADDR", null)?><br>
				DB: <?php //echo $this->db->hostname?> / <?php //echo $this->db->database?>
			</small>
      
      <!-- Insert Assets -->
      <?php echo asset_links($library_foot)?>
      
      <script>
        var t0;
        var t1;
        var total;
        $( document ).ajaxComplete(function( event, request, settings ) {
          
          t1 = performance.now();
          console.log("Footer: ax " + ( (t1 - t0)*1/(1000) ).toFixed(4) + " s");
          setTimeout(function(){
            //$('#nav-content').css('opacity', '1');
            $('.loader').addClass('hide');
          }, 300);
        });
        
        $( document ).ajaxStart(function( event, request, settings ) {
          //$('#nav-content').css('opacity', '0.3');
          $('.loader').removeClass('hide');
          t0 = performance.now();
        });

      </script>
      
    </div>

  </body>
</html>
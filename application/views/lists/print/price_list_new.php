<?php echo asset_links($library_head)?>
<div id='print-content' class=''>

	<div class='mx-2'>

		<div class='row' style='display: flex; align-items: flex-end;'>
			<div class='col'>
				<img src='https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png' class=''><br>
				5788 Venice Blvd.<br>
				Los Angeles, CA 90019<br>
				+1-323-549-3489 / www.opuzen.com
			</div>
			<div class='col'>
				<p class='float-right m-0'>
					<b><?php echo $table['title']?></b><br>
					<?php echo 'Print date: ' . date('m-d-Y')?>
				</p>
			</div>
		</div>

		<div class='row'>
			<div class='col'>
				<p class='float-right m-0' style='font-size:12px;'>
					<small>SBO: Stocked by Opuzen / SBV: Stocked by Vendor / MBO: Manufactured by Opuzen / WTO: Weave to Order</small>
				</p>
			</div>
		</div>

	</div>

	<div id='items_table' class='row my-3'>
		<div class='col'>
			<?php echo $table['html']?>
		</div>
	</div>

</div>
<?php echo asset_links($library_foot)?>
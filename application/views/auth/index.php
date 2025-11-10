<div class='row justify-content-center'>
  <div class='col-md-12'>
		
		<div class='row table-heading'>
    	<div class='col-6 mx-auto text-center'>
				<h3 class='display-4'><?php echo lang('index_heading');?></h3>
				<p><?php echo lang('index_subheading');?></p>
			</div>
		</div>		
		<div class='row table-body'>

					<div class='col-12 <?php echo (is_null($message) ? 'hide' : '')?>'>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<div id="infoMessage" class=''><?php echo $message;?></div>
						</div>

					</div>

					<div class='col-12'>
						
						<table id='users_table' class='table table-hover table-sm' style='width:100%;' cellpadding=0 cellspacing=10>
							<thead>
								<tr>
									<th>Username</th>
									<th><?php echo lang('index_fname_th');?></th>
									<th><?php echo lang('index_lname_th');?></th>
									<th><?php echo lang('index_email_th');?></th>
									<th><?php echo lang('index_groups_th');?></th>
									<th>Showroom</th>
									<th><?php echo lang('index_status_th');?></th>
									<th><?php echo lang('index_action_th');?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($users as $user):?>
								<tr>
									<td><?php echo htmlspecialchars($user->username,ENT_QUOTES,'UTF-8');?></td>
												<td><?php echo htmlspecialchars($user->first_name,ENT_QUOTES,'UTF-8');?></td>
												<td><?php echo htmlspecialchars($user->last_name,ENT_QUOTES,'UTF-8');?></td>
												<td><?php echo htmlspecialchars($user->email,ENT_QUOTES,'UTF-8');?></td>
									<td>
										<?php foreach ($user->groups as $group):?>
											<?php echo anchor("auth/edit_group/".$group->id, htmlspecialchars($group->name,ENT_QUOTES,'UTF-8')) ;?><br />
														<?php endforeach?>
									</td>
									<td>
										<?php foreach ($user->showrooms as $group):?>
											<?php echo htmlspecialchars($group->description,ENT_QUOTES,'UTF-8');?><br />
														<?php endforeach?>
									</td>
									<td><?php echo ($user->active) ? anchor("auth/deactivate/".$user->id, lang('index_active_link')) : anchor("auth/activate/". $user->id, lang('index_inactive_link'));?></td>
									<td><?php echo anchor("auth/edit_user/".$user->id, '<i class="fas fa-pen-square"></i>') ;?></td>
								</tr>
							<?php endforeach;?>
							</tbody>
						</table>
	
					</div>
		</div>
		
	</div>
</div>

<script>

	$('table#users_table').DataTable({
      	"dom": '< <"input-group my-4" <"input-group-prepend"<"input-group-text"<"fas fa-search">>> f> <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
//     "dom": '< <"d-flex flex-row justify-content-between align-items-center my-4" B <"d-flex flex-column" <"items-filter"> l> > <t> i p >',
// 		"dom": '< <"d-flex flex-row justify-content-between align-items-center" B <"d-flex flex-column" f <"items-filter"> l> > <t> i p >',
		"buttons": [
				custom_buttons.back( function(){ window.location.href = "<?php echo site_url()?>"; } ),
        custom_buttons.view(),
        {
                text: '<i class="fa fa-plus" aria-hidden="true"></i> <?php echo lang('index_create_user_link')?> ',
                className: 'btn btn-outline-success',
								action: function( e, dt, node, config ) {
									window.location.href = "<?php echo site_url('auth/create_user')?>";
								}
        },
        {
                text: '<i class="fa fa-plus" aria-hidden="true"></i> <?php echo lang('index_create_group_link')?>',
                className: 'btn btn-outline-success',
								action: function( e, dt, node, config ) {
									window.location.href = "<?php echo site_url('auth/create_group')?>";
								}
        },
				custom_buttons.export()
        
      ]
	});

</script>
<div class="dropdown inline-block" style="padding-top: 15px;">
	<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
	<?php echo __('History'); ?>
		<span class="caret"></span>
	</button>
	<ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
		<li>
			<a href="<?php echo Router::url([
				'controller' => 'Notifications',
				'action' => 'service',
				$object['Objects']['object_id']
			]); ?>"><i class="fa fa-envelope-o"></i> <?php echo __('Notificatios'); ?></a>
		</li>
		<li>
			<a href="<?php echo Router::url([
				'controller' => 'Servicechecks',
				'action' => 'index',
				$object['Objects']['object_id']
			]); ?>"><i class="fa fa-ellipsis-h"></i> <?php echo __('Service checks'); ?></a>
		</li>
		<li>
			<a href="<?php echo Router::url([
				'controller' => 'Statehistory',
				'action' => 'service',
				$object['Objects']['object_id']
			]); ?>"><i class="fa fa-history"></i> <?php echo __('State history'); ?></a>
		</li>
		<li>
			<a href="<?php echo Router::url([
				'controller' => 'Acknowledgements',
				'action' => 'service',
				$object['Objects']['object_id']
			]); ?>"><i class="fa fa-comments"></i> <?php echo __('Acknowledgements'); ?></a>
		</li>
	</ul>
</div>

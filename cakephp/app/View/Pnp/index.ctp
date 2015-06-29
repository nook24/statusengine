<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h3>
			<i class="fa fa-area-chart fa-lg"></i>&nbsp;
			<a href="<?php echo Router::url([
					'controller' => 'Services',
					'action' => 'details',
					$object['Objects']['object_id']
				]); ?>">
				<?php echo h($object['Objects']['name2']); ?> 
			</a>
			(<?php echo h($object['Objects']['name1']);?>)
			</h3>
		</div>
	</div>
	<hr />
	<div class="row">
		<div class="col-xs-12">
			<iframe class="pnpFrame" src="/pnp4nagios/graph?host=<?php echo rawurlencode($object['Objects']['name1']);?>&srv=<?php echo rawurlencode($object['Objects']['name2']);?>"></iframe>
		</div>
	</div>
</div>

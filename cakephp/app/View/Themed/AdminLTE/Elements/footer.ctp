<footer class="main-footer">
	<div class="row">
		<div class="col-md-4 hidden-xs">
			<a href="https://github.com/nook24/statusengine" target="_blank" class="text-muted">
				<i class="fa fa-github"></i>
				<b><?php echo __('Contribute to Statusengine');?></b>
			</a>
		</div>

		<div class="col-xs-6 hidden-sm hidden-md hidden-lg">
			<small>
				<i class="fa fa-github"></i>
				<b><?php echo __('Contribute to Statusengine');?></b>
			</small>
		</div>

		<div class="col-md-4 hidden-xs">
			<center> <!-- because center tag is the only option that simply gets the job done -.- -->
				<a href="http://cakephp.org" target="_blank">
					<?php echo $this->Html->image('cake-logo-smaller2.png', ['border' => '0']); ?>
				</a>
			</center>
		</div>

		<?php Configure::load('Statusengine'); ?>
		<div class="hidden-xs col-md-4">
			<span class="pull-right">
				<b>Statusengine - </b> <?php echo h(STATUSENIGNE_VERSION);?>
			</span>
		</div>

		<div class="col-xs-6 hidden-sm hidden-md hidden-lg">
			<small><b>Statusengine - </b> <?php echo h(STATUSENIGNE_VERSION);?></small>
		</div>

	</div>
</footer>

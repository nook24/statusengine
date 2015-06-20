<nav class="navbar navbar-default navbar-static-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo Router::url('/'); ?>"><?php echo __('Statusengine');?></a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'index']); ?>">
					<i class="fa fa-hdd-o"></i>
					<?php echo __('Hosts');?></a>
				</li>
				<li><a href="#">
					<i class="fa fa-cog"></i>
					<?php echo __('Services');?></a>
				</li>
				<li>
					<a href="#">
						<i class="fa fa-exclamation-triangle"></i>
						<?php echo __('Problems');?></a>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo __('More');?> <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<li><a href="#"><?php echo __('Host groups');?></a></li>
						<li><a href="#"><?php echo __('Service groups');?></a></li>
						<li><a href="<?php echo Router::url(['controller' => 'Objects', 'action' => 'index']); ?>"><?php echo __('Objects');?></a></li>
						<li class="divider"></li>
						<li><a href="#"><?php echo __('Downtimes');?></a></li>
						<li><a href="#"><?php echo __('Comments');?></a></li>
						<li><a href="<?php echo Router::url(['controller' => 'Performance', 'action' => 'index']); ?>"><?php echo __('Performance info');?></a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a href="<?php echo Router::url([
						'controller' => 'login',
						'action' => 'logout']); ?>">
							<i class="fa fa-sign-out"></i>
							<?php echo __('Logout');?>
					</a>
				</li>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>
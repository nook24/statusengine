<?php
$color = [
	0 => 'success',
	1 => 'warning',
	2 => 'danger',
	3 => 'muted'
];

$icon = [
	0 => 'fa-check',
	1 => 'fa-bell',
	2 => 'fa-exclamation-triangle',
	3 => 'fa-question-circle'
];
?>
<header class="main-header">
<!-- Logo -->
<a href="<?php echo Router::url('/'); ?>" class="logo">
	<!-- mini logo for sidebar mini 50x50 pixels -->
	<span class="logo-mini">S</span>
	<!-- logo for regular state and mobile devices -->
	<span class="logo-lg"><b><?php echo h($topMenuAppName); ?></b></span>
</a>

<!-- Header Navbar: style can be found in header.less -->
<nav class="navbar navbar-static-top" role="navigation">
	<!-- Sidebar toggle button-->
	<a href="#" class="sidebar-toggle hidden-sm hidden-md hidden-lg" data-toggle="offcanvas" role="button">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</a>
	<div class="navbar-custom-menu">
	<ul class="nav navbar-nav">
		</li>
		<!-- Notifications: style can be found in dropdown.less -->
		<li class="dropdown tasks-menu">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-exclamation-triangle"></i>
				<span class="label label-warning"><?php echo $topMenuProblemsCounter; ?></span>
			</a>
			<ul class="dropdown-menu">
				<li class="header"><?php echo $topMenuProblemsCounter; ?> <?php echo __('problems detected!'); ?></li>
				<li>
					<ul class="menu">
						<?php if(empty($topMenuProblems)): ?>
							<li>
								<a href="javascript:void(0)">
									<i class="fa fa-check text-success"></i> <span class="text-success"><?php echo __('No problems detected'); ?></span>
								</a>
							</li>
						<?php endif; ?>
						<?php foreach($topMenuProblems as $problem): ?>
							<?php
							$currentState = $problem['Servicestatus']['current_state'];
							if($currentState > 3):
								$currentState = 3;
							endif;
							?>
							<li>
								<a href="<?php echo Router::url(['controller' => 'Services', 'action' => 'details', $problem['Service']['service_object_id']]); ?>">
									<i class="fa <?php echo $icon[$currentState]; ?> text-<?php echo $color[$currentState]; ?>"></i> <?php echo h($problem['Objects']['name1']); ?>/<?php echo h($problem['Objects']['name2']); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li class="footer"><a href="<?php echo Router::url(['controller' => 'Services', 'action' => 'problem']); ?>"><?php echo __('View all problems'); ?></a></li>
			</ul>
		</li>

		<li class="dropdown hidden-xs">
			<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-clock-o"></i>
				<span class="hidden-xs"><?php echo date('H:i'); ?> (<?php echo date_default_timezone_get(); ?>)</span>
			</a>
		</li>

		<li class="dropdown user user-menu">
			<a href="<?php echo Router::url([
				'controller' => 'Users',
				'action' => 'logout']); ?>">
					<i class="fa fa-sign-out"></i>
					<?php echo __('Logout');?>
			</a>
		</li>
	</ul>
	</div>
</nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
	<!-- search form -->
	<?php
	echo $this->Form->create('Search', [
		'inputDefaults' => [
			'div' => 'form-group has-feedback',
			'label' => false,
			'wrapInput' => false,
			'class' => 'form-control'
		],
		'class' => 'sidebar-form',
		'url' => ['controller' => 'Search', 'action' => 'index'],
	]);
	?>

	<div class="input-group">
		<?php echo $this->Form->input('query', [
			'name' => 'query',
			'placeholder' => __('Search...'),
			'class' => 'form-control',
			'div' => false
		]); ?>
		<span class="input-group-btn">
			<?php echo $this->Form->button('<i class="fa fa-search"></i>', [
				'type' => 'submit',
				'name' => 'search',
				'value' => 'submit',
				'div' => false,
				'class' => 'btn btn-flat',
				'escape' => false
			]); ?>
		</span>
	</div>
	<?php echo $this->Form->end(); ?>
	<!-- /.search form -->
	<!-- sidebar menu: : style can be found in sidebar.less -->
	<ul class="sidebar-menu">
	<li class="header"><?php echo __('Navigation'); ?></li>

	<li class="<?php echo (strtolower($this->params['controller']) == 'home')?'active':''; ?>"><a href="<?php echo Router::url('/'); ?>">
		<i class="fa fa-home"></i>
		<?php echo __('Home');?></a>
	</li>

	<li class="<?php echo (strtolower($this->params['controller']) == 'hosts')?'active':''; ?>"><a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'index']); ?>">
		<i class="fa fa-hdd-o"></i>
		<?php echo __('Hosts');?></a>
	</li>

	<?php
	$controller = strtolower($this->params['controller']);
	$action = strtolower($this->params['action']);
	$active = '';
	if($controller == 'services' && $action == 'index'):
		$active = 'active';
	endif;
	?>
	<li class="<?php echo $active ?>"><a href="<?php echo Router::url(['controller' => 'Services', 'action' => 'index']); ?>">
		<i class="fa fa-cog"></i>
		<?php echo __('Services');?></a>
	</li>

	<?php
	$controller = strtolower($this->params['controller']);
	$action = strtolower($this->params['action']);
	$active = '';
	if($controller == 'services' && $action == 'problem'):
		$active = 'active';
	endif;
	?>
	<li class="<?php echo $active ?>"><a href="<?php echo Router::url(['controller' => 'Services', 'action' => 'problem']); ?>">
			<i class="fa fa-exclamation-triangle"></i>
			<?php echo __('Problems');?></a>
	</li>

	<?php
	$active = '';
	if(in_array(strtolower($this->params['controller']), [
		'hostgroups',
		'servicegroups',
		'downtimes',
		'acknowledgements',
		'users',
		//'statuspages'
	])):
		$active = 'active';
	endif;
	?>
	<li class="treeview <?php echo $active; ?>">
		<a href="#">
			<i class="fa fa-folder-open text-aqua"></i> <span><?php echo __('More'); ?></span> <i class="fa fa-angle-left pull-right"></i>
		</a>
		<ul class="treeview-menu">
			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'hostgroups'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Hostgroups', 'action' => 'index']); ?>">
					<i class="fa fa-server"></i>&nbsp;
					<?php echo __('Host groups');?>
				</a>
			</li>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'servicegroups'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Servicegroups', 'action' => 'index']); ?>">
					<i class="fa fa-cogs"></i>&nbsp;
					<?php echo __('Service groups');?>
				</a>
			</li>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'downtimes'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Downtimes', 'action' => 'index']); ?>">
					<i class="fa fa-plug"></i>&nbsp;
					<?php echo __('Downtimes');?>
				</a>
			</li>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'acknowledgements'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Acknowledgements', 'action' => 'index']); ?>">
					<i class="fa fa-comments"></i>&nbsp;
					<?php echo __('Acknowledgements');?>
				</a>
			</li>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'statuspages'):
				$active = 'active';
			endif;
			/*
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Statuspages', 'action' => 'index']); ?>">
					<i class="fa fa-cloud"></i>&nbsp;
					<?php echo __('Status pages');?>
				</a>
			</li>
			*/
			?>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'users'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Users', 'action' => 'index']); ?>">
					<i class="fa fa-users"></i>&nbsp;
					<?php echo __('Users');?>
				</a>
			</li>
		</ul>
	</li>

	<?php
	$active = '';
	if(in_array(strtolower($this->params['controller']), [
		'performance',
		'logentries',
		'objects',
	])):
		$active = 'active';
	endif;
	?>
	<li class="treeview <?php echo $active; ?>">
		<a href="#">
			<i class="fa fa-heartbeat text-red"></i> <span><?php echo __('Debug'); ?></span> <i class="fa fa-angle-left pull-right"></i>
		</a>
		<ul class="treeview-menu">
			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'performance'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Performance', 'action' => 'index']); ?>">
					<i class="fa fa-rocket"></i>&nbsp;
					<?php echo __('Performance info');?>
				</a>
			</li>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'logentries'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Logentries', 'action' => 'index']); ?>">
					<i class="fa fa-align-left"></i>&nbsp;
					<?php echo __('Log entries');?>
				</a>
			</li>

			<?php
			$active = '';
			if(strtolower($this->params['controller']) == 'objects'):
				$active = 'active';
			endif;
			?>
			<li class="<?php echo $active; ?>">
				<a href="<?php echo Router::url(['controller' => 'Objects', 'action' => 'index']); ?>">
					<i class="fa fa-database"></i>&nbsp;
					<?php echo __('Objects');?>
				</a>
			</li>
		</ul>
	</li>

	</ul>
</section>
<!-- /.sidebar -->
</aside>

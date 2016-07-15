<?php
/**
* Copyright (C) 2015 Daniel Ziegler <daniel@statusengine.org>
*
* This file is part of Statusengine.
*
* Statusengine is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* (at your option) any later version.
*
* Statusengine is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Statusengine.  If not, see <http://www.gnu.org/licenses/>.
*/
$this->Paginator->options(['url' => Hash::merge($this->params['named'], $this->params['pass'])]);
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

<section class="content-header hidden-sm hidden-md hidden-lg">
	<h1>
		<i class="fa fa-envelope-o"></i>
		<a href="<?php echo Router::url([
				'controller' => 'Services',
				'action' => 'details',
				$object['Objects']['object_id']
		]); ?>">
			<?php echo __('Service'); ?>
		</a>
		<small><?php echo __('Notifications'); ?></small>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-envelope-o"></i> <?php echo __('Notifications of'); ?>
						<a href="<?php echo Router::url([
								'controller' => 'Services',
								'action' => 'details',
								$object['Objects']['object_id']
							]); ?>">
							<?php echo h($object['Objects']['name2']); ?>
						</a>
						(<?php echo h($object['Objects']['name1']); ?>)
					</h3>
				</div>
				<div class="col-xs-2 col-sm-2">
					<?php echo $this->element('service_history'); ?>
				</div>
				<?php echo $this->Filter->render([
						'class' => 'col-xs-10 col-sm-2',
						'wrapRow' => false,
						'wrapStyle' => 'padding-top: 15px;'
				]);?>
			</div>
			<div class="box-body">
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('ContactObject.name1', __('Contact')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Notification.start_time', __('Date')); ?></div>
				<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('CommandObject.name1', __('Via')); ?></div>
				<div class="col-sm-5 hidden-xs"><?php echo $this->Paginator->sort('Notification.output', __('Output')); ?></div>
				<?php foreach($notifications as $key => $notification): ?>
					<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
						<div class="col-md-2 hidden-xs">
							<?php
							$servicestate = $notification['Notification']['state'];
							if($servicestate > 3){
								$servicestate = 3;
							}
							?>
							<span class="btn btn-xs btn-default">
								<i class="fa <?php echo $icon[$servicestate]; ?> text-<?php echo $color[$servicestate]; ?>"></i>
							</span>
							<?php echo h($notification['ContactObject']['name1']);?>
						</div>

						<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-<?php echo $color[$servicestate]; ?>">
							<h5>
								<span class="label label-<?php echo $color[$servicestate]; ?>"><i class="fa <?php echo $icon[$servicestate]; ?>"></i></span>&nbsp;
								<?php echo h($notification['ContactObject']['name1']);?>
							</h5>
						</div>

						<div class="col-xs-12 col-sm-2">
							<?php echo $this->Time->format($notification['Notification']['start_time'], '%H:%M %d.%m.%Y');?>
						</div>
						<div class="col-xs-12 col-sm-3">
							<?php echo h($notification['CommandObject']['name1']); ?>
						</div>
						<div class="col-sm-5 hidden-xs">
							<?php echo h($notification['Notification']['output']); ?>
						</div>
						<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
							&nbsp;
						</div>
					</div>
				<?php endforeach; ?>

				<?php if(empty($notifications)):?>
					<div class="col-xs-12 text-center text-danger">
						<br />
						<em>
							<?php echo __('No notifications found for this service'); ?>
						</em>
					</div>
				<?php endif;?>
				<?php echo $this->element('paginator'); ?>
			</div>
		</div>
	</div>
</div>
</section>

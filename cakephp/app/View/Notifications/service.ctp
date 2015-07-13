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
?>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-sm-10">
			<h3>
			<i class="fa fa-envelope-o fa-lg"></i>&nbsp;
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
		<div class="col-xs-12 col-sm-2">
			<?php echo $this->element('service_history'); ?>
		</div>
		<div class="col-xs-12">
			<h5><?php echo __('Notifications'); ?></h5>
		</div>
	</div>
	<hr />
	<div class="row">
		<?php echo $this->Filter->render();?>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('ContactObject.name1', __('Contact')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Notification.start_time', __('Date')); ?></div>
		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('CommandObject.name1', __('Via')); ?></div>
		<div class="col-sm-5 hidden-xs"><?php echo $this->Paginator->sort('Notification.output', __('Output')); ?></div>
		<?php foreach($notifications as $notification): ?>
			<div class="col-xs-12 no-padding">
				<?php $borderClass = $this->Status->serviceBorder($notification['Notification']['state']); ?>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?> <?php echo $borderClass;?>_first">
					<?php echo h($notification['ContactObject']['name1']);?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($notification['Notification']['start_time'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-xs-12 col-sm-3 <?php echo $borderClass; ?>">
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
				<em>
					<?php echo __('No notifications found for this service'); ?>
				</em>
			</div>
		<?php endif;?>

		<?php echo $this->element('paginator'); ?>

	</div>
</div>

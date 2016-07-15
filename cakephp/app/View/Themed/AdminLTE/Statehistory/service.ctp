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
		<i class="fa fa-history"></i>
		<a href="<?php echo Router::url([
				'controller' => 'Services',
				'action' => 'details',
				$object['Objects']['object_id']
		]); ?>">
			<?php echo __('Service'); ?>
		</a>
		<small><?php echo __('Statehistory'); ?></small>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-history"></i> <?php echo __('Statehistory of'); ?>
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
				<div class="col-md-2 hidden-xs"><?php echo $this->Paginator->sort('Statehistory.state_time', __('Date')); ?></div>
				<div class="col-md-2 hidden-xs"><?php echo __('Check attempt'); ?></div>
				<div class="col-md-1 hidden-xs"><?php echo $this->Paginator->sort('Statehistory.state_type', __('State type')); ?></div>
				<div class="col-md-7 hidden-xs"><?php echo $this->Paginator->sort('Statehistory.output', __('Output')); ?></div>
				<?php foreach($statehistory as $key => $record): ?>
					<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
						<div class="col-md-2 hidden-xs">
							<?php
							$servicestate = $record['Statehistory']['state'];
							if($servicestate > 3){
								$servicestate = 3;
							}
							?>
							<span class="btn btn-xs btn-default">
								<i class="fa <?php echo $icon[$servicestate]; ?> text-<?php echo $color[$servicestate]; ?>"></i>
							</span>
							<?php echo $this->Time->format($record['Statehistory']['state_time'], '%H:%M %d.%m.%Y');?>
						</div>

						<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-<?php echo $color[$servicestate]; ?>">
							<h5>
								<span class="label label-<?php echo $color[$servicestate]; ?>"><i class="fa <?php echo $icon[$servicestate]; ?>"></i></span>&nbsp;
								<?php echo $this->Time->format($record['Statehistory']['state_time'], '%H:%M %d.%m.%Y');?>
							</h5>
						</div>

						<div class="col-xs-12 col-md-2">
							<?php echo h($record['Statehistory']['current_check_attempt']); ?> / <?php echo h($record['Statehistory']['max_check_attempts']); ?>
						</div>
						<div class="col-xs-12 col-md-1">
							<?php
							if($record['Statehistory']['state_type'] == 0):
								echo __('Soft');
							else:
								echo __('Hard');
							endif;
							?>
						</div>
						<div class=" col-xs-12 col-md-7">
							<?php echo h($record['Statehistory']['output']); ?>
						</div>
						<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
							&nbsp;
						</div>
					</div>
				<?php endforeach; ?>

				<?php if(empty($statehistory)):?>
					<div class="col-xs-12 text-center text-danger">
						<br />
						<em>
							<?php echo __('No state history records found for this service'); ?>
						</em>
					</div>
				<?php endif;?>

				<?php echo $this->element('paginator'); ?>
			</div>
		</div>
	</div>
</div>
</section>

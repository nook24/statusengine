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
$this->Paginator->options(['url' => $this->params['named']]);
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
		<i class="fa fa-cog"></i>
		<?php echo __('Services'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-cog"></i> <?php echo __('Services'); ?></h3>
				</div>
				<?php echo $this->Filter->render([
						'class' => 'col-xs-12 col-sm-4',
						'wrapRow' => false,
						'wrapStyle' => 'padding-top: 15px;'
				]);?>
			</div>
			<div class="box-body">
				<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Service description')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Servicestatus.last_check', __('Last Check')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Servicestatus.last_state_change', __('State since')); ?></div>
				<div class="col-sm-5 hidden-xs"><?php echo $this->Paginator->sort('Servicestatus.output', __('Output')); ?></div>

				<?php $hostName = null; ?>
				<?php foreach($services as $key => $service): ?>
					<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
						<?php
						$servicestate = $service['Servicestatus']['current_state'];
						if($servicestate > 3):
							$servicestate = 3;
						endif;
						if($hostName != $service['Objects']['name1']):
							$hostName = $service['Objects']['name1'];
							?>
							<div class="col-xs-12 bg-info">
								<div style="padding: 5px;">
									<i class="fa fa-hdd-o"></i>
									&nbsp;
									<a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'details', $service['Host']['host_object_id']]); ?>"><?php echo h($hostName);?></a>
								</div>
							</div>
						<?php endif;?>

						<div class="col-md-3 hidden-xs" style="padding-top:2px;">
							<a href="<?php echo Router::url(['action' => 'details', $service['Service']['service_object_id']]); ?>">
								<span class="btn btn-xs btn-default">
									<i class="fa <?php echo $icon[$servicestate]; ?> text-<?php echo $color[$servicestate]; ?>"></i>
								</span>

								<?php if($service['Servicestatus']['problem_has_been_acknowledged'] == 1): ?>
									<span class="btn btn-xs btn-default">
										<i class="fa fa-comments" title="<?php echo __('Acknowledged'); ?>"></i>
									</span>
								<?php endif; ?>

								<?php if($service['Servicestatus']['scheduled_downtime_depth'] > 0): ?>
									<span class="btn btn-xs btn-default">
										<i class="fa fa-plug" title="<?php echo __('Scheduled downtime'); ?>"></i>
									</span>
								<?php endif; ?>

								<?php echo h($service['Objects']['name2']);?>
							</a>
						</div>

						<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-<?php echo $color[$servicestate]; ?>">
							<a href="<?php echo Router::url(['action' => 'details', $service['Service']['service_object_id']]); ?>">
								<h5>
									<span class="label label-<?php echo $color[$servicestate]; ?>"><i class="fa <?php echo $icon[$servicestate]; ?>"></i></span>&nbsp;
									<?php echo h($service['Objects']['name2']);?>
									<?php if($service['Servicestatus']['problem_has_been_acknowledged'] == 1): ?>
										<span class="label label-primary"><i class="fa fa-comments"></i></span>
									<?php endif; ?>
									<?php if($service['Servicestatus']['scheduled_downtime_depth'] > 0): ?>
										<span class="label label-primary"><i class="fa fa-plug"></i></span>
									<?php endif; ?>
								</h5>
							</a>
						</div>

						<div class="col-xs-12 col-sm-2">
							<?php echo $this->Time->format($service['Servicestatus']['last_check'], '%H:%M %d.%m.%Y');?>
						</div>
						<div class="col-xs-12 col-sm-2">
							<?php echo $this->Time->format($service['Servicestatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
						</div>
						<div class="col-xs-12 col-sm-5">
							<?php //var_dump($service['Servicestatus']['output']); ?>
							<?php echo $this->Status->h($service['Servicestatus']['output']); ?>
						</div>
						<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
							&nbsp;
						</div>
					</div>
				<?php endforeach; ?>

				<?php if(empty($services)):?>
					<div class="col-xs-12 text-center text-danger">
						<br />
						<em>
							<?php echo __('No services found'); ?>
						</em>
					</div>
				<?php endif;?>

				<?php echo $this->element('paginator'); ?>
			</div>
		</div>
	</div>
</div>
</section>

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
?>

<section class="content-header hidden-sm hidden-md hidden-lg">
	<h1>
		<i class="fa fa-hdd-o"></i>
		<?php echo __('Hosts'); ?>
		<small><?php echo __('Overview'); ?></small>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-2 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-hdd-o"></i> <?php echo __('Hosts'); ?></h3>
				</div>

				<?php echo $this->Filter->render([
						'class' => 'col-xs-12 col-sm-10'
				]);?>
			</div>
			<div class="box-body">

			<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Name')); ?></div>
			<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Host.address', __('Address')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_check', __('Last Check')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_state_change', __('State since')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo __('Service summary');?></div>


			<?php foreach($hosts as $key => $host): ?>
				<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
					<?php
					$currentState = $host['Hoststatus']['current_state'];
					if($currentState > 2):
						$currentState = 2;
					endif;

					$color = [
						0 => 'success',
						1 => 'danger',
						2 => 'muted'
					];
					$icon = [
						0 => 'fa-check',
						1 => 'fa-exclamation-triangle',
						2 => 'fa-question-circle'
					];
					?>
					<div class="col-sm-3 hidden-xs">
						<a href="<?php echo Router::url(['action' => 'details', $host['Host']['host_object_id']]); ?>">

							<span class="btn btn-xs btn-default">
								<i class="fa <?php echo $icon[$currentState]; ?> text-<?php echo $color[$currentState]; ?>"></i>
							</span>

							<?php if($host['Hoststatus']['problem_has_been_acknowledged'] == 1): ?>
								<span class="btn btn-xs btn-default">
									<i class="fa fa-comments" title="<?php echo __('Acknowledged'); ?>"></i>
								</span>
							<?php endif; ?>

							<?php if($host['Hoststatus']['scheduled_downtime_depth'] > 0): ?>
								<span class="btn btn-xs btn-default">
									<i class="fa fa-plug" title="<?php echo __('Scheduled downtime'); ?>"></i>
								</span>
							<?php endif; ?>

							<?php echo h($host['Objects']['name1']);?>
						</a>
					</div>

					<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-<?php echo $color[$currentState]; ?>">
						<a href="<?php echo Router::url(['action' => 'details', $host['Host']['host_object_id']]); ?>">
							<h5>
								<span class="label label-<?php echo $color[$currentState]; ?>"><i class="fa <?php echo $icon[$currentState]; ?>"></i></span>&nbsp;
								<?php echo h($host['Objects']['name1']);?>
								<?php if($host['Hoststatus']['problem_has_been_acknowledged'] == 1): ?>
									<span class="label label-primary"><i class="fa fa-comments"></i></span>
								<?php endif; ?>
								<?php if($host['Hoststatus']['scheduled_downtime_depth'] > 0): ?>
									<span class="label label-primary"><i class="fa fa-plug"></i></span>
								<?php endif; ?>
							</h5>
						</a>
					</div>


					<div class="col-xs-12 col-sm-3">
						<?php echo h($host['Host']['address']);?>
					</div>
					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Time->format($host['Hoststatus']['last_check'], '%H:%M %d.%m.%Y');?>
					</div>
					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Time->format($host['Hoststatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
					</div>
					<div class="col-sm-2 hidden-xs">
						<?php echo $this->Status->serviceProgressbar($servicestatus, $host['Host']['host_object_id'], false, [
							'class' => 'progress-bar progress-bar-striped',
						]); ?>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
						&nbsp;
					</div>
				</div>
			<?php endforeach; ?>

			<?php echo $this->element('paginator'); ?>
			</div>
		</div>
	</div>
</div>

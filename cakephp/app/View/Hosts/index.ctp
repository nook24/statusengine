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

<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h3><i class="fa fa-hdd-o fa-lg"></i> <?php echo __('Hosts'); ?></h3>
			<hr />
		</div>

		<?php echo $this->Filter->render();?>

		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Name')); ?></div>
		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Host.address', __('Address')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_check', __('Last Check')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_state_change', __('State since')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo __('Service summary');?></div>


		<?php foreach($hosts as $host): ?>
			<div class="col-xs-12 no-padding">
				<?php $borderClass = $this->Status->hostBorder($host['Hoststatus']['current_state']);?>
				<div class="col-xs-12 col-sm-3 <?php echo $borderClass; ?> <?php echo $borderClass; ?>_first">
					<a href="<?php echo Router::url(['action' => 'details', $host['Host']['host_object_id']]); ?>"><?php echo h($host['Objects']['name1']);?></a>
					<?php if($host['Hoststatus']['problem_has_been_acknowledged'] == 1): ?>
						<span><i class="fa fa-comments" title="<?php echo __('Acknowledged'); ?>"></i></span>
					<?php endif; ?>
					<?php if($host['Hoststatus']['scheduled_downtime_depth'] > 0): ?>
						<span><i class="fa fa-pause" title="<?php echo __('Scheduled downtime'); ?>"></i></span>
					<?php endif; ?>
				</div>
				<div class="col-xs-12 col-sm-3 <?php echo $borderClass; ?>">
					<?php echo h($host['Host']['address']);?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($host['Hoststatus']['last_check'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($host['Hoststatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-sm-2 hidden-xs">
					<?php echo $this->Status->serviceProgressbar($servicestatus, $host['Host']['host_object_id']); ?>
				</div>
				<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
					&nbsp;
				</div>
			</div>
		<?php endforeach; ?>

		<?php echo $this->element('paginator'); ?>

	</div>
</div>

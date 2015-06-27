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
			<h3><i class="fa fa-exclamation-triangle fa-lg"></i> <?php echo __('Problems'); ?></h3>
			<hr />
		</div>
		
		<?php echo $this->Filter->render();?>

		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Service description')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Servicestatus.last_check', __('Last Check')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Servicestatus.last_state_change', __('State since')); ?></div>
		<div class="col-sm-5 hidden-xs"><?php echo $this->Paginator->sort('Servicestatus.output', __('Output')); ?></div>
	</div>
	<div class="row">
		<?php $hostName = null; ?>
		<?php foreach($services as $service): ?>
			<?php
			$borderClass = $this->Status->serviceBorder($service['Servicestatus']['current_state']);
			if($hostName != $service['Objects']['name1']):
				$hostName = $service['Objects']['name1'];
			?>
				<div class="col-xs-12 <?php echo $borderClass; ?> bg-info">
					<i class="fa fa-hdd-o"></i>
					&nbsp;
					<a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'details', $service['Host']['host_object_id']]); ?>"><?php echo h($hostName);?></a>
				</div>
			<?php endif;?>
			<div class="col-xs-12 col-sm-3 <?php echo $borderClass; ?> <?php echo $borderClass;?>_first">
				<a href="<?php echo Router::url(['action' => 'details', $service['Service']['service_object_id']]); ?>"><?php echo h($service['Objects']['name2']);?></a>
			</div>
			<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
				<?php echo $this->Time->format($service['Servicestatus']['last_check'], '%H:%M %d.%m.%Y');?>
			</div>
			<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
				<?php echo $this->Time->format($service['Servicestatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
			</div>
			<div class="col-sm-5 hidden-xs">
				<?php //var_dump($service['Servicestatus']['output']); ?>
				<?php echo $this->Status->h($service['Servicestatus']['output']); ?>
			</div>
			<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
				&nbsp;
			</div>
		<?php endforeach; ?>
		
		<?php echo $this->element('paginator'); ?>
		
	</div>
</div>
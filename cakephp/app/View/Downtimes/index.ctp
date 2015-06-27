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
		<div class="col-xs-12 col-md-10">
			<h3><i class="fa fa-pause fa-lg"></i> <?php echo __('Downtimes'); ?></h3>
		</div>
		
		<div class="col-xs-12 col-md-2">
			<div class="dropdown" style="padding-top: 15px;">
				<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				<?php echo __('Schedule downtime'); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
					<li>
						<a href="<?php echo Router::url(['action' => 'create', 'host']); ?>">
							<i class="fa fa-hdd-o"></i> <?php echo __('Host'); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo Router::url(['action' => 'create', 'service']); ?>">
							<i class="fa fa-cog"></i> <?php echo __('Service'); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<hr />
	
	<div class="row">
		<?php echo $this->Filter->render();?>

		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Host name')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Service description')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.author_name', __('Author')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.comment_data', __('Comment')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.scheduled_start_time', __('Start')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.scheduled_end_time', __('End')); ?></div>
		
		<?php foreach($downtimes as $downtime): ?>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Status->h($downtime['Objects']['name1']); ?>
			</div>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Status->h($downtime['Objects']['name2']); ?>
			</div>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Status->h($downtime['Downtimehistory']['author_name']); ?>
			</div>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Status->h($downtime['Downtimehistory']['comment_data']); ?>
			</div>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Time->format($downtime['Downtimehistory']['scheduled_start_time'], '%H:%M %d.%m.%Y');?>
			</div>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Time->format($downtime['Downtimehistory']['scheduled_end_time'], '%H:%M %d.%m.%Y');?>
				<?php if((strtotime($downtime['Downtimehistory']['scheduled_start_time']) > time()) &&
				(strtotime($downtime['Downtimehistory']['scheduled_end_time']) < time()) &&
				 $downtime['Downtimehistory']['was_cancelled'] == 0): ?>
					<a href="#" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
				<?php endif;?>
			</div>
			<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
				&nbsp;
			</div>
		<?php endforeach; ?>
		
		<?php echo $this->element('paginator'); ?>
		
	</div>
</div>
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
		<i class="fa fa-plug"></i>
		<?php echo __('Downtimes'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-7 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-plug"></i> <?php echo __('Downtimes overview'); ?></h3>
				</div>
				<div class="col-xs-5 col-sm-2" style="padding-top: 15px;">
					<div class="dropdown">
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
				<?php echo $this->Filter->render([
						'class' => 'col-xs-7 col-sm-3',
						'wrapStyle' => 'padding-top: 15px;'
				]);?>


			</div>
			<div class="box-body">
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Host name')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Service description')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.author_name', __('Author')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.comment_data', __('Comment')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.scheduled_start_time', __('Start')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Downtimehistory.scheduled_end_time', __('End')); ?></div>

				<?php foreach($downtimes as $key => $downtime): ?>
					<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
						<div class="col-sm-2 hidden-xs">
							<?php echo $this->Status->h($downtime['Objects']['name1']); ?>
						</div>

						<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-info">
							<h5>
								<i class="fa fa-hdd-o"></i>
								<?php echo $this->Status->h($downtime['Objects']['name1']); ?>
							</h5>
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
							<?php if((strtotime($downtime['Downtimehistory']['scheduled_end_time']) > time()) &&
							 $downtime['Downtimehistory']['was_cancelled'] == 0):
								$type = 'service';
								if($downtime['Objects']['name2'] === null):
									$type = 'host';
							 	endif;
								$url = [
									'controller' => 'Downtimes',
									'action' => 'delete',
									$type,
									$downtime['Downtimehistory']['internal_downtime_id']
								];
								$options = [
									'class' => 'btn btn-danger btn-xs',
									'style' => 'margin-bottom: 5px;',
									'escape' => false,
								];
								echo $this->Form->postLink('<i class="fa fa-trash-o"></i>', $url, $options);
							endif;?>
						</div>
						<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
							&nbsp;
						</div>
					</div>
				<?php endforeach; ?>
				<?php if(empty($downtimes)):?>
					<div class="col-xs-12 text-center text-danger">
						<br />
						<em>
							<?php echo __('No result found'); ?>
						</em>
					</div>
				<?php endif;?>

				<?php echo $this->element('paginator'); ?>
			</div>
		</div>
	</div>
</div>
</section>

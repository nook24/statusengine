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
			<i class="fa fa-comments fa-lg"></i>&nbsp;
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
			<h5><?php echo __('Acknowledgements'); ?></h5>
		</div>
	</div>
	<hr />
	<div class="row">
		<?php echo $this->Filter->render();?>
		<div class="col-md-2 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.entry_time', __('Date')); ?></div>
		<div class="col-md-3 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.author_name', __('Author')); ?></div>
		<div class="col-md-6 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.comment_data', __('Comment')); ?></div>
		<div class="col-md-1 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.is_sticky', __('Sticky')); ?></div>
		<?php foreach($acknowledgements as $acknowledgement): ?>
			<div class="col-xs-12 no-padding">
				<?php $borderClass = $this->Status->serviceBorder($acknowledgement['Acknowledgement']['state']); ?>
				<div class="col-xs-12 col-md-2 <?php echo $borderClass; ?> <?php echo $borderClass;?>_first">
					<?php echo $this->Time->format($acknowledgement['Acknowledgement']['entry_time'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-xs-12 col-md-3 <?php echo $borderClass; ?>">
					<?php echo h($acknowledgement['Acknowledgement']['author_name']); ?>
				</div>
				<div class="col-xs-12 col-md-6 <?php echo $borderClass; ?>">
					<?php echo h($acknowledgement['Acknowledgement']['comment_data']); ?>
				</div>
				<div class=" col-xs-12 col-md-1 <?php echo $borderClass; ?>">
					<?php
					if($acknowledgement['Acknowledgement']['is_sticky'] == 0):
						echo __('No');
					else:
						echo __('Yes');
					endif;
					?>
				</div>
				<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
					&nbsp;
				</div>
			</div>
		<?php endforeach; ?>

		<?php if(empty($acknowledgements)):?>
			<div class="col-xs-12 text-center text-danger">
				<em>
					<?php echo __('No acknowledgements found for this service'); ?>
				</em>
			</div>
		<?php endif;?>

		<?php echo $this->element('paginator'); ?>

	</div>
</div>

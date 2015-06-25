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
			<h3><i class="fa fa-database fa-lg"></i> <?php echo __('Objects'); ?></h3>
			<hr />
		</div>
		
		<?php echo $this->Filter->render();?>

		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.objecttype_id', __('Object type')); ?></div>
		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Name 1')); ?></div>
		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Name 2')); ?></div>
		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('Objects.is_active', __('State')); ?></div>
		
		
		<?php foreach($objects as $object): ?>
			<div class="col-xs-12 col-sm-3">
				<?php echo $this->Utils->getObjectName($object['Objects']['objecttype_id']);?>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php echo h($object['Objects']['name1']); ?>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php echo h($object['Objects']['name2']);?>
			</div>
			<div class="col-xs-12 col-sm-3">
				<?php if((int)$object['Objects']['is_active'] === 1):?>
					<span class="label label-success"><?php echo __('Active');?></span>
				<?php else:?>
					<span class="label label-default"><?php echo __('Disabled');?></span>
				<?php endif;?>
			</div>
		<?php endforeach; ?>
		
		<?php echo $this->element('paginator'); ?>
		
	</div>
</div>

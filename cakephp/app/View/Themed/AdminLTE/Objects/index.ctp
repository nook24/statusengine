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
		<i class="fa fa-database"></i>
		<?php echo __('Objects'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-database"></i> <?php echo __('Objects'); ?></h3>
				</div>
				<?php echo $this->Filter->render([
						'class' => 'col-xs-12 col-sm-4',
						'wrapRow' => false,
						'wrapStyle' => 'padding-top: 15px;'
				]);?>
			</div>
			<div class="box-body">
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.objecttype_id', __('Object type')); ?></div>
				<div class="col-sm-4 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Name 1')); ?></div>
				<div class="col-sm-4 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Name 2')); ?></div>
				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.is_active', __('State')); ?></div>


				<?php foreach($objects as $key => $object): ?>
					<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
						<div class="col-sm-2 hidden-xs">
							<i class="<?php echo $this->Utils->getObjectIcon($object['Objects']['objecttype_id']);?>"></i>
							<?php echo $this->Utils->getObjectName($object['Objects']['objecttype_id']);?>
						</div>

						<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-info">
							<h5>
								<i class="<?php echo $this->Utils->getObjectIcon($object['Objects']['objecttype_id']);?>"></i>
								<?php echo $this->Utils->getObjectName($object['Objects']['objecttype_id']);?>
							</h5>
						</div>


						<div class="col-xs-12 col-sm-4">
							<?php echo h($object['Objects']['name1']); ?>
						</div>
						<div class="col-xs-12 col-sm-4">
							<?php echo h($object['Objects']['name2']);?>
						</div>
						<div class="col-xs-12 col-sm-2">
							<?php if((int)$object['Objects']['is_active'] === 1):?>
								<span class="label label-success"><?php echo __('Active');?></span>
							<?php else:?>
								<span class="label label-default"><?php echo __('Disabled');?></span>
							<?php endif;?>
						</div>
					</div>
				<?php endforeach; ?>

				<?php if(empty($objects)):?>
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

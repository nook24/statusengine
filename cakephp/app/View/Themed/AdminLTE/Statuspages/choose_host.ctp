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
		<i class="fa fa-cloud"></i>
		<?php echo __('Status page - Choose host'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-cloud"></i> <?php echo __('Choose host for status page'); ?></h3>
				</div>
			</div>
			<div class="box-body">
				<?php
				echo $this->Form->create('Statuspages',[
					'inputDefaults' => [
						'div' => 'form-group',
						'label' => [
							'class' => 'col col-md-3 control-label'
						],
						'wrapInput' => 'col col-md-5',
						'class' => 'form-control'
					],
					'class' => 'form-horizontal',
					'novalidate' => true,
					'url' => ['controller' => 'Statuspages', 'action' => 'chooseServices'],
				]);
				echo $this->Form->input('host_object_id', [
					'label' => __('Host'),
					'options' => $hosts
				]);

				echo $this->Form->submit(__('Submit'), [
					'div' => 'form-group col-md-8',
					'class' => 'btn btn-success',
					'before' => '<div class="pull-right">',
					'after' => $this->Html->link(__('Cancel'), [
						'controller' => 'Statuspages',
						'action' => 'index'
					], [
						'class' => 'btn btn-default',
						'style' => 'margin-left: 15px;'
					]).'</div>',
				]);

				echo $this->Form->end();
				?>
			</div>
		</div>
	</div>
</div>
</section>

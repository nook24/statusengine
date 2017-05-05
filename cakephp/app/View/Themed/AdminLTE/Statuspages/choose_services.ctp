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
		<?php echo __('Status page - Choose services'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-cloud"></i> <?php echo __('Choose services for status page'); ?></h3>
				</div>
			</div>
			<div class="box-body">
				<?php
				debug($services);
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
					'type' => 'hidden',
					'value' => $hostObjectId
				]);
				echo $this->Form->input('save_services', [
					'type' => 'hidden',
					'value' => true
				]);

				echo $this->Form->input('name', [
					'label' => [
						'text' => __('Status page name'),
						'class' => 'col col-md-4 control-label'
					],
					'value' => 'Status of '.$host['Host']['address']
				]);
				?>
				<br />

				<?php foreach($services as $service): ?>
					<div class="box box-primary">
						<div class="box-header">
							<div class="col-sm-12 col-md-6">
								<h4 class="pull-left">
									<i class="fa fa-cog"></i>
									<?php echo h($service['Objects']['name2']); ?>
								</h4>
							</div>
							<div class="col-xs-12 col-md-6">
								<?php echo $this->Form->input('enabled_services.'.$service['Service']['service_object_id'], [
									'div' => 'form-group col-md-12',
									'class' => 'noclass',
									'type' => 'checkbox',
									'label' => [
										'text' => __('Show on status page?'),
										'class' => 'noclass'
									],
								]); ?>
							</div>
						</div>
						<div class="box-body">
							<b>Public display name:</b> <?php echo h($service['Service']['display_name']); ?>
							<br />
							<b>Public description:</b>
							<?php
							if($service['Service']['notes'] === '0'):
								echo '';
							else:
								echo h($service['Service']['notes']);
							endif;
							?>
							<?php if(!empty($service['Graph'])): ?>
								<br /><br />
								<p><b><i class="fa fa-area-chart"></i> <?php echo __('Performance data:');?></b></p>
								<?php foreach($service['Graph'] as $graph): ?>
									<div class="row">
										<div class="col-xs-12"><?php echo h($graph['name']); ?>:</div>
											<?php
											echo $this->Form->input('services.'.$service['Service']['service_object_id'].'.graph.'.$graph['ds'].'.enabled', [
												'div' => 'form-group col-md-12',
												'class' => 'noclass',
												'type' => 'checkbox',
												'label' => [
													'text' => __('Show on status page?'),
													'class' => 'noclass'
												],
											]);
											echo $this->Form->input('services.'.$service['Service']['service_object_id'].'.graph.'.$graph['ds'].'.name', [
												'label' => [
													'text' => __('Public metric name'),
													'class' => 'col col-md-4 control-label'
												],
												'value' => $graph['name'],
											]);

											echo $this->Form->input('services.'.$service['Service']['service_object_id'].'.graph.'.$graph['ds'].'.unit', [
												'label' => [
													'text' => __('Public metric unit'),
													'class' => 'col col-md-4 control-label'
												],
												'value' => $graph['unit'],
											]);
										?>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>

				<?php
				echo $this->Form->submit(__('Submit'), [
					'div' => 'form-group col-md-12',
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

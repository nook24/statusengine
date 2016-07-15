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
	<div class="alert alert-success externalcommand" role="alert" id="nonFixed">
		<p><?php echo __('External command send successfully.')?></p>
		<p><?php echo __('Automatically reload in'); ?> <span class="externalcommand-counter">5</span> <?php echo __('seconds');?></p>
	</div>
	<div class="alert alert-success externalcommand-fixed" role="alert" id="fixed">
		<p><?php echo __('External command send successfully.')?></p>
		<p><?php echo __('Automatically reload in'); ?> <span class="externalcommand-counter">5</span> <?php echo __('seconds');?></p>
	</div>
	<div class="row">
		<?php if($commandFileError !== false): ?>
			<div class="col-xs-12">
				<div class="alert alert-danger" role="alert">
					<?php echo h($commandFileError); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="col-xs-12 col-sm-7 col-md-8">
			<h3>
				<?php echo $this->Status->serviceStateIcon((isset($servicestatus['Servicestatus']['current_state'])?$servicestatus['Servicestatus']['current_state']:null));?>
			<?php echo h($object['Objects']['name2']); ?>
			(<a href="<?php echo Router::url([
				'controller' => 'Hosts',
				'action' => 'details',
				$service['Service']['host_object_id']
			]); ?>"><?php echo h($object['Objects']['name1']);?></a>)
			</h3>
		</div>
		<div class="col-xs-12 col-sm-2 col-md-1">
			<?php echo $this->element('service_history'); ?>
		</div>
		<div class="col-xs-12 col-sm-3 col-md-3">
			<div class="dropdown" style="padding-top: 15px;">
				<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				<?php echo __('Commands'); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
					<li>
						<a href="javascript:void(0);" id="reschedule">
							<i class="fa fa-refresh"></i>
							&nbsp;
							<?php echo __('Reschedule'); ?>
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="javascript:void(0);" data-toggle="modal" data-target="#passiveResult">
							<i class="fa fa-arrow-down"></i>
							&nbsp;
							<?php echo __('Submit passive check result'); ?>
						</a>
					</li>
					<li>
						<a href="javascript:void(0);" data-toggle="modal" data-target="#customNotify">
							<i class="fa fa-envelope-o"></i>
							&nbsp;
							<?php echo __('Send custom notification'); ?>
						</a>
					</li>
					<?php if(isset($servicestatus['Servicestatus']['current_state']) && $servicestatus['Servicestatus']['current_state'] > 0):?>
						<li>
							<a href="javascript:void(0);" data-toggle="modal" data-target="#setAck">
								<i class="fa fa-comments"></i>
								&nbsp;
								<?php echo __('Set acknowledgment'); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<hr />
	<?php if(!isset($servicestatus['Servicestatus'])):?>
		<div class="alert alert-danger" role="alert">
			<h4><?php echo __('No service status available!');?></h4>
			<p><?php echo __('Try to reschedule the service!');?></p>
		</div>
	</div>
	<?php return; //Return to avoid undefinde index errors?>
	<?php endif;?>
	<div class="row">
		<?php if($servicestatus['Servicestatus']['scheduled_downtime_depth'] > 0):?>
			<div class="col-xs-12">
				<div class="alert alert-info" role="alert">
					<i class="fa fa-pause"></i>
					&nbsp;
					<?php echo __('Notice: This service is is a scheduled downtime.');?>
					<?php if(!empty($downtime)):?>
						<p>
							<?php echo h($downtime['Downtimehistory']['author_name'])?>:&nbsp;
							<?php echo h($downtime['Downtimehistory']['comment_data']);?>
						</p>
						<p>
							<?php echo __('Date')?>:&nbsp;
							<?php echo $this->Time->format($downtime['Downtimehistory']['scheduled_start_time'], '%H:%M %d.%m.%Y');?>
							&nbsp;-&nbsp;
							<?php echo $this->Time->format($downtime['Downtimehistory']['scheduled_end_time'], '%H:%M %d.%m.%Y');?>
						</p>
					<?php endif;?>
				</div>
			</div>
		<?php endif;?>
	</div>
	<div class="row">
		<?php if($servicestatus['Servicestatus']['problem_has_been_acknowledged'] > 0):?>
			<div class="col-xs-12">
				<div class="alert alert-info" role="alert">
					<i class="fa fa-comments"></i>
					&nbsp;
					<?php echo __('Notice: The state of this service is already acknowledged.');?>
					<?php if(!empty($acknowledgement)):?>
						<p>
							<?php echo h($acknowledgement['Acknowledgement']['author_name'])?>:&nbsp;
							<?php echo h($acknowledgement['Acknowledgement']['comment_data']);?>
						</p>
						<p>
							<?php echo __('Date')?>:&nbsp;
							<?php echo $this->Time->format($acknowledgement['Acknowledgement']['entry_time'], '%H:%M %d.%m.%Y');?>
						</p>
					<?php endif;?>
				</div>
			</div>
		<?php endif;?>
		<?php if($servicestatus['Servicestatus']['is_flapping'] == 1):?>
			<div class="col-xs-12">
				<div class="alert alert-info" role="alert">
					<i class="fa fa-adjust"></i>
					&nbsp;
					<?php echo __('Notice: The state of the service is flapping.');?>
				</div>
			</div>
		<?php endif;?>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-7">
			<strong><?php echo __('Status information');?></strong>
			<div class="row">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Current state');?></div>
					<div class="col-xs-12 col-md-9"><?php echo $this->Status->Servicestatus($servicestatus['Servicestatus']['current_state']);?></div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('State type');?></div>
					<div class="col-xs-12 col-md-9">
						<?php
						if($servicestatus['Servicestatus']['state_type'] == 1):
							echo __('Hard');
						else:
							echo __('Soft');
						endif;

						echo ' ('.h($servicestatus['Servicestatus']['current_check_attempt']).'/';
						echo h($servicestatus['Servicestatus']['max_check_attempts']).')';
						?>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Last state change');?></div>
					<div class="col-xs-12 col-md-9"><?php echo $this->Time->format($servicestatus['Servicestatus']['last_state_change'], '%H:%M %d.%m.%Y');?></div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Output');?></div>
					<div class="col-xs-12 col-md-9"><?php echo $this->Status->h($servicestatus['Servicestatus']['output']);?></div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Performance data');?></div>
					<div class="col-xs-12 col-md-9"><?php echo $this->Status->h($servicestatus['Servicestatus']['perfdata']);?></div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Last check');?></div>
					<div class="col-xs-12 col-md-9">
						<?php echo $this->Time->format($servicestatus['Servicestatus']['last_check'], '%H:%M %d.%m.%Y');?>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Next check');?></div>
					<div class="col-xs-12 col-md-9">
						<?php echo $this->Time->format($servicestatus['Servicestatus']['next_check'], '%H:%M %d.%m.%Y');?>
					</div>
				</div>

				<?php if($servicestatus['Servicestatus']['normal_check_interval'] > 0):?>
					<div class="col-xs-12 no-padding">
						<div class="col-xs-12 col-md-3 bold"><?php echo __('Check interval');?></div>
						<div class="col-xs-12 col-md-9"><?php echo $this->Status->h($servicestatus['Servicestatus']['normal_check_interval']);?></div>
					</div>
				<?php endif;?>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Check command');?></div>
					<div class="col-xs-12 col-md-9"><?php echo $this->Status->h($servicestatus['Servicestatus']['check_command']);?></div>
				</div>

			</div>
		</div>
		<div class="col-xs-12 col-md-5">
			<strong><?php echo __('Configuration information');?></strong>
			<div class="row">
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Notifications');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['notifications_enabled'], ['extCommand' => 10]);?>
				</div>

				<div class="col-xs-12 col-md-6 bold"><?php echo __('Active checks');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['active_checks_enabled'], ['extCommand' => 16]);?>
				</div>

				<div class="col-xs-12 col-md-6 bold"><?php echo __('Passive checks');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['passive_checks_enabled'], ['extCommand' => 18]);?>
				</div>

				<div class="col-xs-12 col-md-6 bold"><?php echo __('Flap detection');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['flap_detection_enabled'], ['extCommand' => 12]);?>
				</div>

				<div class="col-xs-12 col-md-6 bold"><?php echo __('Event handler');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['event_handler_enabled'], ['extCommand' => 14]);?>
				</div>
			</div>
		</div>
		<?php if($servicestatus['Servicestatus']['long_output']):?>
			<div class="col-xs-12 bold" style="padding-top: 10px;"><?php echo __('Long output');?></div>
			<div class="col-xs-12">
				<div class="well">
					<?php echo h($servicestatus['Servicestatus']['long_output']);?>
				</div>
			</div>
		<?php endif;?>

		<?php if(!empty($datasources)):?>
			<div class="col-xs-12 text-right">
				<?php echo __('Graph timespan')?>
				<div class="btn-group" role="group" aria-label="...">
					<button type="button" class="selectGraphTimespan btn btn-default active" timespan="<?php echo 3600 * 2.5; ?>"><?php echo __('2.5h');?></button>
					<button type="button" class="selectGraphTimespan btn btn-default" timespan="<?php echo 3600 * 8; ?>"><?php echo __('8h');?></button>
					<button type="button" class="selectGraphTimespan btn btn-default" timespan="<?php echo 3600 * 24; ?>"><?php echo __('24h');?></button>
					<button type="button" class="selectGraphTimespan btn btn-default" timespan="<?php echo 3600 * 24 * 5; ?>"><?php echo __('1w');?></button>
					<button type="button" class="selectGraphTimespan btn btn-default" timespan="<?php echo 3600 * 24 * 30; ?>"><?php echo __('1m');?></button>
					<a href="<?php echo Router::url([
						'controller' => 'pnp',
						'action' => 'index',
						$object['Objects']['object_id']
					]); ?>" class="btn btn-default hidden-xs" title="<?php echo __('Open PNP4Nagios'); ?>" >
						<i class="fa fa-area-chart"></i>
					</a>
				</div>
			</div>
			<div class="col-xs-12">
				<center>
					<?php foreach($datasources as $ds):?>
						<?php $url = Router::url([
							'controller' => 'Rrdtool',
							'action' => 'service',
							'serviceObjectId' => $object['Objects']['object_id'],
							'ds' => $ds['ds'],
						]); ?>
					<img src="<?php echo $url; ?>" org-src="<?php echo $url; ?>" class="img-responsive serviceGraphImg" width="740" height="250" style="padding-top: 15px;">
					<?php endforeach; ?>
					<br />
				</center>
			</div>
		<?php endif; ?>

		<?php if($xmlError !== true): ?>
			<div class="row">
				<div class="col-xs-12">
					<div class="alert alert-danger" role="alert">
						<p><?php echo __('Error:')?></p>
						<p><?php echo h($xmlError); ?></p>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>

<!-- External commands modals -->
<div class="modal fade" id="passiveResult" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-arrow-down"></i> <?php echo __('Submit passive check result'); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				echo $this->Form->create('PassiveResult',[
					'inputDefaults' => [
						'div' => 'form-group',
						'label' => [
							'class' => 'col col-md-3 control-label'
						],
						'wrapInput' => 'col col-md-5',
						'class' => 'form-control'
					],
					'class' => 'form-horizontal',
					'url' => ['controller' => 'Services', 'action' => 'details', $object['Objects']['object_id']],
				]);
				echo $this->Form->input('state', [
					'options' => [
						0 => __('Ok'),
						1 => __('Warning'),
						2 => __('Critical'),
						3 => __('Unknown')
					],
					'label' => __('State')
				]);
				echo $this->Form->input('output', [
					'type' => 'text',
					'label' => __('Output')
				]);
				echo $this->Form->end();
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" id="submitPassiveResult"><?php echo __('Submit');?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customNotify" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-envelope-o"></i> <?php echo __('Send custom notification'); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				echo $this->Form->create('CustomNotify',[
					'inputDefaults' => [
						'div' => 'form-group',
						'label' => [
							'class' => 'col col-md-3 control-label'
						],
						'wrapInput' => 'col col-md-5',
						'class' => 'form-control'
					],
					'class' => 'form-horizontal',
					'url' => ['controller' => 'Services', 'action' => 'details', $object['Objects']['object_id']],
				]);
					echo $this->Form->input('comment', [
						'type' => 'text',
						'label' => __('Comment')
					]);
				echo $this->Form->input('broadcast', [
					'type' => 'checkbox',
					'class' => false,
					'label' => __('Broadcast')
				]);
				echo $this->Form->input('forced', [
					'type' => 'checkbox',
					'class' => false,
					'label' => __('Forced')
				]);
				echo $this->Form->end();
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" id="submitCustomNotify"><?php echo __('Submit');?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="setAck" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-comments"></i> <?php echo __('Set acknowledgment'); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				echo $this->Form->create('SetAck',[
					'inputDefaults' => [
						'div' => 'form-group',
						'label' => [
							'class' => 'col col-md-3 control-label'
						],
						'wrapInput' => 'col col-md-5',
						'class' => 'form-control'
					],
					'class' => 'form-horizontal',
					'url' => ['controller' => 'Services', 'action' => 'details', $object['Objects']['object_id']],
				]);
					echo $this->Form->input('comment', [
						'type' => 'text',
						'label' => __('Comment')
					]);
				echo $this->Form->input('sticky', [
					'type' => 'checkbox',
					'class' => false,
					'label' => [
						'class' => 'col col-md-5 control-label',
						'text' => __('Survive state change? (Sticky)')
					],
					'wrapInput' => 'col col-md-12',
				]);
				echo $this->Form->end();
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" id="submitSetAck"><?php echo __('Submit');?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
			</div>
		</div>
	</div>
</div>

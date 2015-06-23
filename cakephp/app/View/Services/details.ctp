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
	<div class="alert alert-success externalcommand" role="alert">
		<p><?php echo __('External command send successfully.')?></p>
		<p><?php echo __('Automatically reload in'); ?> <span>5</span> <?php echo __('seconds');?></p>
	</div>
	<div class="row">
		<?php if($commandFileError !== false): ?>
			<div class="col-xs-12">
				<div class="alert alert-danger" role="alert">
					<?php echo h($commandFileError); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="col-xs-12 col-sm-8 col-md-9">
			<h3>
				<?php echo $this->Status->serviceStateIcon($servicestatus['Servicestatus']['current_state']);?>
			<?php echo h($object['Objects']['name2']); ?> 
			(<a href="<?php echo Router::url([
				'controller' => 'Hosts',
				'action' => 'details',
				$service['Service']['host_object_id']
			]); ?>"><?php echo h($object['Objects']['name1']);?></a>)
		</h3>
		</div>
		<div class="col-xs-12 col-sm-2 col-md-1">
			<div class="dropdown" style="padding-top: 15px;">
				<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				<?php echo __('History'); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
					<li><a href="#"><?php echo __('Notificatios'); ?></a></li>
					<li><a href="#"><?php echo __('State history'); ?></a></li>
					<li><a href="#"><?php echo __('Comments'); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="col-xs-12 col-sm-2 col-md-2">
			<div class="dropdown" style="padding-top: 15px;">
				<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				<?php echo __('Commands'); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
					<li><a href="javascript:void(0);" class="sendCommand" task="reschedule"><?php echo __('Reschedule'); ?></a></li>
					<li><a href="javascript:void(0);" class="sendCommand" task="passive"><?php echo __('Submit passive check result'); ?></a></li>
					<li><a href="javascript:void(0);" class="sendCommand" task=""><?php echo __('Schedule downtime'); ?></a></li>
					<li><a href="javascript:void(0);" class="sendCommand" task="notify"><?php echo __('Send custom notification'); ?></a></li>
					<?php if($servicestatus['Servicestatus']['current_state'] > 0):?>
						<li><a href="javascript:void(0);" class="sendCommand" task="ack"><?php echo __('Set acknowledgment'); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<hr />
	<div class="row">
		<?php if($servicestatus['Servicestatus']['is_flapping'] == 1):?>
			<div class="col-xs-12">
				<div class="alert alert-info" role="alert">
					<i class="fa fa-adjust"></i>
					&nbsp;
					<?php echo __('Notice: The state of the host is flapping.');?>
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
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Current state');?></div>
				<div class="col-xs-12 col-md-9"><?php echo $this->Status->Servicestatus($servicestatus['Servicestatus']['current_state']);?></div>
				<div class="col-xs-12 col-md-3 bold"><?php echo __('State type');?></div>
				<div class="col-xs-12 col-md-9">
					<?php
					if($servicestatus['Servicestatus']['current_check_attempt'] == 1):
						echo __('Hard');
					else:
						echo __('Soft');
					endif;
					
					echo ' ('.h($servicestatus['Servicestatus']['current_check_attempt']).'/';
					echo h($servicestatus['Servicestatus']['max_check_attempts']).')';
					?>
				</div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Last state change');?></div>
				<div class="col-xs-12 col-md-9"><?php echo $this->Time->format($servicestatus['Servicestatus']['last_state_change'], '%H:%M %d.%m.%Y');?></div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Output');?></div>
				<div class="col-xs-12 col-md-9"><?php echo h($servicestatus['Servicestatus']['output']);?></div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Performance data');?></div>
				<div class="col-xs-12 col-md-9"><?php echo h($servicestatus['Servicestatus']['perfdata']);?></div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Last check');?></div>
				<div class="col-xs-12 col-md-9">
					<?php echo $this->Time->format($servicestatus['Servicestatus']['last_check'], '%H:%M %d.%m.%Y');?>
				</div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Next check');?></div>
				<div class="col-xs-12 col-md-9">
					<?php echo $this->Time->format($servicestatus['Servicestatus']['next_check'], '%H:%M %d.%m.%Y');?>
				</div>
				
				<?php if($servicestatus['Servicestatus']['normal_check_interval'] > 0):?>
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Check interval');?></div>
					<div class="col-xs-12 col-md-9"><?php echo h($servicestatus['Servicestatus']['normal_check_interval']);?></div>
				<?php endif;?>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Check command');?></div>
				<div class="col-xs-12 col-md-9"><?php echo h($servicestatus['Servicestatus']['check_command']);?></div>
				
			</div>
		</div>
		<div class="col-xs-12 col-md-5">
			<strong><?php echo __('Configuration information');?></strong>
			<div class="row">
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Notifications');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['notifications_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Active checks');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['active_checks_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Passive checks');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['passive_checks_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Flap detection');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['flap_detection_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Event handler');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($servicestatus['Servicestatus']['event_handler_enabled']);?>
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
			<div class="col-xs-12">
				<center>
					<?php foreach($datasources as $ds):?>
					<img src="<?php echo Router::url([
						'controller' => 'Rrdtool',
						'action' => 'service',
						'hostName' => $object['Objects']['name1'],
						'serviceName' => $object['Objects']['name2'],
						'ds' => $ds['ds']
					]); ?>" class="img-responsive" width="740" height="250" style="padding-top: 15px;">
					<?php endforeach; ?>
				</center>
			</div>
		<?php endif; ?>
	</div>
</div>

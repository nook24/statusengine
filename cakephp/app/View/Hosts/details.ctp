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
				<?php echo $this->Status->hostStateIcon((isset($hoststatus['Hoststatus']['current_state'])?$hoststatus['Hoststatus']['current_state']:null));?>
			<?php echo h($object['Objects']['name1']); ?>
			<?php if(isset($host['Host']['address'])):?>
				(<?php echo h($host['Host']['address']);?>)
			<?php endif;?>
		</h3>
		</div>
		<div class="col-xs-12 col-sm-2 col-md-1">
			<?php echo $this->element('host_history'); ?>
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
					<?php if(isset($hoststatus['Hoststatus']['current_state']) && $hoststatus['Hoststatus']['current_state'] > 0):?>
						<li><a href="javascript:void(0);" class="sendCommand" task="ack"><?php echo __('Set acknowledgment'); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<hr />
	<?php if(!isset($hoststatus['Hoststatus'])):?>
		<div class="alert alert-danger" role="alert">
			<h4><?php echo __('No host status available!');?></h4>
			<p><?php echo __('Try to reschedule the host!');?></p>
		</div>
	</div>
	<?php return; //Return to avoid undefinde index errors?>
	<?php endif;?>
	<div class="row">
		<?php if($hoststatus['Hoststatus']['is_flapping'] == 1):?>
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
				<div class="col-xs-12 col-md-9"><?php echo $this->Status->hoststatus($hoststatus['Hoststatus']['current_state']);?></div>
				<div class="col-xs-12 col-md-3 bold"><?php echo __('State type');?></div>
				<div class="col-xs-12 col-md-9">
					<?php
					if($hoststatus['Hoststatus']['current_check_attempt'] == 1):
						echo __('Hard');
					else:
						echo __('Soft');
					endif;
					
					echo ' ('.h($hoststatus['Hoststatus']['current_check_attempt']).'/';
					echo h($hoststatus['Hoststatus']['max_check_attempts']).')';
					?>
				</div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Last state change');?></div>
				<div class="col-xs-12 col-md-9"><?php echo $this->Time->format($hoststatus['Hoststatus']['last_state_change'], '%H:%M %d.%m.%Y');?></div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Output');?></div>
				<div class="col-xs-12 col-md-9"><?php echo h($hoststatus['Hoststatus']['output']);?></div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Performance data');?></div>
				<div class="col-xs-12 col-md-9"><?php echo h($hoststatus['Hoststatus']['perfdata']);?></div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Last check');?></div>
				<div class="col-xs-12 col-md-9">
					<?php echo $this->Time->format($hoststatus['Hoststatus']['last_check'], '%H:%M %d.%m.%Y');?>
				</div>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Next check');?></div>
				<div class="col-xs-12 col-md-9">
					<?php echo $this->Time->format($hoststatus['Hoststatus']['next_check'], '%H:%M %d.%m.%Y');?>
				</div>
				
				<?php if($hoststatus['Hoststatus']['normal_check_interval'] > 0):?>
					<div class="col-xs-12 col-md-3 bold"><?php echo __('Check interval');?></div>
					<div class="col-xs-12 col-md-9"><?php echo h($hoststatus['Hoststatus']['normal_check_interval']);?></div>
				<?php endif;?>
				
				<div class="col-xs-12 col-md-3 bold"><?php echo __('Check command');?></div>
				<div class="col-xs-12 col-md-9"><?php echo h($hoststatus['Hoststatus']['check_command']);?></div>
				
			</div>
		</div>
		<div class="col-xs-12 col-md-5">
			<strong><?php echo __('Configuration information');?></strong>
			<div class="row">
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Notifications');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($hoststatus['Hoststatus']['notifications_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Active checks');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($hoststatus['Hoststatus']['active_checks_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Passive checks');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($hoststatus['Hoststatus']['passive_checks_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Flap detection');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($hoststatus['Hoststatus']['flap_detection_enabled']);?>
				</div>
				
				<div class="col-xs-12 col-md-6 bold"><?php echo __('Event handler');?></div>
				<div class="col-xs-12 col-md-6">
					<?php echo $this->Status->booleanValue($hoststatus['Hoststatus']['event_handler_enabled']);?>
				</div>
			</div>
		</div>
		<?php if($hoststatus['Hoststatus']['long_output']):?>
			<div class="col-xs-12 bold" style="padding-top: 10px;"><?php echo __('Long output');?></div>
			<div class="col-xs-12">
				<div class="well">
					<?php echo h($hoststatus['Hoststatus']['long_output']);?>
				</div>
			</div>
		<?php endif;?>
	</div>
	<br />
	<div class="row">
		<div class="col-xs-12">
			<h4><?php echo __('Services');?></h4>
		</div>
		
		<?php
		if(!empty($services)):
			$currentServiceStats = array_count_values(Hash::extract($services, '{n}.Servicestatus.current_state'));
			$currentServiceStats = Hash::merge([0 => null, 1 => null, 2 => null, 3 => null], $currentServiceStats);
			?>
			<div class="col-xs-12">
				<?php echo $this->Status->serviceProgressbar($currentServiceStats, false, true); ?>
			</div>
		<?php
		endif;
		?>
		
		<div class="col-sm-3 hidden-xs"><?php echo __('Service description'); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo __('Last Check'); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo __('State since'); ?></div>
		<div class="col-sm-5 hidden-xs"><?php echo __('Output'); ?></div>
	</div>
	<div class="row">
			<?php foreach($services as $service): ?>
				<?php $borderClass = $this->Status->serviceBorder($service['Servicestatus']['current_state']); ?>
				<div class="col-xs-12 col-sm-3 <?php echo $borderClass; ?> <?php echo $borderClass;?>_first">
					<a href="<?php echo Router::url(['controller' => 'Services', 'action' => 'details', $service['Service']['service_object_id']]); ?>"><?php echo h($service['Objects']['name2']);?></a>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($service['Servicestatus']['last_check'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($service['Servicestatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-sm-5 hidden-xs">
					<?php //var_dump($service['Servicestatus']['output']); ?>
					<?php echo h($service['Servicestatus']['output']); ?>
				</div>
				<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
					&nbsp;
				</div>
			<?php endforeach; ?>
		
		<?php if(empty($services)):?>
			<div class="col-xs-12 text-center text-danger">
				<em>
					<?php echo __('No services associated with this host'); ?>
				</em>
			</div>
		<?php endif;?>
	</div>
</div>
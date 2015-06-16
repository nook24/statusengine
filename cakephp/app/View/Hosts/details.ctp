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
		<div class="col-xs-12 col-sm-8 col-md-9">
			<h3><?php echo $this->Status->hostStateIcon($hoststatus['Hoststatus']['current_state']);?> <?php echo h($object['Objects']['name1']); ?></h3>
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
					<li><a href="#"><?php echo __('Reschedule'); ?></a></li>
					<li><a href="#"><?php echo __('Submit passive check result'); ?></a></li>
					<li><a href="#"><?php echo __('Schedule downtime'); ?></a></li>
					<li><a href="#"><?php echo __('Send custom notification'); ?></a></li>
					<?php if($hoststatus['Hoststatus']['current_state'] > 0):?>
						<li><a href="#"><?php echo __('Set acknowledgment'); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<hr />
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
</div>
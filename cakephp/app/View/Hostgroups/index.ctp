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
			<h3><i class="fa fa-server fa-lg"></i> <?php echo __('Host groups'); ?></h3>
			<hr />
		</div>

		<?php echo $this->Filter->render();?>

		<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('HostObject.name1', __('Host')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_check', __('Last Check')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_state_change', __('State since')); ?></div>
		<div class="col-sm-5 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.output', __('Output')); ?></div>
	</div>
	<div class="row">
		<?php $hostgroupName = null; ?>
		<?php foreach($hostgroups as $hostgroupMember): ?>
			<div class="col-xs-12 no-padding">
				<?php
				$borderClass = $this->Status->hostBorder($hostgroupMember['Hoststatus']['current_state']);
				if($hostgroupName != $hostgroupMember['Objects']['name1']):
					$hostgroupName = $hostgroupMember['Objects']['name1'];
				?>
					<div class="col-xs-12 <?php echo $borderClass; ?> bg-info">
						<i class="fa fa-server"></i>
						&nbsp;
						<strong><?php echo h($hostgroupName);?></strong>
						&nbsp;-&nbsp;
						<em><?php echo $this->Status->h($hostgroupMember['Hostgroup']['alias']);?></em>
					</div>
				<?php endif;?>
				<div class="col-xs-12 col-sm-3 <?php echo $borderClass; ?> <?php echo $borderClass;?>_first">
					<a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'details', $hostgroupMember['HostObject']['object_id']]); ?>"><?php echo h($hostgroupMember['HostObject']['name1']);?></a>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($hostgroupMember['Hoststatus']['last_check'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($hostgroupMember['Hoststatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-sm-5 hidden-xs">
					<?php //var_dump($service['Servicestatus']['output']); ?>
					<?php echo $this->Status->h($hostgroupMember['Hoststatus']['output']); ?>
				</div>
				<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
					&nbsp;
				</div>
			</div>
		<?php endforeach; ?>

		<?php echo $this->element('paginator'); ?>

	</div>
</div>

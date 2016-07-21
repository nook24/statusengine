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
$color = [
	0 => 'success',
	1 => 'danger',
	2 => 'muted'
];
$icon = [
	0 => 'fa-check',
	1 => 'fa-exclamation-triangle',
	2 => 'fa-question-circle'
];
?>

<section class="content-header hidden-sm hidden-md hidden-lg">
	<h1>
		<i class="fa fa-server"></i>
		<?php echo __('Host groups'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-server"></i> <?php echo __('Host groups'); ?></h3>
				</div>
				<?php echo $this->Filter->render([
						'class' => 'col-xs-12 col-sm-4',
						'wrapRow' => false,
						'wrapStyle' => 'padding-top: 15px;'
				]);?>
			</div>
			<div class="box-body">

			<div class="col-sm-3 hidden-xs"><?php echo $this->Paginator->sort('HostObject.name1', __('Host')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_check', __('Last Check')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.last_state_change', __('State since')); ?></div>
			<div class="col-sm-5 hidden-xs"><?php echo $this->Paginator->sort('Hoststatus.output', __('Output')); ?></div>


			<?php $hostgroupName = null; ?>
			<?php foreach($hostgroups as $key => $hostgroupMember): ?>
				<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
					<?php
					$hoststate = $hostgroupMember['Hoststatus']['current_state'];
					if($hoststate > 2):
						$hoststate = 2;
					endif;
					if($hostgroupName != $hostgroupMember['Objects']['name1']):
						$hostgroupName = $hostgroupMember['Objects']['name1'];
						?>
						<div class="col-xs-12 bg-info">
							<div style="padding: 5px;">
								<i class="fa fa-server"></i>
								&nbsp;
								<?php echo h($hostgroupName);?>
								&nbsp;-&nbsp;
								<em><?php echo $this->Status->h($hostgroupMember['Hostgroup']['alias']);?></em>
							</div>
						</div>
					<?php endif;?>

					<div class="col-sm-3 hidden-xs">
						<a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'details', $hostgroupMember['HostObject']['object_id']]); ?>">

							<span class="btn btn-xs btn-default">
								<i class="fa <?php echo $icon[$hoststate]; ?> text-<?php echo $color[$hoststate]; ?>"></i>
							</span>

							<?php if($hostgroupMember['Hoststatus']['problem_has_been_acknowledged'] == 1): ?>
								<span class="btn btn-xs btn-default">
									<i class="fa fa-comments" title="<?php echo __('Acknowledged'); ?>"></i>
								</span>
							<?php endif; ?>

							<?php if($hostgroupMember['Hoststatus']['scheduled_downtime_depth'] > 0): ?>
								<span class="btn btn-xs btn-default">
									<i class="fa fa-plug" title="<?php echo __('Scheduled downtime'); ?>"></i>
								</span>
							<?php endif; ?>

							<?php echo h($hostgroupMember['HostObject']['name1']);?>
						</a>
					</div>

					<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-<?php echo $color[$hoststate]; ?>">
						<a href="<?php echo Router::url(['controller' => 'Hosts', 'action' => 'details', $hostgroupMember['HostObject']['object_id']]); ?>">
							<h5>
								<span class="label label-<?php echo $color[$hoststate]; ?>"><i class="fa <?php echo $icon[$hoststate]; ?>"></i></span>&nbsp;
								<?php echo h($hostgroupMember['HostObject']['name1']);?>
								<?php if($hostgroupMember['Hoststatus']['problem_has_been_acknowledged'] == 1): ?>
									<span class="label label-primary"><i class="fa fa-comments"></i></span>
								<?php endif; ?>
								<?php if($hostgroupMember['Hoststatus']['scheduled_downtime_depth'] > 0): ?>
									<span class="label label-primary"><i class="fa fa-plug"></i></span>
								<?php endif; ?>
							</h5>
						</a>
					</div>


					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Time->format($hostgroupMember['Hoststatus']['last_check'], '%H:%M %d.%m.%Y');?>
					</div>
					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Time->format($hostgroupMember['Hoststatus']['last_state_change'], '%H:%M %d.%m.%Y');?>
					</div>
					<div class="col-xs-12 col-sm-5">
						<?php //var_dump($service['Servicestatus']['output']); ?>
						<?php echo $this->Status->h($hostgroupMember['Hoststatus']['output']); ?>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
						&nbsp;
					</div>
			<?php endforeach; ?>

			<?php if(empty($hostgroups)):?>
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
</section>

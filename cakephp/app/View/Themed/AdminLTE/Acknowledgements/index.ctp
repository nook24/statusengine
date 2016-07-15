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

$hostColor = [
	0 => 'success',
	1 => 'danger',
	2 => 'muted'
];
$hostIcon = [
	0 => 'fa-check',
	1 => 'fa-exclamation-triangle',
	2 => 'fa-question-circle'
];

$serviceColor = [
	0 => 'success',
	1 => 'warning',
	2 => 'danger',
	3 => 'muted'
];

$serviceIcon = [
	0 => 'fa-check',
	1 => 'fa-bell',
	2 => 'fa-exclamation-triangle',
	3 => 'fa-question-circle'
];

?>

<section class="content-header hidden-sm hidden-md hidden-lg">
	<h1>
		<i class="fa fa-comments"></i>
		<?php echo __('Acknowledgements'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-comments"></i> <?php echo __('Acknowledgements'); ?></h3>
				</div>
				<?php echo $this->Filter->render([
						'class' => 'col-xs-12 col-sm-4',
						'wrapRow' => false,
						'wrapStyle' => 'padding-top: 15px;'
				]);?>
			</div>
			<div class="box-body">

			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Host name')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Service description')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.author_name', __('Author')); ?></div>
			<div class="col-sm-4 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.comment_data', __('Comment')); ?></div>
			<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.entry_time', __('Date')); ?></div>

			<?php foreach($acknowledgements as $key => $ack): ?>
				<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
					<?php
					if($ack['Acknowledgement']['acknowledgement_type'] == 0):
						$stateColor = $hostColor[$ack['Acknowledgement']['state']];
						$stateIcon = $hostIcon[$ack['Acknowledgement']['state']];
					else:
						$stateColor = $serviceColor[$ack['Acknowledgement']['state']];
						$stateIcon = $serviceIcon[$ack['Acknowledgement']['state']];
					endif;
					?>

					<div class="col-sm-2 hidden-xs">
						<span class="btn btn-xs btn-default">
							<i class="fa <?php echo $stateIcon; ?> text-<?php echo $stateColor; ?>"></i>
						</span>
						<?php echo $this->Status->h($ack['Objects']['name1']); ?>
					</div>

					<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-<?php echo $stateColor; ?>">
						<h5>
							<span class="label label-<?php echo $stateColor; ?>"><i class="fa <?php echo $stateIcon; ?>"></i></span>&nbsp;
							<?php echo $this->Status->h($ack['Objects']['name1']); ?>
						</h5>
					</div>

					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Status->h($ack['Objects']['name2']); ?>
					</div>
					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Status->h($ack['Acknowledgement']['author_name']); ?>
					</div>
					<div class="col-xs-12 col-sm-4">
						<?php echo $this->Status->h($ack['Acknowledgement']['comment_data']); ?>
					</div>
					<div class="col-xs-12 col-sm-2">
						<?php echo $this->Time->format($ack['Acknowledgement']['entry_time'], '%H:%M %d.%m.%Y');?>
					</div>
					<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
						&nbsp;
					</div>
				</div>
			<?php endforeach; ?>

			<?php if(empty($acknowledgements)):?>
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

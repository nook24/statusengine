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
			<h3><i class="fa fa-comments fa-lg"></i> <?php echo __('Acknowledgements'); ?></h3>
			<hr />
		</div>

		<?php echo $this->Filter->render();?>

		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name1', __('Host name')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Objects.name2', __('Service description')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.author_name', __('Author')); ?></div>
		<div class="col-sm-4 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.comment_data', __('Comment')); ?></div>
		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Acknowledgement.entry_time', __('Date')); ?></div>

		<?php foreach($acknowledgements as $ack): ?>
			<div class="col-xs-12 no-padding">
				<?php
				if($ack['Acknowledgement']['acknowledgement_type'] == 0):
					$borderClass = $this->Status->hostBorder($ack['Acknowledgement']['state']);
				else:
					$borderClass = $this->Status->serviceBorder($ack['Acknowledgement']['state']);
				endif;
				?>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?> <?php echo $borderClass;?>_first">
					<?php echo $this->Status->h($ack['Objects']['name1']); ?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Status->h($ack['Objects']['name2']); ?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Status->h($ack['Acknowledgement']['author_name']); ?>
				</div>
				<div class="col-xs-12 col-sm-4 <?php echo $borderClass; ?>">
					<?php echo $this->Status->h($ack['Acknowledgement']['comment_data']); ?>
				</div>
				<div class="col-xs-12 col-sm-2 <?php echo $borderClass; ?>">
					<?php echo $this->Time->format($ack['Acknowledgement']['entry_time'], '%H:%M %d.%m.%Y');?>
				</div>
				<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
					&nbsp;
				</div>
			</div>
		<?php endforeach; ?>

		<?php echo $this->element('paginator'); ?>

	</div>
</div>

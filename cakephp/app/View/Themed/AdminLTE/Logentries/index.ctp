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
		<i class="fa fa-align-left"></i>
		<?php echo __('Logentries'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-align-left"></i> <?php echo __('Logentries'); ?></h3>
				</div>
				<?php echo $this->Filter->render([
						'class' => 'col-xs-12 col-sm-4',
						'wrapRow' => false,
						'wrapStyle' => 'padding-top: 15px;'
				]);?>
			</div>
			<div class="box-body">

				<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Logentry.entry_time', __('Date')); ?></div>
				<div class="col-sm-10 hidden-xs"><?php echo $this->Paginator->sort('Logentry.logentry_data', __('Data')); ?></div>

				<?php foreach($logentries as $key => $logentry): ?>
					<div class="col-xs-12 no-padding <?php echo ($key % 2 == 0)?'row-bg':'row-default'; ?>">
						<div class="col-sm-2 hidden-xs">
							<?php echo $this->Time->format($logentry['Logentry']['entry_time'], '%H:%M %d.%m.%Y');?>
						</div>

						<div class="col-xs-12 hidden-sm hidden-md hidden-lg bg-info">
							<h5>
								<i class="fa fa-clock-o"></i>&nbsp;
								<?php echo $this->Time->format($logentry['Logentry']['entry_time'], '%H:%M %d.%m.%Y');?>
							</h5>
						</div>

						<div class="col-xs-12 col-sm-10">
							<?php echo $this->Status->h($logentry['Logentry']['logentry_data'])?>
						</div>
						<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
							&nbsp;
						</div>
					</div>
				<?php endforeach; ?>

				<?php if(empty($logentries)):?>
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
</div>
</section>

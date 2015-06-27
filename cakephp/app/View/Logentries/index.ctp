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
			<h3><i class="fa fa-align-left fa-lg"></i> <?php echo __('Logentries'); ?></h3>
			<hr />
		</div>
		
		<?php echo $this->Filter->render();?>

		<div class="col-sm-2 hidden-xs"><?php echo $this->Paginator->sort('Logentry.entry_time', __('Date')); ?></div>
		<div class="col-sm-10 hidden-xs"><?php echo $this->Paginator->sort('Logentry.logentry_data', __('Data')); ?></div>
		
		<?php foreach($logentries as $logentry): ?>
			<div class="col-xs-12 col-sm-2">
				<?php echo $this->Time->format($logentry['Logentry']['entry_time'], '%H:%M %d.%m.%Y');?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<?php echo $this->Status->h($logentry['Logentry']['logentry_data'])?>
			</div>
			<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
				&nbsp;
			</div>
		<?php endforeach; ?>
		
		<?php echo $this->element('paginator'); ?>
		
	</div>
</div>
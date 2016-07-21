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
?>

<section class="content-header hidden-sm hidden-md hidden-lg">
	<h1>
		<i class="fa fa-area-chart"></i>
		<a href="<?php echo Router::url([
				'controller' => 'Services',
				'action' => 'details',
				$object['Objects']['object_id']
		]); ?>">
			<?php echo __('Service'); ?>
		</a>
		<small><?php echo __('Graphs'); ?></small>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-area-chart"></i> <?php echo __('Graphs of'); ?>
						<a href="<?php echo Router::url([
								'controller' => 'Services',
								'action' => 'details',
								$object['Objects']['object_id']
							]); ?>">
							<?php echo h($object['Objects']['name2']); ?>
						</a>
						(<?php echo h($object['Objects']['name1']); ?>)
					</h3>
				</div>
			</div>
			<div class="box-body">
				<iframe class="pnpFrame" src="/pnp4nagios/graph?host=<?php echo rawurlencode($object['Objects']['name1']);?>&srv=<?php echo rawurlencode($object['Objects']['name2']);?>"></iframe>
			</div>
		</div>
	</div>
</div>
</section>

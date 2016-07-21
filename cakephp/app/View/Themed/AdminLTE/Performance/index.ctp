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
		<i class="fa fa-rocket"></i>
		<?php echo __('Performance info'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-rocket"></i> <?php echo __('Performance info'); ?></h3>
				</div>
			</div>
			<div class="box-body">
				<?php if($error !== false): ?>
					<div class="callout callout-danger">
						<h4><?php echo __('Error:'); ?></h4>
						<?php echo h($error); ?>
					</div>
				<?php else:?>
					<div class="col-xs-12">
						<pre>
<?php foreach($output as $line): ?>
<?php echo $line.PHP_EOL; ?>
<?php endforeach; ?>
						</pre>
					</div>
				<?php endif;?>
				<div class="col-xs-12">
					<div class="well">
						Nagios and the Nagios logo are trademarks, servicemarks, registered trademarks or registered servicemarks owned by Nagios Enterprises, LLC. All other trademarks, servicemarks, registered trademarks, and registered servicemarks are the property of their respective owner(s).						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</section>

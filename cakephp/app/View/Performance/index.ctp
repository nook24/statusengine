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

<div class="container">

</div>
	
<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h3><i class="fa fa-rocket fa-lg"></i> <?php echo __('Performance info'); ?></h3>
			<hr />
		</div>
		<?php if($error !== false): ?>
			<div class="col-xs-12">
				<div class="alert alert-danger" role="alert">
					<?php echo h($error); ?>
				</div>
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
	</div>
</div>
<?php
/**
*Copyright (C) 2015 Daniel Ziegler <daniel@statusengine.org>
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
	<div class="row">
		<div class="col-xs-12 col-md-6"><?php echo __('Host name');?></div>
		<?php foreach($hosts as $host): ?>
			<div class="col-xs-12 col-md-6 <?php echo $this->Status->hostBorder($host['Hoststatus']['current_state']); ?>">
				<?php echo h($host['Objects']['name1']);?>
			</div>
			<?php //debug($host);?>
		<?php endforeach; ?>
	</div>
</div>
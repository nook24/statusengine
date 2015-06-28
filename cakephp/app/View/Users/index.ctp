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
		<div class="col-xs-12 col-md-10">
			<h3><i class="fa fa-users fa-lg"></i> <?php echo __('Users'); ?></h3>
		</div>
		
		<div class="col-xs-12 col-md-2">
			<a href="<?php echo Router::url(['action' => 'add']); ?>" class="btn btn-default">
				<i class="fa fa-plus"></i> <?php echo __('Create user');?>
			</a>
		</div>
	</div>
	<hr />
	
	<div class="row">
		<div class="col-sm-11 hidden-xs"><?php echo $this->Paginator->sort('User.name', __('Username')); ?></div>
		<div class="col-sm-1 hidden-xs"><i class="fa fa-pencil"></i></div>
		
		<?php foreach($users as $user): ?>
			<div class="col-xs-12 col-sm-11">
				<?php echo $user['User']['username'];?>
			</div>
			<div class="col-xs-12 col-sm-1">
				<a href="<?php echo Router::url(['action' => 'edit', $user['User']['id']]); ?>">
					<i class="fa fa-pencil"></i>
				</a>
			</div>
			<div class="col-xs-12 hidden-sm hidden-md hidden-lg">
				&nbsp;
			</div>
		<?php endforeach; ?>
		
		<?php echo $this->element('paginator'); ?>
		
	</div>
</div>
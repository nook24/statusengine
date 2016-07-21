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
		<i class="fa fa-users"></i>
		<?php echo __('Edit user'); ?>
	</h1>
</section>

<section class="content">
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<div class="box-header">
				<div class="col-sm-8 hidden-xs">
					<h3 class="pull-left"><i class="fa fa-users"></i> <?php echo __('Edit Statusengine UI user'); ?></h3>
				</div>

				<div class="col-xs-12 col-sm-4" style="padding-top: 15px;">
					<?php
					$url = [
						'controller' => 'Users',
						'action' => 'delete',
						$user['User']['id']
					];
					$options = [
						'class' => 'btn btn-danger',
					];
					echo $this->Form->postLink(__('Delete user'), $url, $options);
					?>
				</div>
			</div>
			<div class="box-body">
				<?php
				echo $this->Form->create('User',[
					'inputDefaults' => [
						'div' => 'form-group',
						'label' => [
							'class' => 'col col-md-3 control-label'
						],
						'wrapInput' => 'col col-md-5',
						'class' => 'form-control'
					],
					'class' => 'form-horizontal',
					'novalidate' => true,
					'url' => ['controller' => 'Users', 'action' => 'edit', $user['User']['id']],
				]);
				echo $this->Form->input('id', [
					'type' => 'hidden',
					'value' => $user['User']['id']
				]);
				echo $this->Form->input('username', [
					'label' => __('Username'),
					'value' => $user['User']['username']
				]);
				echo $this->Form->input('password', [
					'label' => __('Password')
				]);

				echo $this->Form->input('theme', [
					'label' => __('Theme'),
					'options' => $themes,
					'selected' => $userTheme
				]);

				echo $this->Form->submit(__('Submit'), [
					'div' => 'form-group col-md-8',
					'class' => 'btn btn-success',
					'before' => '<div class="pull-right">',
					'after' => $this->Html->link(__('Cancle'), [
						'controller' => 'Users',
						'action' => 'index'
					], [
						'class' => 'btn btn-default',
						'style' => 'margin-left: 15px;'
					]).'</div>',
				]);
				echo $this->Form->end();
				?>
			</div>
		</div>
	</div>
</div>
</section>

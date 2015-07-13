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
	<div class="row">
		<div class="col-xs-12 col-md-10">
			<h3><i class="fa fa-user fa-lg"></i> <?php echo __('Edit user'); ?></h3>
		</div>

		<div class="col-xs-12 col-md-2">
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
	<hr />

	<div class="row">
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
			'novalidate' => true
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

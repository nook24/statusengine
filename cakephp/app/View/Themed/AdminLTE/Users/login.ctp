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
<div id="background-image">
	<?php echo $this->Html->image('switch-hd-dark.jpg', array('alt' => 'Network device')); ?>
</div>

<div class="login-box login-shadow">
	<div class="login-logo login-headline">
		<b><?php echo h($topMenuAppName); ?></b>
	</div>

	<div class="login-box-body login-bg">
		<p class="login-box-msg">
			<?php if($demoMode === true): ?>
				<span class="text-info text-bold"><i class="fa fa-info"></i> Just click Sign in without touching the credentials</span>
			<?php else: ?>
				<?php echo __('Please Sign in'); ?>
			<?php endif; ?>
			</p>

		<?php
		echo $this->Form->create('User', [
			'inputDefaults' => [
				'div' => 'form-group has-feedback',
				'label' => false,
				'wrapInput' => 'col col-xs-12',
				'class' => 'form-control'
			],
			'class' => 'form-horizontal',
			'url' => ['controller' => 'users', 'action' => 'login'],
		]);

		if($demoMode === true):
			echo $this->Form->input('username', [
				'value' => 'statusengine',
				'readonly' => true,
				'after' => '<span class="fa fa-user form-control-feedback"></span>'
			]);
			echo $this->Form->input('password', [
				'value' => 'statusengine',
				'readonly' => true,
				'after' => '<span class="fa fa-lock form-control-feedback"></span>'
			]);
		else:
			echo $this->Form->input('username', [
				'placeholder' => __('Username'),
				'after' => '<span class="fa fa-user form-control-feedback"></span>'
			]);
			echo $this->Form->input('password', [
				'placeholder' => __('Password'),
				'after' => '<span class="fa fa-lock form-control-feedback"></span>'
			]);
		endif;

		?>
		<div class="row">
			<?php
			echo $this->Form->submit(__('Sign in'), [
				'div' => 'col-xs-4 pull-right',
				'class' => 'btn btn-primary btn-block btn-flat login-submit ',
			]);
			?>
		</div>
	</div>
</div>

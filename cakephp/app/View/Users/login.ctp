<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-12">
			<h3><i class="fa fa-sign-in fa-lg"></i> <?php echo __('Statusengine - sign in'); ?></h3>
		</div>
	</div>
	<div class="col-xs-12">
		<?php echo $this->Flash->render('auth'); ?>
	</div>
	<hr />

	<div class="row">
		<?php if($demoMode === true): ?>
			<div class="col col-xs-12 text-center">
				<h4 class="text-info"><i class="fa fa-info"></i> Just click Sign in without touching the credentials</h4>
				<hr />
			</div>
		<?php endif; ?>


		<?php
		echo $this->Form->create('User', [
			'inputDefaults' => [
				'div' => 'form-group',
				'label' => [
					'class' => 'col col-md-3 control-label'
				],
				'wrapInput' => 'col col-md-5',
				'class' => 'form-control'
			],
			'class' => 'form-horizontal'
		]);

		if($demoMode === true):
			echo $this->Form->input('username', [
				'value' => 'statusengine',
				'readonly' => true
			]);
			echo $this->Form->input('password', [
				'value' => 'statusengine',
				'readonly' => true
			]);
		else:
			echo $this->Form->input('username');
			echo $this->Form->input('password');
		endif;
		echo $this->Form->submit(__('Sign in'), [
			'div' => 'form-group col-md-8',
			'class' => 'btn btn-success',
			'before' => '<div class="pull-right">',
			'after' => '</div>',
		]);
		echo $this->Form->end();
		?>
	</div>
</div>

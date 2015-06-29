<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-12">
			<h3><i class="fa fa-sign-in fa-lg"></i> <?php echo __('Statusengine - sign in'); ?></h3>
		</div>
	</div>
	<div class="col-xs-12">
		<?php echo $this->Session->flash('auth'); ?>
	</div>
	<hr />
	
	<div class="row">
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
		
		echo $this->Form->input('username');
		echo $this->Form->input('password');
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

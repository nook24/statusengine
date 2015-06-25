<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	
	<!--
		 ____  _        _                              _            
		/ ___|| |_ __ _| |_ _   _ ___  ___ _ __   __ _(_)_ __   ___ 
		\___ \| __/ _` | __| | | / __|/ _ \ '_ \ / _` | | '_ \ / _ \
		 ___) | || (_| | |_| |_| \__ \  __/ | | | (_| | | | | |  __/
		|____/ \__\__,_|\__|\__,_|___/\___|_| |_|\__, |_|_| |_|\___|
		                                         |___/
		                  the missing event broker
		                http://www.statusengine.org
		            https://github.com/nook24/statusengine
	-->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->element('assets');

		//echo $this->Html->css('cake.generic');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	
	<?php echo $this->element('menu'); ?>
	
	<div class="container">
		<?php echo $this->Session->flash(); ?>
		<div id="content">
			<?php echo $this->fetch('content'); ?>
		</div>
	</div>
	<?php
		Configure::load('Interface');
		if(Configure::read('Interface.sql_dump') === true):
			 echo $this->element('sql_dump');
		endif;

		$hideOitc = false;
		$class ="col-xs-12 col-md-4";
		if(Configure::read('Interface.hide_oitc') === true):
			echo $this->element('oitc_modal');
			$class ="col-xs-12 col-md-6";
			$hideOitc = true;
		endif;
		?>
	<br />
	<footer class="footer">
		<div class="container">
			<div class="row text-muted">
				<div class="<?php echo $class; ?>">
					<a href="https://github.com/nook24/statusengine" class="text-muted" target="_blank">
						<i class="fa fa-github"></i> 
						<?php echo __('Contribute to Statusengine');?>
					</a>
				</div>
				<?php if($hideOitc === false): ?>
					<div class="col-xs-12 col-md-4 text-center">
						<a href="javascript:void(0);" class="text-muted" data-toggle="modal" data-target="#oITCModal">
							<?php echo __('Want more? Check out openITCOCKPIT');?>
						</a>
					</div>
				<?php endif;?>
				<div class="<?php echo $class; ?>">
					<div class="pull-right">
						<a href="http://cakephp.org" target="_blank">
							<?php echo $this->Html->image('cake-logo-smaller2.png', ['border' => '0']); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</footer>
</body>
</html>

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

//$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
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
		               https://www.statusengine.org
		            https://github.com/nook24/statusengine
	-->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<title>
		<?php echo h($topMenuAppName); ?>:
		<?php echo $title_for_layout; ?>
		- powered by Statusengine
	</title>
	<?php
		echo $this->element('favicon');
		echo $this->Html->css('AdminLTE.min');
		echo $this->Html->css('skin-blue.min');
		echo $this->element('assets');

		//echo $this->Html->css('cake.generic');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		echo $this->Html->script('app');

	?>
</head>

<?php if($isLoggedIn === false): ?>
	<body class="hold-transition login-page">
		<?php echo $this->Flash->render(); ?>
		<?php echo $this->fetch('content'); ?>
<?php else: ?>
	<body class="hold-transition skin-blue sidebar-mini">

	<!-- implment id="xpull-trigger" div -->
	<div class="wrapper">
		<?php
		if($isLoggedIn === true):
			echo $this->element('menu');
		endif;
		?>
		<div class="content-wrapper">
			<?php echo $this->Flash->render(); ?>
			<div id="xpull-trigger">
				<?php echo $this->fetch('content'); ?>
			</div>

			<?php
			if(Configure::read('Interface.sql_dump') === true && $isLoggedIn === true):
				echo $this->element('sql_dump');
			endif;
			?>

		</div>
		<?php echo $this->element('footer'); ?>

	</div>
	<?php endif; ?>
</body>
</html>

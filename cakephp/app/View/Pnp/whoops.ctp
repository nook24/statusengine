<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h3>
			<i class="fa fa-frown-o fa-lg"></i>&nbsp;
			<?php echo __('Whoops! PNP4Nagios could not be found on your system'); ?>
			</h3>
		</div>
	</div>
	<hr />
	<div class="row">
		<div class="col-xs-12 bold">
			<?php echo __('Basically there are two reasons why Statusengine is unable to find your PNP4Nagios.');?>
		</div>
		<div class="col-xs-12">
			<br />
			1. <?php echo __('You have set the wrong path in Statusengine\'s Interface.php config');?>
			<div class="well">
				//Path to PNP4Nagios index.php<br />
				'pnp4nagios' => '<?php echo h($path);?>'
			</div>
			<hr />
			2. <?php echo __('PNP4Nagios is not installed on your system. This guide will help you to install PNP4Nagios for <b>Apache2</b>.');?>
			2.1 <?php echo __('Install PNP4Nagios using Ubuntu\'s packet manager'); ?>
			<div class="well">
				sudo su<br/>
				apt-get update<br />
				apt-get install pnp4nagios --no-install-recommends
			</div>
			2.2 <?php echo __('Copy the following to the file');?> <b>/etc/apache2/sites-available/pnp4nagios.conf</b>
			<pre class="well">
Alias /pnp4nagios "/usr/share/pnp4nagios/html"
<?php echo h('
<Directory "/usr/share/pnp4nagios/html/">
    AllowOverride None
    Order allow,deny
    Allow from all
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /pnp4nagios/
        RewriteRule ^(application|modules|system) - [F,L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule .* index.php/$0 [PT,L]
    </IfModule>
</Directory>'); ?>
			</pre>
			<br />
			2.3 <?php echo __('Enable new config and restart Apache2 web server')?>
			<div class="well">
				a2ensite pnp4nagios.conf<br />
				service apache2 restart
			</div>
			2.4 <a href="<?php echo $this->here; ?>" class="btn btn-success"><i class="fa fa-reload"></i> <?php echo __('Done? Click to continue or reload this page');?></a>
			<br />
			<br />
		</div>
	</div>
</div>

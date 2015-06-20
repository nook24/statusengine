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
		<div class="col-xs-12">
			<h3><i class="fa fa-home fa-lg"></i> <?php echo __('Overview'); ?></h3>
			<hr />
		</div>
		
		<div class="col-xs-12 col-md-4">
			<i class="fa fa-hdd-o"></i> <?php echo array_sum($hostStatusCount);?> <?php echo __('hosts are monitored'); ?>
		</div>
		<div class="col-xs-12 col-md-4">
			<i class="fa fa-cog"></i> <?php echo array_sum($serviceStatusCount);?> <?php echo __('services are monitored'); ?>
		</div>
		<div class="col-xs-12 col-md-4">
			<i class="fa fa-exclamation-triangle"></i> 1337 <?php echo __('problems');?>
		</div>
		
		<div class="col-xs-12">
			<hr />
		</div>
		
		<div class="col-xs-12 col-md-6">
			<h4><i class="fa fa-hdd-o"></i> <?php echo __('Hoststatus summary')?></h4>
			<?php echo $this->Status->hostProgressbar($hostStatusCount); ?>
			<div class="row text-center">
				<div class="col-xs-12 col-md-4 text-success">
					<a href="<?php echo Router::url([
						'controller' => 'hosts',
						'action' => 'index',
						'Filter' => [
							'Hoststatus' => [
								'current_state' => [
									0 => 0
								]
							]
						]
						]); ?>" class="btn btn-success" style="margin-bottom: 5px;">
						<?php echo $hostStatusCount[0]; ?>
					</a>
				</div>
				<div class="col-xs-12 col-md-4">
					<a href="<?php echo Router::url([
						'controller' => 'hosts',
						'action' => 'index',
						'Filter' => [
							'Hoststatus' => [
								'current_state' => [
									1 => 1
								]
							]
						]
						]); ?>" class="btn btn-danger" style="margin-bottom: 5px;">
						<?php echo $hostStatusCount[1]; ?>
					</a>
				</div>
				<div class="col-xs-12 col-md-4">
					<a href="<?php echo Router::url([
						'controller' => 'hosts',
						'action' => 'index',
						'Filter' => [
							'Hoststatus' => [
								'current_state' => [
									2 => 2
								]
							]
						]
						]); ?>" class="btn btn-default" style="margin-bottom: 5px;">
						<?php echo $hostStatusCount[2]; ?>
					</a>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<h4><i class="fa fa-cog"></i> <?php echo __('Servicestatus summary')?></h4>
			<?php echo $this->Status->serviceProgressbar($serviceStatusCount); ?>
			<div class="row text-center">
				<div class="col-xs-12 col-md-3 text-success">
					<a href="<?php echo Router::url([
						'controller' => 'services',
						'action' => 'index',
						'Filter' => [
							'Servicestatus' => [
								'current_state' => [
									0 => 0
								]
							]
						]
						]); ?>" class="btn btn-success" style="margin-bottom: 5px;">
						<?php echo $serviceStatusCount[0]; ?>
					</a>
				</div>
				<div class="col-xs-12 col-md-3">
					<a href="<?php echo Router::url([
						'controller' => 'services',
						'action' => 'index',
						'Filter' => [
							'Servicestatus' => [
								'current_state' => [
									1 => 1
								]
							]
						]
						]); ?>" class="btn btn-danger" style="margin-bottom: 5px;">
						<?php echo $serviceStatusCount[1]; ?>
					</a>
				</div>
				<div class="col-xs-12 col-md-3">
					<a href="<?php echo Router::url([
						'controller' => 'services',
						'action' => 'index',
						'Filter' => [
							'Servicestatus' => [
								'current_state' => [
									2 => 2
								]
							]
						]
						]); ?>" class="btn btn-warning" style="margin-bottom: 5px;">
						<?php echo $serviceStatusCount[2]; ?>
					</a>
				</div>
				<div class="col-xs-12 col-md-3">
					<a href="<?php echo Router::url([
						'controller' => 'services',
						'action' => 'index',
						'Filter' => [
							'Servicestatus' => [
								'current_state' => [
									3 => 3
								]
							]
						]
						]); ?>" class="btn btn-default" style="margin-bottom: 5px;">
						<?php echo $serviceStatusCount[3]; ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
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
		<i class="fa fa-home"></i>
		<?php echo __('Overview'); ?>
	</h1>
</section>

<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box box-primary">
				<div class="box-header">
					<div class="col-sm-2 hidden-xs">
						<h3 class="pull-left"><i class="fa fa-home"></i> <?php echo __('Overview'); ?></h3>
					</div>

					<?php echo $this->Filter->render([
							'class' => 'col-xs-12 col-sm-10'
					]);?>
				</div>
				<div class="box-body">

					<?php
					$allHostCount = array_sum($hostStatusCount);
					$allServiceCount = array_sum($serviceStatusCount);
					?>

					<div class="row">
						<div class="col-lg-4 col-xs-6">
							<a href="<?php echo Router::url([
								'controller' => 'hosts',
								'action' => 'index',
								]); ?>" class="a-no-hover">
								<div class="small-box bg-primary">
									<div class="inner">
										<h3><?php echo $allHostCount; ?></h3>

										<p><?php echo __('Hosts are monitored'); ?></p>
									</div>
									<div class="icon">
										<i class="fa fa-hdd-o"></i>
									</div>
								</div>
							</a>
						</div>

						<div class="col-lg-4 col-xs-6">
							<a href="<?php echo Router::url([
								'controller' => 'services',
								'action' => 'index',
								]); ?>" class="a-no-hover">
								<div class="small-box bg-aqua">
									<div class="inner">
										<h3><?php echo $allServiceCount; ?></h3>

										<p><?php echo __('Services are monitored'); ?></p>
									</div>
									<div class="icon">
										<i class="fa fa-cog"></i>
									</div>
								</div>
							</a>
						</div>

						<div class="col-lg-4 col-xs-12">
							<a href="<?php echo Router::url([
								'controller' => 'services',
								'action' => 'problem',
							]); ?>" class="a-no-hover">
								<div class="small-box bg-info a-no-hover">
									<div class="inner">
										<h3><?php echo $problems; ?></h3>

										<p><?php echo __('Problems detected'); ?></p>
									</div>
									<div class="icon">
										<i class="fa fa-exclamation-triangle"></i>
									</div>
								</div>
							</a>
						</div>
					</div>


					<div class="col-xs-12">
						<hr />
					</div>

					<div class="col-xs-12 col-md-6">
						<div class="box box-primary">
							<div class="box-header with-border">
								<h3 class="box-title"><i class="fa fa-hdd-o"></i> <?php echo __('Hoststatus summary')?></h3>
							</div>
							<div class="box-body">
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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-green">
										<span class="info-box-icon"><i class="fa fa-check"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Hosts up'); ?></span>
											<span class="info-box-number"><?php echo round($hostStatusCount[0] / $allHostCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $hostStatusCount[0] / $allHostCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s hosts of %s are in state: Up', $hostStatusCount[0], $allHostCount); ?>
											</span>
										</div>
									</div>
								</a>

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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-red">
										<span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Hosts down'); ?></span>
											<span class="info-box-number"><?php echo round($hostStatusCount[1] / $allHostCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $hostStatusCount[1] / $allHostCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s hosts of %s are in state: Down', $hostStatusCount[1], $allHostCount); ?>
											</span>
										</div>
									</div>
								</a>

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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-gray">
										<span class="info-box-icon"><i class="fa fa-question-circle"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Hosts unreachable'); ?></span>
											<span class="info-box-number"><?php echo round($hostStatusCount[2] / $allHostCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $hostStatusCount[2] / $allHostCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s hosts of %s are in state: Unreachable', $hostStatusCount[2], $allHostCount); ?>
											</span>
										</div>
									</div>
								</a>
							</div>
						</div>
					</div>

					<div class="col-xs-12 col-md-6">
						<div class="box box-primary">
							<div class="box-header with-border">
								<h3 class="box-title"><i class="fa fa-cog"></i> <?php echo __('Servicestatus summary')?></h3>
							</div>
							<div class="box-body">
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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-green">
										<span class="info-box-icon"><i class="fa fa-check"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Services ok'); ?></span>
											<span class="info-box-number"><?php echo round($serviceStatusCount[0] / $allServiceCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $serviceStatusCount[0] / $allServiceCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s services of %s are in state: Ok', $serviceStatusCount[0], $allServiceCount); ?>
											</span>
										</div>
									</div>
								</a>

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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-yellow">
										<span class="info-box-icon"><i class="fa fa-bell"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Services warning'); ?></span>
											<span class="info-box-number"><?php echo round($serviceStatusCount[1] / $allServiceCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $serviceStatusCount[1] / $allServiceCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s services of %s are in state: Warning', $serviceStatusCount[1], $allServiceCount); ?>
											</span>
										</div>
									</div>
								</a>

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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-red">
										<span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Services critical'); ?></span>
											<span class="info-box-number"><?php echo round($serviceStatusCount[2] / $allServiceCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $serviceStatusCount[2] / $allServiceCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s services of %s are in state: Critical', $serviceStatusCount[2], $allServiceCount); ?>
											</span>
										</div>
									</div>
								</a>

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
									]); ?>" class="a-no-hover">
									<div class="info-box bg-gray">
										<span class="info-box-icon"><i class="fa fa-check"></i></span>

										<div class="info-box-content">
											<span class="info-box-text"><?php echo __('Services unknown'); ?></span>
											<span class="info-box-number"><?php echo round($serviceStatusCount[3] / $allServiceCount * 100); ?>%</span>

											<div class="progress">
												<div class="progress-bar" style="width: <?php echo $serviceStatusCount[3] / $allServiceCount * 100; ?>%"></div>
											</div>
											<span class="progress-description">
												<?php echo __('%s services of %s are in state: Unknown', $serviceStatusCount[3], $allServiceCount); ?>
											</span>
										</div>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

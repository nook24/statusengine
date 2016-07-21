<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = [
		'Paginator',
		'RequestHandler',
		'Frontend.Frontend',
		'Session',
		'Filter',
		'Flash',
		'Auth' => [
			'loginRedirect' => [
				'controller' => 'Home',
				'action' => 'index'
			],
			'logoutRedirect' => [
				'controller' => 'Users',
				'action' => 'login',
			],
			'authenticate' => [
				'Form' => [
					'passwordHasher' => 'Blowfish'
				]
			]
		]
	];

	public $helpers = [
		'Paginator',
		'Frontend.Frontend',
		'Html' => ['className' => 'BoostCake.BoostCakeHtml'],
		'Form' => ['className' => 'BoostCake.BoostCakeForm'],
		'Paginator' => ['className' => 'BoostCake.BoostCakePaginator'],
		'Filter',
		'Flash'
	];

	function beforeFilter(){
		parent::beforeFilter();
		//Set global default limit for pagination
		$this->Paginator->settings['limit'] = 50;
		$isLoggedIn = $this->Auth->loggedIn();
		$this->set('isLoggedIn', $isLoggedIn);

		$this->themes = [
			'classic' => 'classic',
			'AdminLTE' => 'AdminLTE'
		];
	}

	public function beforeRender(){
		parent::beforeRender();
		Configure::load('Interface');
		$topMenuAppName = Configure::read('Interface.app_name');
		if($topMenuAppName == ''){
			$topMenuAppName = 'Statusengine';
		}
		$this->set('topMenuAppName', $topMenuAppName);

		if(!$this->Auth->loggedIn()){
			$this->theme = 'AdminLTE';
		}else{
			$theme = $this->Auth->user('theme');
			if($theme != 'classic'){
				if($theme == ''){
					$theme = 'AdminLTE';
				}
				$this->theme = $theme;
			}
		}

		if($this->theme == 'AdminLTE'){
			$models = [
				'Host' => 'host_object_id',
				'Servicestatus' => 'service_object_id',
				'Service' => 'service_object_id'
			];

			foreach($models as $modelName => $primaryKey){
				$this->loadModel('Legacy.'.$modelName);
				$this->{$modelName}->primaryKey = $primaryKey;
			}

			$query = [
				'bindModels' => true,
				'fields' => [
					'Objects.name1',
					'Objects.name2',

					'Host.host_object_id',

					'Service.service_id',
					'Service.service_object_id',
					'Service.host_object_id',

					'Servicestatus.current_state',
					'Servicestatus.problem_has_been_acknowledged',
					'Servicestatus.scheduled_downtime_depth',

				],

				'conditions' => [
					'Servicestatus.current_state > ' => 0,
					'Servicestatus.problem_has_been_acknowledged' => 0,
					'Servicestatus.scheduled_downtime_depth' => 0
				]
			];
			$count = $this->Service->find('count', $query);

			$query['order']	= [
				'Objects.name1' => 'asc'
			];

			//Avoid annoying short scrollbar
			$query['limit'] = 4;
			$problems = $this->Service->find('all', $query);
			$this->set('topMenuProblemsCounter', $count);
			$this->set('topMenuProblems', $problems);
		}
	}

	public function setFlash($message, $success = true, $key = 'flash'){
		$this->Flash->set($message, [
			'key' => $key,
			'element' => ($success ? 'success' : 'danger')
		]);
		/*$this->Session->setFlash($message, 'default', array(
			'class' => 'alert alert-' . ($success ? 'success' : 'danger')
		), $key);*/
	}

	public function fixPaginatorOrder($defaultOrder = []){
		if(isset($this->request->params['named']['sort'])){
			return [$this->request->params['named']['sort']];
		}

		return $defaultOrder;
	}
}

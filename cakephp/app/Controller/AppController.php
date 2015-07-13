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
		'Filter'
	];

	function beforeFilter(){
		parent::beforeFilter();
		//Set global default limit for pagination
		$this->Paginator->settings['limit'] = 50;
		$isLoggedIn = $this->Auth->loggedIn();
		$this->set('isLoggedIn', $isLoggedIn);
	}

	public function setFlash($message, $success = true, $key = 'flash'){
		$this->Session->setFlash($message, 'default', array(
			'class' => 'alert alert-' . ($success ? 'success' : 'danger')
		), $key);
	}

	public function fixPaginatorOrder($defaultOrder = []){
		if(isset($this->request->params['named']['sort'])){
			return [$this->request->params['named']['sort']];
		}

		return $defaultOrder;
	}
}

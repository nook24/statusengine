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
class UsersController extends AppController{
	public function index(){
		$users = $this->Paginator->paginate();
		$this->set('users', $users);
		$this->set('_serialize', ['users']);
	}

	public function beforeFilter(){
		parent::beforeFilter();
		$this->demoMode = false;
		Configure::load('Interface');
		if(Configure::read('Interface.demo_mode')){
			$this->demoMode = true;
		}
		$this->set('demoMode', $this->demoMode);
	}

	public function add(){
		if($this->demoMode){
			$this->setFlash('Sorry - disabled in demo mode', false);
			$this->redirect(['action' => 'index']);
			return;
		}
		$this->set('themes', $this->themes);

		if($this->request->is('post') || $this->request->is('put')){
			$this->User->create();
			if($this->User->save($this->request->data)){
				$this->setFlash(__('User created successfully'));
				return $this->redirect(['action' => 'index']);
			}
			$this->setFlash(__('Could not save data'), false);
		}
	}

	public function edit($id = null){
		if($this->demoMode){
			$this->setFlash('Sorry - disabled in demo mode', false);
			$this->redirect(['action' => 'index']);
			return;
		}

		if(!$this->User->exists($id)){
			throw new NotFoundException(__('User not found'));
		}

		if($this->request->is('post') || $this->request->is('put')){
			if($this->User->save($this->request->data)){
				$this->setFlash(__('User edit successfully'));
				if($id == $this->Auth->user('id')){
					$this->Session->write('Auth.User.theme', $this->request->data('User.theme'));
				}
				return $this->redirect(['action' => 'index']);
			}
			$this->setFlash(__('Could not save data'), false);
		}

		$user = $this->User->findById($id);
		$this->set('user', $user);
		$this->set('themes', $this->themes);
		$this->set('userTheme', $this->Auth->user('theme'));
	}

	public function delete($id){
		if($this->demoMode){
			$this->setFlash('Sorry - disabled in demo mode', false);
			$this->redirect(['action' => 'index']);
			return;
		}

		if(!$this->request->is('post')){
			throw new MethodNotAllowedException();
		}
		if(!$this->User->exists($id)){
			throw new NotFoundException(__('User not found'));
		}

		if($this->User->find('count') == 1){
			$this->setFlash(__('You can\'t delete the only user :-)'), false);
			return $this->redirect(['action' => 'index']);
		}

		if($this->User->delete($id)){
			if($id == $this->Auth->user('id')){
				$this->Auth->logout();
			}

			$this->setFlash(__('User deleted successfully'));
			return $this->redirect(['action' => 'index']);
		}
		$this->setFlash(__('Could not deleted user'), false);
		return $this->redirect(['action' => 'index']);
	}

	public function login(){
		if($this->Auth->loggedIn()){
			$this->redirect(['controller' => 'Home', 'action' => 'index']);
		}

		if($this->request->is('post')){
			if($this->Auth->login()){
				return $this->redirect($this->Auth->redirectUrl());
			}
			$this->setFlash(__('Invalid username or password, try again'), false);
		}
	}

	public function logout() {
		return $this->redirect($this->Auth->logout());
	}
}

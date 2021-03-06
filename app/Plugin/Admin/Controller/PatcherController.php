<?php
class PatcherController extends AdminAppController {
    public $components = array('Patcher');
    public $uses = array('Character', 'EventsCharacter', 'Event');

    var $adminOnly = true;
    var $availablePatchs = array('beta-2', 'beta-3', 'v-1.1', 'v-1.3', 'v-1.3.5', 'v-1.4', 'v-1.4.1');

    function beforeFilter() {
        parent::beforeFilter();
    }

    public function index() {
    }

    public function apply($patch = null) {
        if(!in_array($patch, $this->availablePatchs)) {
    		$this->Session->setFlash(__('MushRaider can\'t find this patch'), 'flash_error');
    		return $this->redirect('/admin');
    	}

    	if(!empty($this->request->data['Patcher'])) {
            $db = ConnectionManager::getDataSource('default');
            $db->cacheSources = false;
            
    		$error = false;
	    	$databaseConfig = Configure::read('Database');
	        if(!$mysqlLink = mysqli_connect($databaseConfig['host'], $databaseConfig['login'], $databaseConfig['password'], $databaseConfig['database'], $databaseConfig['port'])) {
	            $error = true;
	        }
	    	
	    	$sqlReport = $this->Patcher->run_sql_file($mysqlLink, '../Config/Schema/sql/mushraider_patch_'.$patch.'.sql', $databaseConfig['prefix']);    			    	
	    	$mysqlLink = null;

	        if($sqlReport['success'] != $sqlReport['total']) {
	            $error = true;
	        }

	        if($error) {
	        	$this->Session->setFlash(__('MushRaider can\'t apply the SQL patch, please try again or apply it by yourself using the following file : /app/Config/Schema/sql/mushraider_patch_%s.sql', $patch), 'flash_error');
                return $this->redirect('/admin/patcher/apply/'.$patch);
	        }else {
	        	// If there is code to execute...
                $methodName = str_replace('-', '', $patch);
	        	$methodName = str_replace('.', '', $methodName);
	        	if(method_exists($this, $methodName)) {
	        		$this->$methodName();	        		
	        	}

                // Delete cache for obvious reasons :p
                Cache::clear(false, '_cake_core_');
                Cache::clear(false, '_cake_model_');

	        	$this->Session->setFlash(__('MushRaider successfully apply the patch !'), 'flash_success');
	        }

	        return $this->redirect('/admin');
	    }

	    $this->set('patch', $patch);
    }

    public function beta3() {
    	// Update new 'created' field of each character
    	// It will match the date of the first event the character signin
    	// This ensure the stats to be accurate

    	// Generate new schema
    	$this->Character->schemaBeta3();
    	Cache::clear();

    	$params = array();
    	$params['recursive'] = -1;
    	$params['fields'] = array('id', 'game_id');
    	if($characters = $this->Character->find('all', $params)) {
    		foreach($characters as $character) {
    			$toSaveCharacter['id'] = $character['Character']['id'];

				// Get first event
				$params = array();
				$params['recursive'] = -1;
				$params['fields'] = array('id', 'created');
				$params['order'] = 'created ASC';
				$params['conditions']['character_id'] = $character['Character']['id'];
				if($eventCharacter = $this->EventsCharacter->find('first', $params)) {
					$toSaveCharacter['created'] = $eventCharacter['EventsCharacter']['created'];
				}else {
					$toSaveCharacter['created'] = date('Y-m-d H:i:s');
				}
												
				$this->Character->save($toSaveCharacter);
    		}
    	}
    }

    public function v14() {
        // Regenerate cache
        Cache::clear();

        /*
        * API
        */
        // Copy bridge secret key to API private key
        $bridge = json_decode($this->Setting->getOption('bridge'));
        if(!empty($bridge) && $bridge->enabled && !empty($bridge->secret)) {
            $api = array();
            $api['enabled'] = 0;
            $api['privateKey'] = $bridge->secret;
            $this->Setting->setOption('api', json_encode($api));

            // Disable bridge to make use users update their bridge plugin
            $bridgeSettings = array();
            $bridgeSettings['enabled'] = 0;
            $bridgeSettings['url'] = $bridge->url;
            $this->Setting->setOption('bridge', json_encode($bridgeSettings));

            $this->Session->setFlash(__('Bridge has been disabled ! Be sure to use an updated version of your bridge plugin for MushRaider 1.4. If you don\'t you\'re gonna have a bad time !'), 'flash_important', array(), 'important');
        }

        /*
        * Import
        */
        // Add absolute path to games's logo field to prepare import functionallity
        App::uses('Game', 'Model');
        $GameModel = new Game();
        $params = array();
        $params['recursive'] = -1;
        $params['fields'] = array('id', 'logo');
        if($games = $GameModel->find('all', $params)) {
            foreach($games as $game) {
                if(!empty($game['Game']['logo']) && strpos($game['Game']['logo'], '/files/') === false) {
                    $toUpdate = array();
                    $toUpdate['id'] = $game['Game']['id'];
                    $toUpdate['logo'] = '/files/logos/'.$game['Game']['logo'];
                    $GameModel->create();
                    $GameModel->save($toUpdate);
                }
            }
        }

        /*
        * Roles permissions
        */
        // Add roles permissions
        $rolesPermissions = array(
            array('title' => __('Full permissions'), 'alias' => 'full_permissions', 'description' => __('Like Chuck Norris, he can do anything. This overwrite every permissions')),
            array('title' => __('Limited admin access'), 'alias' => 'limited_admin', 'description' => __('Like Robin, he can do some things but not all (like driving the batmobile or change user role)')),
            array('title' => __('Can manage events'), 'alias' => 'manage_events', 'description' => __('Can create, edit and delete events. Can also manage the roster for each events')),
            array('title' => __('Can create templates'), 'alias' => 'create_templates', 'description' => __('Can create events templates')),
            array('title' => __('Can create reports'), 'alias' => 'create_reports', 'description' => __('Can create events reports'))
        );
        App::uses('RolePermission', 'Model');
        $RolePermissionModel = new RolePermission();
        foreach($rolesPermissions as $rolesPermission) {
            $RolePermissionModel->create();
            $RolePermissionModel->save($rolesPermission);
        }

        // Add new roles permissions to existing roles
        App::uses('Role', 'Model');
        $RoleModel = new Role();
        App::uses('RolePermissionRole', 'Model');
        $RolePermissionRoleModel = new RolePermissionRole();
        $RolePermissionRoleModel->__add(array('role_id' => $RoleModel->getIdByAlias('admin'), 'role_permission_id' => $RolePermissionModel->getIdByAlias('full_permissions')));
        $RolePermissionRoleModel->__add(array('role_id' => $RoleModel->getIdByAlias('officer'), 'role_permission_id' => $RolePermissionModel->getIdByAlias('limited_admin')));
        $RolePermissionRoleModel->__add(array('role_id' => $RoleModel->getIdByAlias('officer'), 'role_permission_id' => $RolePermissionModel->getIdByAlias('manage_events')));
        $RolePermissionRoleModel->__add(array('role_id' => $RoleModel->getIdByAlias('officer'), 'role_permission_id' => $RolePermissionModel->getIdByAlias('create_templates')));
        $RolePermissionRoleModel->__add(array('role_id' => $RoleModel->getIdByAlias('officer'), 'role_permission_id' => $RolePermissionModel->getIdByAlias('create_reports')));
    }
}
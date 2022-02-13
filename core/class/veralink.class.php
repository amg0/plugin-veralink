<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

const VERALINK = 'veralink';     // plugin logical name

class veralink extends eqLogic
{   
   const PREFIX_ROOM = 'R_';           // prefix for rooms
   const PREFIX_BINLIGHT = 'B_';       // prefix for Bin Lights
   const CMD_SCENE = 'S-';       // prefix for scenes commands
   const CMD_BLON = 'BLON-';     // prefix for Bin Light ON commands
   const CONFIGTYPE_ROOM = 'room';           // config type for Room
   const CONFIGTYPE_BINLIGHT = 'binlight';   // config type for BinLight
   const MIN_REFRESH = 5;           // min sec for vera refresh
   const MAX_REFRESH = 240;         // max sec for vera refresh

   /*     * *************************Attributs****************************** */

   /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

   /*     * ***********************Methode static*************************** */
	public static function daemon() {
      log::add(VERALINK, 'debug', __METHOD__ . ' running: start');
      $starttime = microtime (true);   // current time in sec as a float
      //
      // do the work for all eqlogic of type root
      // !isset($this->getConfiguration('type', null))
      //
      foreach (self::byType(VERALINK) as $eqLogic) {
         $config = $eqLogic->getConfiguration('type',null);
			if ($config===null) {
				$eqLogic->refreshData();
			}
		}
      $seconds = config::byKey('refresh_freq', VERALINK, 60, true);
      $endtime = microtime (true);     // current time in sec as a float
      if ( $endtime - $starttime < $seconds )
      {
         usleep(floor(($seconds - ($endtime - $starttime))*1000000));
      }
      log::add(VERALINK, 'debug', __METHOD__ . ' running: end');
	}

	public static function deamon_info() {
      //log::add(VERALINK, 'debug', __METHOD__);
		$return = array();
		$return['log'] = '';
		$return['state'] = 'nok';
		$cron = cron::byClassAndFunction(VERALINK, 'daemon');
		if (is_object($cron) && $cron->running()) {
			$return['state'] = 'ok';
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start($debug = false) {
      log::add(VERALINK, 'debug', __METHOD__);
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$cron = cron::byClassAndFunction(VERALINK, 'daemon');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->run();
	}

	public static function deamon_stop() {
      log::add(VERALINK, 'debug', __METHOD__);
		$cron = cron::byClassAndFunction(VERALINK, 'daemon');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->halt();
	}

	public static function deamon_changeAutoMode($mode) {
      log::add(VERALINK, 'debug', __METHOD__.'('.$mode.')');
		$cron = cron::byClassAndFunction(VERALINK, 'daemon');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->setEnable($mode);
		$cron->save();
	}

   /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
      public static function cron5() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
      public static function cron10() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
      public static function cron15() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
      public static function cron30() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {
      }
     */

   /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {
      }
     */

   /*     * *********************Méthodes d'instance************************* */

   // Fonction exécutée automatiquement avant la création de l'équipement 
   public function preInsert()
   {
      log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement après la création de l'équipement 
   public function postInsert()
   {
      log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
   public function preUpdate()
   {
      log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement après la mise à jour de l'équipement 
   public function postUpdate()
   {
      log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
   public function preSave()
   {
      log::add(VERALINK, 'debug', __METHOD__);
      //$this->setDisplay("width","800px");                   // widget display width
   }

   // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
   public function postSaveRoot()
   {
      //
      // this is the root EQLOGIC.  so create the Data command if needed
      //
      $data = $this->getCmd(null, 'data');
      if (!is_object($data)) {
         $data = new veralinkCmd();
         $data->setName(__('Data', __FILE__));
      }
      $data->setLogicalId('data');
      $data->setEqLogic_id($this->getId());
      $data->setType('info');
      $data->setSubType('string');
      //$data->setTemplate('dashboard','default');   //template pour le dashboard
      $data->setIsVisible(0);
      $data->save();   

      //
      // refresh data command
      //
      $refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new veralinkCmd();
			$refresh->setName(__('Rafraichir', __FILE__));
		}
		$refresh->setEqLogic_id($this->getId());
		$refresh->setLogicalId('refresh');
		$refresh->setType('action');
		$refresh->setSubType('other');
      $refresh->save();  

      //
      // Refresh the Data command if needed
      //
      $objects = json_decode($this->refreshData(1));
      if (isset($objects)) {
         //
         // Create Room Equipment objects
         //

         // create eqlogic for room 0 for scenes not assigned to a room
         $this->createRoomEqLogic( (object) array('id'=>0, 'name'=>__('Sans Piece', __FILE__)) );   // cast to object to enable -> access
         // create a eqLogic per room
         foreach ($objects->rooms as $room) {
               $this->createRoomEqLogic( $room );
         }

         //
         // Create PowerBinary Equipment objects
         //
         foreach( $objects->devices as $device ) {
            if ($device->device_type == 'urn:schemas-upnp-org:device:BinaryLight:1') {
               $this->createBinaryLightEqLogic( $device );
            }
         }
      }
   }

   public function createBinaryLightEqLogic($device) {
      log::add(VERALINK, 'debug', __METHOD__.sprintf(' for device:#%s %s',$device->id,$device->name));
      $eqLogic = self::byLogicalId(self::PREFIX_BINLIGHT . $device->id, VERALINK);
      if (!is_object($eqLogic)) {
         log::add(VERALINK, 'info', 'create another EQ for device #' . $device->id);
         $eqLogic = new veralink();
         $eqLogic->setEqType_name(VERALINK);
         $eqLogic->setConfiguration('type', self::CONFIGTYPE_BINLIGHT);
         $eqLogic->setLogicalId(self::PREFIX_BINLIGHT . $device->id);
         $eqLogic->setConfiguration('ipaddr', $this->getConfiguration('ipaddr'));
         $eqLogic->setConfiguration('rootid', $this->getId());
         $eqLogic->setIsEnable(0);
         $eqLogic->setIsVisible(0);
      }
      $eqLogic->setObject_id($this->getObject_id());  // same parent as root parent
      $eqLogic->setName($device->name);
      $eqLogic->save();
   }

   public function createRoomEqLogic($room) 
   {
      //
      // for each room , if and only if the EQ for the room does not exist, create it
      //
      $eqLogic = self::byLogicalId(self::PREFIX_ROOM . $room->id, VERALINK);
      if (!is_object($eqLogic)) {
         log::add(VERALINK, 'info', 'create another EQ for room #' . $room->id);
         $eqLogic = new veralink();
         $eqLogic->setEqType_name(VERALINK);
         $eqLogic->setConfiguration('type', self::CONFIGTYPE_ROOM);
         $eqLogic->setLogicalId(self::PREFIX_ROOM . $room->id);
         $eqLogic->setConfiguration('ipaddr', $this->getConfiguration('ipaddr'));
         $eqLogic->setConfiguration('rootid', $this->getId());
         $eqLogic->setIsEnable(0);
         $eqLogic->setIsVisible(0);
      }
      $eqLogic->setObject_id($this->getObject_id());  // same parent as root parent
      $eqLogic->setName($this->getName().' '.__('Pièce', __FILE__).' '.$room->name);
      $eqLogic->save();
   }

   public function postSaveBinLight($configtype) 
   {
      //
      // This is a BinLight EQLOGIC
      //
      log::add(VERALINK, 'debug', 'EQ configuration type is ' . $configtype . ' logical Id:' . $this->getLogicalId());
      $idroot = $this->getConfiguration('rootid');
      $root_eqlogic = eqLogic::byId($idroot);

      $veraid = substr( $this->getLogicalId(), strlen(self::PREFIX_BINLIGHT) );
      //$device = $root_eqlogic->getDevice($veraid);

      $logicalid = self::CMD_BLON.$veraid;
      $cmd = $this->getCmd(null, $logicalid);
      if (!is_object($cmd)) {
         log::add(VERALINK, 'info', 'About to create Cmd for dev '.$veraid );
         $cmd = new veralinkCmd();
         $cmd->setLogicalId($logicalid);
         $cmd->setName('On');
         $cmd->setEqLogic_id($this->getId());
         $cmd->setIsVisible(1);
         $cmd->setType('action');
         $cmd->setSubType('other');
         $cmd->setTemplate('dashboard','default');   //template pour le dashboard
         //$cmd->setdisplay('icon', '<i class="' . 'jeedomapp-playerplay' . '"></i>');
         $cmd->setdisplay('showIconAndNamedashboard', 1);
         $cmd->setdisplay('showIconAndNamemobile', 1);
         $cmd->save();   
      }
   }

   public function postSaveRoom($configtype)
   {
      //
      // This is a room EQLOGIC
      //
      log::add(VERALINK, 'debug', 'EQ configuration type is ' . $configtype . ' logical Id:' . $this->getLogicalId());
      $idroot = $this->getConfiguration('rootid');
      $root_eqlogic = eqLogic::byId($idroot);

      $idroom = substr( $this->getLogicalId(), strlen(self::PREFIX_ROOM) );
      $scenes = $root_eqlogic->getScenesOfRoom($idroom);

      foreach($scenes as $scene) {
         $logicalid = self::CMD_SCENE.$scene->id;
         $cmd = $this->getCmd(null, $logicalid);
         if (!is_object($cmd)) {
            log::add(VERALINK, 'info', 'About to create Cmd for scene '.$scene->id.' name:'.$scene->name);
            $cmd = new veralinkCmd();
            $cmd->setIsVisible(1);
         }
         $cmd->setName(__('Scène', __FILE__).' '.$scene->name);
         $cmd->setLogicalId($logicalid);
         $cmd->setEqLogic_id($this->getId());
         $cmd->setType('action');
         $cmd->setSubType('other');
         $cmd->setTemplate('dashboard','default');   //template pour le dashboard
         $cmd->setdisplay('icon', '<i class="' . 'jeedomapp-playerplay' . '"></i>');
         $cmd->setdisplay('showIconAndNamedashboard', 1);
         $cmd->setdisplay('showIconAndNamemobile', 1);
         $cmd->save();   
      }
   }
   
   public function postSave()
   {
      log::add(VERALINK, 'debug', __METHOD__);

      $configtype = $this->getConfiguration('type', null);
      switch( $configtype ) {
         case self::CONFIGTYPE_ROOM:
            $this->postSaveRoom($configtype);
            break;
         case self::CONFIGTYPE_BINLIGHT:
            $this->postSaveBinLight($configtype);
            break;
         default:
            $this->postSaveRoot();
      }
   }

   // Fonction exécutée automatiquement avant la suppression de l'équipement 
   public function preRemove()
   {
      log::add(VERALINK, 'debug', __METHOD__);

      // only remove associated room equipments if this is a root eqLogic equipment ( a vera ) 
      $configtype = $this->getConfiguration('type', null);
      if (!isset($configtype)) {
         $cart = array();
         foreach (self::byType(VERALINK) as $eqLogic) {
            $eqtype = $eqLogic->getConfiguration('type');
            switch($eqtype) {
               case self::CONFIGTYPE_ROOM:
               case self::CONFIGTYPE_BINLIGHT: 
                  log::add(VERALINK, 'debug', 'About to delete eqLogic Room '.$eqLogic->getId());
                  $cart[] = $eqLogic;
                  break;
            }
         }
         foreach($cart as $eqLogic) {
            $eqLogic->Remove();
         }
      }
   }

   // Fonction exécutée automatiquement après la suppression de l'équipement 
   public function postRemove()
   {
      log::add(VERALINK, 'debug', __METHOD__);
   }

   /*
     * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
      public function toHtml($_version = 'dashboard') {

      }
     */

   /*
     * Non obligatoire : permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
    */

    //  Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_refresh_freq( $value ) {
      log::add(VERALINK, 'debug', __METHOD__); 
      // verity it is numeric and within range
      $resvalue = config::checkValueBetween($value, self::MIN_REFRESH, self::MAX_REFRESH);
      if ($value != $resvalue) {
         log::add(VERALINK, 'debug', 'outside range, modified value for refresh frequency '.$resvalue);
      }
      return $resvalue;
   }
   
   public function getUserData($ipaddr,$initial=null)
   {
      log::add(VERALINK, 'debug', __METHOD__);
      $user_dataversion = ( isset($initial) ? $initial : $this->getConfiguration('user_dataversion', 1) );
      log::add(VERALINK, 'debug', 'initial userdataversion:'. $user_dataversion);

      $url = 'http://' . $ipaddr . '/port_3480/data_request?id=user_data&DataVersion='.$user_dataversion;
      log::add(VERALINK, 'info', 'getting user_data from ' . $url);
      $json = file_get_contents($url);
      if ($json===false) {
         throw new Exception(__('Vera ne répond pas', __FILE__));
      }
      log::add(VERALINK, 'debug', 'received :'.substr($json,0,200));

      if (($json == 'NO_CHANGES') || ($json == 'Exiting')) {
         log::add(VERALINK, 'debug', 'No change with result:'.$json);
         $json="";
      } else {
         $this->checkAndUpdateCmd('data', $json);
         $user_data = json_decode($json,false);
         $user_dataversion = $user_data->DataVersion;
         $this->setConfiguration('user_dataversion', $user_dataversion);

         // make sure the initial call from postSave does not trigger an infinite loop 
         $this->save(true);
         //log::add(VERALINK, 'debug', 'received devices:'. json_encode($user_data->devices));
         log::add(VERALINK, 'debug', 'received userdataversion:'. $user_dataversion);
      }
      return $json;
   }

   public function getLuStatus($ipaddr)
   {
      log::add(VERALINK, 'debug', __METHOD__);

      $statusdataversion = $this->getConfiguration('statusdataversion', 1);
      $lastloadtime = $this->getConfiguration('lastloadtime', 0);
      $userdatadataversion = $this->getConfiguration('user_dataversion', 1);

      log::add(VERALINK, 'debug', sprintf('OLD statusdataversion:%s loadtime:%s userdataversion:%s',$statusdataversion,$lastloadtime,$userdatadataversion));
      $url = sprintf('http://%s/port_3480/data_request?id=lu_status2&output_format=json&DataVersion=%s&LoadTime=%s&Timeout=%s&MinimumDelay=%s',
         $ipaddr,$statusdataversion,$lastloadtime,60,1500);

      log::add(VERALINK, 'info', 'getting lu_status from ' . $url);
      $json = file_get_contents($url);
      if ($json===false) {
         throw new Exception(__('Vera ne répond pas', __FILE__));
      }
      log::add(VERALINK, 'debug', 'received :'.substr($json,0,200));

      if (($json == 'NO_CHANGES') || ($json == 'Exiting')) {
         log::add(VERALINK, 'debug', 'No change with result:'.$json);
         $json="";
      } else {
         $lu_data = json_decode($json);
         $statusdataversion = $lu_data->DataVersion;
         $lastloadtime = $lu_data->LoadTime;
         $this->setConfiguration('statusdataversion', $statusdataversion);
         $this->setConfiguration('lastloadtime', $lastloadtime);
         $this->save(true);
         
         log::add(VERALINK, 'debug', sprintf('NEW statusdataversion:%s loadtime:%s userdataversion:%s',$statusdataversion,$lastloadtime,$lu_data->UserData_DataVersion));
         if ($userdatadataversion != $lu_data->UserData_DataVersion) 
         {
            log::add(VERALINK, 'info', 'refresh user_data:'.$lu_data->UserData_DataVersion);
            $json = $this->getUserData($ipaddr,$userdatadataversion);
         } else {
            $cmd = $this->getCmd(null, 'data');
            $old = json_decode($cmd->execCmd());
            // il faut ecraser $old.devices etc... with $lu_datas
            foreach( $lu_data->devices as $dev ) {
               foreach($old->devices as $olddev ) {
                  if ($olddev->id == $dev->id) {
                     foreach($dev->states as $state) {
                        foreach($olddev->states as $oldstate) {
                           if (($oldstate->service == $state->service) && ($oldstate->variable == $state->variable) && ($oldstate->value != $state->value)){
                              if ($state->variable != 'LastPollSuccess') {
                                 log::add(VERALINK, 'info', sprintf('dev:%s-%s %s %s=>%s (%s)',$dev->id,$olddev->name,$state->variable, $oldstate->value, $state->value, $state->service));
                                 $oldstate->value = $state->value;                              
                              }
                           }
                        }
                     }
                  }
               }
            }
            //log::add(VERALINK, 'debug', 'updated devices:'. json_encode($old->devices));
            $json = json_encode($old);
            $this->checkAndUpdateCmd('data', $json);
            $this->save(true);
         }
      }
      return $json;
   }

   public function refreshData( $initial=null )
   {
      log::add(VERALINK, 'debug', __METHOD__ . ' Initial:'.json_encode($initial));
      $ipaddr = $this->getConfiguration('ipaddr', null);
      if (is_null($ipaddr)) {
         log::add(VERALINK, 'info', 'null IP addr');
         return null;
      }

      if ($initial==1) {
         $json = $this->getUserData($ipaddr,$initial);         
      }
      else {
         $json = $this->getLuStatus($ipaddr);         
      }
      return $json;
   }

   public function getScenesOfRoom($idroom)
   {
      log::add(VERALINK, 'debug', __METHOD__.' idroom:'.$idroom);
      $searchfor = strval($idroom);
      $datacmd = $this->getCmd('info','data');      // get Cmd data of type info
      $data = json_decode( $datacmd -> execCmd() );

      // pass the searchfor into the scope of the anonymous function using the use keyword
      // only keep scenes from the same room and which are not pure notification scenes
      $scenes = array_filter( $data->scenes, function($elem) use ($searchfor) {
         return (strval($elem->room) == $searchfor) && (isset($elem->notification_only)==false);
      });

      return $scenes;
   }

   public function runScene($id)
   {
      $ipaddr = $this->getConfiguration('ipaddr', null);
      if (is_null($ipaddr)) {
         log::add(VERALINK, 'warning', 'null IP addr, no action taken');
         return null;
      }
      $url = 'http://' . $ipaddr . '/port_3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum=' . $id;
      $xml = file_get_contents($url);
      log::add(VERALINK, 'debug', 'runscene returned ' . $xml);
      return $xml;
   }

   /*     * **********************Getteur Setteur*************************** */
}

class veralinkCmd extends cmd
{
   /*     * *************************Attributs****************************** */

   /*
      public static $_widgetPossibility = array();
    */

   /*     * ***********************Methode static*************************** */


   /*     * *********************Methode d'instance************************* */

   /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

   // Exécution d'une commande  
   public function execute($_options = array())
   {
      $eqlogic = $this->getEqLogic(); //Récupération de l’eqlogic

      list( $cmd, $param ) = explode('-',$this->getLogicalId());
      switch ($cmd) {

         case 'refresh':
            $eqlogic->refreshData();
            break;

         case veralink::CMD_BLON:
            break;

         case veralink::CMD_SCENE:
            // this is a scene command
            log::add(VERALINK, 'info', 'execute SCENE ' . $param);
            $xml = $eqlogic->runScene($param);
            break;
      }
   }
   /*     * **********************Getteur Setteur*************************** */
}

/* 
TOKNOW:  THIS does not work, the original idea would be that execute is an abstract method of an abstract class Cmd but it is not the case
<PluginID>Cmd is a important naming convention. cf https://community.jeedom.com/t/plusieurs-classes-de-commandes-cmd/76608/2

class veraSceneCmd extends cmd {
} 
*/

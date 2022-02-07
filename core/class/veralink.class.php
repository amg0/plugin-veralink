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
const SCENECMD = 'S_';           // prefix for scenes
const ROOMEQ = 'R_';             // prefix for rooms
const MIN_REFRESH = 5;           // min sec for vera refresh
const MAX_REFRESH = 240;         // max sec for vera refresh

class veralink extends eqLogic
{
   /*     * *************************Attributs****************************** */

   /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

   /*     * ***********************Methode static*************************** */
	public static function daemon() {
      log::add(VERALINK, 'debug', __METHOD__ . ' running: start');
      $seconds = config::byKey('refresh_freq', VERALINK, 60, true);
      usleep($seconds * 1000000); // 15s
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
      $objects = json_decode($this->refreshData());
      if (isset($objects)) {
         // create eqlogic for room 0 for scenes not assigned to a room
         $this->createRoomEqLogic( (object) array('id'=>0, 'name'=>__('Sans Piece', __FILE__)) );   // cast to object to enable -> access
         // create a eqLogic per room
         foreach ($objects->rooms as $room) {
               $this->createRoomEqLogic( $room );
         }
      }
   }

   public function createRoomEqLogic($room) 
   {
      //
      // for each room , if and only if the EQ for the room does not exist, create it
      //
      $eqLogic = self::byLogicalId(ROOMEQ . $room->id, VERALINK);
      if (!is_object($eqLogic)) {
         log::add(VERALINK, 'debug', 'create another EQ for room #' . $room->id);
         $eqLogic = new veralink();
         $eqLogic->setEqType_name(VERALINK);
         $eqLogic->setConfiguration('type', 'room');
         $eqLogic->setLogicalId(ROOMEQ . $room->id);
         $eqLogic->setConfiguration('ipaddr', $this->getConfiguration('ipaddr'));
         $eqLogic->setConfiguration('rootid', $this->getId());
         $eqLogic->setIsEnable(0);
         $eqLogic->setIsVisible(0);
      }
      $eqLogic->setObject_id($this->getObject_id());  // same parent as root parent
      $eqLogic->setName($this->getName().' '.$room->name);
      $eqLogic->save();
   }
   
   public function postSaveRoom($configtype)
   {
      //
      // This is a room EQLOGIC
      //
      log::add(VERALINK, 'debug', 'EQ configuration type is ' . $configtype . ' logical Id:' . $this->getLogicalId());

      $idroot = $this->getConfiguration('rootid');
      $root_eqlogic = eqLogic::byId($idroot);

      $idroom = substr( $this->getLogicalId(), strlen(ROOMEQ) );
      $scenes = $root_eqlogic->getScenesOfRoom($idroom);

      foreach($scenes as $scene) {
         $logicalid = SCENECMD.$scene->id;
         $cmd = $this->getCmd(null, $logicalid);
         if (!is_object($cmd)) {
            log::add(VERALINK, 'info', 'About to create Cmd for scene '.$scene->id.' name:'.$scene->name);
            $cmd = new veralinkCmd();
            $cmd->setIsVisible(1);
         }
         $cmd->setName($scene->name);
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
      if (isset($configtype)) {
         $this->postSaveRoom($configtype);
      } else {
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
            if ($eqtype == 'room') {
               log::add(VERALINK, 'debug', 'About to delete eqLogic Room '.$eqLogic->getId());
               $cart[] = $eqLogic;
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
      $resvalue = config::checkValueBetween($value, MIN_REFRESH, MAX_REFRESH);
      if ($value != $resvalue) {
         log::add(VERALINK, 'debug', 'outside range, modified value for refresh frequency '.$resvalue);
      }
      return $resvalue;
   }
   
   public function refreshData()
   {
      log::add(VERALINK, 'debug', __METHOD__);
      $ipaddr = $this->getConfiguration('ipaddr', null);
      $timestamp = $this->getConfiguration('dataversion', 1);
      if (is_null($ipaddr)) {
         log::add(VERALINK, 'info', 'null IP addr');
         return null;
      }

      $url = 'http://' . $ipaddr . '/port_3480/data_request?id=user_data&DataVersion='.$timestamp;
      log::add(VERALINK, 'info', 'getting user_data from ' . $url);
      $json = file_get_contents($url);

      if ($json===false) {
			throw new Exception(__('Vera ne répond pas', __FILE__));
		}
      if (($json != 'NO_CHANGES') && ($json != 'Exiting')) {
         $this->checkAndUpdateCmd('data', $json);
         $user_data = json_decode($json,false);
         $timestamp = $user_data->DataVersion;
         $this->setConfiguration('dataversion', $timestamp);
         $this->save();
         log::add(VERALINK, 'debug', 'DataVersion:'.$timestamp);
      } else {
         log::add(VERALINK, 'debug', 'No change with :'.$json);
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
      switch ($this->getLogicalId()) {

         case 'refresh':
            $eqlogic->refreshData();
            break;

         default:
            // this is a scene command
            if (substr($this->getLogicalId(), 0, strlen(SCENECMD)) == SCENECMD) {
               $id = substr($this->getLogicalId(), strlen(SCENECMD));
               log::add(VERALINK, 'info', 'execute SCENE ' . $id);
               $xml = $eqlogic->runScene($id);
            }
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

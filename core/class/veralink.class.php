<?php

/* This file is part of Veralink , a plugin for Jeedom.
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

   /* Documentation Jeedom Config ( category, generic types, etc )
   https://github.com/jeedom/core/blob/alpha/core/config/jeedom.config.php#L153
   */


require_once __DIR__  . '/../../../../core/php/core.inc.php';

const VERALINK = 'veralink';     // plugin logical name
const PREFIX_VERADEVICE =  'D_';        // prefix for Vera devices
const PREFIX_ROOM =        'R_';        // prefix for rooms

const CONFIGTYPE_ROOM =    'room';  // config type for Room
const CONFIGTYPE_BINLIGHT= 'urn:schemas-upnp-org:device:BinaryLight:1';   // config type for BinLight
const CONFIGTYPE_TEMP =    'urn:schemas-micasaverde-com:device:TemperatureSensor:1';  // config type for Temperature

const CMD_BLON =        'BLON';        // prefix for Bin Light ON commands - DO NOT include '-'
const CMD_BLOFF =       'BLOFF';       // prefix for Bin Light OFF commands - DO NOT include '-'
const CMD_BLETAT =      'BLETAT';      // prefix for Bin Light State info
const CMD_BLWATTS =     'BLWATTS';     // prefix for Bin Light State info
const CMD_TEMPSENSOR =  'TEMPS';       // prefix for Temp sensors
const CMD_LIGHTSENSOR = 'LIGHTS';      // prefix for Temp sensors
const CMD_MOTIONSENSOR = 'MOTION';     // prefix for motion sensors
const CMD_BATTERY = 'BATTERY';         // prefix for battery commands
const CMD_SCENE = 'SC';                // prefix for scenes commands - DO NOT include '-'

const MIN_REFRESH = 5;           // min sec for vera refresh
const MAX_REFRESH = 240;         // max sec for vera refresh

const CmdByVeraType = array(
   'urn:schemas-upnp-org:device:BinaryLight:1'=>
      array(
         'EqCategory'=>'light',
         'commands'=> [
            array( 'logicalid'=>CMD_BLWATTS, 'name'=>'Watts','type'=>'info|numeric', 'generic'=>'POWER', 'unite'=>'W', 'variable'=>'Watts', 'service'=>'urn:micasaverde-com:serviceId:EnergyMetering1'),
            array( 'logicalid'=>CMD_BLOFF,   'name'=>'Off', 'type'=>'action|other', 'generic'=>'LIGHT_OFF', 'function'=>'switchLight', 'value'=>0),
            array( 'logicalid'=>CMD_BLON,    'name'=>'On',  'type'=>'action|other', 'generic'=>'LIGHT_ON', 'function'=>'switchLight', 'value'=>1),
            array( 'logicalid'=>CMD_BLETAT,  'name'=>'Etat', 'type'=>'info|binary', 'generic'=>'LIGHT_STATE', 'template'=>'prise', 'variable'=>'Status', 'service'=>'urn:upnp-org:serviceId:SwitchPower1')
         ]
      ),
   'urn:schemas-micasaverde-com:device:TemperatureSensor:1'=>         
      array(
         'EqCategory'=>'heating',
         'commands'=> [
            array( 'optional'=>true, 'logicalid'=>CMD_BATTERY,  'name'=>'Batterie', 'type'=>'info|numeric', 'generic'=>'BATTERY',  'variable'=>'BatteryLevel', 'service'=>'urn:micasaverde-com:serviceId:HaDevice1'),
            array( 'logicalid'=>CMD_TEMPSENSOR, 'name'=>'Température',  'type'=>'info|numeric', 'generic'=>'TEMPERATURE', 'variable'=>'CurrentTemperature','service'=>'urn:upnp-org:serviceId:TemperatureSensor1' )
         ]
      ),
   'urn:schemas-micasaverde-com:device:LightSensor:1'=>
      array(
         'EqCategory'=>'light',
         'commands'=> [
            array( 'optional'=>true, 'logicalid'=>CMD_BATTERY,  'name'=>'Batterie', 'type'=>'info|numeric', 'generic'=>'BATTERY',  'variable'=>'BatteryLevel', 'service'=>'urn:micasaverde-com:serviceId:HaDevice1'),
            array( 'logicalid'=>CMD_LIGHTSENSOR,   'name'=>'Luminosité',  'type'=>'info|numeric', 'generic'=>'LIGHT_BRIGHTNESS', 'variable'=>'CurrentLevel','service'=>'urn:micasaverde-com:serviceId:LightSensor1' )
         ]
         ),
   'urn:schemas-micasaverde-com:device:MotionSensor:1'=>
      array(     
         'EqCategory'=>'security',       
         'commands'=> [
            array( 'optional'=>true, 'logicalid'=>CMD_BATTERY,  'name'=>'Batterie', 'type'=>'info|numeric', 'generic'=>'BATTERY',  'variable'=>'BatteryLevel', 'service'=>'urn:micasaverde-com:serviceId:HaDevice1'),
            array( 
               'logicalid'=>CMD_MOTIONSENSOR,   'name'=>'Présence',  'type'=>'info|binary', 'generic'=>'PRESENCE', 'template'=>'presence',
               'variable'=>'Tripped','service'=>'urn:micasaverde-com:serviceId:SecuritySensor1' )
         ]
      )
);

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
      $starttime = microtime (true);   // current time in sec as a float
      //
      // do the work for all eqLogic of type root and isEnable is one
      //
      foreach (self::byType(VERALINK) as $eqLogic) {
         $config = $eqLogic->getConfiguration('type',null);
         // if root
			if ( is_null($config) && ($eqLogic->getIsEnable() == 1) ) { 
				$eqLogic->refreshData();
			}
		}
      $seconds = config::byKey('refresh_freq', VERALINK, 60, true);
      $endtime = microtime (true);     // current time in sec as a float
      if ( $endtime - $starttime < $seconds )
      {
         $ms = floor(($seconds - ($endtime - $starttime))*1000000);
         log::add(VERALINK, 'info', __METHOD__ . ' sleeping microsec:'.$ms);
         usleep($ms);
      }
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
      //log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement après la création de l'équipement 
   public function postInsert()
   {
      //log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
   public function preUpdate()
   {
      //log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement après la mise à jour de l'équipement 
   public function postUpdate()
   {
      //log::add(VERALINK, 'debug', __METHOD__);
   }

   // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
   public function preSave()
   {
      //log::add(VERALINK, 'debug', __METHOD__);
      //$this->setDisplay("width","800px");                   // widget display width
   }

   // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
   public function postSaveRoot()
   {
      //
      // this is the root EQLOGIC.  so create the scenes and devices command if needed
      //
      $cmds = array(

         (object)array('lid'=>'firmware', 'name'=>__('Firmware', __FILE__)),
         //(object)array('lid'=>'devices', 'name'=>__('Devices', __FILE__)),

      );
      foreach( $cmds as $cmd) {
         $data = $this->getCmd(null, $cmd->lid);
         if (!is_object($data)) {
            $data = new veralinkCmd();

            //$data->setTemplate('dashboard','default');   //template pour le dashboard
            $data->setIsVisible(0);
         }
         $data->setName($cmd->name);
         $data->setLogicalId($cmd->lid);
         $data->setEqLogic_id($this->getId());
         $data->setType('info');
         $data->setSubType('string');
         $data->save();   
      }

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

      $reset = $this->getCmd(null, 'reset');
		if (!is_object($reset)) {
			$reset = new veralinkCmd();
			$reset->setName(__('Reset', __FILE__));
		}
		$reset->setEqLogic_id($this->getId());
		$reset->setLogicalId('reset');
		$reset->setType('action');
		$reset->setSubType('other');
      $reset->save();  

      //
      // Get the VERA data for the first time and create the rooms and binary lights
      //
      $results = $this->refreshData(1);
      $array = $results->arr;
      if (isset($array)) {
         //
         // Create Room Equipment objects
         //

         // create a special eqLogic for room 0 (scenes not assigned to a room)
         $this->createRoomEqLogic( (object) array('id'=>0, 'name'=>__('Sans Piece', __FILE__)) );   // cast to object to enable -> access

         // create a eqLogic per room
         foreach ($array['rooms'] as $room) {
            $room=(object)$room;
            $this->createRoomEqLogic( $room );
         }

         //
         // Create supported types Equipment objects
         //
         foreach( $array['devices']as $device ) {
            $device = (object)$device;
            $config = CmdByVeraType[$device->device_type] ;
            if (isset($config)) {
               $this->createChildEqLogic($device,$device->device_type);
            } 
         }
      }
   }

   public function getRoot()
   {
      log::add(VERALINK, 'debug', __METHOD__);
      $idroot = $this->getConfiguration('rootid',null);
      return ( $idroot==null) ? $this : eqLogic::byId($idroot);
   }

   public function createChildEqLogic($device,$configtype) {
      log::add(VERALINK, 'debug', __METHOD__);
      $eqLogic = self::byLogicalId(PREFIX_VERADEVICE . $device->id, VERALINK);

      if (!is_object($eqLogic)) {
         log::add(VERALINK, 'info', __METHOD__.sprintf(' for device:#%s %s',$device->id,$device->name));
         $eqLogic = new veralink();
         $eqLogic->setEqType_name(VERALINK);
         $eqLogic->setConfiguration('type', $configtype);
         $eqLogic->setLogicalId(PREFIX_VERADEVICE . $device->id);
         //$eqLogic->setConfiguration('ipaddr', $this->getConfiguration('ipaddr'));
         $eqLogic->setConfiguration('rootid', $this->getId());
         $eqLogic->setIsEnable(0);
         $eqLogic->setIsVisible(0);
         $category = CmdByVeraType[$configtype]['EqCategory'] ?? 'default';
         $eqLogic->setCategory($category,'1');
      }
      $eqLogic->setObject_id($this->getObject_id());  // same parent as root parent
      $eqLogic->setName($device->name);
      $eqLogic->save();      
   }

   public function createRoomEqLogic($room) 
   {
      log::add(VERALINK, 'debug', __METHOD__);
      //
      // for each room , if and only if the EQ for the room does not exist, create it
      //
      $eqLogic = self::byLogicalId(PREFIX_ROOM . $room->id, VERALINK);
      if (!is_object($eqLogic)) {
         log::add(VERALINK, 'info', __METHOD__.sprintf(' for room:#%s %s',$room->id,$room->name));
         $eqLogic = new veralink();
         $eqLogic->setEqType_name(VERALINK);
         $eqLogic->setConfiguration('type', CONFIGTYPE_ROOM);
         $eqLogic->setLogicalId(PREFIX_ROOM . $room->id);
         $eqLogic->setConfiguration('ipaddr', $this->getConfiguration('ipaddr'));
         $eqLogic->setConfiguration('rootid', $this->getId());
         $eqLogic->setIsEnable(0);
         $eqLogic->setIsVisible(0);
      }
      $eqLogic->setObject_id($this->getObject_id());  // same parent as root parent
      $eqLogic->setName($this->getName().' '.__('Pièce', __FILE__).' '.$room->name);
      $eqLogic->save();
   }

   private function shouldCreateCommand( $service, $variable, $veradevid) {
      //log::add(VERALINK, 'debug', __METHOD__.sprintf(' service:%s variable:%s device:%s',$service, $variable, $veradevid));
      $cfg = $this->getConfiguration('veralink_devices',null);
      $devices = json_decode( $cfg ?? [] , true );    // array
      foreach($devices as $dev) {
         if ($dev['id'] == $veradevid) {
            foreach( $dev['states'] as $state ) {
               if (($state['service']==$service) && ($state['variable']==$variable)) {
                  //log::add(VERALINK, 'debug', __METHOD__.sprintf(' should create command for service:%s variable:%s device:%s',$service, $variable, $veradevid));
                  return true;
               }
            }
         }
      }
      return false;
   }

   public function postSaveEqLogic($configtype) 
   {
      //
      // This is a Temperature EQLOGIC
      //
      log::add(VERALINK, 'debug', __METHOD__.' EQ configuration type is ' . $configtype . ' logical Id:' . $this->getLogicalId());
      $idroot = $this->getConfiguration('rootid');
      $root_eqLogic = eqLogic::byId($idroot);

      $veradevid = substr( $this->getLogicalId(), strlen(PREFIX_VERADEVICE) );

      // Create Mandatory commands
      $array = CmdByVeraType[$configtype]['commands'];
      foreach( $array as $item) {
         $item = (object) $item;
         if (!isset($item->optional) || $root_eqLogic->shouldCreateCommand( $item->service, $item->variable, $veradevid )) {
            $cmdid = $item->logicalid.'-'.$veradevid;
            $cmd = $this->getCmd(null, $cmdid);
            if (!is_object($cmd)) {
               log::add(VERALINK, 'info', 'About to create Cmd '.$cmdid.' for dev '.$veradevid );
               $cmd = new veralinkCmd();
               $cmd->setLogicalId($cmdid);
               $cmd->setEqLogic_id($this->getId());
               $cmd->setName(  $item->name );
   
               $split = explode('|',$item->type);
               $cmd->setType( $split[0] );
               $cmd->setSubType( $split[1] );
   
               if (isset($item->template)) {
                  $cmd->setTemplate('dashboard',$item->template );    //template pour le dashboard
                  $cmd->setTemplate('mobile',$item->template );    //template pour le dashboard
               }
   
               if (isset($item->generic))
                  $cmd->setGeneric_type($item->generic);
   
               if (isset($item->unite))
                  $cmd->setUnite($item->unite);
                  
               $cmd->setIsVisible($item->optional ? 0 : 1);
               //$cmd->setdisplay('icon', '<i class="' . 'jeedomapp-playerplay' . '"></i>');
               $cmd->setdisplay('showIconAndNamedashboard', 1);
               $cmd->setdisplay('showIconAndNamemobile', 1);
               $cmd->save();   
            }
         }
      }
   }


   public function postSaveRoom($configtype)
   {
      //
      // This is a room EQLOGIC
      //
      log::add(VERALINK, 'debug', 'EQ configuration type is ' . $configtype . ' logical Id:' . $this->getLogicalId());
      $idroot = $this->getConfiguration('rootid');
      $root_eqLogic = eqLogic::byId($idroot);

      $idroom = substr( $this->getLogicalId(), strlen(PREFIX_ROOM) );
      $scenes = $root_eqLogic->getScenesOfRoom($idroom);

      foreach($scenes as $scene) {
         $logicalid = CMD_SCENE.'-'.$scene->id;
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
         case CONFIGTYPE_ROOM:
            $this->postSaveRoom($configtype);
            break;
         default:
            if ( isset($configtype )){
               $this->postSaveEqLogic($configtype);   
            } else {
               $this->postSaveRoot();
            } 
      }
   }

   // Fonction exécutée automatiquement avant la suppression de l'équipement 
   public function preRemove()
   {
      //log::add(VERALINK, 'debug', __METHOD__);

      // only remove associated room equipments if this is a root eqLogic equipment ( a vera ) 
      $configtype = $this->getConfiguration('type', null);
      if (!isset($configtype)) {
         self::deamon_stop();
         $cart = array();
         foreach (self::byType(VERALINK) as $eqLogic) {
            $eqtype = $eqLogic->getConfiguration('type',null);
            if (isset($eqtype)) {
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
      //log::add(VERALINK, 'debug', __METHOD__);
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
   
   public function getUserData($ipaddr,$initial=null)
   {
      log::add(VERALINK, 'debug', __METHOD__);
      $result = (object)['json'=>'', 'obj'=>null];

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
         // result already prepared

      } else {
         $user_data = json_decode($json,true);
         $user_dataversion = $user_data['DataVersion'];

         $scenestosave = array_map(function ($v) {
               $v=(object)$v;
               return (object)array('id'=>$v->id,'name'=>$v->name, 'room'=>$v->room, 'notification_only'=>$v->notification_only);
            },
            $user_data['scenes']
         );

         // not the use of array_values as array_filter presevers the keys in the result which is not what we want
         $filtereddevices = array_values( array_filter($user_data['devices'],function($d){
            return in_array($d['device_type'], array_keys(CmdByVeraType));
         }));

         $devicestosave = array_map(function ($d) {
               return (object)array('id'=>$d['id'],'device_type'=>$d['device_type'],'name'=>$d['name'],'states'=>$d['states']);
            },
            $filtereddevices
         );

         $this->checkAndUpdateCmd('firmware', $user_data['BuildVersion']);
         $this->setConfiguration('veralink_scenes', (json_encode($scenestosave)));
         $this->setConfiguration('veralink_devices', (str_replace('\\n', '', json_encode($devicestosave))));
         $this->setConfiguration('user_dataversion', $user_dataversion);

         // make sure the initial call from postSave does not trigger an infinite loop 
         $this->save(true);

         log::add(VERALINK, 'debug', 'received userdataversion:'. $user_dataversion);
         $result->json = $json;
         $result->arr = $user_data;
      }
      return $result;
   }

   public function getLuStatus($ipaddr)
   {
      log::add(VERALINK, 'debug', __METHOD__);
      $result = (object)['json'=>'', 'obj'=>null];

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
         //log::add(VERALINK, 'debug', 'No change with result:'.$json);
         // empty result is already prepared
      } else {
         $lu_data = json_decode($json,false);   // object
         $statusdataversion = $lu_data->DataVersion;
         $lastloadtime = $lu_data->LoadTime;
         $this->setConfiguration('statusdataversion', $statusdataversion);
         $this->setConfiguration('lastloadtime', $lastloadtime);
         $this->save(true);
         
         log::add(VERALINK, 'debug', sprintf('NEW statusdataversion:%s loadtime:%s userdataversion:%s',$statusdataversion,$lastloadtime,$lu_data->UserData_DataVersion));
         if ($userdatadataversion != $lu_data->UserData_DataVersion) {
            //log::add(VERALINK, 'info', 'refresh user_data:'.$lu_data->UserData_DataVersion);
            $result = $this->getUserData($ipaddr,$userdatadataversion);
         } else {
            // il faut ecraser $old.devices etc... with $lu_datas
            //$cmd = $this->getCmd(null, 'devices');
            //$olddevices = json_decode( ( $cmd->execCmd()) , false );      // object
            $cmd = $this->getConfiguration('veralink_devices',null);
            $olddevices = json_decode( $cmd ?? [], false );      // object
            foreach( $lu_data->devices as $dev ) {
               foreach($olddevices as $olddev ) {
                  if ($olddev->id == $dev->id) {
                     foreach($dev->states as $state) {
                        foreach($olddev->states as $oldstate) {
                           if (($oldstate->service == $state->service) && ($oldstate->variable == $state->variable) && ($oldstate->value != $state->value)){
                              if ($state->variable != 'LastPollSuccess') {
                                 log::add(VERALINK, 'debug', sprintf('dev:%s-%s %s %s=>%s (%s)',$dev->id,$olddev->name,$state->variable, $oldstate->value, $state->value, $state->service));
                                 $oldstate->value = $state->value;   
                                 break;                           
                              }
                           }
                        }
                     }
                  }
               }
            }

            $json = json_encode($olddevices);
            //$this->checkAndUpdateCmd('devices', ($json) );
            $this->setConfiguration('veralink_devices',$json);
            $this->save(true);
            $result->json = $json;
            $result->arr = $olddevices;
         }
      }
      return $result;
   }

   public function updateInfoCommands() 
   {
      log::add(VERALINK, 'debug', __METHOD__);
      // $cmd = $this->getCmd(null, 'devices');
      // $devices = json_decode( ( $cmd->execCmd()) , true );    // array
      $cmd = $this->getConfiguration('veralink_devices',null);
      $devices = json_decode( $cmd ?? [] , true );    // array

      foreach ($devices as $device) {         
         $device=(object)$device;
         $eqLogic = self::byLogicalId(PREFIX_VERADEVICE . $device->id, VERALINK);
         if ( is_object($eqLogic) ) {

            // only do this for enabled equipments
            if ($eqLogic->getIsEnable() == 1) {

               // iterate through possible commands for this device type
               $map=CmdByVeraType[$device->device_type];
               foreach( $map['commands'] as $command) {
                  $type = substr( $command['type'], 0, 4 );
                  if ($type!='info')
                     continue;

                  $cmdid = $command['logicalid'].'-'.$device->id;
                  $cmd = $eqLogic->getCmd(null, $cmdid);
                  if (is_object($cmd)) {
                     // search the device state for that command
                     foreach( $device->states as $state ) {
                        $state = (object)$state;
                        // matching variable
                        if (($state->service == $command['service']) && ($state->variable == $command['variable']) ) {

                           // specific code for Battery level to report it in Jeedom
                           if ($command['variable']=='BatteryLevel')
                              $eqLogic->batteryStatus($state->value);

                              // if no change, skip
                           if ($cmd->execCmd()==$state->value)
                              continue;

                           log::add(VERALINK, 'info', sprintf('device %s eq:%s cmd:%s => set value:%s',
                              $device->id,
                              PREFIX_VERADEVICE . $device->id,
                              $cmdid,
                              $state->value
                           ));
                           $eqLogic->checkAndUpdateCmd($cmd,$state->value);
                           break;
                        }
                     }
                  } else {
                     if (!isset($command['optional'])) 
                        log::add(VERALINK, 'warning', 'Cmd '.$cmdid.' is not found for device '.$device->id);
                  }
               }
            }
         } else {
            log::add(VERALINK, 'warning', 'Cannot find EQ logic '.PREFIX_VERADEVICE . $device->id);
         }
      }
   }

   // returns an object with 2 properties : (object)['json'=>'', 'obj'=>null];
   public function refreshData( $initial=null )
   {
      log::add(VERALINK, 'debug', __METHOD__ . ' Initial:'.json_encode($initial));
      $ipaddr = $this->getConfiguration('ipaddr', null);
      if (is_null($ipaddr)) {
         log::add(VERALINK, 'warning', 'null IP addr, no action taken');
         return null;
      }

      if ($initial==1) {
         $result = $this->getUserData($ipaddr,$initial);         
      }
      else {
         $result = $this->getLuStatus($ipaddr);         
      }

      // now udpate all Info commands
      // info commands have a logicalid like CMD_BLETAT.'-'.$veradevid
      $this->updateInfoCommands( );
      return $result;
   }

   public function getScenesOfRoom($idroom)
   {
      log::add(VERALINK, 'debug', __METHOD__.' idroom:'.$idroom);
      $searchfor = strval($idroom);
      $datacmd = $this->getConfiguration('veralink_scenes',null);
      $data = json_decode( $datacmd ?? '[]'  );

      // pass the searchfor into the scope of the anonymous function using the use keyword
      // only keep scenes from the same room and which are not pure notification scenes
      $scenes = array_filter( $data, function($elem) use ($searchfor) {
         return (strval($elem->room) == $searchfor) && (isset($elem->notification_only)==false);
      });

      return $scenes;
   }

   public function switchLight($id,int $mode=0)
   {
      log::add(VERALINK, 'debug', __METHOD__ . sprintf(' dev:%s mode:%s',$id,$mode));

      $ipaddr = $this->getConfiguration('ipaddr', null);
      if (is_null($ipaddr)) {
         log::add(VERALINK, 'warning', 'null IP addr, no action taken');
         return null;
      }
      if (($mode!=1) && ($mode!=0)) {
         throw new Exception(__('Parametre invalide pour l action', __FILE__));
      }
      $url = sprintf('http://%s/port_3480/data_request?id=action&output_format=json&DeviceNum=%s&serviceId=urn:upnp-org:serviceId:SwitchPower1&action=SetTarget&newTargetValue=%s',
         $ipaddr,
         $id,
         $mode
         );

      $xml = file_get_contents($url);
      log::add(VERALINK, 'debug', 'action returned ' . $xml);
      return $xml;
   }

   public function runScene($id)
   {
      log::add(VERALINK, 'debug', __METHOD__);
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
      log::add(VERALINK, 'debug', __METHOD__);
      $eqLogic = $this->getEqLogic(); //Récupération de l’eqLogic
      $root_eqLogic = $eqLogic->getRoot();
      list( $cmdid, $param ) = explode('-',$this->getLogicalId());

      switch ($cmdid) {
         case 'refresh':
            $root_eqLogic->refreshData(1);
            break;

         case 'reset':
            $root_eqLogic->setConfiguration('veralink_scenes', (json_encode('')));
            $root_eqLogic->setConfiguration('veralink_devices', (json_encode('')));
            $root_eqLogic->save();
            break;

         case CMD_SCENE:
            // this is a scene command
            log::add(VERALINK, 'info', 'execute SCENE ' . $param);
            $xml = $root_eqLogic->runScene($param);
            $root_eqLogic->refreshData();
            break;
         
         default:

            log::add(VERALINK, 'info', 'execute ' . $cmdid .' on device '. $param);

            $configtype = $eqLogic->getConfiguration('type',null);
            $array = CmdByVeraType[$configtype]['commands'];
            //log::add(VERALINK, 'debug', 'array of commands '.json_encode($array));
            foreach($array as $command) {
               if ($command['logicalid'] != $cmdid)
                  continue;
               $function = $command['function'];
               $xml = $root_eqLogic->$function($param,$command['value']);
               $root_eqLogic->refreshData();
            }
            break;
      }
   }
   /*     * **********************Getteur Setteur*************************** */
}

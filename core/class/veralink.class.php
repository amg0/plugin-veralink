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

const SCENECMD = 'Scene';

class veralink extends eqLogic {
    /*     * *************************Attributs****************************** */
   
  /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */
    
    /*     * ***********************Methode static*************************** */

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
    public function preInsert() {
      log::add('veralink','debug',__METHOD__);
    }

 // Fonction exécutée automatiquement après la création de l'équipement 
    public function postInsert() {
      log::add('veralink','debug',__METHOD__);
    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
    public function preUpdate() {
      log::add('veralink','debug',__METHOD__);
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement 
    public function postUpdate() {
      log::add('veralink','debug',__METHOD__);
    }

 // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
    public function preSave() {
      log::add('veralink','debug',__METHOD__);
      //$this->setDisplay("width","800px");                   // widget display width
    }

 // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
    public function postSave() {
      log::add('veralink','debug',__METHOD__);
      // VERA Data information
      // $info = $this->getCmd(null, 'scenes');
      // if (!is_object($info)) {
      //   $info = new veralinkCmd();
      //   $info->setName(__('Scenes', __FILE__));
      // }
      // $info->setLogicalId('scenes');
      // $info->setEqLogic_id($this->getId());
      // $info->setType('info');
      // $info->setSubType('string');
      // $info->setTemplate('dashboard','default');   //template pour le dashboard
      // $info->setIsVisible(0);
      // $info->save();   

      // Refresh Action
      // $refresh = $this->getCmd(null, 'refresh');
      // if (!is_object($refresh)) {
      //   $refresh = new veralinkCmd();
      //   $refresh->setName(__('Rafraichir', __FILE__));
      // }
      // $refresh->setEqLogic_id($this->getId());
      // $refresh->setLogicalId('refresh');
      // $refresh->setType('action');
      // $refresh->setSubType('other');
      // $refresh->setIsVisible(0);
      // $refresh->save();

      $configtype = $this->getConfiguration('type',null);
      if (isset($configtype)) {
         log::add('veralink','info','EQ configuration type is '.$configtype.' logical Id:'.$this->getLogicalId());
      } else {
         $objects = json_decode($this->getVeraObjects('rooms,scenes'));
         if (isset($objects)) {
            foreach ($objects->rooms as $room) {
               // if and only if the EQ for the room does not exist, create it
               $eqLogic = self::byLogicalId('R_'.$room->id, 'veralink');
               if ( ! is_object($eqLogic) ) {
                  log::add('veralink','info','create another EQ for room #'.$room->id);
                  $eqLogic = new veralink();
                  $eqLogic->setEqType_name('veralink');
                  $eqLogic->setConfiguration('type','room');
                  $eqLogic->setLogicalId('R_'.$room->id);
                  $eqLogic->setConfiguration('ipaddr', $this->getConfiguration('ipaddr'));
                  $eqLogic->setIsEnable(0);
                  $eqLogic->setIsVisible(0);
               }
               $eqLogic->setObject_id( $this->getObject_id() );
               $eqLogic->setName($room->name);
               $eqLogic->save();     
            }
         }
      }  

      // foreach ($objects->rooms as $room) {
      //    log::add('veralink','info','creating EQ for room '.$room->name);   
      //    foreach ($objects->scenes as $scene) {
      //       if ($scene->room == $room->id) {
      //          log::add('veralink','info','creating Cmd for scene '.$scene->id);  
      //       }
      //    }
      // }

      // foreach ($objects->scenes as $idx => $scene) {
      //    log::add('veralink','info','creating sceneCmd for scene '.$scene->id);
      //    $cmd = $this->getCmd(null, SCENECMD.$scene->id);
      //    if (!is_object($cmd)) {
      //       log::add('veralink','info','creating New Cmd for id '.$scene->id.' name '.$scene->name);
      //       $cmd = new veralinkCmd();
      //    }
      //    $cmd->setName($scene->name.' ('.$scene->id.')');
      //    $cmd->setLogicalId(SCENECMD.$scene->id);
      //    $cmd->setConfiguration('room',$scene->room);
      //    $cmd->setEqLogic_id($this->getId());
      //    $cmd->setType('action');
      //    $cmd->setSubType('other');
      //    $cmd->save();   
      // }
    }

 // Fonction exécutée automatiquement avant la suppression de l'équipement 
    public function preRemove() {
      log::add('veralink','debug',__METHOD__);
    }

 // Fonction exécutée automatiquement après la suppression de l'équipement 
    public function postRemove() {
      log::add('veralink','debug',__METHOD__);
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

    /*
     * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */
    public function getDevices() {
      $ipaddr = $this->getConfiguration('ipaddr',null);
      if (is_null($ipaddr)) {
         log::add('veralink','info','null IP addr');
         return null;
      }
      $url = 'http://'.$ipaddr.'/port_3480/data_request?id=status';
      log::add('veralink','info','getting data from '.$url);
      $json = file_get_contents($url);
      $obj = json_decode($json);
      $devices = $obj->devices[0];
      return json_encode($devices);
    }

    public function getVeraObjects($objects) {
      $ipaddr = $this->getConfiguration('ipaddr',null);
      if (is_null($ipaddr)) {
         log::add('veralink','info','null IP addr');
         return null;
      }
      $url = 'http://'.$ipaddr.'/port_3480/data_request?id=objectget&key='.$objects;
      log::add('veralink','info','getting scenes from '.$url);
      $json = file_get_contents($url);
      // $obj = json_decode($json);
      // $scenes = array_map(function ($elem) {
      //    return array("name"=>$elem->name.'('.$elem->id.')', "id"=>$elem->id ,"room"=>$elem->room);
      // }, $obj->scenes);
      //return json_encode($scenes);
      return $json;
    }

    public function runScene($id) {
      $ipaddr = $this->getConfiguration('ipaddr',null);
      if (is_null($ipaddr)) {
         log::add('veralink','info','null IP addr');
         return null;
      }
      $url = 'http://'.$ipaddr.'/port_3480/data_request?id=action&serviceId=urn:micasaverde-com:serviceId:HomeAutomationGateway1&action=RunScene&SceneNum='.$id;
      $xml = file_get_contents($url);
      log::add('veralink','debug','runscene returned '.$xml);
      return $xml;
    }

    /*     * **********************Getteur Setteur*************************** */
}

class veralinkCmd extends cmd {
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
      public function execute($_options = array()) {
         $eqlogic = $this->getEqLogic(); //Récupération de l’eqlogic
         switch ($this->getLogicalId()) {
            case 'refresh': //LogicalId de la commande rafraîchir que l’on a créé dans la méthode Postsave 
               $scenes_json = $eqlogic->getScenes() ; //Lance la fonction et stocke le résultat dans la variable $info
               $eqlogic->checkAndUpdateCmd('scenes', $scenes_json);
               break;
               
            default:
               if (substr($this->getLogicalId(), 0, strlen(SCENECMD))==SCENECMD) {
                  $id = substr($this->getLogicalId(),strlen(SCENECMD));
                  log::add('veralink','info','execute SCENE '. $id);   
                  $xml = $eqlogic->runScene($id);
               }
         }
     }

    /*     * **********************Getteur Setteur*************************** */
}
/* TOKNOW:  THIS does not work, the original idea would be that execute is an abstract method of an abstract class Cmd but it is not the case
<PluginID>Cmd is a important naming convention. cf https://community.jeedom.com/t/plusieurs-classes-de-commandes-cmd/76608/2

class veraSceneCmd extends cmd {
   private $verascenename;
   private $verasceneid;

   public function init($id,$name) {
      $this->verasceneid = $id;
      $this->verascenename = $name;
   }

   public function execute($_options = array()) {
      log::add('veralink','info','execute');
   }
} */



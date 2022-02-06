//#	 sourceURL=configuration.php
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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Fréquence de refresh en secondes}}
        <sup><i class="fas fa-question-circle tooltips" title="{{en secondes entre 5 et 100}}"></i></sup>
      </label>
      <div class="col-md-4">
        <input type="number" class="configKey form-control" data-l1key="refresh_freq"/>
      </div>
    </div>
  </fieldset>
</form>

<script type="text/javascript">
  $('input[data-l1key=refresh_freq]').off('change').on('change',function(e) {
    var value = $(this).value();
    if (!Number.isInteger(value)) {
      alert('Merci de rentrer un nombre pour ce parametre');
    }
  });

  //  in case we want to trigger some actions from JS after the save of the config
  function veralink_postSaveConfiguration() {};
  
</script>

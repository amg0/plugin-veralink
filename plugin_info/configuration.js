//#	 sourceURL=configuration.js
// "use strict";
$('input[data-l1key=refresh_freq]').off('change').on('change',function(e) {
    var val = $(this).value();
    if (Number.isInteger(val)=false) {
      alert('Merci de rentrer un nombre pour ce parametre');
    }
  });

//  in case we want to trigger some actions from JS after the save of the config
function veralink_postSaveConfiguration() {};

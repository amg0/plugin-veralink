//#	sourceURL=configuration.js
// "use strict";

$("input[data-l1key='refresh_freq']").on('change',function() {
    var val = $(this).value();
    if ( (val=='') || isNaN(val) ) {
      alert('{{Merci de rentrer un nombre pour ce parametre}}');
    }
  });

//  in case we want to trigger some actions from JS after the save of the config
function veralink_postSaveConfiguration() {};

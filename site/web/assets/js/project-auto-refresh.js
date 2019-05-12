const $ = require('jquery')

$(function () {
  let wasInProcessing = false;

  function checkForUpdate() {
    $.getJSON(window.location + '/crawl_status', function (status) {
      if (status.inProcessing) {
        wasInProcessing = true;
        setTimeout(checkForUpdate, 10000);
      } else if (wasInProcessing) {
        window.location.reload();
      }
    });
  }

  checkForUpdate();
});

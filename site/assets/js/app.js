// import dependencies for the base template
import $ from 'jquery';
import 'bootstrap';

import 'cookieconsent';
import "cookieconsent/build/cookieconsent.min.css";

// uncomment to support legacy code
global.$ = global.jQuery = $;

import '../scss/app.scss'

window.addEventListener("load", function(){
  window.cookieconsent.initialise({
    "palette": {
      "popup": {
        "background": "#173e43",
        "text": "#dddfd4"
      },
      "button": {
        "background": "transparent",
        "border": "#fae596",
        "text": "#fae596"
      }
    }
  })});

// To stop Notification dropdown from disappearing when
// anything inside it is clicked
$(function() {
  $('.notification-list').on('click', function(event) {
    event.stopPropagation();
  });

  $(window).on('click', function() {
    $('.notification-list').slideUp();
  });

});

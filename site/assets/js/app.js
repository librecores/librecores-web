// import dependencies for the base template
import $ from 'jquery';
import 'bootstrap-sass';

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

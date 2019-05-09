// bootstrap tooltips need to be explicitly initialized
import $ from 'jquery';
import livestamp from 'kuende-livestamp';
import 'bootstrap';

$(function () {
    $('[data-toggle="tooltip"]').tooltip({'selector': 'span'});
});

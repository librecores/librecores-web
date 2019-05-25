// bootstrap tooltips need to be explicitly initialized
import 'bootstrap'
import $ from 'jquery'

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
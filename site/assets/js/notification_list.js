import livestamp from "kuende-livestamp";
import moment from "moment";

$(document).ready(() => {
  // Mark a notification as seen on click
  $(".notification-markasseen").on('click', function (e) {
    e.preventDefault();

    const notificationId = $(this).attr("data-notification");

    $.ajax({
      url: "/user/notification/seen",
      data: {'notification': notificationId},
      dataType: 'json',
      method: 'post',
      success: function (response) {
        $("#notification-"+notificationId).remove();
        $(".notification-count").html(response);
      },
      error: function (xhr) {
        console.log('Could not process that request');
        console.log(xhr.responseText);
      }
    })
  });

  $(".notification-markallseen").on('click', function (e) {
    e.preventDefault();

    $.ajax({
      url: "/user/notification/markallseen",
      method: 'post',
      success: function(response) {
        $(".unseen-notifications").empty();
        $(".notification-count").html(response);
      },
      error: function(xhr) {
        console.log('Could not process that request');
        console.log(xhr.responseText);
      }
    })
  });

});

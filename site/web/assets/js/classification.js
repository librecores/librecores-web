var insertClassification = function (classificationDetails) {
  var count = 1;
  for (var i = 0; i < classificationDetails.length; i++) {
    if (classificationDetails[i]["parentId"] === null) {
      $('#category-' + count + '').append($("<option>").val(classificationDetails[i]["name"]).html(classificationDetails[i]["name"]));
    }
  }
  // Adding tooltip to the button
  $('[data-toggle="tooltip"]').tooltip();

  // Add a new category to the project
  updateCategory();

  // Update the selected category
  function updateCategory() {
    $('select').on('change', function () {
      var id = $(this).attr('id');
      var getCount = id.split('-');
      count = parseInt(getCount[1]);
      $(this).nextAll().remove();
      var value = $('#category-' + count + '').val();
      if (value !== "NULL") {
        var increase = false;
        var id = 0;
        for (var i = 0; i < classificationDetails.length; i++) {
          if (classificationDetails[i]["name"] === value) {
            id = classificationDetails[i]["id"];
            break;
          }
        }
        for (var i = 0; i < classificationDetails.length; i++) {
          if (classificationDetails[i]["parentId"] === id) {
            if (increase === false) {
              var category = $('#category-' + count + '').clone().appendTo('.classification-system');
              count++;
              category.attr('id', 'category-' + count + '').empty().append('<option value="NULL">select a category</option>');
              $('#category-' + count + '').append($("<option>").val(classificationDetails[i]["name"]).html(classificationDetails[i]["name"]));
              increase = true;
            }
            else {
              $('#category-' + count + '').append($("<option>").val(classificationDetails[i]["name"]).html(classificationDetails[i]["name"]));
            }
          }
        }
        // Include this current select element to the remove parameter
        updateCategory();
      }
    })
  }

  // Remove a classification from
  $('.close-category').on('click', function (event) {
    event.preventDefault();
    if (count != 1) {
      $('#category-' + count + '').remove();
      count--;
    }
  })


  // Send ajax request for inserting classification for a project
  $('.insert-classification').on('click', function (event) {
    event.preventDefault();
    var classification = '';
    for (var j = 1; j <= count; j++) {
      if ($('#category-' + j + '').val() !== 'NULL') {
        if (j == 1) {
          classification = $('#category-' + j + '').val();
        }
        else {
          classification = classification + '::' + $('#category-' + j + '').val();
        }
      }
      else {
        break;
      }
    }

    if (classification !== '') {
      if ($('.update-classification').children().length  === 0) {
        $('.update-classification').append('<p><span style="color: red">*</span> Click update project to add these classifications</p>')
      }
      $('.update-classification').append('<div class="categories update">'+
                        '<input type="hidden" name="classification[]" value="' + classification + '" />'+
                        '<span>' + classification + '</span>'+
                    '<a class="remove-classification" href="#">'+
                      '<i class="fa fa-close" aria-hidden="true"></i>'+
                    '</a>'+
                  '</div>')
      count = 1;
      $('#category-' + count + '').empty();
      $('#category-' + count + '').append('<option value="NULL" selected="selected">select a category</option>')
      for (var i = 0; i < classificationDetails.length; i++) {
        if (classificationDetails[i]["parentId"] === null) {
          $('#category-' + count + '').append($("<option>").val(classificationDetails[i]["name"]).html(classificationDetails[i]["name"]));
        }
      }
      $('#category-' + count + '').nextAll().remove();
      removeClassification()
    }
  })

  function removeClassification() {
    $('.remove-classification').on('click', function (event) {
      event.preventDefault();
      if ($('.remove-classification').parent().hasClass('delete')) {
        $('.classification-'+$(this).siblings('input').val()+'').show();
        $(this).parent().remove();
        if ($('.delete-classifications').children().length <= 1) {
          $('.delete-classifications').empty();
        }
      } else if ($('.remove-classification').parent().hasClass('update')) {
        $(this).parent().remove();
        if ($('.update-classification').children().length <= 1) {
          $('.update-classification').empty();
        }
      }

    })
  }

  $('.delete-classification').on('click', function (event) {
    event.preventDefault();
    var clasificationId = $(this).attr('href');
    var classificationName = $(this).siblings('span').html();
    $(this).parent().hide();
    if ($('.delete-classifications').children().length === 0) {
      $('.delete-classifications').append('<p><span style="color: red">*</span> Click update project to delete these classifications</p>')
    }
    $('.delete-classifications').append('<div class="categories delete">'+
                        '<input type="hidden" name="deleteClassification[]" value="' + clasificationId + '" />'+
                        '<span>' + classificationName + '</span>'+
                    '<a class="remove-classification" href="#">'+
                      '<i class="fa fa-close" aria-hidden="true"></i>'+
                    '</a>'+
                  '</div>')
    removeClassification()
  })
}

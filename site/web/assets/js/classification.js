
  var insertClassification = function(classificationDetails, parentName, projectName) {
    var count = 1;
    for(var i = 0; i < classificationDetails.length; i++) {
      if(classificationDetails[i][2] == null) {
        $('#category-'+count+'').append('<option value="'+classificationDetails[i][3]+'">'+classificationDetails[i][3]+'</option>');
      }
    }
    //adding tooltip to the button
    $('[data-toggle="tooltip"]').tooltip();

    // add a new category to the project
    removeChildCategory();
    // remove next all child category if a parent category change
    function removeChildCategory(){
      $('select').on('change',function(){
        var id = $(this).attr('id');
        var getCount = id.split('-');
        count = parseInt(getCount[1]);
        $(this).nextAll().remove();
      })
    }
    //add a new child category
    $('.add-category').on('click',function(event){
      event.preventDefault();
      var value = $('#category-'+count+'').val();
      if(value != "NULL") {
        var increase = false;
        var id = 0;
        for(var i = 0; i < classificationDetails.length; i++) {
          if(classificationDetails[i][3] == value) {
            id = classificationDetails[i][1];
            break;
          }
        }
        for(var i = 0; i < classificationDetails.length; i++) {
          if(classificationDetails[i][2] == id) {
            if(increase == false){
              var category = $('#category-'+count+'').clone().appendTo('.classification-system');
              count++;
              category.attr('id','category-'+count+'').empty().append('<option value="NULL">select a category</option>');
              $('#category-'+count+'').append('<option value="'+classificationDetails[i][3]+'">'+classificationDetails[i][3]+'</option>');
              increase = true;
            }
            else {
              $('#category-'+count+'').append('<option value="'+classificationDetails[i][3]+'">'+classificationDetails[i][3]+'</option>');
            }
          }
        }
        //include this current select element to the remove paramenter
        removeChildCategory();
      }
    })

    //remove a category to the project
    $('.close-category').on('click',function(event){
      event.preventDefault();
      if(count != 1) {
        $('#category-'+count+'').remove();
        count--;
      }
    })
    //send ajax request for inserting classification for a project
    $('.insert-classification').on('click',function(event){
      event.preventDefault();
      var classification = '';
      for(var j = 1; j <= count;j++) {
        if($('#category-'+j+'').val() != 'NULL') {
          if(j == 1) {
            classification = $('#category-'+j+'').val();
          }
          else {
            classification = classification + '::' + $('#category-'+j+'').val();
          }
        }
        else {
          break;
        }
      }

      if( classification != '') {
        $.ajax({
          url: "/project/insert/classifications",
          type: "GET",
          data: { "classification" : classification, "parentName" : parentName, "projectName" : projectName },
          async: true,
          success: function(data, status) {
            count = 1;
            $('#category-'+count+'').empty();
            $('#category-'+count+'').append('<option value="NULL" selected="selected">select a category</option>')
            for(var i = 0; i < classificationDetails.length; i++) {
              if(classificationDetails[i][2] == null) {
                $('#category-'+count+'').append('<option value="'+classificationDetails[i][3]+'">'+classificationDetails[i][3]+'</option>');
              }
            }
            $('#category-'+count+'').nextAll().remove();
            getClassification();

          },
          error : function(xhr, textStatus, errorThrown) {
          }
        })
      }

    })

    //displaying classification details for a project
    $('.classifications').hide();
    getClassification();
    function getClassification() {
      $.ajax({
        url: "/"+parentName+"/"+projectName+"/classification",
        type: "GET",
        async: true,
        success: function(data,status) {
          if(data.length >= 1) {
            $('.classifications').show();
            $('.classifications').empty();
            $('.classifications').append('<h4>Classifications</h4>');
            for(var i = 0; i < data.length; i++) {
              $('.classifications').append('<div class="categories">\
                        <span>'+data[i]['classification']+'</span>\
                    <a class="update-classification" href="#">\
                      <i class="fa fa-edit" aria-hidden="true"></i>\
                    </a>\
                    <a class="delete-classification" href="#">\
                      <i class="fa fa-trash" aria-hidden="true"></i>\
                    </a>\
                  </div>')
            }
          }
          else {
            $('.classifications').hide();
          }
        }
      })
    }

  }

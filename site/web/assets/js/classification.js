
  var insertClassification = function(classificationDetails) {
    var count = 1;
    for(var i = 0; i < classificationDetails.length; i++) {
      if(classificationDetails[i][2] == null) {
        $('#category-'+count+'').append('<option value="'+classificationDetails[i][3]+'" data-id='+classificationDetails[i][1]+'>'+classificationDetails[i][3]+'</option>');
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

    //send the classification of the project to controller
    $('form').on('submit',function() {
      var $self = $(this);
      var classification = '';
      for(var j = 1; j <= count ;j++) {
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
      if(classification == '') {
        var input = $("<input>")
          .attr("type", "hidden")
          .attr("name", "classification").val("NULL");
      }
      else	{
        var input = $("<input>")
          .attr("type", "hidden")
          .attr("name", "classification").val(classification);
      }
      $self.append($(input));
      $self.submit();
    })
  }

$('input[type="button"]').on("keyenter",function(eve){
     var key = eve.keyCode || e.which ;
     if (key == 13) {
          $(this).click();
      }
      return false;        
});
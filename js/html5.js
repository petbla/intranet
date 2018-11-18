/*
 * Styles for HTML5 a CSS3
 * ----------------------------------------------------------------------------   
 */

// Podpora strukturálních elementù HTML5 v pøípadì IE 6, 7 a 8
document.createElement("header");
document.createElement("footer");
document.createElement("section");
document.createElement("aside");
document.createElement("article");
document.createElement("nav");

$(function(){
  
  $("#login, p.btnlogout" ).hide().addClass("skryty" );
  
  $("p").click(function(udalost){ 
    
    cil = $(udalost.target);
    if(cil.is("a")){    
      udalost.preventDefault();      
      if ( $(cil.attr("href")).hasClass("skryty") ){   
        $(".viditelny").removeClass("viditelny")
          .addClass("skryty")
          .hide();
        $(cil.attr("href"))
           .removeClass("skryty")
           .addClass("viditelny")
           .show();
      };
    };
    
  });
  
});

/* 
 * Autofocus
 */  
function fosterAutofocus() {
  var element = document.createElement('input');
  return 'autofocus' in element;
}
$(function(){
   if(!fosterAutofocus()){
     $('input[autofocus=true]').focus();
   }
});
/* END Autofocus */

/* 
 * RADUIS
 * Solution for radius for old browser by jQuery Corner 
 */ 
function podporujeBorderRadius(){
  var element = document.documentElement;
  var styl = element.style;
  if (styl){
    return typeof styl.borderRadius == "string" ||
      typeof styl.MozBorderRadius == "string" ||
      typeof styl.WebkitBorderRadius == "string" ||
      typeof styl.KhtmlBorderRadius == "string" ;
  }
  return null;
}

(function($){  

  $.fn.formCorner = function(){   
    return this.each(function() {  
      var pole = $(this);
      var pole_pozadi = pole.css("background-color");
      var pole_ramecek = pole.css("border-color");
      pole.css("border", "none");
      var obal_sirka = parseInt(pole.css("width")) + 4;
      var obal = pole.wrap("<div></div>").parent();
      var ramecek = obal.wrap("<div></div>").parent();
      obal.css("background-color", pole_pozadi)
             .css("padding", "1px");
      ramecek.css("background-color",pole_ramecek)
            .css("width", obal_sirka + "px")
            .css('padding', '1px');
      obal.corner("round 5px");
      ramecek.corner("round 5px");
    });  
  };  
})(jQuery); 

$(function(){
  if(!podporujeBorderRadius()){
    $("input" ).formCorner();
    $("fieldset" ).corner("round 5px" );
    $("legend" ).corner("round top 5px cc:#fff" );
  }
});
/* END Radius */

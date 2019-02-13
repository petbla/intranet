<div id="breads">
{breads}
</div>

<div>
  <div class="w3-content" style="max-width:800px">
    <img class="dmsSlides" src="Samples/Img01.jpg" style="width:100%">
    <img class="dmsSlides" src="Samples/Img02.jpg" style="width:100%">
    <img class="dmsSlides" src="Samples/Img03.jpg" style="width:100%">
    <img class="dmsSlides" src="Samples/Img04.jpg" style="width:100%">
  </div>

  <div class="w3-center">
    <div class="w3-section">
      <button class="w3-button w3-light-grey" onclick="plusDivs(-1)">❮ Prev</button>
      <button class="w3-button w3-light-grey" onclick="plusDivs(1)">Next ❯</button>
    </div>
    <!-- START IndexList -->
    <button class="w3-button slideImage" onclick="currentDiv({index})">{index}</button> 
    <!-- END IndexList -->
  </div>
</div>

<script>
  var slideIndex = 1;
  showDivs(slideIndex);

  function plusDivs(n) {
    showDivs(slideIndex += n);
  }

  function currentDiv(n) {
    showDivs(slideIndex = n);
  }

  function showDivs(n) {
    var i;
    var x = document.getElementsByClassName("dmsSlides");
    var dots = document.getElementsByClassName("slideImage");
    if (n > x.length) {slideIndex = 1}    
    if (n < 1) {slideIndex = x.length}
    for (i = 0; i < x.length; i++) {
      x[i].style.display = "none";  
    }
    for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" w3-red", "");
    }
    x[slideIndex-1].style.display = "block";  
    dots[slideIndex-1].className += " w3-red";
  }
</script>

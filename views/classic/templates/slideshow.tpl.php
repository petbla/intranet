<div id="breads">
{breads}
</div>

<div>
  <div class="w3-center">
    <div class="w3-section">
      <button class="w3-button w3-light-grey" onclick="plusDivs(-1)">❮ Prev</button>
      <button class="w3-button w3-light-grey" onclick="plusDivs(1)">Next ❯</button>
    </div>
  </div>
  
  <div class="w3-content w3-display-container">
    <!-- START ImageList -->
    <div class="w3-display-container dmsSlides">
      <img src="{imagepath}" style="width:100%">
      <div class="w3-display-topleft w3-large w3-container w3-padding-16 w3-black">
        {Title}
      </div>
    </div>
    <!-- END ImageList -->
  </div>
</div>

<div class="w3-center">
  <!-- START IndexList -->
  <button class="w3-button slideImage" onclick="currentDiv({index})">{index}</button> 
  <!-- END IndexList -->
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

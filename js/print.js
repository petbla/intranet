/*
 * Script for IE-CSS3 - formating for print
 *
 */
$(function() {
  if (window.onbeforeprint !== undefined) {  
      window.onbeforeprint = showLinks;
      window.onafterprint = hideLinks;
  }
});
function showLinks() {  
  $("a").each(function() {
    $(this).data("textOdkazu", $(this).text());
    $(this).append(" (" + $(this).attr("href") + ")");                
  });
}
function hideLinks() {   
  $("a").each(function() {
    $(this).text($(this).data("textOdkazu"));
  });
}

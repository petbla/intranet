<div id="breads">{breads}</div>
{actionpanel}
<div id="pagecounter">{navigate_menu}</div>
<p id="pageMessage" class="message" onClick="this.style.display = 'none';" >{message}</p>
<script>
    var e;
    e = document.getElementById('pageMessage');
    if(e.innerHTML == '')
        e.style.display = 'none';
</script>
<p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
<script>
    var e;
    e = document.getElementById('pageErrorMesage');
    if(e.innerHTML == '')
        e.style.display = 'none';
</script>
{folders}
{addFiles}
<div id="pagecounter" class="bottom">{navigate_menu}</div>
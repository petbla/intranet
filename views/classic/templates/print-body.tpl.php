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
<p class="center">
    <a href="index.php?page=zob/electionperiod" class="action_button">Zpět</a>
</p>
<p>{content}</p>
<div id="breads">{breads}</div>
{actionpanel}
<div style="display: none;" id="formImportNoteCSV">
    <form action="index.php?page=document/importCsv/" method="POST" id="formFileToUploadNote" enctype="multipart/form-data">
        <label for="file">{lbl_ImportNotesCsv}</label>
        <input type="file" name="fileToUpload" id="fileToUploadNote" class="action" >  
        &nbsp;
        <input type="image" src="views/classic/images/nav/upload.png" name="submit" id="submitImport">
    </form>
</div>

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
{documents}
<div id="pagecounter" class="bottom">{navigate_menu}</div>

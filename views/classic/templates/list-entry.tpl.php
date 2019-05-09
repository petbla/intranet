{search}
<div id="breads">
{breads}
</div>
{actionpanel}
<div style="display: none;" id="formImportNoteCSV">
    <form action="index.php?page=document/importCsv/" method="POST" id="formFileToUploadNote" enctype="multipart/form-data">
        <label for="file">{lbl_ImportNotesCsv}</label>
        <input type="file" name="fileToUpload" id="fileToUploadNote" class="action" >  
        &nbsp;
        <input type="image" src="views/classic/images/nav/upload.png" name="submit" id="submitImport">
    </form>
</div>
<div id="pagecounter">
{navigate_menu}
</div>
{pageTitle}
{folders}
{documents}
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

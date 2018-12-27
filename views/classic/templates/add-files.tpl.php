<tr>
    <td>&nbsp;</td>
    <td>
        <div id="insertDocument">
            <form action="index.php?page=document/addFiles" method="POST" enctype="multipart/form-data">
                <input type="file" name="fileToUpload[]" id="fileToUpload" multiple class="action" >  
                <input type="image" src="views/classic/images/icon/addFiles.png" name="submit" id="submitAddFile">
                <input type="hidden" name="path" value="{parentfoldername}">
                <input type="hidden" name="ID" value="{parentID}">
            </form>
        </div>
    </td>
    <td>&nbsp;</td>
</tr>

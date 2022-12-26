<div id="actionpanel">
    <form action="index.php?page=document/addFolder" method="post">
        <table>
            <tr>
                <td class="alignleft">
                    <label for="fld_name">{lbl_Create}</label>                    
                    <input type="text" name="fld_name" id="fld_name" placeholder="{lbl_folderBlocName}" title="{lbl_createNewFolBlo}">
                </td>
                {addFolder}
                <td>
                    <input type="image" id="fld_newBlock" name="addBlock" src="views/classic/images/nav/addBlock.png" alt="{lbl_newBlock}">
                    <br>
                    <label for="fld_newBlock">{lbl_NewBlock}</label>
                </td><td>
                    <input type="image" id="fld_newNote" name="addNote" src="views/classic/images/nav/addNote.png" alt="{lbl_newNote} (ALT + N)" title="Alt + n" accesskey="n">
                    <br>
                    <label for="fld_newNote">{lbl_NewNote}</label>
                </td><td>
                    <img src="views/classic/images/nav/import.png" alt="{lbl_Import}" title="{lbl_Import}" onclick="importNoteCSV('{parentID}');">
                    <br>
                    {lbl_ImportNote}
                </td><td>
                    <a href="files/Poznamky.xlsx"><img src="views/classic/images/nav/export.png" alt="{lbl_Export}" title="{lbl_Export}"></a>                
                    <br>
                    {lbl_NoteTemplateImp}
                </td>
                <td>
                    <input type="checkbox" name="handled" id="fld_handled" onClick="setHideHandled();">
                    <br>
                    <label for="fld_handled">{lbl_hidehandled}</label>
                </td>
                <td>
                    {slideshow}      
                </td>
            </tr>
        </table>
        <input type="hidden" name="parentID" value="{parentID}">
        <input type="hidden" name="root" value="{parentfoldername}">
    </form>      
</div>


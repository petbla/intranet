<div id="actionpanel">
    <form action="index.php?page=document/addFolder" method="post">
        <table>
            <tr>
                <td class="alignleft">
                    {lbl_Create}                    
                    <div id="newDmsentryText" class="contentEditable" contentEditable="true"></div>
                </td>
                {addFolder}
                <td>
                    <img src="views/classic/images/nav/addBlock.png" parentID="{parentID}" alt="{lbl_newBlock}" onClick="wsDmsentry(this, 'Block');">
                    <br>
                    {lbl_NewBlock}
                </td><td>
                    <img src="views/classic/images/nav/addNote.png" parentID="{parentID}" alt="{lbl_newNote}" onClick="wsDmsentry(this, 'Note');">
                    <br>
                    {lbl_NewNote}
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


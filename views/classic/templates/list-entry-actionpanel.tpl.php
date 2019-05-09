<div id="actionpanel">
    <form action="index.php?page=document/addFolder" method="post">
        <ul>
            <li>
                <label for="fld_name">{lbl_Create}</label>
                <input type="text" name="fld_name" id="fld_name" placeholder="{lbl_folderBlocName}" title="{lbl_createNewFolBlo}">
                {addFolder}
                <label for="fld_newBlock">{lbl_NewBlock}</label>
                <input type="image" id="fld_newBlock" name="addBlock" src="views/classic/images/nav/addBlock.png" alt="{lbl_newBlock}">
                <label for="fld_newNote">{lbl_NewNote}</label>
                <input type="image" id="fld_newNote" name="addNote" src="views/classic/images/nav/addNote.png" alt="{lbl_newNote} (ALT + N)" title="Alt + n" accesskey="n">
                {lbl_ImportNote}
                <img src="views/classic/images/nav/import.png" alt="{lbl_Import}" title="{lbl_Import}" onclick="importNoteCSV('{parentID}');">
                &nbsp;
                {lbl_NoteTemplateImp}
                <a href="files/Poznamky.xlsx"><img src="views/classic/images/nav/export.png" alt="{lbl_Export}" title="{lbl_Export}"></a>                
                <input type="hidden" name="parentID" value="{parentID}">
                <input type="hidden" name="root" value="{parentfoldername}">
            </li>
        </ul>
    </form>      
    {slideshow}      
</div>


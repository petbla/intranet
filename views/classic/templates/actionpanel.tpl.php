<div id="actionpanel">
    <form action="index.php?page=admin/addfolder" method="post">
        <ul>
            <li>
                <label for="fld_name">{lbl_Create}</label>
                <input type="text" name="fld_name" id="fld_name" placeholder="{lbl_folderBlocName}" title="{lbl_createNewFolBlo}" required>
                <label for="fld_newFolder">{lbl_NewFolder}</label>
                <input type="image" id="fld_newFolder" src="views/classic/images/nav/addFolder.png" alt="{lbl_newFolder}">
                <label for="fld_newBlock">{lbl_NewBlock}</label>
                <input type="image" id="fld_newBlock" src="views/classic/images/nav/addBlock.png" alt="{lbl_newBlock}">
                <input type="hidden" name="root" value="{parentfoldername}">
            </li>
        </ul>    
    </form>    
</div>

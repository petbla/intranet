<div id="actionpanel">
    <form action="index.php?page=admin/addfolder" method="post">
        <ul>
            <li>
                <input type="text" name="fld_name" id="fld_name" placeholder="{lbl_folderName}" title="{lbl_createNewFolder}" required>
                <input type="image" src="views/classic/images/icon/addfolder.png" alt="{lbl_newFolder}">
                <input type="hidden" name="root" value="{parentfoldername}">
            </li>
        </ul>    
    </form>    
</div>

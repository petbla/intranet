<div id="actionpanel">
    <form action="index.php?page=admin/addfolder" method="post">
        <ul>
            <li>
                <input type="text" name="fld_name" id="fld_name" placeholder="Název složky" title="Vytvořit novou složku">
                <input type="image" src="views/classic/images/icon/addfolder.png" alt="Nová složka">
                <input type="hidden" name="{parentfoldername}">
            </li>
        </ul>    
    </form>    
</div>

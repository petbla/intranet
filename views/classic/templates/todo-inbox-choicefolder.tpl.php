<div id="ListItems">
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
    <h2>Výběr složky</h2>
    <div id="breads">
    {breads}
    </div>
    <form action="index.php?page=todo/inbox/folder" method="post">
        <table style="width:70%;">
            <tr>
                <td>
                    Vytvořit<div id="newDmsentryText" class="contentEditable" contentEditable="true"></div>
                </td>
                <td>
                    <img src="views/classic/images/nav/addFolder.png" parentID="{ParentID}" alt="{lbl_newFolder}" onClick="wsDmsentry(this, 'Folder');">
                </td>
            </tr>
            <!-- START listFolder -->
            <tr>
                <td>       
                    <a href="index.php?page=todo/inbox/folder/select/{InboxID}/{ID}">{Title}</a>
                </td>
                <td>
                    <a href="index.php?page=todo/inbox/folder/set/{InboxID}/{ID}" class="action_button">Vložit do složky</a>
                    <input type="submit" name="storno" class="action_button" value="{lbl_Storno}">
                </td>
            </tr>
            <!-- END listFolder -->
        </table>
        <input type="hidden" id="fieldInboxID" name="InboxID" value="{InboxID}">
        <input type="hidden" id="fieldParentID" name="ParentID" value="{ParentID}">
    </form>
</div>

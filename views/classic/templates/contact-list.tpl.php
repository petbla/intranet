<div id="actionpanel">
    <a href="index.php?page=zob/manage/backupContact" title="Záloha kontaktů (CSV Export)"><img src="views/classic/images/nav/backup48.png" /></a>        
    <img src="views/classic/images/nav/line48.png"/>
    <img src="views/classic/images/nav/addContact.png" alt="{lbl_New}" title="{lbl_NewContact}" onclick="document.getElementById('newcardID').style.display = 'block';">
</div>
<div style="display: none;" id="formImportContactCSV">
    <form action="index.php?page=contact/importCsv" method="POST" enctype="multipart/form-data">
        <label for="file">{lbl_ImmportContactsCsv}</label>
        <input type="file" name="fileToUpload" id="fileToUpload" class="action" >  
        &nbsp;
        <input type="image" src="views/classic/images/nav/upload.png" name="submit" id="submitImport">
    </form>
</div>
<div id="DocumentItems">
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
    <div id="newcardID" style="display:none;">
        <span class="action_close" onclick="document.getElementById('newcardID').style.display = 'none';">{lbl_Close}</span>
        <br><br>
        {newcardContact}                
    </div>
    <table>
        <tr>
            <th style="width:100px;">..........</th>      
            <th>{lbl_FirstLast_name}</th>
            <th></th>      
            <th></th>      
            <th>{lbl_Function}</th>
            <th>{lbl_Phone}</th>
            <th>{lbl_Email}</th>
            <th>{lbl_Comment}</th>
            <th>{lbl_Label}</th>
            <th></th>
        </tr>
        <tr>
            <td></td>
            <td colspan="8" >
                <form action="index.php?page=general/searchcontact">
                    <input type="text" name="searchContact" id="search" placeholder="{lbl_PlaceText}">
                </form>
            </td>
        </tr>
        <!-- START ContactList -->
        <tr>        
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{ID}" dmsClassName="{dmsClassName}"/>
                <a href="index.php?page={deleteLink}/{ID}">
                    <img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{ID}" onclick="return ConfirmDelete();"/>
                </a>
            </td>
            <td class="col_fullname">
                <a href="" onclick="document.getElementById('{viewContactCardID}').style.display = 'block'; this.href = 'javascript:void(0)';">
                    {FullName}
                </a>
            </td>
            <td class="col_company" recID="contact{ID}" pkID="{ID}" table="contact" name="Address">{Address}</td>
            <td class="col_company" recID="contact{ID}" pkID="{ID}" table="contact" name="Company">{Company}</td>
            <td class="col_function" recID="contact{ID}" pkID="{ID}" table="contact" name="Function">{Function}</td>
            <td class="col_phone" ><span class="phone" recID="contact{ID}" pkID="{ID}" table="contact" name="Phone">{Phone}</span></td>
            <td class="col_email"><span class="email" recID="contact{ID}" pkID="{ID}" table="contact" name="Email">{Email}</span></td>
            <td class="col_note" recID="contact{ID}" pkID="{ID}" table="contact" name="Note">{Note}</td>
            <td class="tags" recID="contact{ID}" pkID="{ID}" table="contact" name="ContactGroups">
                {ContactGroups}
            </td>
        </tr>
        <tr style="display:none;"></tr>        
        <tr>
            <td colspan = "9" >
            <div id="{viewContactCardID}" style="display:none;" onclick="this.style.display = 'none';">
                <span class="action_close" onclick="document.getElementById('{viewContactCardID}').style.display = 'none';">{lbl_Close}</span>
                <br>
                {viewcardContact}
            </div>
            <div id="{editContactCardID}" style="display:none;">
                <span class="action_close" onclick="document.getElementById('{editContactCardID}').style.display = 'none';">{lbl_Close}</span>
                <br>
                {editcardContact}
            </div>
            </td>
        </tr>
        <!-- END ContactList -->
    </table>
    <p id="sqlrequest" valus={sqlrequest} stype="display:none;"></p>
</div>
<div id="pagecounter" class="bottom">{navigate_menu}</div>


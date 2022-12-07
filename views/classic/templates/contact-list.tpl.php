<div id="actionpanel">
    <table>
        <tr>
            <td>
                <img src="views/classic/images/nav/addContact.png" alt="{lbl_New}" title="{lbl_NewContact}" onclick="document.getElementById('newcardID').style.display = 'block';">
                <br>
                {lbl_NewContact}
            </td><td>
                <img src="views/classic/images/nav/groups.png" alt="{lbl_Groups}" title="{lbl_Groups}">
                <br>
                {lbl_Groups}
            </td><td>
                <img src="views/classic/images/nav/import.png" alt="{lbl_Import}" title="{lbl_Import}" onclick="importContactCSV();">
                <br>
                {lbl_Import}
            </td><td>
                <a href="files/Kontakty.xlsx"><img src="views/classic/images/nav/export.png" alt="{lbl_Export}" title="{lbl_Export}"></a>
                <br>
                {lbl_ContTemplateImp}
            </td>
        </tr>
    </table>
</div>
<div style="display: none;" id="formImportContactCSV">
    <form action="index.php?page=contact/importCsv" method="POST" enctype="multipart/form-data">
        <label for="file">{lbl_ImmportContactsCsv}</label>
        <input type="file" name="fileToUpload" id="fileToUpload" class="action" >  
        &nbsp;
        <input type="image" src="views/classic/images/nav/upload.png" name="submit" id="submitImport">
    </form>
</div>

{pageTitle}
<div id="DocumentItems">
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
            <td class="col_company">{Address}</td>
            <td class="col_company">{Company}</td>
            <td class="col_function">{Function}</td>
            <td class="col_phone" ><span class="phone" >{Phone}</span></td>
            <td class="col_email"><span class="email">{Email}</span></td>
            <td class="col_note">{Note}</td>
            <td class="tags">
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
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>


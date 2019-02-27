<div id="searchForm">
    <form action="index.php?page=contact/search">
        <label for="search">{lbl_Search}</label>
        <input type="text" name="searchContact" id="search" placeholder="{lbl_PlaceText}">
        <input type="image" src="views/classic/images/icon/search.png" value="{lbl_Searching}" id="submit">
    </form>
</div>
<div id="actionpanel">
    &nbsp;
    {lbl_NewContact}
    <img src="views/classic/images/nav/addContact.png" alt="{lbl_New}" title="{lbl_NewContact}" onclick="addNewContact();">
    &nbsp;
    {lbl_Groups}
    <img src="views/classic/images/nav/groups.png" alt="{lbl_Groups}" title="{lbl_Groups}">
    &nbsp;
    {lbl_Import}
    <img src="views/classic/images/nav/import.png" alt="{lbl_Import}" title="{lbl_Import}" onclick="importContactCSV();">
    &nbsp;
    {lbl_ContTemplateImp}
    <a href="files/Kontakty.xlsx"><img src="views/classic/images/nav/export.png" alt="{lbl_Export}" title="{lbl_Export}"></a>
</div>
<div style="display: none;" id="formImportContactCSV">
    <form action="index.php?page=contact/importCsv" method="POST" enctype="multipart/form-data">
        <label for="file">{lbl_ImmportContactsCsv}</label>
        <input type="file" name="fileToUpload" id="fileToUpload" class="action" >  
        &nbsp;
        <input type="image" src="views/classic/images/nav/upload.png" name="submit" id="submitImport">
    </form>
</div>
<div id="pagecounter">
{navigate_menu}
</div>
{pageTitle}
<div id="ContactItems">
    <table>
        <tr>
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
        {editcard}                
        <!-- START ContactList -->
        <tr>        
            <td class="col_fullname">
                {FullName}
                {editcard}                
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
            <td class="col_action">
                <a href="index.php?page=contact/view/{ID}" onclick="wsLogContactView('{ID}');">
                    <img src="views/classic/images/icon/view.png" alt="{lbl_edit}" id="{ID}" />
                </a>
                {editIcon}
            </td>
        </tr>
        <!-- END ContactList -->
    </table>
    <p id="sqlrequest" valus={sqlrequest} stype="display:none;"></p>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>


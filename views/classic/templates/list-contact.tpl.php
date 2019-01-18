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
    <a href="index.php?page=contact/new" class="actions"><img src="views/classic/images/nav/addContact.png" alt="{lbl_New}" title="{lbl_NewContact}"></a>
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
            <th>{lbl_Function}</th>
            <th>{lbl_Phone}</th>
            <th>{lbl_Email}</th>
            <th>{lbl_Comment}</th>
            <th>{lbl_Label}</th>
        </tr>
        <!-- START ContactList -->
        <tr>        
            <td class="col_fullname">
                <a href="index.php?page=contact/view/{ID}">
                    <img src="views/classic/images/icon/view.png" alt="{lbl_edit}" id="{ID}" dmsClassName="contact" />
                </a>
                {editIcon}
                <a href="index.php?page=contact/edit/{ID}" a_id="{ID}">{FullName}</a>
                {editEntry}                
            </td>
            <td>
                <td class="col_company">{Company}</td>
            </td>
            <td class="col_function">{Function}</td>
            <td class="col_phone" ><span class="phone" >{Phone}</span></td>
            <td class="col_email"><span class="email">{Email}</span></td>
            <td class="col_note">{Note}</td>
            <td class="tags">{ContactGroups}</td>
        </tr>
        <!-- END ContactList -->
    </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>


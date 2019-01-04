<div id="searchForm">
    <form action="index.php?page=contact/search">
        <label for="search">{lbl_Search}</label>
        <input type="text" name="searchContact" id="search" placeholder="{lbl_PlaceText}">
        <input type="image" src="views/classic/images/icon/search.png" value="{lbl_Searching}" id="submit">
    </form>
</div>
<a href="index.php?page=contact/new" class="actions"><img src="views/classic/images/nav/addcontact.png" alt="{lbl_New}" title="{lbl_NewContact}"></a>
<div id="pagecounter">
{navigate_menu}
</div>
{pageLink}
<div id="ContactItems">
    <table>
        <tr>
            <th>{lbl_FirstLast_name}</th>
            <th>{lbl_Function}</th>      
            <th>{lbl_Phone}/{lbl_Email}</th>
            <th>{lbl_Label}</th>
            <th>{lbl_Comment}</th>
        </tr>
        <!-- START ContactList -->
        <tr onclick='window.location = "index.php?page=contact/view/{ID}";' >        
            <td class="fullname"><a href="index.php?page=contact/edit/{ID}">{FullName}</a></td>
            <td>{Function}<br>{Company}</td>
            <td> 
                <span class="phone" >{Phone}</span>
                <br>
                <span class="email">{Email}</span>
            </td>
            <td class="tags">
                {Groups}
            </td>
            <td style="width: 300px; font-size: 12px;">{Note}</td>
        </tr>
        <!-- END ContactList -->
    </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>


<div id="searchForm">
    <form action="index.php?page=contact/search">
        <label for="search">{lbl_Search}</label>
        <input type="text" name="searchContact" id="search" placeholder="{lbl_PlaceText}">
        <input type="image" src="views/classic/images/icon/search.png" value="{lbl_Searching}" id="submit">
    </form>
</div>
<h1>{lbl_Contacts}</h1>
<div id="pagecounter">
{navigate_menu}
</div>
{pageLink}
<div id="DocumentItems">
    <table>
        <tr>
            <th>{lbl_FirstLast_name}</th>
            <th>{lbl_Function}</th>      
            <th>{lbl_Phone}</th>
            <th></th>
        </tr>
        <!-- START ContactList -->
        <tr>        
            <td>{FullName}</td>
            <td>{Function}<br>{Company}</td>
            <td>{Phone}<br>{Email}</td>
            <td style="width: 300px; font-size: 12px;">{Note}</td>
        </tr>
        <!-- END ContactList -->
    </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

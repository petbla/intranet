<div id="searchForm">
    <form action="index.php?page=contact/search">
        <label for="search">{lbl_Search}</label>
        <input type="text" name="searchContact" id="search" placeholder="{lbl_PlaceText}">
        <input type="image" src="views/classic/images/icon/search.png" value="{lbl_Searching}" id="submit">
    </form>
</div>
<div id="contact">
    <address>
        <table>
            <tr>
                <td class="label">{lbl_First_name}</td>
                <td class="value">{FirstName}</td>
            </tr>
            <tr>
                <td class="label">{lbl_Last_name}</td>
                <td class="value">{LastName}</td>
            </tr>
            <tr>
                <td class="label">{lbl_Title}</td>
                <td class="value">{Title}</td>
            </tr>
            <tr>
                <td class="label">{lbl_Function}</td>    
                <td class="value">{Function}</td>
            </tr>
            <tr>
                <td class="label">{lbl_Company}</td>    
                <td class="value">{Company}</td>
            </tr>
            <tr>
                <td class="label">{lbl_Phone}</td>
                <td> 
                    <span class="phone">{Phone}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{lbl_Email}</td>
                <td class="value">
                    <a href="mailto:{Email}"  class="email">{Email}</a>
                </td>
            </tr>
            <tr>
                <td class="label">{lbl_Web}</td>
                <td class="value">
                    <a href="http://{Web}" class="web">{Web}</a>
                </td>
            </tr>
            <tr>
                <td class="label">{lbl_Address}</td>
                <td class="value">
                    <pre>{Address}</pre>
                </td>
            </tr>
            <tr>
                <td class="label">{lbl_Comment}</td>
                <td class="value">
                    <pre>{Note}</pre>    
                </td>
            </tr>
            <tr>
                <td class="label">{lbl_Label}</td>
                <td class="value">{ContactGroups}</td>
            </tr>
        </table>
    </address>
</div>



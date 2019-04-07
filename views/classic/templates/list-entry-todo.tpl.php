<div id="searchForm">
    <form action="index.php?page=document/search">
        <label for="search">{lbl_Search}</label>
        <input type="text" name="searchDocument" id="search" placeholder="{lbl_PlaceText}">
        <input type="image" src="views/classic/images/icon/search.png" value="{lbl_Searching}" id="submit">
    </form>
</div>
<div id="pagecounter">
{navigate_menu}
</div>
{pageTitle}
<div id="DocumentItems">
  <table>
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_State}</th>
      <th>{lbl_RemindRespPers}</th>
      <th>{lbl_RemindToDate}</th>
      <th>{lbl_Days}</th>
    </tr>
    <!-- START DocumentItems -->
    <tr>
      <td class="col_action term{term}">
        {editIcon}
        {remindIcon}        
      </td>
      <td class="term{term}">
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-extension="{FileExtension}" data-dms-server="{cfg_webroot}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID},'{cfg_siteurl}');" class="term{term}">
            {Title}
        </a>
        {editcard}
      </td>
      <td class="term{term}">{RemindState_{RemindState}}</td>
      <td class="term{term}">{RemindResponsiblePerson}</td>
      <td class="term{term}">{RemindLastDate}</td>
      <td class="term{term}">{termDays}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

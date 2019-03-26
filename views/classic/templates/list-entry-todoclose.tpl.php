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
    </tr>
    <!-- START DocumentItems -->
    <tr>
      <td class="col_action">
        {editIcon}
        {remindIcon}        
      </td>
      <td class="">
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-extension="{FileExtension}" data-dms-server="{cfg_webroot}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID}');" class="">
            {Title}
        </a>
        {editcard}
      </td>
      <td class="">{RemindState}</td>
      <td class="">{RemindResponsiblePerson}</td>
      <td class="">{RemindLastDate}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

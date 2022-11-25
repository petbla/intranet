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
      <td class="term{term} column">
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-extension="{FileExtension}" data-dms-server="{cfg_webroot}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID},'{cfg_siteurl}');" class="term{term}">
            {Title}
        </a>
        {editcard}
      </td>
      <td class="term{term} column">{RemindState_{RemindState}}</td>
      <td class="term{term} column">{RemindResponsiblePerson}</td>
      <td class="term{term}">{RemindLastDate}</td>
      <td class="term{term}">{termDays}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

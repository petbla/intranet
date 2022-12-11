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
    <!-- START listTodo -->
    <tr>
      <td class="col_action term{term}">
        <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{ID}" dmsClassName="{dmsClassName}" ADocumentNo="{ADocumentNo}" dmsClassType="{DocumentType}"/>
        <img src="views/classic/images/icon/remind{Remind}{RemindClose}.png" alt="Připomenout" title="{lbl_msg_SetRemind}" onclick="wsSetRemindEntry('{ID}','{cfg_siteurl}','{BaseUrl}');" />
      </td>
      <td class="term{term} column">
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-extension="{FileExtension}" data-dms-server="{cfg_webroot}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID},'{cfg_siteurl}');" class="term{term}">
            {Title}
        </a>
      </td>
      <td class="term{term} column">{RemindState}</td>
      <td class="term{term} column">{RemindResponsiblePerson}</td>
      <td class="term{term}">{RemindLastDate}</td>
      <td class="term{term}">{termDays}</td>
  	</tr>
    <tr></tr>
    <tr>
      <td></td>
      <td colspan="6">
        <div id="{editFileCardID}" style="display:none;">
          <span class="action_close" onclick="document.getElementById('{editFileCardID}').style.display = 'none';" >{lbl_Close}</span>
          {editcard}
        </div>
      </td>
    </tr>
    <!-- END listTodo -->
  </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

<div id="DocumentItems">
  <table class="ContentEntryType{Type}">
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_DocumentNo}</th>
      <th>{lbl_RemindState}</th>
      <th>{lbl_RemindToDate}</th>
      <th>{lbl_ModifyDate}</th>
      <th>&nbsp;</th>
    </tr>
    {addFiles}
    <!-- START DocumentItems -->
    <tr>
      <td class="col_action">
        {icon{Type}{FileExtension}}
        {editIcon}
        {remindIcon}        
      </td>
      <td>
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-extension="{FileExtension}" data-dms-server="{cfg_webroot}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID}','{cfg_siteurl}');">{Title}</a>
        {mediaplayer}
      </td>
      <td>{DocumentNo}</td>
      <td>{RemindState_{RemindState}}</td>
      <td>{RemindLastDate}</td>
      <td>{ModifyDateTime}</td>
      <td>.{FileExtension}</td>
  	</tr>
    <tr></tr>
    <tr>
      <td></td>
      <td colspan="6">
        <div id="{editFileCardID}" style="display:none;">
          <span class="action_close" onclick="document.getElementById('{editFileCardID}').style.display = 'none';" >{lbl_Close}</span>
          {editcardFile}
        </div>
      </td>
    </tr>
    <!-- END DocumentItems -->
  </table>
</div>

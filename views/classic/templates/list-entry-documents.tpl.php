<div id="DocumentItems">
  <table class="ContentEntryType{Type}">
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_ModifyDate}</th>
      <th>{lbl_RemindToDate}</th>
      <th></th>
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
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-extension="{FileExtension}" data-dms-server="{cfg_webserver}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID}');">{Title}</a>
        {editcard}
      </td>
      <td>{ModifyDateTime}</td>
      <td>{RemindLastDate}</td>
      <td>.{FileExtension}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>

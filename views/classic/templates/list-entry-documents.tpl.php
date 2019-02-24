<div id="DocumentItems">
  <table class="ContentEntryType{Type}">
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_ModifyDate}</th>
      <th></th>
      <th></th>
    </tr>
    {addFiles}
    <!-- START DocumentItems -->
    <tr>
      <td>
        {icon{Type}{FileExtension}}
        {remindIcon}        
      </td>
      <td>
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-server="{cfg_webserver}" data-dms-entrytype="{Type}" onclick="wsLogDocumentView('{ID}');">{Title}</a>
        {editcard}
      </td>
      <td>{ModifyDateTime}</td>
      <td>.{FileExtension}</td>
      <td class="col_action">
        {editIcon}
      </td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>

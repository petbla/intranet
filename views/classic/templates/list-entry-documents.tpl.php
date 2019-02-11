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
      </td>
      <td>
        <a href="" a_id="{ID}" a_type="entry" data-dms-url="{Url}" data-dms-name="{Name}" data-dms-entrytype="{Type}" onclick="sendEntryRequest('{ID}');">{Title}</a>
        {editcard}
      </td>
      <td>{ModifyDateTime}</td>
      <td>.{FileExtension}{Multimedia}</td>
      <td class="col_action">
        {editIcon}
      </td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>

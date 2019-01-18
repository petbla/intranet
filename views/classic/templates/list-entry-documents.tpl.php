<div id="DocumentItems">
  <table class="ContentEntryType{Type}">
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_ModifyDate}</th>
      <th></th>
    </tr>
    {addFiles}
    <!-- START DocumentItems -->
    <tr>
      <td>
        <a href="FileServer/{Name}" target="_blank" entrytype="{Type}">
          {icon{Type}{FileExtension}}
        </a>
        {editIcon}
      </td>
      <td>
        <a href="index.php?page=document/view/{ID}" a_id="{ID}">{Title}</a>
        {editEntry}
      </td>
      <td>{ModifyDateTime}</td>
      <td>.{FileExtension}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
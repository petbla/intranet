<div id="DocumentItems">
  <table>
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_ModifyDate}</th>
      <th></th>
      <th>{lbl_ACTIONS}</th>
    </tr>
    {addFiles}
    <!-- START DocumentItems -->
    <tr>
      <td><a href="FileServer/{Name}" target="_blank">{icon{FileExtension}}</a></td>
      <td>
        <a href="index.php?page=document/view/{ID}" a_id="{ID}">{Title}</a>
        <form action="index.php?page=document/modify/{ID}" method="post" form_id="{ID}" style="display: none;">
          <input type="text" class="editInLine" name="newTitle" input_id="{ID}">
          <input type="submit" value="Zapsat">
        </form>
      </td>
      <td>{ModifyDateTime}</td>
      <td>.{FileExtension}</td>
      <td>
        <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{ID}" className="item" />
      </td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
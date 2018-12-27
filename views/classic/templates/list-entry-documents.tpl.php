<div id="DocumentItems">
  <table>
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_ModifyDate}</th>
    </tr>
    {addFiles}
    <!-- START DocumentItems -->
    <tr>
      <td><a href="FileServer/{Name}">{icon{FileExtension}}</a></td>
      <td><a href="index.php?page=document/view/{ID}">{title}</a></td>
      <td>{ModifyDateTime}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
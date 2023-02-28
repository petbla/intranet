<div id="DocumentItems">
  <table>
    <tr>
      <th></th>
      <th>Popis</th>
      <th>Odkaz</th>
      <th>Typ</th>
    </tr>
    <!-- START ResultItems -->
    <tr>
      <td>
        {icon{Type}{FileExtension}}
        <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{ID}" dmsClassName="{dmsClassName}" ADocumentNo="{ADocumentNo}" dmsClassType="{Type}"/>       
        <a href="index.php?page={deleteLink}/{ID}">
          <img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{ID}" onclick="return ConfirmDelete();"/>
        </a>
        {remindIcon}        
      </td>
      <td class="action_close" onclick="document.getElementById('{view{Type}CardID}').style.display = 'block'">
          {Description}
      </td>
      <td>
        <a href="{Url}" target="{Target}" SET_HREF id="{ID}" table="{Table}" name="{Name}" type="{Type}">
          <small>{Name}</small>
        </a>
      </td>
      <td>{Type}</td>
  	</tr>
  	<tr>
  	</tr>
  	<tr>
      <td colspan = "3">
        <div id="{viewContactCardID}" style="display:none;" onclick="this.style.display = 'none';" >          
          <span class="action_close" onclick="document.getElementById('{viewContactCardID}').style.display = 'none';">{lbl_Close}</span>
          {viewcardContact} 
        </div>
        <div id="{viewFileCardID}" style="display:none;" onclick="this.style.display = 'none';" >          
          <span class="action_close" onclick="document.getElementById('{viewFileCardID}').style.display = 'none';">{lbl_Close}</span>
          {viewcardFile}
        </div>
        <div id="{editContactCardID}" style="display:none;">
          <span class="action_close" onclick="document.getElementById('{editContactCardID}').style.display = 'none';">{lbl_Close}</span>
          {editcardContact}
        </div>
        <div id="{editFileCardID}" style="display:none;">
          <span class="action_close" onclick="document.getElementById('{editFileCardID}').style.display = 'none';">{lbl_Close}</span>
          {editcardFile}
        </div>
        <div id="{editFolderCardID}" style="display:none;">
          <span class="action_close" onclick="document.getElementById('{editFolderCardID}').style.display = 'none';">{lbl_Close}</span>
          {editcardFolder}
        </div>
      </td>
  	</tr>
    <!-- END ResultItems -->
  </table>
</div>
<div id="pagecounter" class="bottom">{navigate_menu}</div>


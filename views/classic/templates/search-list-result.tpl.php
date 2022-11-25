<div id="DocumentItems">
  <table>
    <tr>
      <th></th>
      <th>Popis</th>
      <th>Typ</th>
    </tr>
    <!-- START ResultItems -->
    <tr>
      <td>
        <img src="views/classic/images/icon/modify.png" onclick="document.getElementById('{editcardID}').style.display = 'block';" />
        <a href="index.php?page={deleteLink}/{ID}">
          <img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{ID}" onclick="return ConfirmDelete();"/>
        </a>
      </td>
      <td>
        <a href="{Url}" target="{Target}" onclick="document.getElementById('{viewcardID}').style.display = 'block'; this.href = 'javascript:void(0)';" >
          {Description}
        </a>
      </td>
      <td>{Type}</td>
  	</tr>
  	<tr>
  	</tr>
  	<tr>
      <td colspan = "3">
        <div id="{viewcardID}" style="display:none;" onclick="this.style.display = 'none';" >          
          <span class="action" onclick="document.getElementById('{viewcardID}').style.display = 'none';" style="margin:20px 10px;">{lbl_Close}</span>
          {viewcard}
        </div>
        <div id="{editcardID}" style="display:none;">
          <span class="action" onclick="document.getElementById('{editcardID}').style.display = 'none';" style="margin:20px 10px;">{lbl_Close}</span>
          {editcard}
        </div>
      </td>
  	</tr>
    <!-- END ResultItems -->
  </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>


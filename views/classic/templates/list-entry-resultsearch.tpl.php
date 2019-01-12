<div id="searchForm">
    <form action="index.php?page=document/search">
        <label for="search">{lbl_Search}</label>
        <input type="text" name="searchDocument" id="search" placeholder="{lbl_PlaceText}">
        <input type="image" src="views/classic/images/icon/search.png" value="{lbl_Searching}" id="submit">
    </form>
</div>
<div id="pagecounter">
{navigate_menu}
</div>
{pageLink}
<div id="DocumentItems">
  <table>
    <tr>
      <th>&nbsp;</th>
      <th>{lbl_Name}</th>
      <th>{lbl_ModifyDate}</th>
    </tr>
	  <!-- START DocumentItems -->
    <tr>
      <td><a href="FileServer/{Name}" target="_blank">{icon{FileExtension}}</a></td>
      <td>
        <a href="index.php?page=document/view/{ID}">{Title}</a>
        <br>
        <span style="font-size:12px;color:black;">{Name}</span>
      </td>
      <td>{ModifyDateTime}</td>
  	</tr>
    <!-- END DocumentItems -->
  </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

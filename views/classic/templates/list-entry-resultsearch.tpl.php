<div id="searchForm">
    <form action="index.php?page=document/search">
        <label for="search">Vyhledat</label>
        <input type="text" name="searchDocument" id="search" placeholder="Zadejte text...">
        <input type="image" src="views/classic/images/icon/search.png" value="Hledat" id="submit">
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
      <th>Název</th>
      <th>Datum změny</th>
    </tr>
	  <!-- START DocumentItems -->
    <tr>
      <td>{icon{FileExtension}}</td>
      <td>
        <a href="index.php?page=document/view/{ID}">{title}</a>
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

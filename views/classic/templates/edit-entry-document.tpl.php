<div id="breads">
{breads}
</div>
<div id="document">
    <table>
        <tr>
            <td>{lbl_Document}</td>
            <td><span id="FileTitle">{Title}</span>.<span id="FileExtension">{FileExtension}</span></td>
        </tr>
        <tr>
            <td>{lbl_Created}</td>
            <td>{CreateDate}</td>
        </tr>
        <tr>
            <td>{lbl_Modified}</td>
            <td>{ModifyDateTime}</td>
        </tr>
    </table>
    <br />
    <p>
        <a id="cosumentLink" href="{Name}" target="_blank">{lbl_View}</a>
    </p>
    
    <form action="index.php?page=document/savecontent/{ID}" method="post">
        <fieldset>
            <label for="content"></label>
            <textarea name="content" id="content" cols="120" rows="30">{Content}</textarea>
            <input type="submit" value="submit">
        </fieldset>
    </form>
</div>

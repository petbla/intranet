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
    <div id="actionpanel">
        <img src="views/classic/images/icon/modify.png" dmsEntryType="{Type}" alt="{lbl_edit}" id="{ID}" dmsClassName="item" onclick="wsLogContactView('{ID}');" />
        <a href="index.php?page=document/editcontent/{ID}"><img src="views/classic/images/icon/edit.png" alt="OBSAH" title="{lbl_msg_ModifyContentDoc}"></a>
        <br />
        {editcard}
    </div>
    <p>
        <a id="cosumentLink" href="{Name}" target="_blank">{lbl_View}</a>
    </p>
    <pre class="content">{Content}</pre>
</div>

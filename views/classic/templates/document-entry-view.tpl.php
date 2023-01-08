<div id="breads">
{breads}
</div>
<div id="document">
    <table>
        <tr>
            <td>{lbl_Document}</td>
            <td><span id="FileTitle">{Title}</span>.<span id="FileExtension">{FileExtension}</span>                
            </td>
        </tr>
        <tr>
            <td>{lbl_Created}</td>
            <td>{CreateDate}</td>
        </tr>
        <tr>
            <td>{lbl_Modified}</td>
            <td>{ModifyDateTime}</td>
        </tr>
        <tr>
            <td>{lbl_Title}</td>
            <td>{Title}</td>
        </tr><tr>
            <td>Url</td>
            <td>{Url}</td>
        </tr><tr>
            <td>{lbl_Remind}</td>
            <td><input type="checkbox" checked="{Remind}"></td>
        </tr><tr>
            <td>{lbl_RemindFromDate}</td>
            <td>{RemindFromDate}</td>
        </tr><tr>
            <td>{lbl_RemindToDate}</td>
            <td>{RemindLastDate}</td>
        </tr><tr>
            <td>{lbl_RemindRespPers}</td>
            <td>{RemindResponsiblePerson}</td>
        </tr><tr>
            <td>{lbl_RemindState}</td>
            <td>{RemindState_{RemindState}}</td>
        </tr>
    </table>
    <div id="actionpanel">
        <img src="views/classic/images/icon/modify.png" dmsEntryType="{Type}" alt="{lbl_edit}" id="{ID}" dmsClassName="item" onclick="wsLogContactView('{ID}');" />
        <br />
        {editcard}
    </div>
    <p>
        <a id="cosumentLink" href="{Name}" target="_blank">{lbl_View}</a>
    </p>
    <pre class="content"><code>{Content}</code></pre>
</div>

<div id="editwindow"  form_id="{ID}" style="display: none;">
    <div id="editwindowHeader">{lbl_msg_EditContact}</div>
        <form action="index.php?page=document/modify/{ID}" method="post">
        <fieldset>
            <label for="newTitle">
            <span>{lbl_Title}</span>
            <input type="text" class="editInLine" name="newTitle" inputTitle_id="{ID}">
            </label>
            <label for="newUrl">
            <span>Url</span>
            <input type="text" class="editInLine" name="newUrl" inputUrl_id="{ID}">
            </label>
            <button type="submit" name="save"  value="Zapsat>">{lbl_Save}</button>
            <button back_id="{ID}">{lbl_Cancel}</button>
            <input type="hidden" name="EntryType" value="EntryType{Type}">
            <input type="hidden" name="Title" value="{Title}" oldTitle_id="{ID}">
            <input type="hidden" name="Url" value="{Url}" oldUrl_id="{ID}">
        </fieldset>
        </form>
    </div>
</div>
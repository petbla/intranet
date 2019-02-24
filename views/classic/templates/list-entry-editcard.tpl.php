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
            <label for="newRemind">
            <span>Připomenutí</span>
            <input type="checkbox" name="newRemind" inputRemind_id="{ID}">
            </label>
            <label for="newRemindFromDate">
            <span>Připomenout dne</span>
            <input type="date" name="newRemindFromDate" inputRemindFromDate_id="{ID}">
            </label>
            <label for="newRemindLastDate">
            <span>Termín splnění</span>
            <input type="date" name="newRemindLastDate" inputRemindLastDate_id="{ID}">
            </label>
            <button type="submit" name="save"  value="Zapsat>">{lbl_Save}</button>
            <button back_id="{ID}">{lbl_Cancel}</button>
            <input type="hidden" name="EntryType" value="EntryType{Type}">
            <input type="hidden" name="Title" value="{Title}" oldTitle_id="{ID}">
            <input type="hidden" name="Url" value="{Url}" oldUrl_id="{ID}">
            <input type="hidden" name="Remind" value="{Remind}" oldRemind_id="{ID}">
            <input type="hidden" name="RemindFromDate" value="{RemindFromDate}" oldRemindFromDate_id="{ID}">
            <input type="hidden" name="RemindLastDate" value="{RemindLastDate}" oldRemindLastDate_id="{ID}">
        </fieldset>
        </form>
    </div>
</div>
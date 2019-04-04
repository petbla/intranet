<div id="editwindow"  form_id="{ID}" style="display: none;">
    <div id="editwindowheader{ID}" class="editwindowheader">{lbl_msg_EditDocument}</div>
        <form action="index.php?page=document/modify/{ID}" method="post">
        <fieldset>
            <label for="newTitle">
            <span>{lbl_Title}</span>
            <input type="text" class="editInLine" name="newTitle" inputTitle_id="{ID}">
            </label>
            <label for="newFileExt">
            <span>{lbl_FileExtension}</span>
            <input type="text" class="editInLine" name="newFileExt" inputTitle_id="{ID}">
            </label>
            <label for="newUrl">
            <span>Url</span>
            <input type="text" class="editInLine" name="newUrl" inputUrl_id="{ID}">
            </label>
            <label for="newRemind">
            <span>{lbl_Remind}</span>
            <input type="checkbox" name="newRemind" inputRemind_id="{ID}">
            </label>
            <label for="newRemindClose">
            <span>Vyřízeno</span>
            <input type="checkbox" name="newRemindClose" inputRemindClose_id="{ID}" disabled>
            </label>
            <label for="newRemindFromDate">
            <span>{lbl_RemindFromDate}</span>
            <input type="date" name="newRemindFromDate" inputRemindFromDate_id="{ID}">
            <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('inputRemindFromDate_id','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindFromDate_id','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindFromDate_id','{ID}','addDay',30);">měsíce</u></b></div>
            </label>
            <label for="newRemindLastDate">
            <span>{lbl_RemindToDate}</span>
            <input type="date" name="newRemindLastDate" inputRemindLastDate_id="{ID}">
            <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('inputRemindLastDate_id','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindLastDate_id','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindLastDate_id','{ID}','addDay',30);">měsíce</u></b></div>
            </label>
            <label for="newRemindResponsiblePerson">
            <span>{lbl_RemindRespPers}</span>
            <input type="text" name="newRemindResponsiblePerson" inputRemindResponsiblePerson_id="{ID}">
            </label>
            <label for="newRemindState">
            <span>{lbl_RemindState}</span>
            <select name="newRemindState" inputRemindState_id="{ID}">
                <option value="00_new"></option>
                <option value="10_process">Rozpracováno</option>
                <option value="20_wait">Čeká na schválení</option>
                <option value="30_aprowed">Schváleno</option>
                <option value="40_storno">Zrušeno</option>
                <option value="50_finish">Dokončeno</option>
            </select>
            </label>

            <button type="submit" name="save"  value="Zapsat>">{lbl_Save}</button>
            <button type="submit" name="stornoRemind"  value="{lbl_CancelRemind}">{lbl_CancelRemind}</button>
            <button back_id="{ID}">{lbl_Cancel}</button>
            <input type="hidden" name="EntryType" value="EntryType{Type}">
            <input type="hidden" name="Title" value="{Title}" oldTitle_id="{ID}">
            <input type="hidden" name="Url" value="{Url}" oldUrl_id="{ID}">
            <input type="hidden" name="Remind" value="{Remind}" oldRemind_id="{ID}">
            <input type="hidden" name="RemindFromDate" value="{RemindFromDate}" oldRemindFromDate_id="{ID}">
            <input type="hidden" name="RemindLastDate" value="{RemindLastDate}" oldRemindLastDate_id="{ID}">
            <input type="hidden" name="RemindResponsiblePerson" value="{RemindResponsiblePerson}" oldRemindResponsiblePerson_id="{ID}">
            <input type="hidden" name="RemindClose" value="{RemindClose}" oldRemindClose_id="{ID}">
            <input type="hidden" name="RemindState" value="{RemindState}" oldRemindState_id="{ID}">
        </fieldset>
        </form>
    </div>
</div>
<form action="index.php?page=document/modify/{ID}" method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label for="newTitle">{lbl_Name}</label><br>
                <input type="text" class="editInLine col_name" name="newTitle" value="{Title}" inputTitle_id="{ID}"><br>                
                <label for="newFileExt">{lbl_FileExtension}</label><br>
                <input type="text" class="editInLine col_fileext" name="newFileExt" value="{FileExtension}" inputTitle_id="{ID}"><br>
                <label for="newUrl">Url</label><br>
                <input type="text" class="editInLine col_name" name="newUrl" value="{Url}" inputUrl_id="{ID}">
            </td>
            <td>
                <label for="newRemindResponsiblePerson">{lbl_RemindRespPers}</label><br>
                <input type="text" name="newRemindResponsiblePerson" value="{RemindResponsiblePerson}" inputRemindResponsiblePerson_id="{ID}"><br>
                <label for="newRemindState">{lbl_RemindState}</label><br>
                <select name="newRemindState" value="{RemindState}" inputRemindState_id="{ID}">
                    <option value="00_new"></option>
                    <option value="10_process">Rozpracováno</option>
                    <option value="20_wait">Čeká na schválení</option>
                    <option value="30_aprowed">Schváleno</option>
                    <option value="40_storno">Zrušeno</option>
                    <option value="50_finish">Dokončeno</option>
                </select><br><br>
                <label for="newRemindClose">Vyřízeno</label>
                <input type="checkbox" name="newRemindClose" value="{RemindClose}" inputRemindClose_id="{ID}">
            </td>
            <td>
                <label for="newRemindLastDate">{lbl_RemindToDate}</label><br>
                <input type="date" name="newRemindLastDate" value="{RemindLastDate}" inputRemindLastDate_id="{ID}"><br>
                <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('inputRemindLastDate_id','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindLastDate_id','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindLastDate_id','{ID}','addDay',30);">měsíce</u></b></div>
                <br>
                <label for="newRemind">{lbl_Remind}</label>
                <input type="checkbox" name="newRemind" value="{Remind}" inputRemind_id="{ID}"><br>
                <label for="newRemindFromDate">{lbl_RemindFromDate}</label>
                <input type="date" name="newRemindFromDate" value="{RemindFromDate}" inputRemindFromDate_id="{ID}">
                <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('inputRemindFromDate_id','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindFromDate_id','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('inputRemindFromDate_id','{ID}','addDay',30);">měsíce</u></b></div>                
            </td>
        </tr>
        <tr>
            <td>
                <label for="content"></label><br>
                <textarea name="Content" id="content" cols="120" rows="30">{Content}</textarea>
            </td>
            <td>
                <label>{lbl_DocumentNo}</label><br>
                <b>{ADocumentNo}</b>
                <div id="SelectedADocumentNo{ID}">{SelectedDocumentNo}</div>
            </td>
            <td>
                <label>{lbl_Created}: </label>
                <span>{CreateDate}</span><br>
                <label>{lbl_Modified}: </label>
                <span>{ModifyDateTime}</span>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <button type="submit" name="save"  value="Zapsat>">{lbl_Save}</button>
                <button type="submit" name="stornoRemind"  value="{lbl_CancelRemind}">{lbl_CancelRemind}</button>
                <button back_id="{ID}">{lbl_Cancel}</button>
            </td>
        </tr>
    </form>
</table>

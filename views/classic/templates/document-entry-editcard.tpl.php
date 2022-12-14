<form action="index.php?page=document/modify/{ID}" method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label class="title">{lbl_Name} / {lbl_FileExtension}</label><br>
                <span>
                    <input type="text" class="value col_filename" name="Title" value="{Title}" TitleID="{ID}">
                    <input type="text" class="value col_fileext"  name="FileExtension" value="{FileExtension}" FileExtensionID="{ID}"><br>
                    <input type="hidden" name="oldTitle" value="{Title}" oldTitleID="{ID}">
                    <input type="hidden" name="oldFileExtension" value="{FileExtension}" oldFileExtensionID="{ID}">
                </span>

                <label class="title">Url</label><br>
                <input type="text" class="value col_web" name="Url" value="{Url}" UrlID="{ID}">
                <input type="hidden" name="oldUrl" value="{Url}" oldUrlID="{ID}">
            </td>
            <td>
                <label class="title">{lbl_RemindRespPers}</label><br>
                <input type="text" class="value" name="RemindResponsiblePerson" value="{RemindResponsiblePerson}" RemindResponsiblePersonID="{ID}">
                <input type="hidden" name="oldRemindResponsiblePerson" value="{RemindResponsiblePerson}" oldRemindResponsiblePersonID="{ID}"><br>
                
                <label class="title">{lbl_RemindState}</label><br>
                <select class="title" name="RemindState">
                    <option value="00_new" optionRemindState_id="00_new{ID}"></option>
                    <option value="10_process" optionRemindState_id="10_process{ID}">Rozpracováno</option>
                    <option value="20_wait" optionRemindState_id="20_wait{ID}">Čeká na schválení</option>
                    <option value="30_aprowed" optionRemindState_id="30_aprowed{ID}">Schváleno</option>
                    <option value="40_storno" optionRemindState_id="40_storno{ID}">Zrušeno</option>
                    <option value="50_finish" optionRemindState_id="50_finish{ID}">Dokončeno</option>
                </select><br>
                <label class="title">{lbl_DocumentNo}</label>
                <span class="value col_documentno" name="ADocumentNo" ADocumentNoID="{ID}">{ADocumentNo}</span>

                <img src="views/classic/images/icon/unlink.png" alt="Zrušit vazbu" title="Odebrat číslo jednací" onclick="wsUnlinkAgenda('{AgendaID}','{cfg_siteurl}','{BaseUrl}');" />


                <input type="hidden" name="oldADocumentNo" value="{ADocumentNo}" oldADocumentNoID="{ID}">
                <div id="SelectedADocumentNo{ID}">{SelectedDocumentNo}</div>
                <script>
                    var inputValue;
                    inputValue = document.querySelector( '[optionRemindState_id="{RemindState}{ID}"]' );
                    if (inputValue){
                        inputValue.selected = 'selected';            
                    }
                </script>
            </td>
            <td>               
                <label class="title"">Vyřízeno</label>
                <input type="checkbox" class="value" name="RemindClose" value="{RemindClose}" RemindCloseID="{ID}">
                <script>
                    var inputValue;
                    inputValue = document.querySelector( '[RemindCloseID="{ID}"]' );
                    if (inputValue.getAttribute('value') == '1'){
                        inputValue.checked = true;
                    }else{
                        inputValue.checked = false;
                    }
                </script>                
            </td>
        </tr>
        <tr>
            <td>
                <label class="title">Popis</label><br>
                <textarea class="value col_note" name="Content" cols="120" rows="30" ContentID="{ID}">{Content}</textarea>
                <input type="hidden" name="oldContent" value="{Content}" oldContentID="{ID}"><br>
            </td>
            <td colspan="2">
                <label class="title">{lbl_RemindToDate}</label>
                <input type="date" class="value" name="RemindLastDate" value="{RemindLastDate}" RemindLastDateID="{ID}"><br>
                <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('RemindLastDateID','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindLastDateID','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindLastDateID','{ID}','addDay',30);">měsíce</u></b></div><br>
                <input type="hidden" name="oldRemindLastDate" value="{RemindLastDate}" oldRemindLastDateID="{ID}">
                
                <label class="title">{lbl_Remind}</label>
                <input type="checkbox" class="value" name="Remind" value="{Remind}" RemindID="{ID}"><br>
                <script>
                    var inputValue;
                    inputValue = document.querySelector( '[RemindID="{ID}"]' );
                    if (inputValue.getAttribute('value') == '1'){
                        inputValue.checked = true;
                    }else{
                        inputValue.checked = false;
                    }
                </script>

                <input type="hidden" name="oldRemind" value="{Remind}" oldRemindID="{ID}">

                <label class="title">{lbl_RemindFromDate}</label>
                <input type="date" class="value" name="RemindFromDate" value="{RemindFromDate}" RemindFromDateID="{ID}">
                <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('RemindFromDateID','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindFromDateID','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindFromDateID','{ID}','addDay',30);">měsíce</u></b></div><br>
                <input type="hidden" name="oldRemindFromDate" value="{RemindFromDate}" oldRemindFromDateID="{ID}">

                <input type="hidden" name="oldRemindClose" value="{RemindClose}" oldRemindCloseID="{ID}">
            </td>
        </tr>
        <tr>
            <td>
                <button type="submit" name="save" class="action_button" value="Zapsat>">{lbl_Save}</button>
                <button type="submit" name="stornoRemind" class="action_button" value="{lbl_CancelRemind}">{lbl_CancelRemind}</button>
                <input type="hidden" name="searchText" value={searchText} />
                <input type="hidden" name="searchType" value="general" />
                <input type="hidden" name="controller" value="{controller}" />
                <input type="hidden" name="controllerAction" value="{controllerAction}" />
            </td>
            <td colspan="2">
                <label class="title">{lbl_Modified}: </label>
                <span class="value col_date">{ModifyDateTime}</span>
            </td>
        </tr>
    </form>
</table>

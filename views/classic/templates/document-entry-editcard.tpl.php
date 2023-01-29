<form action="index.php?page=document/modify/{ID}" method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label class="title">{lbl_Name} / {lbl_FileExtension}</label><br>
                <span>
                    <input type="text" class="value col_filename" name="Title" value="{Title}" pkID="{ID}" table="dmsentry" onchange="wsUpdate(this);">
                    <input type="text" class="value col_fileext"  name="FileExtension" value="{FileExtension}" pkID="{ID}" table="dmsentry" onchange="wsUpdate(this);"><br>
                </span>

                <label class="title">Url</label><br>
                <input type="text" class="value col_web" name="Url" value="{Url}" pkID="{ID}" table="dmsentry" onchange="wsUpdate(this);">
            </td>
            <td>
                <label class="title">{lbl_RemindRespPers}</label><br>
                <input type="text" class="value" name="RemindResponsiblePerson" value="{RemindResponsiblePerson}" pkID="{ID}" table="dmsentry" onchange="wsUpdate(this);">
                
                <label class="title">{lbl_RemindState}</label><br>
                <select class="title" name="RemindState" pkID="{ID}" table="dmsentry" onchange="wsUpdate(this);">
                    <option value="00_new" optionRemindState_id="00_new{ID}"></option>
                    <option value="10_process" optionRemindState_id="10_process{ID}">Rozpracováno</option>
                    <option value="20_wait" optionRemindState_id="20_wait{ID}">Čeká na schválení</option>
                    <option value="30_aprowed" optionRemindState_id="30_aprowed{ID}">Schváleno</option>
                    <option value="40_storno" optionRemindState_id="40_storno{ID}">Zrušeno</option>
                    <option value="50_finish" optionRemindState_id="50_finish{ID}">Dokončeno</option>
                </select><br>
                <label class="title">{lbl_DocumentNo}</label>
                <span class="value col_documentno" name="ADocumentNo" pkID="{ID}" table="dmsentry" ADocumentNoID="{ID}" onchange="wsUpdate(this);">{ADocumentNo}</span>

                <img src="views/classic/images/icon/unlink.png" style="cursor: pointer;" alt="Zrušit vazbu" title="Odebrat číslo jednací" onclick="wsUnlinkAgenda('{AgendaID}');" />

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
                <input type="checkbox" class="value" name="RemindClose" value="{RemindClose}" pkID="{ID}" table="dmsentry" RemindCloseID="{ID}" onClick = "validateCheckbox( this );"/>
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
                <textarea class="value col_note" name="Content" cols="120" rows="30" pkID="{ID}" table="dmsentry" onchange="wsUpdate(this);">{Content}</textarea>
            </td>
            <td colspan="2">
                <label class="title">{lbl_RemindToDate}</label>
                <input type="date" class="value" name="RemindLastDate" value="{RemindLastDate}" pkID="{ID}" table="dmsentry" RemindLastDateID="{ID}" onchange="wsUpdate(this);"><br>
                <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('RemindLastDateID','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindLastDateID','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindLastDateID','{ID}','addDay',30);">měsíce</u></b></div><br>
                
                <label class="title">{lbl_Remind}</label>
                <input type="checkbox" class="value" name="Remind" value="{Remind}" pkID="{ID}" table="dmsentry" RemindID="{ID}" onClick = "validateCheckbox( this );"><br>
                <script>
                    var inputValue;
                    inputValue = document.querySelector( '[RemindID="{ID}"]' );
                    if (inputValue.getAttribute('value') == '1'){
                        inputValue.checked = true;
                    }else{
                        inputValue.checked = false;
                    }
                </script>

                <label class="title">{lbl_RemindFromDate}</label>
                <input type="date" class="value" name="RemindFromDate" value="{RemindFromDate}" pkID="{ID}" table="dmsentry" RemindFromDateID="{ID}" onchange="wsUpdate(this);">
                <div class="quickChoice">Rychlá volba termínu: do <b><u onclick="setValue('RemindFromDateID','{ID}','addDay',7);">7dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindFromDateID','{ID}','addDay',14);">14dní</u>&nbsp;-&nbsp;<u onclick="setValue('RemindFromDateID','{ID}','addDay',30);">měsíce</u></b></div><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">
                <label class="title">{lbl_Modified}: </label>
                <span class="value col_date">{ModifyDateTime}</span>
            </td>
        </tr>
    </form>
</table>

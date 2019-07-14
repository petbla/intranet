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
    <br>
    <form action="index.php?page=document/savecontent/{ID}" method="post">
        <fieldset>
            <input type="submit" value="{lbl_Save}"><br><br>
            <table>
                <tr>
                    <td>{lbl_Title}</td>
                    <td><input type="text" name="Title" value="{Title}"></td>
                </tr><tr>
                    <td>Url</td>
                    <td><input type="text" name="Url" value="{Url}"></td>
                </tr><tr>
                    <td>{lbl_Remind}</td>
                    <td><input type="checkbox" name="Remind" value="{Remind}" inputRemind_id="{ID}"></td>
                    <script>
                        var inputValue;
                        inputValue = document.querySelector( '[inputRemind_id="{ID}"]' );
                        if (inputValue.getAttribute('value') == '1'){
                            inputValue.setAttribute('checked','');
                            inputValue.value = 'on';            
                        }
                    </script>
                </tr><tr>
                    <td>{lbl_RemindFromDate}</td>
                    <td><input type="date" name="RemindFromDate" value="{RemindFromDate}"></td>
                </tr><tr>
                    <td>{lbl_RemindToDate}</td>
                    <td><input type="date" name="RemindLastDate" value="{RemindLastDate}"></td>
                </tr><tr>
                    <td>{lbl_RemindRespPers}</td>
                    <td><input type="text" name="RemindResponsiblePerson" value="{RemindResponsiblePerson}"></td>
                </tr><tr>
                    <td>{lbl_RemindState}</td>
                    <td><select name="RemindState" >
                        <option value="00_new">{lbl_RemindState00}</option>
                        <option value="10_process">{lbl_RemindState10}</option>
                        <option value="20_wait">{lbl_RemindState20}</option>
                        <option value="30_aprowed">{lbl_RemindState30}</option>
                        <option value="40_storno">{lbl_RemindState40}</option>
                        <option value="50_finish">{lbl_RemindState50}</option>
                    </select></td>
                    <script>
                        var inputValue;
                        inputValue = document.querySelector( '[value="{RemindState}"]' );
                        if (inputValue){
                            inputValue.selected = 'selected';            
                        }
                    </script>
                </tr><tr>
                    <td>{lbl_DocumentNo}</td>
                    <td>
                        {SelectedDocumentNo}
                    </td>
                </tr>
            </table>
            <br>
            <label for="content"></label>
            <textarea name="Content" id="content" cols="120" rows="30">{Content}</textarea>
            <hr>
            <input type="submit" value="{lbl_Save}">
        </fieldset>
    </form>
    <br />
    <p>
        <a id="cosumentLink" href="{Name}" target="_blank">{lbl_View}</a>
    </p>
</div>

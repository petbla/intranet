<div id="pagecounter">{navigate_menu}</div>
<div id="DocumentItems">
    <p id="pageMessage" class="message" onClick="this.style.display = 'none';" >{message}</p>
    <script>
        var e;
        e = document.getElementById('pageMessage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>
    <p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
    <script>
        var e;
        e = document.getElementById('pageErrorMesage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>    <table>
        <tr>
            <th style="width:100px;">...............</th>
            <th>{lbl_electionperiod}</th>
            <th colspan="2">{lbl_Actual}</th>
        </tr>
        <!-- START electionPeriodList -->
        <tr>      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" onClick = "modifyZobElectionPeriod('{ElectionPeriodID}','{PeriodName}','{Actual}','modify');"/>
                <a href="index.php?page=zob/electionperiod/delete/{ElectionPeriodID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{ElectionPeriodID}" onclick="return ConfirmDelete();"/></a>
                <img src="views/classic/images/icon/arrowdone.png" alt="Typ jednání" onClick="document.getElementById('meetingtypeCard{ElectionPeriodID}').style.display = 'block';"/>
            </td>
            <td class="col_text">
                {PeriodName}
                <div id ="meetingtypeCard{ElectionPeriodID}" style="display:none;">
                    {meetingtypeCard}
                </div >
            </td>
            <td colspan="2" class="col_action" >
                <a href="index.php?page=zob/electionperiod/active/{ElectionPeriodID}"><img src="views/classic/images/icon/remind0{Actual}.png" /></a>
            </td>
        </tr>
        <!-- END electionPeriodList -->
        <form action="index.php?page=zob/electionperiod/add"  method="post">
            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" onClick = "modifyZobElectionPeriod('','','add');"/>                    
                </td>
                <td>
                    <input type="text" id="fieldEpPeriodName" class="value col_fullname" name="PeriodName" value="" autofocus required/>
                </td>
                <td>
                    <input type="checkbox" id="fieldEpActual" class="value" name="Actual" value="0" onClick = "validateCheckbox( this );"/>
                </td>
                    <script>
                        var inputValue;
                        inputValue = document.getElementById( 'fieldEpActual' );
                        if (inputValue.getAttribute('value') == '1'){
                            inputValue.checked = true;
                        }else{
                            inputValue.checked = false;
                        }
                    </script>
                <td>
                    <input type="hidden" id="fieldEpElectionPeriodID" name="ElectionPeriodID" value="">
                    <input type="hidden" id="fieldEpAction" name="action" value="add">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                    <input type="hidden" id="activeElectionPeriod" name="activeElectionPeriod" value="{activeElectionPeriodID}">
                    <input type="hidden" id="activeMemberType" name="activeMemberType" value="{activeMemberTypeID}">
                </td>
            </tr>
        </form>
    </table>
</div>
<div id="pagecounter" class="bottom">{navigate_menu}</div>


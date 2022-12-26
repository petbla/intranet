<form action="index.php?page=zob/meeting/modify" method="post" disable>
    <fieldset {disabled} style="padding:0; border:0;">
        <table class="edit-card">
            <tr>
                <td colspan="3">
                    <big><b>{MeetingName} - {EntryNo}/{Year}</b></big>
                    &nbsp;({PeriodName})
                    <br>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_Actual}</label>
                            </td>
                            <td style="border:0;">
                                <input type="checkbox" id="Actual{MeetingID}" class="value" name="Actual" value="{Actual}" ActualID="{MeetingID}" disabled/>
                                <script>
                                    var e;
                                    e = document.getElementById('Actual{MeetingID}');
                                    if (e.value == 1)
                                        e.checked = true;
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_AtDate}</label>
                            </td>
                            <td style="border:0;">
                                <input type="date" class="value" name="AtDate" value="{AtDate}" AtDateID="{MeetingID}"/><br>
                                <input type="hidden" name="oldAtDate" value="{AtDate}" oldAtDateID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_MeetingPlace}</label>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value col_function" name="MeetingPlace" value="{MeetingPlace}" MeetingPlaceID="{MeetingID}"/><br>
                                <input type="hidden" name="oldMeetingPlace" value="{MeetingPlace}" oldMeetingPlaceID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_State}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="State" value="{State}" StateID="{MeetingID}"/><br>
                                <input type="hidden" name="oldState" value="{State}" oldStateID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_Close}</label>
                            </td>
                            <td style="border:0;">
                                <input type="checkbox" id = "Close{MeetingID}" class="value" name="Close" value="{Close}" CloseID="{MeetingID}" onClick = "validateCheckbox( this );"/>
                                <input type="hidden" name="oldClose" value="{Close}" oldCloseID="{MeetingID}"/>
                                <script>
                                    var e;
                                    e = document.getElementById('Close{MeetingID}');
                                    if (e.value == 1)
                                        e.checked = true;
                                </script>
                            </td>
                        </tr>
                    </table>                
                </td>
                <td>                
                    <table>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_PostedUpDate}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="date" class="value" name="PostedUpDate" value="{PostedUpDate}" PostedUpDateID="{MeetingID}"/><br>
                                <input type="hidden" name="oldPostedUpDate" value="{PostedUpDate}" oldPostedUpDateID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_PostedDownDate}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="date" class="value" name="PostedDownDate" value="{PostedDownDate}" PostedDownDateID="{MeetingID}"/><br>
                                <input type="hidden" name="oldPostedDownDate" value="{PostedDownDate}" oldPostedDownDateID="{MeetingID}"/>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>                               
                    <table>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_RecorderBy}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="RecorderBy" value="{RecorderBy}" RecorderByID="{MeetingID}"/><br>
                                <input type="hidden" name="oldRecorderBy" value="{RecorderBy}" oldRecorderByID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_RecorderAtDate}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="date" class="value" name="RecorderAtDate" value="{RecorderAtDate}" RecorderAtDateID="{MeetingID}"/><br>
                                <input type="hidden" name="oldRecorderAtDate" value="{RecorderAtDate}" oldRecorderAtDateID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_VerifierBy} 1</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="VerifierBy1" value="{VerifierBy1}" VerifierBy1ID="{MeetingID}"/><br>
                                <input type="hidden" name="oldVerifierBy1" value="{VerifierBy1}" oldVerifierBy1ID="{MeetingID}"/>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_VerifierBy} 2</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="VerifierBy2" value="{VerifierBy2}" VerifierBy2ID="{MeetingID}"/><br>
                                <input type="hidden" name="oldVerifierBy2" value="{VerifierBy2}" oldVerifierBy2ID="{MeetingID}"/>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3">                
                    <input type="submit" name="submitEditMeeting" class="action_button" value="{lbl_Save}">
                    <input type="hidden" name="MeetingID" value="{MeetingID}">
                    <input type="hidden" name="MeetingTypeID" value="{MeetingTypeID}">
                    <input type="hidden" id="submit{MeetingID}" name="searchText" value="{searchText}" close="{Close}">
                </td>
            </tr>    
        </table>
    </fieldset>
</form>

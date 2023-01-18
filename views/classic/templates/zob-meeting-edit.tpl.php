<form action="" method="post" disable>
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
                                <input type="checkbox" id="Actual{MeetingID}" class="value" name="Actual" value="{Actual}" disabled/>
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
                                <input type="date" class="value" name="AtDate" value="{AtDate}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/>
                                &nbsp;
                                <input type="time" class="value" name="AtTime" value="{AtTime}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_MeetingPlace}</label>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value col_function" name="MeetingPlace" value="{MeetingPlace}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_Present}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="Present" value="{Present}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_Close}</label>
                            </td>
                            <td style="border:0;">
                                <input type="checkbox" id = "Close{MeetingID}" class="value" name="Close" value="{Close}" pkID="{MeetingID}" table="meeting" onchange = "validateCheckbox( this );"/>
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
                                <input type="date" class="value" name="PostedUpDate" value="{PostedUpDate}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_PostedDownDate}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="date" class="value" name="PostedDownDate" value="{PostedDownDate}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
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
                                <input type="text" class="value" name="RecorderBy" value="{RecorderBy}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_RecorderAtDate}</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="date" class="value" name="RecorderAtDate" value="{RecorderAtDate}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_VerifierBy} 1</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="VerifierBy1" value="{VerifierBy1}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:0;">
                                <label class="title">{lbl_VerifierBy} 2</label><br>
                            </td>
                            <td style="border:0;">
                                <input type="text" class="value" name="VerifierBy2" value="{VerifierBy2}" pkID="{MeetingID}" table="meeting" onchange="wsUpdate(this);"/><br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </fieldset>
</form>

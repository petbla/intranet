<h1 id="header">{Header}</h1>
<a href="index.php?page={formhref}" id="closePage" accesskey="x" class="button"><span class="action_close">{lbl_Close}</span></a>

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
    </script>
    <form id="NewFile" action="index.php?page=agenda/document" method="post">
        <fieldset style="padding:0; border:0;">
            <table class="new-field">
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <label class="col_title">Typ Evidence</label>
                                </td>
                                <td>
                                    <select id="agendaType" class="value">{AgendaTypeOption}</select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title">Název souboru</label>
                                </td>
                                <td>
                                    <input type="Text" name="FileName" class="col_filename">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title">Vyřizuje / telefon</label>
                                </td>
                                <td>
                                    <input type="Text" name="Presenter" class="col_name" value="Petr Blažek / 603772658">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_date">Datum</label>
                                </td>
                                <td>
                                    <input type="date" name="AtDate" class="col_date" value="{Today}" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_date">Číslo jednací</label>
                                </td>
                                <td>
                                    <input type="text" name="DocumentNo" class="col_date" value="{DocumentNo}" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title">
                                        </br>Firma</br>
                                        Kontakt</br>
                                        Adresa
                                    </label>
                                </td>
                                <td>
                                    <input type="button" name="FindContact" value="Najít" onclick="alert('Výběr kontaktu');"/></br>
                                    <input type="text" name="Company" class="col_name bigger" value=""/></br>
                                    <input type="text" name="LastName" class="col_name bigger" value="" placeholder="Jméno"/>
                                    <input type="text" name="FirstName" class="col_name bigger" value=""  placeholder="Příjmení"/>
                                    <input type="text" name="Title" class="col_name bigger" value=""  placeholder="Titul"/></br>
                                    <textarea name="Address" rows="4" cols="20" value="" class="value"></textarea></br>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    E-mail</br>
                                    Telefon</br>
                                    Datovka
                                </td>
                                <td>
                                    <input type="text" name="Email" class="col_email" value=""/></br>
                                    <input type="text" name="Phone" class="col_email" value=""/></br>
                                    <input type="text" name="DataBox" class="value" value=""/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title big">Věc</label>
                                </td>
                                <td>
                                    <input type="Text" class="col_max bigger" name="Subject">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label class="col_title big">{lbl_Content}</label><br>                   
                        <textarea  id="meetinglineContent{MeetingLineID}" name="Content" rows="10" cols="140" value="" class="value" ></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table>    
                            <tr>
                                <td>
                                    <label class="col_title">Podpis</label>
                                </td>
                                <td>
                                    <textarea name="Address" rows="2" cols="20" value="" class="value" ></textarea>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                    <input type="submit" name="save" value="Vytvořit dokument" class="action_close">
                    <input type="submit" name="preview" value="Náhled" class="action">
                    <input type="hidden" name="AgendaTypeID" value="{AgendaTypeID}">    
                    </td>
                </tr>
            </table>
        </fieldset>
    </form>
</div>


<h1 id="header">{Header}</h1>
<a href="index.php?page={formhref}" id="closePage" accesskey="x" class="button"><span class="action_close">{lbl_Close}</span></a>

<div id="DocumentItems">
    <p id="pageMessage" class="message" onClick="this.style.display = 'none';" >{message}</p>
    <p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
    <script>
        var e;
        e = document.getElementById('pageMessage');
        if(e.innerHTML == ''){
            e.style.display = 'none';
        };
        e = document.getElementById('pageErrorMesage');
        if(e.innerHTML == ''){
            e.style.display = 'none';
        }
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
                                    <select style="margin-top: 5px;margin-bottom: 5px;" id="agendaType" class="value" onchange="getDocumentNo(this,this.options[this.selectedIndex].text);">{AgendaTypeOption}</select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title">Název souboru</label>
                                </td>
                                <td>
                                    Složka<br>
                                    <input type="text" name="ParentName" class="col_fullname" value="{ParentName}" id="ParentName">
                                    <img src="views/classic/images/icon/search.png" width="24px" table="dmsentry" name="findfolder" divId="Folder" value="Najít" id="FindFolder" onClick="selectRecord(this);"/>

                                    <div id="Folder"></div>
                                    Název<br>
                                    <input type="Text" id="FileName" name="FileName" class="col_filename" required>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title">Vyřizuje / telefon</label>
                                </td>
                                <td>
                                    <label for="PresenterName">Jméno</label>
                                    <input type="Text" name="PresenterName" class="col_name" value="Blažek Petr">
                                    <label for="PresenterPhone">Telefon</label>
                                    <input type="Text" name="PresenterPhone" class="col_name" value="603772658">
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
                                    <input type="text" id="DocumentNo" name="DocumentNo" class="col_date" value="{DocumentNo}" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title" for="FirstName">Kontakt</label>
                                    <br><br><br>
                                    <label class="col_title" For="Address">Adresa</label>
                                </td>
                                <td>
                                    <input type="text" id="Company" name="Company" class="col_name bigger" value=""/>
                                    <img src="views/classic/images/icon/search.png" width="24px" table="contact" name="findcontact" divId="Contact" value="Najít" id="FindContact" onClick="selectRecord(this);"/>
                                    <div id="Contact"></div>
                                    <input type="text" id="FirstName" name="FirstName" class="col_name bigger" value=""  placeholder="Jméno"/>
                                    <input type="text" id="LastName" name="LastName" class="col_name bigger" value="" placeholder="Příjmení"/>
                                    <input type="text" id="Title" name="Title" class="col_name bigger" value=""  placeholder="Titul"/></br>
                                    <textarea id="Address" name="Address" rows="4" cols="20" class="value"></textarea></br>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    E-mail</br>
                                    Telefon</br>
                                    Datovka
                                </td>
                                <td>
                                    <input type="email" id="Email" name="Email" class="col_email" value=""/></br>
                                    <input type="tel" id="Phone" name="Phone" class="col_email" value=""/></br>
                                    <input type="text" id="DataBox" name="DataBox" class="value" value=""/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="col_title big">Věc</label>
                                </td>
                                <td>
                                    <input type="Text" class="col_max bigger" id="Subject" name="Subject" required>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: white; border: 2px solid #E1D9F1 ">
                        <div id="toolbar-container">
                            <span class="ql-formats">
                                <select class="ql-font" title="Font"></select>
                                <select class="ql-size" title="Velikost"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                                <button class="ql-strike"></button>
                            </span>
                            <span class="ql-formats">
                                <select class="ql-color"></select>
                                <select class="ql-background"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-script" value="sub"></button>
                                <button class="ql-script" value="super"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-header" value="1" title="Nadpis 1"></button>
                                <button class="ql-header" value="2" title="Nadpis 2"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered" title="Číslovaný seznam"></button>
                                <button class="ql-list" value="bullet" title="Seznam"></button>
                                <button class="ql-indent" value="-1" title="Odsazení zprava"></button>
                                <button class="ql-indent" value="+1" title="Odsazení zleva"></button>
                                <select class="ql-align" title="Zarovnání textu"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link" title="Vložit odkaz"></button>
                                <button id="insert-image" class="ql-image" title="Vložit obrázek"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-clean" title="Výmaz formátování"></button>
                            </span>
                        </div>
                        <div id="editor"></div>    

                        <!-- Dialog pro změnu velikosti obrázku -->
                        <div id="image-resize-dialog" style="display:none; position:absolute; border:1px solid #ccc; background:#fff; padding:10px;">
                            <label for="image-width">Šířka (px): </label>
                            <input type="number" id="image-width" style="width:60px;">
                            <label for="image-height">Výška (px): </label>
                            <input type="number" id="image-height" style="width:60px;">
                            <button id="apply-image-size">Použít</button>
                        </div>
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
                                    <label for="SignatureName">Jméno</label>
                                    <input type="Text" name="SignatureName" class="col_name" value="ing. Jánoš Vlastimil">
                                    <label for="SignatureFunction">Funkce</label>
                                    <input type="Text" name="SignatureFunction" class="col_name" value="starosta">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                    <input type="submit" id="save" name="save" value="Vytvořit dokument" class="action_close">
                    <input type="submit" id="preview" name="preview" value="Náhled" class="action">
                    <input type="hidden" id="contactRecordID" name="ContactID" value="">    
                    <input type="hidden" id="dmsentryRecordID" name="ParentID" value="{ParentID}">    
                    <input type="hidden" name="AgendaTypeID" value="{AgendaTypeID}">    
                    <input type="hidden" name="Table" value="meetingline">    
                    <input type="hidden" id="content" name="Content" value="">    
                    <input type="hidden" id="FullName" name="FullName" value="">    
                    </td>
                </tr>
            </table>
        </fieldset>        
    </form>
</div>


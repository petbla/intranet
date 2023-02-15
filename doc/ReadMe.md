* -----------------------------------------------------------------------------------------------------------------------------------------------
*  Systém synchronní online aktulizace elementů a databáze
* ------------------------------------------------------------------------------------------------------------------------------------------------

Elementy polí z tabulek, které lze editovat <input...> nebo jsou zobrazeny v tabulkách a mají reagovat na online změny <span...> <td...> 
Povinné atributy:
 - recID="meetingline{ID}" .... id záznamu va tvaru <tableName><primaryKey>
 - pkID="{ID}"  ............... primární klíč záznamu
 - table="meetingline" ........ název tabulky
 - name="FullName" ............ název pole

 Příklad - OnlyRead Element: <td  recID="meetingline{ID}" pkID="{ID}" table="meetingline" name="FullName">{FullName}</td>
 Příklad - Editable Element: <input type="text" value="{FullName}" recID="meetingline{ID}" pkID="{ID}" table="meetingline" name="FullName" onchange="wsUpdate(this);" />
            volitelné atributy <id= "meetinglineFullName{ID}" class="col_code">
                                    id="[<table>]<fieldName><primaryKey>"
 Příklad - checkbox          
    <input type="checkbox" id = "Close{MeetingID}" class="value" name="Close" value="{Close}" pkID="{MeetingID}" table="meeting" onchange = "validateCheckbox( this );"/ >
    <script>
        var e;
        e = document.getElementById('Close{MeetingID}');
        if (e.value == 1)
            e.checked = true;
    </script>

Příklad <select>
    <select id="fielLineType{MeetingLineID}" class="value" name="LineType" value="{LineType}" pkID="{MeetingLineID}" table="meetingline" onchange="this.setAttribute('value',this.options[this.selectedIndex].text); wsUpdate(this);">
        <option id="Bod{MeetingLineID}">Bod</option>
        <option id="Podbod{MeetingLineID}">Podbod</option>
        <option id="Doplňující bod{MeetingLineID}">Doplňující bod</option>
    </select>
    <script>
        var e;
        e = document.getElementById("{LineType}{MeetingLineID}");
        if(e)
            e.setAttribute('selected',true);
    </script>


Na tránce by měl být element pro zobrazení chybových zpráv - hned za nevyšším elementem DIV
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


Online update zajišťuje JS skript .\js\dbConnect.js > wsUpdate(this)
//    element for whow ERROR :  id="pageErrorMesage"
//    ID pro set position    :  id="header" 
//    Http request           :  ?page=general/ws/upd/<table>/<pkID>/<name>

Zpracování UPDATE zánamu v DB provádí controler .\general\ws.php > funkce update()
Do této je třeba přidat vždy kód pro nové tabulky. Kód pak řeší logiku a vzájemné vazby mezi editovanými poli (flowfields).


Skript následně pak zajistí refresh aktualizovaného záznamu. Pole (elementy) jsou identifikovány dle atributu <recID>

* -----------------------------------------------------------------------------------------------------------------------------------------------
*  Systém sestavení odkazů
* ------------------------------------------------------------------------------------------------------------------------------------------------
Platné pouze pro elementy <a>
Povinné adributy:
- SET_HREF ................. konstanta pro funkce JS skriptu
- table="agenda" ........... tabulka
- entryid="{EntryID}" ...... ID (guid) záznamu v tabulce <dmsentry>
- type="PDF" ............... typ odkazu pro danou tabulku
- name="{Name}"> ........... název souboru s příponou

Volitelné atributy:
- id  (pro type="odkaz")

Odkaz je generován JS skriptem .\js\onAfterScripts.js a nastavuje atributy <href> a <innerHTML> a to za předpokladu <entryid !="">
Aktuálně lze použít tyto  atributy:
    =============================================================
    <table>         <type>
    =============================================================
    dmsentry        30,35,20,25
    agenda          DocumentNo, odkaz, SourceFolder, PDF

Příklad: <a href="" SET_HREF table="agenda" entryid="{EntryID}" type="PDF" name="{Name}"></a>
* ------------------------------------------------------------------------------------------------------------------------------------------------

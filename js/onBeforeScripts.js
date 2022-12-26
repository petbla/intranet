'use strict';

function addNewContact(e){
    var form, back, contactGroups, tags;

    form = document.querySelector( '[form_id="newcontact"]' );
    if(form == null)
        return;
    form.style.display = '';
    form.style.left = '200px';
    form.style.top = '100px';
    contactGroups = document.querySelector('#ContactGroupsnewcontact');
    contactGroups.value = '';
    tags = document.querySelector('[class="tagsnewcontact"]');

    tags.innerText = '';
    back = document.querySelector( '[back_id="newcontact"]' );
    back.onclick = function (ee) {
        var form;
        form = document.querySelector( '[form_id="newcontact"]' );
        form.style.display = 'none';
        ee.preventDefault();
    };
    activeForm = form;
    window.onkeyup = function (event) {
        if (event.keyCode == 27) {
            activeForm.style.display = "none";
        }
    }
    // Make the DIV element draggable:
    dragElement(form);    
}    

function setValue(attName,attValue,keyWord,value){
    var att, input, d;
    switch (keyWord) {
        case 'addDay':
            att = '[' + attName + '="' + attValue +'"]';
            input = document.querySelector( att );
            d = new Date();
            d.setDate(d.getDate() + value);
            input.value = d.toJSON().slice(0, 10);
            console.log(input);
            break;
    }
}
function ConfirmDelete(){
    var opt;
    opt = confirm("Skutečně chete odstranit položku?");
    if (opt === false){
        return false;
    }
}
function ConfirmAction(){
    var opt;
    opt = confirm("Chcete spustit akci?");
    if (opt === false){
        return false;
    }
}
function ConfirmUnlink(){
    var opt;
    opt = confirm("Skutečně chete odstranit propojení evidence na dokument (zrušení čísla jednacího)?");
    if (opt === false){
        return false;
    }
}

function wsLogContactView(ID,siteurl) {
    const Http = new XMLHttpRequest();
    var url;
    url= siteurl + 'index.php?page=contact/WS/logView/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange=(e)=>{
      //console.log(Http.responseText)      
    }
}

function wsLogDocumentView(ID,siteurl) {
    const Http = new XMLHttpRequest();
    var url;
    url = siteurl + 'index.php?page=document/WS/logView/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        //console.log(Http.responseText)      
    };
}

function wsSetRemindEntry(ID,siteurl,BaseUrl) {
    const Http = new XMLHttpRequest();
    var url, result;
    url = siteurl + 'index.php?page=todo/WS/setRemind/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        result = Http.responseText;
        if(result == 'OK'){
            console.log('url: ',window.location);
            window.location = siteurl + 'index.php?page=/' + BaseUrl;
        }       
    };
}

function wsUnlinkAgenda(AgendaID,siteurl,BaseUrl) {
    const Http = new XMLHttpRequest();
    var url, result;
    url = siteurl + 'index.php?page=agenda/WS/unlink/' + AgendaID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        result = Http.responseText;
        if(result == 'OK'){
            console.log('url: ',window.location);
            window.location = siteurl + 'index.php?page=/' + BaseUrl;
        }
    };
}

function importContactCSV() {
    var form;
    form = document.querySelector('#formImportContactCSV');
    if(form){
        form.style.display = "";
    }
}

function importNoteCSV( parentID ) {
    var form,e;
    form = document.querySelector('#formImportNoteCSV');
    if(form){
        form.style.display = "";
        e = document.querySelector('#formFileToUploadNote');
        if(e){
            e.action = e.action + parentID;
        }
        console.log(e.action);
        console.log(parentID);
    }
}

function isValidFileExtension(extension) {
    var ext = [
        'doc',
        'docx',
        'ppt',
        'pptx',
        'odt',
        'pdf',
        'xls',
        'xlsx',
        'txt',
        'html',
        'msg',
        'txt',
        'csv',
        'jpg',
        'bmp',
        'png',
        'mp3'
    ];
    return (ext.indexOf(extension) >= 0)
}

function openFolder( folderpath )
{
    var myshell = new ActiveXObject("WScript.shell");
    myshell.run(folderpath, 1, true); 
} 

function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function setHideHandled()
{
    var cname, cvalue;
    var d = new Date();
    d.setTime(d.getTime() + (1 * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();

    cname = "HideHandledNote";
    cvalue = getCookie(cname);

    if(cvalue === "checked"){
        document.cookie = "HideHandledNote=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }else{
        document.cookie = cname + "=checked; " + expires + "; path=/";    
    }
    window.location.reload();
}

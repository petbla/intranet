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
function ConfirnDelete(){
    var opt;
    opt = confirm("Skutečně chete odstranit položku?");
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
    url = siteurl + 'index.php?page=document/WS/setRemind/' + ID;
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
        'png'
    ];
    return (ext.indexOf(extension) >= 0)
}

function openFolder( folderpath )
{
    var myshell = new ActiveXObject("WScript.shell");
    myshell.run(folderpath, 1, true); 
} 
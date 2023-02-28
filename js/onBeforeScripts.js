'use strict';

function setValue(attName,attValue,keyWord,value){
    var att, input, d;
    switch (keyWord) {
        case 'addDay':
            att = '[' + attName + '="' + attValue +'"]';
            input = document.querySelector( att );
            d = new Date();
            d.setDate(d.getDate() + value);
            input.value = d.toJSON().slice(0, 10);
            wsUpdate(input);
            console.log(input);
            break;
    }
}
function ConfirmDelete(msg = ''){
    var opt;
    if(msg == '')
        msg = "Skutečně chete odstranit položku?";
    opt = confirm(msg);
    if (opt === false){
        return false;
    }
    return true;
}
function ConfirmAction(msg = ''){
    var opt;
    if(msg == '')
        msg = "Chcete spustit akci?";
    opt = confirm(msg);
    if (opt === false){
        return false;
    }
    return true;
}
function ConfirmUnlink(){
    var opt;
    opt = confirm("Skutečně chete odstranit propojení evidence na dokument (zrušení čísla jednacího)?");
    if (opt === false){
        return false;
    }
    return true;
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
        'zip',
        'mp3'
    ];
    return (ext.indexOf(extension) >= 0)
}

function getApplication (extension)
{
    /**
     * HELP
     * https://docs.microsoft.com/en-us/office/client-developer/office-uri-schemes#sectionSection9
     */
    var app = '';
    switch (extension) {
        case 'xls':
        case 'xlsx':
        case 'csv':
            app = "ms-excel:ofe|u|";
            break;
        case 'doc':
        case 'docx':
        case 'rtf':
            app = "ms-word:ofe|u|";
            break;
        case 'ppt':
        case 'pptx':
            app = "ms-powerpoint:ofv|u|";
            break;
    }
    return app;
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

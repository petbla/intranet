'use strict';

function addNewContact(e){
    var form, back, contactGroups, tags;

    form = document.querySelector( '[form_id="newcontact"]' );
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

function wsLogContactView(ID) {
    const Http = new XMLHttpRequest();
    var url;
    url= 'http://localhost/intranet/index.php?page=contact/WS/logView/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange=(e)=>{
      //console.log(Http.responseText)      
    }
}

function wsLogDocumentView(ID) {
    const Http = new XMLHttpRequest();
    var url;
    url = 'http://localhost/intranet/index.php?page=document/WS/logView/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        //console.log(Http.responseText)      
    };
}

function wsSetRemindEntry(ID, BaseUrl) {
    const Http = new XMLHttpRequest();
    var url, result;
    url = 'http://localhost/intranet/index.php?page=document/WS/setRemind/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        result = Http.responseText;
        if(result == 'OK'){
            console.log('url: ',window.location);
            window.location = 'http://localhost/intranet/index.php?page=/' + BaseUrl;
        }       
    };
}

function importContactCSV() {
    var form;
    form = document.querySelector('#formImportContactCSV');
    if(form){
        form.style.display = "";
        console.log('Import');
    }
}

'use strict';

var documentLink;
var fileTitle, fileExtension;
var linkTitle;
var pagecounter;
var search;
var formAdUser;
var password, password_confirm;
var loginForm;
var documents;
var lastEditElement;

documentLink = document.querySelector('#cosumentLink');
fileTitle = document.querySelector('#FileTitle');
fileExtension = document.querySelector('#FileExtension');
pagecounter = document.querySelector('#pagecounter');
search = document.querySelector('#search');
password = document.querySelector('#usr_psw1');
password_confirm = document.querySelector('#usr_psw2');
loginForm = document.querySelector('#loginForm');

function validatePassword () {
    if (password.value != password_confirm.value) {
        password_confirm.setCustomValidity('Heslo se neshoduje.');
    }    
    else
    {
        password_confirm.setCustomValidity('');
    }
}

if (pagecounter != null){
    if (pagecounter.innerText == ""){
        pagecounter.style.display = 'none';
    }
}

if ((fileExtension) && ('innerText' in fileExtension)) {
    switch (fileExtension.innerText) {
        case 'pdf':
            linkTitle = 'Náhled';
            break;
        case 'doc':
        case 'docx':
        case 'xls':
        case 'xlsx':
        case 'rtf':
            linkTitle = 'Stáhnout';
            break;
        case 'ppt':
        case 'pptx':
            linkTitle = 'Stáhnout';
            break;
        default:
            linkTitle = '';
            documentLink.style.display = 'none';
            break;
    }
    if (linkTitle) {
        documentLink.innerText = linkTitle;
    }   
}

if (password) {
    password.onchange = validatePassword;
}
if (password_confirm) {
    password_confirm.onkeyup = validatePassword;
}

documents = document.querySelectorAll('[className="item"]');
documents.forEach(function(item){
    item.onclick = function (e) {
        var att;
        var a,form,input;

        if (lastEditElement)
        {
            att = '[a_id="' + lastEditElement.target.id + '"]';
            a = document.querySelector(att)
            a.style.display = '';
            
            att = '[form_id="' + lastEditElement.target.id + '"]';
            form = document.querySelector(att)
            form.style.display = 'none';
        }

        att = '[a_id="' + e.target.id + '"]';
        a = document.querySelector(att)
        att = '[form_id="' + e.target.id + '"]';
        form = document.querySelector(att)
        att = '[input_id="' + e.target.id + '"]';
        input = document.querySelector(att)
        
        a.style.display = 'none';
        form.style.display = '';
        input.value = a.innerText;

        lastEditElement = e;
    }
})

console.log(documents);

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
var tags;
var grouplist;

documentLink = document.querySelector('#cosumentLink');
fileTitle = document.querySelector('#FileTitle');
fileExtension = document.querySelector('#FileExtension');
pagecounter = document.querySelector('#pagecounter');
search = document.querySelector('#search');
password = document.querySelector('#usr_psw1');
password_confirm = document.querySelector('#usr_psw2');
loginForm = document.querySelector('#loginForm');
tags = document.querySelectorAll('[class="tags"]');


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

function formatElementClass (classText) {
    var att,e,i;
    att = '[class="' + classText + '"]';
    e = document.querySelectorAll(att);
    for (i = 0; i < e.length; i++) {
        e[i].innerHTML = formatText(e[i].innerHTML,classText);
    }   
}

function formatText (text, type)
{
    var newtext = ''
    var arr,val;        
    if(type == '')
    {
        return text;
    }
    type = type.toLowerCase();
    if(text == '')
    {
        return '';
    }
    arr = text.split(',');              
    arr.forEach(val => {
        switch (type) {
            case 'phone':
                val = formatPhoneNumber(val);
                break;
            case 'email':
                val = formatEmailTo(val);
                break;
        }
        if(newtext)
        {
            newtext = newtext + ',';
        }
        newtext = newtext + val;
    });
    return(newtext);
}   

function formatPhoneNumber (phone)
{
    var newphone;
    if(phone.length == 9) {
        newphone = phone.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3'); 
    }
    if(phone.length == 14) {
        newphone = phone.replace(/(\d{5})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4'); 
    }
    else if(phone[0] == '+'){
        newphone = phone.replace(/(\+\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4'); 
    }            
    if(newphone !== '')
    {
        newphone = "<a href='tel:" + newphone + "'>" + newphone + "</a>";
    }    
    return(newphone);
}

function formatEmailTo (email)
{
    var newemail = '';
    if(email !== '')
    {
        newemail = "<a href='mailto:" + email + "'>" + email + "</a>";
    }    
    return(newemail);
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

formatElementClass('phone');
formatElementClass('email');

var arrGroup = null;

if(tags){
    for (let i = 0; i < tags.length; i++) {
        var e;
        e = tags[i];
        arrGroup = (e.innerText).split(',');
        if (e.innerText !== ''){
            e.innerHTML = tags2Html( arrGroup );
        }
    }
}

function tags2Html( arr ){
    var newval='';
    arr.forEach(str => {
        str = "<span class='tags-item'>" + str + "</span>";
        newval += str;
    });
    return newval;
}

grouplist = document.querySelector('#grouplist').onchange = function (e2) {
    if (tags[0] !== null)
    {
        var e,oldValue,newValue;
        if(arrGroup)
        {
            var idx;
            idx = arrGroup.indexOf(e2.target.value);
            if (idx > -1)
            {
                arrGroup.splice(idx,1);
            }
            else
            {
                arrGroup.push(e2.target.value);
            }            
            tags[0].innerHTML = tags2Html( arrGroup );            
        }
    }
};

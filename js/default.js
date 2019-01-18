'use strict';

var documentLink;
var fileTitle, fileExtension;
var linkTitle;
var pagecounter;
var search;
var formAdUser;
var password, password_confirm;
var loginForm;
var items,contacts;
var lastEditEntry,lastEditContact;
var tags,deleteEntryType20;
var grouplist;
var contactGroups;
var entriesType35;

documentLink = document.querySelector('#cosumentLink');
fileTitle = document.querySelector('#FileTitle');
fileExtension = document.querySelector('#FileExtension');
pagecounter = document.querySelector('#pagecounter');
search = document.querySelector('#search');
password = document.querySelector('#usr_psw1');
password_confirm = document.querySelector('#usr_psw2');
loginForm = document.querySelector('#loginForm');
tags = document.querySelectorAll('[class="tags"]');
contactGroups = document.querySelector('#ContactGroups');
grouplist = document.querySelector('#grouplist');
deleteEntryType20 = document.querySelectorAll('#DeleteEntryType20');
items = document.querySelectorAll('[dmsClassName="item"]');
contacts = document.querySelectorAll('[dmsClassName="contact"]');
entriesType35 = document.querySelectorAll('a[entrytype="35"]');

console.log(items);

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
            newtext = newtext + '<br>';
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

if(items){
    items.forEach(function(item){
        item.onclick = function (e) {
            var a,form;
            var oldValue,inputValue,back;
    
            // Clean (HIDE) Old Entry
            if (lastEditEntry)
            {
                a = document.querySelector( '[a_id="' + lastEditEntry.target.id + '"]' );
                a.style.display = '';
                
                form = document.querySelector( '[form_id="' + lastEditEntry.target.id + '"]' );
                form.style.display = 'none';
            }
            // Prepare New Entry to Edit
            a = document.querySelector( '[a_id="' + e.target.id + '"]' );
            a.style.display = 'none';
            form = document.querySelector( '[form_id="' + e.target.id + '"]' );
            form.style.display = '';
            back = document.querySelector( '[back_id="' + e.target.id + '"]' );
            back.onclick = function (ee) {
                var a,form;
                a = document.querySelector( '[a_id="' + lastEditEntry.target.id + '"]' );
                a.style.display = '';
                form = document.querySelector( '[form_id="' + e.target.id + '"]' );
                form.style.display = 'none';
                ee.preventDefault();
            };

            // Write value from Hidden do Forms Input
            oldValue = document.querySelector( '[oldtitle_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputtitle_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
            
            oldValue = document.querySelector( '[oldurl_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputurl_id="' + e.target.id + '"]' );
            
            //todo - nefunguje
            console.log(e.target.dmsEntryType);
            if(e.target.dmsEntryType == "35"){
                inputValue.style.display = "none";
            }else{
                inputValue.value = oldValue.value;
            }
    
            lastEditEntry = e;
        }
    })   
}

if(contacts){
    contacts.forEach(function(contact){
        contact.onclick = function (e) {
            var a,form;
            var oldValue,inputValue,back;
    
            // Clean (HIDE) Old Entry
            if (lastEditContact)
            {
                a = document.querySelector( '[a_id="' + lastEditContact.target.id + '"]' );
                a.style.display = '';
                
                form = document.querySelector( '[form_id="' + lastEditContact.target.id + '"]' );
                form.style.display = 'none';
            }
            // Prepare New Entry to Edit
            a = document.querySelector( '[a_id="' + e.target.id + '"]' );
            a.style.display = 'none';
            form = document.querySelector( '[form_id="' + e.target.id + '"]' );
            form.style.display = '';
            back = document.querySelector( '[back_id="' + e.target.id + '"]' );
            back.onclick = function (ee) {
                var a,form;
                a = document.querySelector( '[a_id="' + lastEditContact.target.id + '"]' );
                a.style.display = '';
                form = document.querySelector( '[form_id="' + e.target.id + '"]' );
                form.style.display = 'none';
                ee.preventDefault();
            };

            // Write value from Hidden do Forms Input
            oldValue = document.querySelector( '[oldtitle_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputtitle_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
            
            oldValue = document.querySelector( '[oldurl_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputurl_id="' + e.target.id + '"]' );
   
            lastEditContact = e;
        }
    })   
}


if(entriesType35){
    entriesType35.forEach( function(entry) {
        entry.onclick = function (e) {
            e.preventDefault();
        }
    })
}

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

if(deleteEntryType20){
    for (let i = 0; i < deleteEntryType20.length; i++) {
        var e;
        e = deleteEntryType20[i];
        e.style.display = "none";
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

if(grouplist){
    grouplist.onchange = function (e2) {
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
                arrGroup = arrGroup.filter(function(el){ return el;});
                tags[0].innerHTML = tags2Html( arrGroup );            
                if(contactGroups)
                {
                    contactGroups.value = arrGroup.join(',');
                }
            }
        }
    };
};
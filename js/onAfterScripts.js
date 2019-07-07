'use strict';

// ----------------------------------------------------------------------------------------
// Init variables
// ----------------------------------------------------------------------------------------
var documentLink;
var fileTitle, fileExtension;
var linkTitle;
var pagecounter;
var search;
var formAdUser;
var password, password_confirm;
var loginForm;
var items,contacts;
var lasteditcard,lastEditContact;
var tags,deleteEntryType20;
var grouplist;
var contactGroups;
var entriesType35;
var mouseFromX,mouseFromY;
var activeForm;
var sqlrequest;
var a_type;
var arrGroup = null;
var grouplistnewcontact;
var fld_handled;


// ----------------------------------------------------------------------------------------
// Set variables
// ----------------------------------------------------------------------------------------
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
sqlrequest = document.querySelector('#sqlrequest');
a_type = document.querySelectorAll('[a_type="entry"]');
fld_handled = document.querySelector('#fld_handled');
grouplistnewcontact = document.querySelector( '[id="grouplistnewcontact"]' );


// ----------------------------------------------------------------------------------------
// Functions
// ----------------------------------------------------------------------------------------
function tags2Html( arr ){
    var newval='';
    arr.forEach(str => {
        str = "<span class='tags-item'>" + str + "</span>";
        newval += str;
    });
    return newval;
}

function validatePassword () {
    if (password.value != password_confirm.value) {
        password_confirm.setCustomValidity('Heslo se neshoduje.');
    }    
    else
    {
        password_confirm.setCustomValidity('');
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
    if(type === '')
    {
        return text;
    }
    type = type.toLowerCase();
    if(text === '')
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

function dragElement(elmnt) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    var ID;
    ID = elmnt.getAttribute('form_id');
    if (document.getElementById(elmnt.id + "header" + ID)) {
        // if present, the header is where you move the DIV from:
        document.getElementById(elmnt.id + "header" + ID).onmousedown = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV: 
        elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }
}

function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}


// ----------------------------------------------------------------------------------------
// Code
// ----------------------------------------------------------------------------------------
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

if(items){
    items.forEach(function(item){
        item.onclick = function (e) {
            var form;
            var oldValue,inputValue,back,username;
            var activeForm;
            var isNew;
    
            // Clean (HIDE) Old Entry
            if (lasteditcard)
            {
                form = document.querySelector( '[form_id="' + lasteditcard.target.id + '"]' );
                form.style.display = 'none';
            }
            // Prepare New Entry to Edit
            form = document.querySelector( '[form_id="' + e.target.id + '"]' );
            form.style.display = '';
            form.style.left = '300px';
            form.style.top = '100px';
            
            activeForm = form;
            window.onkeyup = function (event) {
                if (event.keyCode == 27) {
                    activeForm.style.display = "none";
                }
            }
            // Make the DIV element draggable:
            dragElement(form);

            back = document.querySelector( '[back_id="' + e.target.id + '"]' );
            back.onclick = function (ee) {
                var form;
                form = document.querySelector( '[form_id="' + e.target.id + '"]' );
                form.style.display = 'none';
                ee.preventDefault();
            };

            // Write value from Hidden do Forms Input
            oldValue = document.querySelector( '[oldTitle_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputtitle_id="' + e.target.id + '"]' );
            isNew = (oldValue.value == 'Nová poznámka');
            if (isNew)
                inputValue.value = '';
            else
                inputValue.value = oldValue.value;
            inputValue.focus();
            
            oldValue = document.querySelector( '[oldUrl_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputurl_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
      
            oldValue = document.querySelector( '[oldRemind_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputRemind_id="' + e.target.id + '"]' );
            if ((oldValue.getAttribute('value') == '1') || (isNew)){
                inputValue.setAttribute('checked','');
                inputValue.value = 'on';            
            }

            oldValue = document.querySelector( '[oldRemindClose_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputRemindClose_id="' + e.target.id + '"]' );
            if (oldValue.getAttribute('value') == '1'){
                inputValue.setAttribute('checked','');
                inputValue.value = 'on';            
            }

            oldValue = document.querySelector( '[oldRemindFromDate_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputRemindFromDate_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
            
            oldValue = document.querySelector( '[oldRemindLastDate_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputRemindLastDate_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
            
            oldValue = document.querySelector( '[oldRemindState_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputRemindState_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
            
            username = document.querySelector( '#UserName' );
            oldValue = document.querySelector( '[oldRemindResponsiblePerson_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputRemindResponsiblePerson_id="' + e.target.id + '"]' );
            if(isNew && (username != null)){
                inputValue.value = username.innerHTML;
            }
            else
                inputValue.value = oldValue.value;
            
            lasteditcard = e;
        }
    })   
}

if(contacts){
    contacts.forEach(function(contact){
        contact.onclick = function (e) {
            var form;
            var oldValue,inputValue,back;
            var grouplist;
            var tag,arrGroup;
            
            tag = document.querySelector('[class="tags' + e.target.id + '"]' );
            grouplist = document.querySelector( '[id="grouplist' + e.target.id + '"]' );
            arrGroup = (tag.innerText).split(',');
            if (tag.innerText !== ''){
                tag.innerHTML = tags2Html( arrGroup );
            }
            grouplist.onchange = function  (ee) {
                var tag,arrGroup; 
                var oldValue,newValue;
                var contactGroups;
                
                tag = document.querySelector('[class="tags' + e.target.id + '"]' );
                contactGroups = document.querySelector('[id="ContactGroups' + e.target.id + '"]' );
                if (tag !== null)
                {
                    arrGroup = (contactGroups.value).split(',');
                    if(arrGroup)
                    {
                        var idx;
                        idx = arrGroup.indexOf(ee.target.value);
                        if (idx > -1)
                        {
                            arrGroup.splice(idx,1);
                        }
                        else
                        {
                            arrGroup.push(ee.target.value);
                        }            
                        arrGroup = arrGroup.filter(function(el){ return el;});
                        tag.innerHTML = tags2Html( arrGroup );       
                        if(contactGroups)
                        {
                            contactGroups.value = arrGroup.join(',');
                        }
                    }
                }
                ee.target.value = '';
            };
    
            // Clean (HIDE) Old Entry
            if (lastEditContact)
            {
                form = document.querySelector( '[form_id="' + lastEditContact.target.id + '"]' );
                form.style.display = 'none';
            }
            // Prepare New Entry to Edit
            form = document.querySelector( '[form_id="' + e.target.id + '"]' );
            form.style.display = '';
            form.style.left = '300px';
            form.style.top = '100px';
            
            activeForm = form;
            window.onkeyup = function (event) {
                if (event.keyCode == 27) {
                    activeForm.style.display = "none";
                }
            }
            // Make the DIV element draggable:
            dragElement(form);

            back = document.querySelector( '[back_id="' + e.target.id + '"]' );
            back.onclick = function (ee) {
                var form;
                form = document.querySelector( '[form_id="' + e.target.id + '"]' );
                form.style.display = 'none';
                ee.preventDefault();
            };

            // Write value from Hidden do Forms Input
            oldValue = document.querySelector( '[oldFirstName_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputFirstName_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
            inputValue.focus();
            
            oldValue = document.querySelector( '[oldLastName_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputLastName_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldTitle_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputTitle_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldBirthDate_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputBirthDate_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldFunction_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputFunction_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldCompany_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputCompany_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldPhone_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputPhone_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldEmail_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputEmail_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldWeb_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputWeb_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldAddress_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputAddress_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            oldValue = document.querySelector( '[oldNote_id="' + e.target.id + '"]' );
            inputValue = document.querySelector( '[inputNote_id="' + e.target.id + '"]' );
            inputValue.value = oldValue.value;
   
            lastEditContact = e;
        }
    })   
}

if (grouplistnewcontact !== null){
    grouplistnewcontact.onchange = function  (ee) {
        var tag,arrGroup; 
        var oldValue,newValue;
        var contactGroups;
        
        tag = document.querySelector('[class="tagsnewcontact"]' );
        contactGroups = document.querySelector('[id="ContactGroupsnewcontact"]' );
        if (tag !== null)
        {
            arrGroup = (contactGroups.value).split(',');
            if(arrGroup)
            {
                var idx;
                idx = arrGroup.indexOf(ee.target.value);
                if (idx > -1)
                {
                    arrGroup.splice(idx,1);
                }
                else
                {
                    arrGroup.push(ee.target.value);
                }            
                arrGroup = arrGroup.filter(function(el){ return el;});
                tag.innerHTML = tags2Html( arrGroup );       
                if(contactGroups)
                {
                    contactGroups.value = arrGroup.join(',');
                }
            }
        }
        ee.target.value = '';
    };
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


if(a_type){
    a_type.forEach( function (entry) {
        switch (entry.getAttribute('data-dms-entrytype')) {
            case '30':
                // File
                var extension,id;
                extension = entry.getAttribute('data-dms-extension');
                if (isValidFileExtension(extension))
                {
                    var $url, $app;
                    $url = entry.getAttribute('data-dms-server') + entry.getAttribute('data-dms-name');
                    $app = getApplication(extension);
                    entry.href = $app + $url;
                    entry.target = '';
                    if($app == '')
                        entry.target = '_blank';
                }
                else
                {
                    id = entry.getAttribute('a_id');
                    entry.href = 'index.php?page=document/view/' + id;
                    entry.target = '';
                }
                break;
            case '35':
                // Note
                var url;
                url = entry.getAttribute('data-dms-url');
                if(url !== ''){
                    entry.href = 'http://' + url;
                    entry.target = '_blank';
                }else{
                    entry.href = 'index.php?page=document/view/' + entry.getAttribute('a_id');
                }
                break;
            case '20':
            case '25':
                // Folder, Block
                entry.href = 'index.php?page=document/list/' + entry.getAttribute('a_id');
                entry.target = '';
            default:
                break;
        }
    })
}

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

if(grouplist){
    grouplist.onchange = function  (e2) {
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


if(fld_handled){
    var name;
    name = "HideHandledNote";
    fld_handled.checked = getCookie(name);
}


'use strict';

// ----------------------------------------------------------------------------------------
// Public Functions
// ----------------------------------------------------------------------------------------
function stringToArray(inputString) {
    const lines = inputString.trim().split('\n');
    const result = [];

    lines.forEach(line => {
        const values = line.split(', ');
        result.push(values);
    });
    return result;
}

// ----------------------------------------------------------------------------------------
// Environment Functions
// ----------------------------------------------------------------------------------------
function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}


// ----------------------------------------------------------------------------------------
// Element value Functions
// ----------------------------------------------------------------------------------------
function setElementValue(elementId,value='') {
    var e = document.getElementById(elementId);
    if(e){
        e.value = value;
    }
}

function getElementValue(elementId) {
    var e = document.getElementById(elementId);
    if(e){
        return e.value;
    }
    return null;
}


// ----------------------------------------------------------------------------------------
// HTML format Functions
// ----------------------------------------------------------------------------------------
function tags2Html( arr ){
    var newval='';
    arr.forEach(str => {
        str = "<span class='tags-item'>" + str + "</span>";
        newval += str;
    });
    return newval;
}

function formatElementClass (classText) {
    var att,e,i;
    att = '[class="' + classText + '"]';
    e = document.querySelectorAll(att);
    for (i = 0; i < e.length; i++) {
        e[i].innerHTML = formatText(e[i].innerHTML,classText);
    }   
}

function formatText (text, field)
{
    var newtext = ''
    var arr,val;        
    if(field === '')
    {
        return text;
    }
    field = field.toLowerCase();
    if(text === '')
    {
        return '';
    }
    arr = text.split(',');              
    arr.forEach(val => {
        switch (field) {
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

function initForm(tag,id) {
    var element, oldelement;

    element = document.querySelector( '[' + tag + 'ID="' + id + '"]' );
    oldelement = document.querySelector( '[old' + tag + 'ID="' + id + '"]' );
    if (element){
        if (oldelement){
            switch (element.type) {
                case 'checkbox':
                    if (oldelement.getAttribute('value') == '1'){
                        element.checked = true;
                    }else{
                        element.checked = false;
                    }
                    break;
                default:
                    element.value = oldelement.value;
                    break;
            }
        }
    }
}

// ----------------------------------------------------------------------------------------
// Validation values Functions
// ----------------------------------------------------------------------------------------
function validatePassword () {
    if (password.value != password_confirm.value) {
        password_confirm.setCustomValidity('Heslo se neshoduje.');
    }    
    else
    {
        password_confirm.setCustomValidity('');
    }
}

// ----------------------------------------------------------------------------------------
// HTML Element Events
// ----------------------------------------------------------------------------------------
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


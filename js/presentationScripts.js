'use strict';
var editEnable = false;

function pptSetEditable( value ){
    editEnable = value;
}

function pptEditLineTitle(selectElement,type) {

    if (!editEnable)
        return;

    var elementID;
    var newElement = document.createElement(type);
    var pkID ,value, name, table, style

    // Selected element to edit its value
    elementID = selectElement.getAttribute('iD');
    pkID = selectElement.getAttribute('pkID');
    name = selectElement.getAttribute('name');
    table = selectElement.getAttribute('table');
    style = selectElement.getAttribute('style');
    value = selectElement.innerHTML;
    
    // New INPUT element for write change value
    if(type == 'input')
        newElement.type = 'text';
    newElement.style = style; 
    newElement.contentEditable = true;
    newElement.setAttribute('name',name);
    newElement.setAttribute('pkID',pkID);
    newElement.setAttribute('table',table); 
    newElement.setAttribute('selectElementID',elementID); 
    newElement.className = 'value autosize'; 
    newElement.value = value;
    newElement.innerHTML = value;
    newElement.addEventListener('change',function(e) {
        wsUpdate(this);
        var elementID = e.srcElement.getAttribute('selectElementID');
        var value = e.srcElement.value;
        if(value == "")
            value = e.srcElement.value;
        if (elementID){
            var selectElement = document.getElementById(elementID);
            if(selectElement){
                selectElement.style.display = "inline";
                selectElement.innerHTML = value;
                selectElement.value = value;
            };
        };
        this.remove();
    });
    newElement.addEventListener('mouseout',function(e) {
        wsUpdate(this);
        var elementID = e.srcElement.getAttribute('selectElementID');
        var value = e.srcElement.innerHTML;
        if(value == "")
            value = e.srcElement.value;
        if (elementID){
            var selectElement = document.getElementById(elementID);
            if(selectElement){
                selectElement.style.display = "inline";
                selectElement.innerHTML = value;
                selectElement.value = value;
            };
        };
        this.remove();
    });

    selectElement.after(newElement);
    selectElement.style.display = 'none';
    autosize();
}
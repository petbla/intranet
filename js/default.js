'use strict';

var documentLink;
var fileTitle, fileExtension;
var linkTitle;
var pagecounter;
var search;

documentLink = document.querySelector('#cosumentLink');
fileTitle = document.querySelector('#FileTitle');
fileExtension = document.querySelector('#FileExtension');
pagecounter = document.querySelector('#pagecounter');
search = document.querySelector('#search');

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

search.onclick = function (e) {
    console.log('click')
    e.target.value = '';
}
search.onmouseout = function (e) {
    if (e.target.value == ''){
        e.target.value = 'Zadejte text...';
    }
}

/*
var i;
for (i in search){
    if (i.indexOf('on') == 0) {
        console.log(i,search[i]);
    }
}
*/


console.log(search);
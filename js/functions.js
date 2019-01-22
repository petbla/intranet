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
TlMemberCellEdit = new Class({
    initialize: function () {
        var self = this;
        $$('.editable').each(function (elCell) {
            elCell.addEvent('click', function (event) {
                event.stopPropagation();
                var elSpan = this.getChildren('span')[0];
                self.showInputField(elSpan);
            });
        });

        $$('.checkable input[type=checkbox').each(function (elChbox) {
            elChbox.addEvent('change', function (event) {
                event.stopPropagation();
                self.updateGroupmembership(elChbox);
            });
        });


    },

    updateGroupmembership: function (elChbox) {
        var self = this;

        // get the span element
        var elSpan = elChbox.getSiblings('span')[0];

        // get some params from the name attribute of the input element
        var name = elChbox.get('name');
        var match = name.match(/^data(?:\[(.+?)\])?\[(.+?)_(.+?)\]$/);
        var memberId = match[1];
        var field = match[2];
        var groupId = match[3];

        var checked = elChbox.checked ? 'true' : 'false';
        // remove property checked
        if (elChbox.checked) {
            elChbox.checked = true;
            elChbox.checked = 'checked';
        } else {
            elChbox.checked = false;
        }
        // create form data object
        var form = new FormData();
        form.append('FORM_SUBMIT', self.formSubmit);
        form.append('REQUEST_TOKEN', self.requestToken);
        form.append('field', field);
        form.append('memberId', memberId);
        form.append('groupId', groupId);
        form.append('checked', checked);
        form.action = 'contao/main.php?do=tl_member_cell_edit&action=updateGroupmembership';

        // create request object
        var xhr = new XMLHttpRequest();

        // onprogress event
        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                var percentComplete = event.loaded / event.total * 100;
                //
            } else {
                //
            }
        }, false);

        // onload event
        xhr.addEventListener('load', function (event) {
            // hide message Text
            document.id('statusBox').fade('hide');
            var serverResponse = '';
            // get the server-response
            var json = JSON.decode(xhr.responseText);
            // if server returns error
            if (json.status == 'error') {
                serverResponse = json.errorMsg;
            }

            // if server returns success
            if (json.status == 'success') {
                elSpan.innerHTML = json.value;
                serverResponse = 'Daten erfolgreich übernommen!';
            }

            var fadein = (function () {
                document.id('statusBox').innerHTML = serverResponse;
                document.id('statusBox').fade('in');
            }.delay(200));

            var fadeOut = (function () {
                document.id('statusBox').innerHTML = '';
                document.id('statusBox').fade('out')
            }.delay(4000));

        }, false);

        // onerror event
        xhr.addEventListener('error', function (event) {
            //self.currentRequests--;
            alert('Upload-error! Please check connectivity.');
        }, false);

        // onabort event
        xhr.addEventListener('abort', function (event) {
            //self.currentRequests--;
        }, false);


        // open and send xhr
        xhr.open('POST', form.action, true);
        // send request
        xhr.send(form);
    },

    showInputField: function (elSpan) {
        var self = this;

        var elActiveCell = elSpan.getParent('td');
        elActiveCell.addClass('activeCell');

        var elInput = elSpan.getSiblings('input')[0];

        // hide all input fields, to be sure that only one can be opened at once
        $$('.editable input').each(function (el) {
            el.setStyle('display', 'none');
        });

        // display all span elements
        $$('.editable span').each(function (el) {
            el.setStyle('display', 'inline');
        });

        elSpan.setStyle('display', 'none');
        elInput.value = elSpan.innerHTML;
        elInput.setStyle('display', 'inline');
        elInput.focus();


        // remove event click to active cell
        elActiveCell.removeEvents();

        elInput.addEvent('blur', function (event) {
            elInput.removeEvent('blur');
            event.stopPropagation();


            var resetOnClickEvent = (function () {
                elInput.getParent('td').addEvent('click', function (event) {
                    event.stopPropagation();
                    var elSpan = this.getChildren('span')[0];
                    self.showInputField(elSpan);
                });

            }.delay(300));

            self.send(this);


        });


    },

    send: function (elInput) {
        var self = this;
        elInput.getParent('td').removeClass('activeCell');

        var elSpan = elInput.getSiblings('span')[0];
        elInput.setStyle('display', 'none');
        elSpan.setStyle('display', 'inline');

        if (elInput.value == elSpan.innerHTML) return;

        // get some params from the name attribute of the input element
        var name = elInput.get('name');
        var match = name.match(/^data(?:\[(.+?)\])?\[(.+?)\]$/);
        var id = match[1];
        var field = match[2];

        // create form data object
        var form = new FormData();
        form.append('FORM_SUBMIT', self.formSubmit);
        form.append('REQUEST_TOKEN', self.requestToken);
        form.append('field', field);
        form.append(field, elInput.value);
        form.append('id', id);
        form.append('value', elInput.value);
        form.action = 'contao/main.php?do=tl_member_cell_edit&action=update';

        // create request object
        var xhr = new XMLHttpRequest();

        // onprogress event
        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                var percentComplete = event.loaded / event.total * 100;
                //
            } else {
                //
            }
        }, false);

        // onload event
        xhr.addEventListener('load', function (event) {
            // hide message Text
            document.id('statusBox').fade('hide');
            var serverResponse = '';
            // get the server-response
            var json = JSON.decode(xhr.responseText);
            // if server returns error
            if (json.status == 'error') {
                serverResponse = json.errorMsg;
            }

            // if server returns success
            if (json.status == 'success') {
                elSpan.innerHTML = json.value;
                serverResponse = 'Daten erfolgreich übernommen!';
            }

            var fadein = (function () {
                document.id('statusBox').innerHTML = serverResponse;
                document.id('statusBox').fade('in');
            }.delay(200));

            var fadeOut = (function () {
                document.id('statusBox').innerHTML = '';
                document.id('statusBox').fade('out')
            }.delay(4000));

        }, false);

        // onerror event
        xhr.addEventListener('error', function (event) {
            //self.currentRequests--;
            alert('Upload-error! Please check connectivity.');
        }, false);

        // onabort event
        xhr.addEventListener('abort', function (event) {
            //self.currentRequests--;
        }, false);


        // open and send xhr
        xhr.open('POST', form.action, true);
        // send request
        xhr.send(form);

    },

    delete: function (elImg, id) {
        var self = this;
        if (!confirm('Soll der Eintrag ID ' + id + ' wirklich gelöscht werden?'))return false;


        elCell = elImg.getParent('td');

        // create form data object
        var action = 'contao/main.php?do=member&act=delete&id=' + id + '&rt=' + self.requestToken;

        var myRequest = new Request({
            url: action,
            method: 'get',
            onSuccess: function (responseJson) {
                elImg.getParent('tr').destroy();
                document.id('statusBox').innerHTML = 'Datensatz erfolgreich gelöscht';

            }
        });
        myRequest.send();


    }
});

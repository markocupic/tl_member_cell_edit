TlMemberCellEdit = new Class({
    initialize: function () {

        var self = this;
        this.lastBlur = Date.now();
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


        var req = new Request({
            method: 'post',
            url: 'contao/main.php?do=tl_member_cell_edit&act=updateGroupmembership',
            data: {
                'FORM_SUBMIT': self.formSubmit,
                'REQUEST_TOKEN': self.requestToken,
                'action': 'updateGroupmembership',
                'field': field,
                'memberId': memberId,
                'groupId': groupId,
                'checked': checked
            },
            onRequest: function () {
                //
            },
            onComplete: function (response) {
                // hide message Text
                document.id('statusBox').fade('hide');
                var serverResponse = '';
                // catch  server-response
                var json = JSON.decode(response);
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
            }
        });
        req.send();
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
            // prevent firing more then one request at once
            if (Date.now() - self.lastBlur < 1000){
                return;
            }
            self.lastBlur = Date.now();

            this.removeEvent('blur');
            event.stopPropagation();

            var resetOnClickEvent = (function () {
                elInput.getParent('td').addEvent('click', function (event) {
                    event.stopPropagation();
                    var elSpan = this.getChildren('span')[0];
                    var elInput = this.getChildren('input')[0];
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
        // post data
        var objPost = {
            'FORM_SUBMIT': self.formSubmit,
            'REQUEST_TOKEN': self.requestToken,
            'action': 'updateField',
            'field': field,
            'id': id,
            'value': elInput.value
        }
        objPost[field] = elInput.value;

        var req = new Request({
            method: 'post',
            url: 'contao/main.php?do=tl_member_cell_edit&act=updateField',
            data: objPost,
            onRequest: function () {
                //
            },
            onComplete: function (response) {
                // hide message Text
                document.id('statusBox').fade('hide');
                var serverResponse = '';
                // get the server-response
                var json = JSON.decode(response);
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
            }
        });
        req.send();
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

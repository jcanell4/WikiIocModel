require([
     "dijit/registry"
    ,"dojo/dom"
    ,"dojo/dom-construct"
    ,"dijit/layout/BorderContainer"
    ,"dijit/Dialog"
    ,"dijit/layout/ContentPane"
    ,"dijit/form/Form"
    ,"dijit/form/TextBox"
    ,"dijit/form/Button"
],
function (registry,dom,domConstruct,BorderContainer,Dialog,ContentPane,Form,TextBox,Button) {

    var validateButton = registry.byId('prgfploeValidateProjectButton');

    function dateNowDMY() {
        function pad(s) { return (s.length < 2 || s.toString().length < 2) ? '0' + s : s; }
        var d = new Date(Date.now());
        return [pad(d.getDate()), pad(d.getMonth()+1), d.getFullYear()].join('-');
    };

    if (validateButton) {
        validateButton.onClick = function () {
            var globalState = validateButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newValidateDlg");

            if (!dialog){
                dialog = new Dialog({
                    id: "newValidateDlg",
                    title: validateButton.title,
                    style: "width: 210px; height: 170px;",
                    validateButton: validateButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newValidateDlg");
                });

                dialog.on('show', function () {
                    //var d = new Date(Date.now());
                    //dom.byId('textBoxDateValidation').value = d.getDate() + "-" + (d.getMonth()+1) + "-" + d.getFullYear();
                    dom.byId('textBoxDateValidation').value = dateNowDMY();
                    dom.byId('textBoxDateValidation').focus();
                });

                var bc = new BorderContainer({
                    style: "width: 200px; height: 120px;"
                });

                // create a ContentPane as the center pane in the BorderContainer
                var cpCentre = new ContentPane({
                    region: "center"
                });
                bc.addChild(cpCentre);

                // put the top level widget into the document, and then call startup()
                bc.placeAt(dialog.containerNode);

                // Un formulari dins del contenidor
                var divForm = domConstruct.create('div', {
                    className: 'divform'
                },cpCentre.containerNode);

                var form = new Form().placeAt(divForm);

                //DateValidation: Un camp de text per poder escriure la data de validació
                var divDateValidation = domConstruct.create('div', {
                    className: 'divDateValidation'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: validateButton.DateLabel + '<br>'
                },divDateValidation);

                var dataValidacio = new TextBox({
                    id: 'textBoxDateValidation'
                }).placeAt(divDateValidation);
                dialog.textBoxDateValidation = dataValidacio;


                // Botons
                var divBotons = domConstruct.create('div', {
                    className: 'divBotons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, divBotons);

                new Button({
                    label: validateButton.labelButtonAcceptar,

                    onClick: function(){
                        if (dataValidacio.value !== '') {
                            var page = globalState.pages[globalState.currentTabId];
                            var query = 'do=workflow' +
                                        '&action=validate' +
                                        '&data_validacio=' + dataValidacio.value +
                                        '&id=' + page.ns +
                                        '&projectType=' + page.projectType;
                            validateButton.sendRequest(query);
                            dialog.hide();
                        }
                    }
                }).placeAt(divBotons);

                // Botó cancel·lar
                new Button({
                    label: validateButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(divBotons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }

});

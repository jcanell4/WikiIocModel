require([
     "dijit/registry"
    ,"dojo/dom"
    ,"dojo/dom-construct"
    ,"dijit/layout/BorderContainer"
    ,"dijit/Dialog"
    ,"dijit/layout/ContentPane"
    ,"dijit/form/Form"
    ,"dijit/form/Button"
],
function (registry,dom,domConstruct,BorderContainer,Dialog,ContentPane,Form,Button) {

    var toModifyButton = registry.byId('prgfplogseToModifyProjectButton');

    if (toModifyButton) {
        toModifyButton.onClick = function () {
            var globalState = toModifyButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newToModifyDlg");

            if (!dialog){
                dialog = new Dialog({
                    id: "newToModifyDlg",
                    title: toModifyButton.title,
                    style: "width: 230px; height: 190px;",
                    toModifyButton: toModifyButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newToModifyDlg");
                });

                var bc = new BorderContainer({
                    style: "width: 220px; height: 140px;"
                });

                // create a ContentPane as center pane in the BorderContainer
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

                //MessageText: Un missatge de text informatiu
                var divMessageText = domConstruct.create('div', {
                    className: 'divMessageText'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: toModifyButton.labelText + '<br>'
                },divMessageText);


                // Botons
                var divBotons = domConstruct.create('div', {
                    className: 'divBotons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, divBotons);

                new Button({
                    label: toModifyButton.labelButtonAcceptar,

                    onClick: function(){
                        var page = globalState.pages[globalState.currentTabId];
                        var query = 'do=workflow' +
                                    '&action=toModify' +
                                    '&id=' + page.ns +
                                    '&projectType=' + page.projectType;
                        toModifyButton.sendRequest(query);
                        dialog.hide();
                    }
                }).placeAt(divBotons);

                // Botó cancel·lar
                new Button({
                    label: toModifyButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(divBotons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }
});

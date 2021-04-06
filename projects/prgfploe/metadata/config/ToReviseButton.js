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

    var toReviseButton = registry.byId('prgfploeToReviseProjectButton');

    if (toReviseButton) {
        toReviseButton.onClick = function () {
            var globalState = toReviseButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newToReviseDlg");

            if (!dialog){
                dialog = new Dialog({
                    id: "newToReviseDlg",
                    title: toReviseButton.title,
                    style: "width: 210px; height: 170px;",
                    toReviseButton: toReviseButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newToReviseDlg");
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

                //MessageText: Un missatge de text informatiu
                var divMessageText = domConstruct.create('div', {
                    className: 'divMessageText'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: toReviseButton.labelText + '<br>'
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
                    label: toReviseButton.labelButtonAcceptar,

                    onClick: function(){
                        var page = globalState.pages[globalState.currentTabId];
                        var query = 'do=workflow' +
                                    '&action=toRevise' +
                                    '&id=' + page.ns +
                                    '&projectType=' + page.projectType;
                        toReviseButton.sendRequest(query);
                        dialog.hide();
                    }
                }).placeAt(divBotons);

                // Botó cancel·lar
                new Button({
                    label: toReviseButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(divBotons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }
});

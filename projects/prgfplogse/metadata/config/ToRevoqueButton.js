require([
     "dijit/registry"
    ,"dojo/dom"
    ,"dojo/dom-construct"
    ,"dijit/layout/BorderContainer"
    ,"dijit/Dialog"
    ,"dijit/layout/ContentPane"
    ,"dijit/form/Form"
    ,"dijit/form/Button"
    ,"dijit/form/Textarea"
],
function (registry,dom,domConstruct,BorderContainer,Dialog,ContentPane,Form,Button, Textarea) {

    var toRevoqueButton = registry.byId('prgfploeReturnToModifyProjectButton');

    if (toRevoqueButton) {
        toRevoqueButton.onClick = function () {
            var globalState = toRevoqueButton.dispatcher.getGlobalState();
            var dialog = registry.byId("newToRevoqueDlg");

            if (!dialog){
                var w = window.innerWidth<500?window.innerWidth:500;
                var h = window.innerHeight<500?window.innerHeight:500;

                dialog = new Dialog({
                    id: "newToRevoqueDlg",
                    title: toRevoqueButton.title,
                    style: "width: "+w+"px; height: "+h+"px;",
                    toRevoqueButton: toRevoqueButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newToRevoqueDlg");
                });
                

                var bc = new BorderContainer({
                    style: "width: "+(w-10)+"px; height: "+(h-40)+"px;"
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
                    innerHTML: "<p>" +toRevoqueButton.labelText + '</p>'
                },divMessageText);

                //textArea
                var divTextArea =  domConstruct.create('div', {
                    id: 'divTextArea'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<b>Motiu:</b> <br>'
                },divTextArea);
                
                var textarea = new Textarea({
                     id: "motiu",
                     name: "motiu",
                     style: "resize:both;  height: "+(h-50)+"px;"
                }).placeAt(divTextArea);
                
                // Botons
                var divBotons = domConstruct.create('div', {
                    className: 'divBotons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, divBotons);

                new Button({
                    label: toRevoqueButton.labelButtonAcceptar,

                    onClick: function(){
                        var page = globalState.pages[globalState.currentTabId];
                        var query = 'do=workflow' +
                                    '&action=toRevoque' +
                                    '&id=' + page.ns +
                                    '&projectType=' + page.projectType +
                                    '&motiu=' + textarea.value;
                        toRevoqueButton.sendRequest(query);
                        dialog.hide();
                    }
                }).placeAt(divBotons);

                // Botó cancel·lar
                new Button({
                    label: toRevoqueButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(divBotons);

                form.startup();
            }
            dialog.show();
            return false;
        };
    }
});

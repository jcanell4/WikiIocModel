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
    ,"ioc/gui/NsTreeContainer"
    ,"ioc/functions/normalitzaCaracters"
],
function (registry,dom,domConstruct,BorderContainer,Dialog,ContentPane,Form,TextBox,Button,NsTreeContainer,normalitzaCaracters) {

    var importButton = registry.byId('prgfploeImportProjectButton');
    
    if (importButton) {
        importButton.onClick = function () {
            var globalState = importButton.dispatcher.getGlobalState();
            var path = [];
            var dialog = registry.byId("newDocumentDlg");

            if(!dialog){
                dialog = new Dialog({
                    id: "newDocumentDlg",
                    title: importButton.title,
                    style: "width: 470px; height: 350px;",
                    importButton: importButton
                });

                dialog.on('hide', function () {
                    dialog.destroyRecursive(false);
                    domConstruct.destroy("newDocumentDlg");
                });

                dialog.on('show', function () {
                    dialog.dialogTree.tree.set('path',path).then(function(){
                        dom.byId('textBoxProjectImport').focus();
                    });
                    dom.byId('textBoxProjectImport').value = path[path.length-1] || "";
                    dom.byId('textBoxProjectImport').focus();
                });

                dialog.nsActivePage = function (){
                    path.length=0;
                    if (globalState.currentTabId) {
                        var stPath = "";
                        var aPath = globalState.getContent(globalState.currentTabId)['ns'] || '';
                        aPath = aPath.split(':');
                        aPath.pop();
                        aPath.unshift("");
                        for (var i=0; i<aPath.length; i++) {
                            if (i > 1) {
                                stPath = stPath + ":";
                            }
                            stPath = stPath + aPath[i];
                            path[i]=stPath;
                        }
                    }    
                };

                var bc = new BorderContainer({
                    style: "height: 300px; width: 450px;"
                });

                // create a ContentPane as the left pane in the BorderContainer
                var cpEsquerra = new ContentPane({
                    region: "left",
                    style: "width: 220px"
                });
                bc.addChild(cpEsquerra);

                // create a ContentPane as the center pane in the BorderContainer
                var cpDreta = new ContentPane({
                    region: "center"
                });
                bc.addChild(cpDreta);

                // put the top level widget into the document, and then call startup()
                bc.placeAt(dialog.containerNode);

                //L'arbre de navegació a la banda esquerra del quadre.
                var divizquierda = domConstruct.create('div', {
                    className: 'izquierda'
                },cpEsquerra.containerNode);

                var dialogTree = new NsTreeContainer({
                    treeDataSource: 'lib/exe/ioc_ajaxrest.php/ns_tree_rest/',
                    onlyDirs:true,
                    hiddenProjects:false
                }).placeAt(divizquierda);
                dialogTree.startup();

                dialog.dialogTree = dialogTree;

                dialogTree.tree.onClick=function(item) {
                    if (importButton.projectType.includes(item.projectType)) {
                        dom.byId('textBoxProjectImport').value= item.id;
                        dom.byId('textBoxProjectImport').focus();
                    }else {
                        alert("El tipus de projecte " + item.projectType + " no està permés.");
                    }
                };

                // Un formulari a la banda dreta contenint:
                var divdreta = domConstruct.create('div', {
                    className: 'dreta'
                },cpDreta.containerNode);

                var form = new Form().placeAt(divdreta);

                //ProjectImport: Un camp de text per poder escriure el nom del projecte a importar
                var divProjectImport = domConstruct.create('div', {
                    className: 'divProjectImport'
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: importButton.NomProjectelabel + '<br>'
                },divProjectImport);

                var ProjectImport = new TextBox({
                    id: 'textBoxProjectImport',
                    placeHolder: importButton.NomProjectelplaceHolder
                }).placeAt(divProjectImport);
                dialog.textBoxProjectImport = ProjectImport;

                // Botons
                var botons = domConstruct.create('div', {
                    className: 'botons',
                    style: "text-align:center;"
                },form.containerNode);

                domConstruct.create('label', {
                    innerHTML: '<br><br>'
                }, botons);

                new Button({
                    label: importButton.labelButtonAcceptar,

                    onClick: function(){
                        if (ProjectImport.value !== '') {
                            var page = globalState.pages[globalState.currentTabId];
                            var query = 'do=workflow' + 
                                        '&action=import' + 
                                        '&project_import=' + normalitzaCaracters(ProjectImport.value, true) +
                                        '&id=' + page.ns + 
                                        '&projectType=' + page.projectType +
                                        '&fromPageAction=' + page.action;
                            importButton.sendRequest(query);
                            dialog.hide();
                        }
                    }
                }).placeAt(botons);

                // Botó cancel·lar
                new Button({
                    label: importButton.labelButtonCancellar,
                    onClick: function(){dialog.hide();}
                }).placeAt(botons);

                form.startup();
            }
            dialog.nsActivePage();
            dialog.show();
            return false;
        };
    }
});

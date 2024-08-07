require([
    "dijit/registry",
    "dojo/dom",
    "dojo/dom-form",
    "dojo/domReady!"
], function (registry, dom, domForm) {

    var guieseoiPdfExportButton = registry.byId('guieseoiPdfExport');

    var fOnClick=function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        registry.byId("zonaMetaInfo").selectChild(id + "_iocexport");
        this.setStandbyId(id + "_iocexport");
    };

    var fGetQuery=function(){
        var filetype = "";
        var globalState = this.dispatcher.getGlobalState();
        var id = globalState.getCurrentId();
        var ns = globalState.getContent(id).ns;
        var projectType = globalState.getContent(id)["projectType"];

        var nodeForm = dom.byId("export__form_" + id);
        if (nodeForm) {
            var aux = [];
            for(var i=0; i<nodeForm.elements.length; i++){
                aux[i] = nodeForm.elements[i].disabled;
                if(aux[i]){
                    nodeForm.elements[i].disabled=false;
                }
            }
            var form = domForm.toObject(nodeForm);
            filetype = "&filetype="+form.filetype;

            for(var i=0; i<nodeForm.elements.length; i++){
                nodeForm.elements[i].disabled = aux[i];
            }
        }

        var ret = "id="+ns + "&projectType="+projectType + "&mode=pdf" + filetype;
        return ret;
    };

    if (guieseoiPdfExportButton){
        guieseoiPdfExportButton.getQuery=fGetQuery;
        guieseoiPdfExportButton.set("hasTimer", true);
        guieseoiPdfExportButton.onClick =fOnClick;
    }
});



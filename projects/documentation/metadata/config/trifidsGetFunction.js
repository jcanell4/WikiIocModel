require([
        "dijit/registry",
        "dojo/dom",
        "dojo/dom-form",
        "dojo/on",
        "dojo/domReady!"
    ], function (registry, dom, domForm, on) {
        var trifidsButton = registry.byId('trifids');
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_wikiiocmodel");
            this.setStandbyId(id + "_wikiiocmodel");
        };

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var aux = [];
            var nodeForm = dom.byId("export__form_" + id);
            for(var i=0; i<nodeForm.elements.length; i++){
                aux[i] = nodeForm.elements[i].disabled;
                if(aux[i]){
                    nodeForm.elements[i].disabled=false;
                }
            }
            var form = domForm.toObject(nodeForm);
            var ret = "id="+form.pageid + "&mode="+form.mode + "&ioclanguage="+form.ioclanguage + "&toexport="+form.toexport;
        
            for(var i=0; i<nodeForm.elements.length; i++){
                nodeForm.elements[i].disabled = aux[i];
            }
            return ret;
        };
        if (trifidsButton){
            trifidsButton.getQuery=fGetQuery;
            trifidsButton.set("hasTimer", true);
            on(trifidsButton, "click", fOnClick);
        }
});



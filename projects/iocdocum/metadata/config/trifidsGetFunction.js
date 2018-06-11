require([
        "dijit/registry",
        "dojo/dom",
        "dojo/dom-form",
        "dojo/domReady!"
    ], function (registry, dom, domForm) {
        
        var trifidsButton = registry.byId('i_trifids');
        
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_iocexport");
            this.setStandbyId(id + "_iocexport");
        };

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"];
            
            var aux = [];
            var nodeForm = dom.byId("export__form_" + id);
            for(var i=0; i<nodeForm.elements.length; i++){
                aux[i] = nodeForm.elements[i].disabled;
                if(aux[i]){
                    nodeForm.elements[i].disabled=false;
                }
            }
            var form = domForm.toObject(nodeForm);
            var ret = "id="+id + "&projectType="+projectType + "&mode=pdf" + "&filetype="+form.filetype;
        
            for(var i=0; i<nodeForm.elements.length; i++){
                nodeForm.elements[i].disabled = aux[i];
            }
            
            return ret;
        };
        
        if (trifidsButton){
            trifidsButton.getQuery=fGetQuery;
            trifidsButton.set("hasTimer", true);
            trifidsButton.onClick =fOnClick;
        }
});



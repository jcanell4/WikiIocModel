require([
        "dijit/registry",
        "dojo/on",
        "dojo/domReady!"
    ], function (registry, on) {
        
        var trifidsButton = registry.byId('trifids');
        
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_iocexportpdf");
            this.setStandbyId(id + "_iocexportpdf");
        };

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
            var ret = "id="+id + "&projectType="+projectType + "&mode=pdf";
            return ret;
        };
        
        if (trifidsButton){
            trifidsButton.getQuery=fGetQuery;
            trifidsButton.set("hasTimer", true);
            on(trifidsButton, "click", fOnClick);
        }
});



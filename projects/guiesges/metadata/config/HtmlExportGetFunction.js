require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var guiesgesHtmlExportButton = registry.byId('guiesgesHtmlExport');
       
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_iocexport");
            this.setStandbyId(id + "_iocexport");
        };

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var ns = this.dispatcher.getGlobalState().getContent(id)["ns"];             
            var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
            var ret = "id="+ns + "&projectType="+projectType + "&mode=xhtml";
            return ret;
        };
       
        if (guiesgesHtmlExportButton){
            guiesgesHtmlExportButton.getQuery=fGetQuery;
            guiesgesHtmlExportButton.set("hasTimer", true);
            guiesgesHtmlExportButton.onClick =fOnClick;
        }
});

require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var ptfploeHtmlExportButton = registry.byId('ptfploeHtmlExport');
       
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
       
        if (ptfploeHtmlExportButton){
            ptfploeHtmlExportButton.getQuery=fGetQuery;
            ptfploeHtmlExportButton.set("hasTimer", true);
            ptfploeHtmlExportButton.onClick =fOnClick;
        }
});

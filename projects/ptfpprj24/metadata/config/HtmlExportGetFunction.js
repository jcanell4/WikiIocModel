require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var ptfpprj24HtmlExportButton = registry.byId('ptfpprj24HtmlExport');
       
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
       
        if (ptfpprj24HtmlExportButton){
            ptfpprj24HtmlExportButton.getQuery=fGetQuery;
            ptfpprj24HtmlExportButton.set("hasTimer", true);
            ptfpprj24HtmlExportButton.onClick =fOnClick;
        }
});

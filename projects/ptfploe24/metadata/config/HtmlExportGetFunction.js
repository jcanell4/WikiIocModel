require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var ptfploe24HtmlExportButton = registry.byId('ptfploe24HtmlExport');
       
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
       
        if (ptfploe24HtmlExportButton){
            ptfploe24HtmlExportButton.getQuery=fGetQuery;
            ptfploe24HtmlExportButton.set("hasTimer", true);
            ptfploe24HtmlExportButton.onClick =fOnClick;
        }
});

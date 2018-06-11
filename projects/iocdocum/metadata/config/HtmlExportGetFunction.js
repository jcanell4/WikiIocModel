require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var iocDocumHtmlExportButton = registry.byId('iocDocumHtmlExport');
       
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_iocexport");
            this.setStandbyId(id + "_iocexport");
        };

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
            var ret = "id="+id + "&projectType="+projectType + "&mode=xhtml";
            return ret;
        };
       
        if (iocDocumHtmlExportButton){
            iocDocumHtmlExportButton.getQuery=fGetQuery;
            iocDocumHtmlExportButton.set("hasTimer", true);
            iocDocumHtmlExportButton.onClick =fOnClick;
        }
});

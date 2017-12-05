require([
        "dijit/registry",
        "dojo/domReady!"
    ], function (registry) {
       
        var lampreasButton = registry.byId('lampreas');
       
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
       
        if (lampreasButton){
            lampreasButton.getQuery=fGetQuery;
            lampreasButton.set("hasTimer", true);
            lampreasButton.onClick =fOnClick;
        }
});

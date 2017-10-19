require([
        "dijit/registry",
        "dojo/on",
        "dojo/domReady!"
    ], function (registry, on) {
       
        var lampreasButton = registry.byId('lampreasProva');
       /*
        var fOnClick=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            registry.byId("zonaMetaInfo").selectChild(id + "_wikiiocmodel");
            this.setStandbyId(id + "_wikiiocmodel");
        };*/

        var fGetQuery=function(){
            var id = this.dispatcher.getGlobalState().getCurrentId();
            var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
            var ret = "id="+id + "&projectType="+projectType + "&mode=xhtml";
            return ret;
        };
       
        if (lampreasButton){
            lampreasButton.getQuery=fGetQuery;
            lampreasButton.set("hasTimer", true);
//            on(lampreasButton, "click", fOnClick);
        }
});
require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {

    var ptfpprj24UpdateDataProjectButton = registry.byId('ptfpprj24UpdateDataProject');

    var fGetQuery=function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        var ns = this.dispatcher.getGlobalState().getContent(id)["ns"]; 
        var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
        var ret = "id="+ns + "&projectType="+projectType;
        return ret;
    };

    if (ptfpprj24UpdateDataProjectButton){
        ptfpprj24UpdateDataProjectButton.getQuery=fGetQuery;
    }
});

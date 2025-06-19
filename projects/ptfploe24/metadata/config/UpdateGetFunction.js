require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {

    var ptfploe24UpdateDataProjectButton = registry.byId('ptfploe24UpdateDataProject');

    var fGetQuery=function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        var ns = this.dispatcher.getGlobalState().getContent(id)["ns"]; 
        var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
        var ret = "id="+ns + "&projectType="+projectType;
        return ret;
    };

    if (ptfploe24UpdateDataProjectButton){
        ptfploe24UpdateDataProjectButton.getQuery=fGetQuery;
    }
});

require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {

    var fctUpdateDataProjectButton = registry.byId('fctUpdateDataProject');

    var fGetQuery=function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        var ns = this.dispatcher.getGlobalState().getContent(id)["ns"]; 
        var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
        var ret = "id="+ns + "&projectType="+projectType;
        return ret;
    };

    if (fctUpdateDataProjectButton){
        fctUpdateDataProjectButton.getQuery=fGetQuery;
    }
});

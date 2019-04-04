require([
    "dijit/registry",
    "dojo/domReady!"
], function (registry) {

    var consolidateButton = registry.byId('guiesgesConsolidateData');

    var fGetQuery=function(){
        var id = this.dispatcher.getGlobalState().getCurrentId();
        var ns = this.dispatcher.getGlobalState().getContent(id)["ns"]; 
        var projectType = this.dispatcher.getGlobalState().getContent(id)["projectType"]; 
        var ret = "do=generate&id="+ns + "&projectType="+projectType;
        return ret;
    };

    if (consolidateButton){
        consolidateButton.getQuery=fGetQuery;
    }
});

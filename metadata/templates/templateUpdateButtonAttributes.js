require([
    "ioc/wiki30/dispatcherSingleton",
    "ioc/wiki30/UpdateViewHandler",
     "dijit/registry",
    "dojo/domReady!"
    ], function (getDispatcher, UpdateViewHandler, registry) {
        var oldValues={};
        var wikiIocDispatcher = getDispatcher();
        var updateHandler = new UpdateViewHandler();
        updateHandler.update = function () {
            var disp = wikiIocDispatcher;
            var id = disp.getGlobalState().getCurrentId();
            if (id) {
                var page = disp.getGlobalState().getContent(id);

                if (page && page.projectType && page.projectType=='%_projectType_%') {
                    var buttons = ___JSON_BUTTON_ATTRIBUTES_DATA___;
                    buttons.forEach(function(buttonAttributes){
                        var buttonId = buttonAttributes.id;
                        var button = registry.byId(buttonId);

                        if(oldValues[page.projectType]==undefined || oldValues[page.projectType][buttonId]==undefined){
                            if(oldValues[page.projectType]==undefined){
                                oldValues[page.projectType]={};
                            }
                            oldValues[page.projectType][buttonId]={"toSet":{},"toDelete":[]};  
                            if(buttonAttributes.toDelete && buttonAttributes.toDelete.length>0){
                                buttonAttributes.toDelete.forEach(function(key){
                                    if(button[key] !== undefined){
                                        oldValues[page.projectType][buttonId]["toSet"][key] = button[key];
                                    }
                                });
                            }
                            if(buttonAttributes.toSet){
                                for (const [key, value] of Object.entries(buttonAttributes.toSet)) {
                                    if(button[key] == undefined){
                                        oldValues[page.projectType][buttonId]["toDelete"]=key
                                    }else{
                                        oldValues[page.projectType][buttonId]["toSet"][key] = button[key];
                                    }
                                }
                            }
                        }
                        if(buttonAttributes.toDelete && buttonAttributes.toDelete.length>0){
                            buttonAttributes.toDelete.forEach(function(key){
                                if(button[key] !== undefined){
                                    button[key]=undefined;
                                }
                            });
                        }
                        if(buttonAttributes.toSet){
                           for (const [key, value] of Object.entries(buttonAttributes.toSet)) {
                               button.set(key, value);
                           }
                        }
                    });

                }else if(oldValues['%_projectType_%']!=undefined){
                    for (const [buttonId, actions] of Object.entries(oldValues['%_projectType_%'])) {
                        var button = registry.byId(buttonId);
                        actions["toDelete"].forEach(function(element){
                            delete button[element];
                        });      
                        for (const [key, value] of Object.entries(actions['toSet'])) {
                            button.set(key, value);
                        }
                    }
                }
            }
        };
        wikiIocDispatcher.addUpdateView(updateHandler);
});
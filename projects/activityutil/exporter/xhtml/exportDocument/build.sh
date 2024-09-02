node "jsBuilder/r.js" -o out="templates/css/main.css" cssIn="cssBuilder/master_main.css" optimizeCss="standard.keepLines"
node "jsBuilder/r.js" -o out="templates/css/boostioc.css" cssIn="cssBuilder/master_boostioc.css" optimizeCss="standard.keepLines"
node "jsBuilder/r.js" -o "jsBuilder/app.build.js"
node "jsBuilder/r.js" -o "jsBuilder/app.build_u.js"

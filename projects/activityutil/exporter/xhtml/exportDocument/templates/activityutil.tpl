<!doctype html>
<html lang="ca">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
    <!-- Own files -->
    <!--link rel="stylesheet" href="css/basic.css"-->
    <link rel="stylesheet" href="css/{##estil##}.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <script type="text/javascript">
        if('{##estil##}'=='boostioc'){
            paramsForColumnB={
                forceIcons:true, 
                defaultIcon: 'img/more_b.png',
            };
        }else{
            paramsForColumnB={
                forceIcons:false, 
                defaultIcon: 'img/more.png',
            };
        }
    </script>
    <script src="js/modernizr-1.7.min.js"></script>
    <script type="text/javascript" src="js/Hyphenator.js"></script>
    <script type="text/javascript" src="js/build.js"></script>
    <title>IOC - Utilitats d'activitats</title>
</head>
<body>
<div class="article container">
<article lang="ca" class="sheet" data-figure-footer-type="@@FIGURE_FOOTER_TYPE@@" data-table-footer-type="@@TABLE_FOOTER_TYPE@@">
    {##documentPartsHtml##}
</article>
</div>
 <div id="back_preview" class="hidden"></div>
 <div id="preview" class="hidden">
  <div class="prevcontent"></div>
 </div>
<div class="dades_autoria">
<WIOCCL:IF condition="{##mostrarAutor##}==true">
    <p>autor: {##nom_real##}</p>
</WIOCCL:IF>
<WIOCCL:IF condition="{#_IS_STR_EMPTY(''{##entitatResponsable##}'')_#}!=true">
    <p>Editat per: {##entitatResponsable##}</p>
</WIOCCL:IF>
    <p>Darrera modificació: {##data_fitxercontinguts##}</p>
</div>
</body>
</html>

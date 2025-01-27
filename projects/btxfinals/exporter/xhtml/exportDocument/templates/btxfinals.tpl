<!doctype html>
<html lang="ca">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="expires" content="0">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
    <!-- Own files -->
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/{##estil##}.css">
    <title>IOC - Batxillerat Proves Finals - {##titol##}</title>
</head>
<body>
    <header class="main_header">
    <div class="top_row">
      <div class="logo_container">
          <img src="img/IOC_horitzontal.jpg" alt="IOC Logo" class="ioc-logo">
      </div>
    </div>
    <div class="container  purple_header">     
      <h1>BATXILLERAT</h1>
      <h2>Convocatòria {##titol##}</h2>
      <h2>Semestre {##semestre##} ({##curs##})</h2>
      <h2>{##subtitol##}</h2>
    </div>
  </header>

 
  <!-- Secció 5 -->
<section class="section section5">
  <div class="container">
      <p><b>Lloc d'examen:</b> Trobareu el lloc on heu de fer els exàmens a: Secretaria Batxillerat > Estudiant > Exàmens</p>
      <p><b>Dia i hora d'examen:</b> Al quadre següent trobareu el dia i hora d'examen de cada institut.</p>
      <p><b>Matèries:</b>  {##dadesProves##}</p>
    

    <table class="own_table">
      <tr>
        <th>Població</th>
        <th>Centre</th>
        <th>Adreça</th>
        <th>{#_DATE("{##data1##}")_#}<br> {##labelData1##}</th>
        <WIOCCL:IF condition="{##activaData2##}==true"><th>{#_DATE("{##data2##}")_#}<br>{##labelData2##}</th></WIOCCL:IF>
        <WIOCCL:IF condition="{##activaData3##}==true"><th>{#_DATE("{##data3##}")_#}<br>{##labelData3##}</th></WIOCCL:IF> 
      </tr>
      <WIOCCL:FOREACH var="item" array="{##dadesCentres##}">
      <tr>
        <td>{##item[població]##}</td>
        <td>{##item[centre]##}</td>
        <td>{##item[adreça]##}</td>
        <td>{##item[hora_data1]##}</td>
        <WIOCCL:IF condition="{##activaData2##}==true"><td> {##item[hora_data2]##}</td> </WIOCCL:IF>
        <WIOCCL:IF condition="{##activaData3##}==true"><td> {##item[hora_data3]##}</td> </WIOCCL:IF>
      </tr>
      </WIOCCL:FOREACH>      
    </table>
  

  </div>
</section>
<!-- /Secció 5 -->

  <script src="js/main.js"></script>
</body>
</html>

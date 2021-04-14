<?php
/**
 * upgrader_7: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 9 a la versión 10
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_10 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                 //Transforma los datos del proyecto "ptfplogse" desde la estructura de la versión 19 a la versión 11
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }

                $dataProject['duradaPAF'] = "Té una durada d'".$dataProject['duradaPAF'];

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver. Simultànea a l'actualització de 18 a 19 de templates", '{"fields":'.$ver.'}');
                break;

            case "templates":

                /* Buscar y Sustituir en el archivo 'continguts'
                 * 1-B) ***QF{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var="item" array="{##filtered##}" counter="indFiltered">{##item[abreviació qualificació]##} * {##item[ponderació]##}% <WIOCCL:IF condition="{##indFiltered##}\<{#_SUBS({#_ARRAY_LENGTH({##filtered##})_#},1)_#}">+</WIOCCL:IF></WIOCCL:FOREACH>**
                 * 1-S) ***QUF{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var="item" array="{##filtered##}" counter="indFiltered">{##item[abreviació qualificació]##} * {##item[ponderació]##}% <WIOCCL:IF condition="{##indFiltered##}\<{#_SUBS({#_ARRAY_LENGTH({##filtered##})_#},1)_#}">+
                 * 2-B) ***QF{##itemUf[unitat formativa]##} = {#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}% de la nota de la UF{##itemUf[unitat formativa]##} obtinguda a la PAF**.
                 * 2-S) ***QUF{##itemUf[unitat formativa]##} = {#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}% de la nota de la UF{##itemUf[unitat formativa]##} obtinguda a la PAF**.
                 * 3-B) La planificació establerta per a la UF{##ind##} és la següent: (veure:table:T11-{##itemUf[unitat formativa]##}:)
                 * 3-S) La planificació establerta per a la UF{##itemUf[unitat formativa]##} és la següent: (veure:table:T11-{##itemUf[unitat formativa]##}:)
                 */
                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [
                    [
                        "\\*\\*\\*QF\\{##itemUf\\[unitat formativa\\]##\\} \\= \\<WIOCCL:FOREACH var\\=\"item\" array\\=\"\\{##filtered##\\}\" counter\\=\"indFiltered\"\\>\\{##item\\[abreviació qualificació\\]##\\} \\* \\{##item\\[ponderació\\]##\\}\\% \\<WIOCCL:IF condition=\"\\{##indFiltered##\\}\\\\\<\\{#_SUBS\\(\\{#_ARRAY_LENGTH\\(\\{##filtered##\\}\\)_#\\},1\\)_#\\}\"\\>\\+",
                        "***QUF{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var=\"item\" array=\"{##filtered##}\" counter=\"indFiltered\">{##item[abreviació qualificació]##} * {##item[ponderació]##}% <WIOCCL:IF condition=\"{##indFiltered##}\\<{#_SUBS({#_ARRAY_LENGTH({##filtered##})_#},1)_#}\">+ "
                    ],
                    [
                        "\\*\\*\\*QF\\{##itemUf\\[unitat formativa\\]##\\} \\= \\{#_FIRST\\(\\{##filtered##\\}, ''FIRST\\[ponderació\\]''\\)_#\\}\\% de la nota de la UF\\{##itemUf\\[unitat formativa\\]##\\} obtinguda a la PAF\\*\\*\\.",
                        "***QUF{##itemUf[unitat formativa]##} = {#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}% de la nota de la UF{##itemUf[unitat formativa]##} obtinguda a la PAF**."
                    ],
                    [
                        "La planificació establerta per a la UF\\{##ind##\\} és la següent: \\(veure ?:table:T11\\-\\{##itemUf\\[unitat formativa\\]##\\}:\\)",
                        "La planificació establerta per a la UF{##itemUf[unitat formativa]##} és la següent: (veure :table:T11-{##itemUf[unitat formativa]##}:)"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                /* Buscar y Sustituir en el archivo 'continguts'
                 * 1-B) Es recomana cursar-lo el <WIOCCL:IF condition="{##semestre##}==1">{##itinerariRecomanatS1##}</WIOCCL:IF><WIOCCL:IF condition="{##semestre##}==2">{##itinerariRecomanatS2##}</WIOCCL:IF> semestre de l'itinerari formatiu i suposa una **dedicació setmanal mínima  de {##dedicacio##}h.**
                 * 1-S) Es recomana cursar-lo el semestre <WIOCCL:IF condition="{##semestre##}==1">{##itinerariRecomanatS1##}</WIOCCL:IF><WIOCCL:IF condition="{##semestre##}==2">{##itinerariRecomanatS2##}</WIOCCL:IF> de l'itinerari formatiu i suposa una **dedicació setmanal mínima  de {##dedicacio##}h.**
                 * 2-B) Es segueix una metodologia  basada en l’aprenentatge significatiu mitjançant el seguiment de les eines d'aprenentatge que se us proposen al següent apartat per assolir els resultats d’aprenentatge
                 * 2-S) Es segueix una metodologia  basada en l’aprenentatge significatiu, mitjançant el seguiment de les eines d'aprenentatge que se us proposen al següent apartat per assolir els resultats d’aprenentatge
                 * 3-B) És recomanable realitzar totes les activitats proposades, tant les que avalua el professor i per tant ponderen a l'avalució contínua (AC), com les altres. Les activitats i exercicis no avaluats disposen de solució que us permetrà portar el propi control del vostre aprenentatge.
                 * 3-S) És recomanable realitzar totes les activitats proposades, tant les que avalua el professor i per tant ponderen a l'avalució contínua (AC), com les altres. Les activitats i exercicis no avaluats disposen de solució, fet que us permetrà portar el propi control del vostre aprenentatge.
                 * 4-B) Aquest <WIOCCL:IF condition="''mòdul''!={##tipusBlocModul##}">{##tipusBlocModul##}</WIOCCL:IF> disposa també de jornades tècniques (JT) per ajudar-vos a consolidar parts pràctiques específiques.
                 * 4-S) Aquest <WIOCCL:IF condition="''mòdul''=={##tipusBlocModul##}">{##tipusBlocModul##}</WIOCCL:IF><WIOCCL:IF condition="''mòdul''!={##tipusBlocModul##}">bloc</WIOCCL:IF> disposa també de jornades tècniques (JT) per ajudar-vos a consolidar parts pràctiques específiques.
                 * 5-B) A banda de les PAF caldrà realitzar EAF durant el semestre (vegeu l'apartat d'avaluació)
                 * 5-S) A banda de les PAF, caldrà fer EAF durant el semestre (vegeu l'apartat d'avaluació)
                 * 6-B  Té una assignació de \\{##durada##\\} h de les \\{##duradaCicle##\\}h del cicle de \\{##cicle##\\}. El  coodinador del mòdul és \\{##coordinador##\\}, els professors són: \\{##professors##\\}\\.
                 * 6-S  Té una assignació de {##durada##} h de les {##duradaCicle##} h del cicle de {##cicle##}. El  coodinador del mòdul és {##coordinador##}, els professors són: {##professors##}.
                 * 7-B  | {##item[tipus]##} | {##item[eina]##} | {##item[opcionalitat]##} | <WIOCCL:IF condition="{##item[puntuable]##}==true">si</WIOCCL:IF><WIOCCL:IF condition="{##item[puntuable]##}==false">no</WIOCCL:IF> |
                 * 7-S  | {#_UCFIRST(\"{##item[tipus]##}\")_#} | {##item[eina]##} | {##item[opcionalitat]##} | <WIOCCL:IF condition=\"{##item[puntuable]##}==true\">Sí</WIOCCL:IF><WIOCCL:IF condition=\"{##item[puntuable]##}==false\">No</WIOCCL:IF> |
                 */
//                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [
                    [
                        "Es recomana cursar-lo el \\<WIOCCL:IF condition=\"\\{##semestre##\\}\\=\\=1\"\\>\\{##itinerariRecomanatS1##\\}\\<\\/WIOCCL:IF\\>\\<WIOCCL:IF condition\\=\"\\{##semestre##\\}\\=\\=2\"\\>\\{##itinerariRecomanatS2##\\}\\<\\/WIOCCL:IF\\> semestre de l'itinerari formatiu i suposa una \\*\\*dedicació setmanal mínima  de \\{##dedicacio##\\} ?h\\.\\*\\*",
                        "Es recomana cursar-lo el semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##itinerariRecomanatS1##}</WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##itinerariRecomanatS2##}</WIOCCL:IF> de l'itinerari formatiu i suposa una **dedicació setmanal mínima de {##dedicacio##} h.**"
                    ],
                    [
                        "Es segueix una metodologia  basada en l’aprenentatge significatiu mitjançant el seguiment de les eines d'aprenentatge que se us proposen al següent apartat per assolir els resultats d’aprenentatge",
                        "Es segueix una metodologia  basada en l’aprenentatge significatiu, mitjançant el seguiment de les eines d'aprenentatge que se us proposen al següent apartat per assolir els resultats d’aprenentatge"
                    ],
                    [
                        "És recomanable realitzar totes les activitats proposades, tant les que avalua el professor i per tant ponderen a l'avalució contínua \\(AC\\), com les altres\\. Les activitats i exercicis no avaluats disposen de solució que us permetrà portar el propi control del vostre aprenentatge\\.",
                        "És recomanable realitzar totes les activitats proposades, tant les que avalua el professor i per tant ponderen a l'avaluació contínua (AC), com les altres. Les activitats i exercicis no avaluats disposen de solució, fet que us permetrà portar el propi control del vostre aprenentatge."
                    ],
                    [
                        "Aquest \\<WIOCCL:IF condition=\"''mòdul''\\!\\=\\{##tipusBlocModul##\\}\"\\>\\{##tipusBlocModul##\\}\\<\\/WIOCCL:IF\\> disposa també de jornades tècniques \\(JT\\) per ajudar-vos a consolidar parts pràctiques específiques\\.",
                        "Aquest <WIOCCL:IF condition=\"''mòdul''=={##tipusBlocModul##}\">{##tipusBlocModul##}</WIOCCL:IF><WIOCCL:IF condition=\"''mòdul''!={##tipusBlocModul##}\">bloc</WIOCCL:IF> disposa també de jornades tècniques (JT) per ajudar-vos a consolidar parts pràctiques específiques."
                    ],
                    [
                        "A banda de les PAF caldrà realitzar EAF durant el semestre \\(vegeu l'apartat d'avaluació\\)",
                        "A banda de les PAF, caldrà fer EAF durant el semestre (vegeu l'apartat d'avaluació)"
                    ],
                    [
                        "Té una assignació de \\{##durada##\\} h de les \\{##duradaCicle##\\}h del cicle de \\{##cicle##\\}. El  coodinador del mòdul és \\{##coordinador##\\}, els professors són: \\{##professors##\\}\\.",
                        "Té una assignació de {##durada##} h de les {##duradaCicle##} h del cicle de {##cicle##}. El  coodinador del mòdul és {##coordinador##}, els professors són: {##professors##}."
                    ],
                    [
                        "\\| \\{##item\\[tipus\\]##\\} \\| \\{##item\\[eina\\]##\\} \\| \\{##item\\[opcionalitat\\]##\\} \\| \\<WIOCCL:IF condition\\=\"\\{##item\\[puntuable\\]##\\}\\=\\=true\"\\>si\\<\\/WIOCCL:IF\\>\\<WIOCCL:IF condition\\=\"\\{##item\\[puntuable\\]##\\}\\=\\=false\"\\>no\\<\\/WIOCCL:IF\\> \\|",
                        "| {#_UCFIRST(\"{##item[tipus]##}\")_#} | {##item[eina]##} | {##item[opcionalitat]##} | <WIOCCL:IF condition=\"{##item[puntuable]##}==true\">Sí</WIOCCL:IF><WIOCCL:IF condition=\"{##item[puntuable]##}==false\">No</WIOCCL:IF> |"
                    ],
                    [
                        "En aquest \\<WIOCCL:IF condition\\=\"''mòdul''\\=\\=\\{##tipusBlocModul##\\}\"\\>mòdul\\<\\/WIOCCL:IF\\>\\<WIOCCL:IF condition\\=\"''mòdul''\\!\\=\\{##tipusBlocModul##\\}\"\\>bloc\\<\\/WIOCCL:IF\\> es descriuen els següents resultats d'aprenentatge\\:\\:",
                        "En aquest <WIOCCL:IF condition=\"''mòdul''=={##tipusBlocModul##}\">mòdul</WIOCCL:IF><WIOCCL:IF condition=\"''mòdul''!={##tipusBlocModul##}\">bloc</WIOCCL:IF> es descriuen els següents resultats d'aprenentatge:"
                    ],
                    [
                        "  \\:title\\:Unitats Formatives",
                        "  :title:Unitats formatives"
                    ],
                    [
                        "Cada Unitat Formativa es divideix en diferents unitats\\:",
                        "Cada unitat formativa es divideix en diferents unitats:"
                    ],
                    [
                        "Cada Unitat es divideix en diferents apartats\\:",
                        "Cada unitat es divideix en diferents apartats:"
                    ],
                    [
                        "\\^ Id \\^  unitat  \\^  data de publicació de l'enunciat  \\^ data de publicació del lliurament \\<WIOCCL:IF condition\\=\"\\{##hiHaSolucioPerAC##\\}\\=\\=true\"\\>\\^ data de publicació de la solució \\<\\/WIOCCL:IF\\>\\^ data de publicació de la qualificació \\^",
                        "^ Id ^  Unitat  ^  Data de publicació de l'enunciat  ^ Data de publicació del lliurament <WIOCCL:IF condition=\"{##hiHaSolucioPerAC##}==true\">^ Data de publicació de la solució </WIOCCL:IF>^ Data de publicació de la qualificació ^"
                    ],
                    [
                        "\\^ Id \\^  unitat  \\^  data de publicació de l'enunciat  \\^ data de publicació del lliurament \\<WIOCCL:IF condition\\=\"\\{##hiHaSolucioPerEAF##\\}\\=\\=true\"\\>\\^ data de publicació de la solució \\<\\/WIOCCL:IF\\>\\^ data de publicació de la qualificació \\^",
                        "^ Id ^  Unitat  ^  Data de publicació de l'enunciat  ^ Data de publicació del lliurament <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">^ Data de publicació de la solució </WIOCCL:IF>^ Data de publicació de la qualificació ^"
                    ],
                    [
                        "\\^ Id \\^  unitat  \\<WIOCCL:IF condition\\=\"\\{##hiHaEnunciatRecuperacioPerEAF##\\}\\=\\=true\"\\>\\^  data de publicació de l'enunciat  \\<\\/WIOCCL:IF\\>\\^ data de publicació del lliurament \\<WIOCCL:IF condition\\=\"\\{##hiHaSolucioPerEAF##\\}\\=\\=true\"\\>\\^ data de publicació de la solució \\<\\/WIOCCL:IF\\>\\^ data de publicació de la qualificació \\^",
                        "^ Id ^  Unitat  <WIOCCL:IF condition=\"{##hiHaEnunciatRecuperacioPerEAF##}==true\">^  Data de publicació de l'enunciat  </WIOCCL:IF>^ Data de publicació del lliurament <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">^ Data de publicació de la solució </WIOCCL:IF>^ Data de publicació de la qualificació ^"
                    ],
                    [
                        "\\^ Id \\^  inscripció  \\^  publicació llista provisional  \\^ publicació llista definitiva \\^ data de la jornada tècnica \\(JT\\) \\^ data publicació de la qualificació \\^",
                        "^ Id ^  Inscripció  ^  Publicació llista provisional  ^ Publicació llista definitiva ^ Data de la jornada tècnica (JT) ^ Data publicació de la qualificació ^"
                    ],
                    [
                        "\\^  PAF  \\^  Data de realització  \\^  Publicació qualificació \\^",
                        "^  PAF  ^  Data  ^  Publicació qualificació  ^"
                    ],
                    [
                        "El model pedagògic de l'IOC es fonamenta en l’avaluació acadèmica de l’alumne mitjançant l’AC. Està constatat que l’èxit en els estudis a distància està directament relacionat amb el ritme d’estudi continuat al llarg de tot el període acadèmic. Es recomana a tots els alumnes que segueixin l’avaluació contínua atès que forma part de la qualificació final\\. ?\n\nL'AC es realitza a distància, es concreta en\\:",
                        "El model pedagògic de l'IOC es fonamenta en l’avaluació acadèmica de l’alumne mitjançant l’AC. Està constatat que l’èxit en els estudis a distància està directament relacionat amb el ritme d’estudi continuat al llarg de tot el període acadèmic. Es recomana a tots els alumnes que segueixin l’avaluació contínua atès que forma part de la qualificació final. \n\nL'AC es fa a distància i es concreta en:"
                    ],
                    [
                        "  \\* El seguiment correcte de l’AC compromet l’alumne a realitzar les activitats proposades de manera individual, original i seguint les indicacions del professor\\. Si no es respecten aquestes condicions, s’obtindrà una qualificació negativa\\. Els treballs o tasques d'AC que siguin còpia literal \\(total o parcial\\) d'altres treballs, exercicis o fonts es consideraran suspesos amb una qualificació de 0\\.\n  \\* En els EAC s'estableixen els criteris d'avaluació corresponents tant de contingut com de forma \\(per exemple, l'expressió lingüística i ortogràfica\\)\\. \n  \\* El professor corregeix els EAC i emet una qualificació numèrica de l'escala entre el 0 i el 10, amb dos decimals\\. \n  \\* Per poder tenir en compte la qualificació de l’AC, s’ha d’aconseguir \\*\\*a la PAF\\*\\* una qualificació \\*\\*mínima de \\{##notaMinimaPAF##\\},00 sense cap  arrodoniment\\*\\* \\(PAF1 o PAF 2\\)\\.\n  \\* La qualificació de l'AC es té en compte tant a la PAF1 com a la PAF2 del mateix semestre però no es guarda d'un semestre a l'altre\\.",
                        "  * El seguiment correcte de l’AC compromet l’alumne a fer les activitats proposades de manera individual, original i seguint les indicacions del professor. Si no es respecten aquestes condicions, s’obtindrà una qualificació negativa. Els treballs o tasques d'AC que siguin còpia literal (total o parcial) d'altres treballs, exercicis o fonts es consideraran suspesos amb una qualificació de 0.\n  * En els EAC s'estableixen els criteris d'avaluació corresponents tant de contingut com de forma (per exemple, l'expressió lingüística i ortogràfica). \n  * El professor corregeix els EAC i emet una qualificació numèrica de l'escala entre el 0 i el 10, amb dos decimals. \n  * Per poder tenir en compte la qualificació de l’AC, s’ha d’aconseguir **a la PAF** una qualificació **mínima de {##notaMinimaPAF##},00 sense cap  arrodoniment** (PAF 1 o PAF 2).\n  * La qualificació de l'AC es té en compte tant a la PAF 1 com a la PAF 2 del mateix semestre, però no es guarda d'un semestre a l'altre."
                    ],
                    [
                        "  \\* és\\*\\* no presencial\\*\\*, es realitza a distància al llarg de la unitat formativa\\. \\(consulteu  dates clau a les taules :table:T06: i :table:T07:\\)\\.\n  \\* la seva realització és \\*\\*obligatòria\\*\\* per aprovar  la unitat formativa\\.\n  \\* és de caràcter \\[##TODO: escolliu \"individual\" o \"grupal\" segons sigui el cas##\\]\\.  \n  \\* té una ponderació en la \\*\\*qualificació final\\*\\* \\<WIOCCL:IF condition\\=\"''mòdul''\\=\\=\\{##tipusBlocModul##\\}\"\\>del mòdul \\(vegeu l'apartat \"Qualificació final QF\"\\<\\/WIOCCL:IF\\>\\<WIOCCL:IF condition\\=\"''mòdul''\\!\\=\\{##tipusBlocModul##\\}\"\\>de la UF \\(vegeu l'apartat \"Qualificació final de cada UF\"\\<\\/WIOCCL:IF\\>\\)\\.\n  \\* s'ha d'obtenir una qualificació \\*\\*mínima de \\{##notaMinimaEAF##\\},00 sense arrodoniment\\*\\* per poder aplicar el càlcul de la qualificació final\\.",
                        "  * És** no presencial**, es realitza a distància al llarg de la unitat formativa (consulteu  dates clau a les taules :table:T06: i :table:T07:).\n  * La seva realització és **obligatòria** per aprovar  la unitat formativa.\n  * És de caràcter [##TODO: escolliu \"individual\" o \"grupal\" segons sigui el cas##].  \n  * Té una ponderació en la **qualificació final** <WIOCCL:IF condition=\"''mòdul''=={##tipusBlocModul##}\">del mòdul (vegeu l'apartat \"Qualificació final QF\"</WIOCCL:IF><WIOCCL:IF condition=\"''mòdul''!={##tipusBlocModul##}\">de la UF (vegeu l'apartat \"Qualificació final de cada UF\"</WIOCCL:IF>).\n  * S'ha d'obtenir una qualificació **mínima de {##notaMinimaEAF##},00 sense arrodoniment** per poder aplicar el càlcul de la qualificació final."
                    ],
                    [
                        "L'EAF ha de ser lliurat dins els terminis fixats; el termini de lliurament és improrrogable\\. El sistema no permet lliurar cap exercici passades les 23\\.55 hores de la data prevista per al lliurament \\(consulteu dates clau a les taules :table:T06: i :table:T07:\\)\\. Es recomana no esperar a darrer moment per evitar imprevistos\\.\n\nEls EAF que siguin una còpia literal \\(total o parcial\\) d'altres treballs, exercicis o fonts es consideraran automàticament suspesos amb qualificació de 0\\. \n\nEn ser part de l'avaluació final, l'EAF té dues convocatòries ordinàries cada semestre:  EAF i recuperació EAF\\. L'alumne pot: \n\n  \\* Presentar-se a l'EAF i en cas de no superar-lo, presentar-se a la recuperació EAF del mateix semestre\\. En cap cas es pot presentar a la recuperació EAF per pujar nota tenint l'EAF superat\\.\n  \\* Presentar-se directament a la recuperació EAF\\.\n\nLa convocatòria EAF s'exhaureix tant si l'estudiant s'hi presenta com si no \\(excepte que l'alumne hagi anul·lat la matrícula\\)\\. La convocatòria de recuperació EAF només s'exhaureix si l'estudiant s'hi presenta\\. \n\nEn el cas dels EAF establerts amb metodologia de treball en equip, l'estudiant té l'obligació de presentar-se a la convocatòria EAF i només opta a presentar-se a l'EAF recuperació quan:\n\n  \\* La recuperació implica a tots els components de l'equip que han de lliurar de nou i conjuntament una mateixa tasca\\.\n  \\* La recuperació individual fa referència al contingut de l'exercici i no a la recuperació de les competències de treball en equip que només es poden superar de nou, treballant novament en grup\\.",
                        "L'EAF ha de ser lliurat dins els terminis fixats; el termini de lliurament és improrrogable. El sistema no permet lliurar cap exercici passades les 23.55 hores de la data prevista per al lliurament (consulteu dates clau a les taules :table:T06: i :table:T07:). Es recomana no esperar al darrer moment per evitar imprevistos.\n\nEls EAF que siguin una còpia literal (total o parcial) d'altres treballs, exercicis o fonts es consideraran automàticament suspesos amb qualificació de 0. \n\nCom que és part de l'avaluació final, l'EAF té dues convocatòries ordinàries cada semestre:  EAF i recuperació EAF. L'alumne pot: \n\n  * Presentar-se a l'EAF i, en cas de no superar-lo, presentar-se a la recuperació de l'EAF del mateix semestre. En cap cas es pot presentar a la recuperació de l'EAF per pujar nota tenint l'EAF superat.\n  * Presentar-se directament a la recuperació de l'EAF.\n\nLa convocatòria de l'EAF s'exhaureix tant si l'estudiant s'hi presenta com si no (excepte que l'alumne hagi anul·lat la matrícula). La convocatòria de recuperació de l'EAF només s'exhaureix si l'estudiant s'hi presenta. \n\nEn el cas dels EAF establerts amb metodologia de treball en equip, l'estudiant té l'obligació de presentar-se a la convocatòria de l'EAF i només opta a presentar-se a l'EAF de recuperació quan:\n\n  * La recuperació implica tots els membres de l'equip, que han de lliurar de nou i conjuntament una mateixa tasca.\n  * La recuperació individual fa referència al contingut de l'exercici i no a la recuperació de les competències de treball en equip, que només es poden superar treballant novament en grup."
                    ],
                    [
                        "Jornades presencials procedimentals de de la unitat formativa:\n\n  \\* és presencial\\. \n  \\* és obligatòria per aprovar la unitat formativa\\.\n  \\* es realitza al llarg del semestre \\(consulteu dates clau  a les taules :table:T08: i :table:T09:\\)\\. \n  \\* es confirma la identitat de l'alumne que la realitza\\. \n  \\* té una durada màxima de \\[##TODO: X dies\\. Cada dia té una durada màxima de X h\\. \\(si ho considereu oportú\\)##\\]\\.\n  \\* s'avalua numèricament  de entre el 0 i el 10, amb dos decimals\\.\n  \\* té una ponderació en la \\*\\*qualificació final\\*\\* \\<WIOCCL:IF condition\\=\"''mòdul''\\=\\={##tipusBlocModul##}\"\\>del mòdul \\(vegeu l'apartat \"Qualificació final QF\"\\<\\/WIOCCL:IF\\>\\<WIOCCL:IF condition\\=\"''mòdul''\\!\\={##tipusBlocModul##}\"\\>de la UF \\(vegeu l'apartat \"Qualificació final de cada UF\"\\<\\/WIOCCL:IF\\>\\)\\.\n  \\* es necessita una nota mínima de {##notaMinimaJT##},00 sense arrodoniment per poder aplicar el càlcul de la QF\\.\n\nPer assistir a la JT és imprescindible:\n\n  \\* apuntar-se a l'espai indicat pel professor dins dels terminis establerts \\(si ho considereu\\)",
                        "Jornada presencial procedimental de la unitat formativa:\n\n  * És presencial. \n  * És obligatòria per aprovar la unitat formativa.\n  * Es fa al llarg del semestre (consulteu dates clau  a les taules :table:T08: i :table:T09:). \n  * Es confirma la identitat de l'alumne que la fa. \n  * Té una durada màxima de [##TODO: X dies. Cada dia té una durada màxima de X h. (si ho considereu oportú)##].\n  * S'avalua numèricament entre el 0 i el 10, amb dos decimals.\n  * Té una ponderació en la **qualificació final** <WIOCCL:IF condition=\"''mòdul''=={##tipusBlocModul##}\">del mòdul (vegeu l'apartat \"Qualificació final QF\"</WIOCCL:IF><WIOCCL:IF condition=\"''mòdul''!={##tipusBlocModul##}\">de la UF (vegeu l'apartat \"Qualificació final de cada UF\"</WIOCCL:IF>).\n  * Es necessita una nota mínima de {##notaMinimaJT##},00 sense arrodoniment per poder aplicar el càlcul de la QF.\n\nPer assistir a la JT és imprescindible:\n\n  * Apuntar-se a l'espai indicat pel professor dins dels terminis establerts (si ho considereu)"
                    ],
                    [
                        "Al final de cada semestre l’alumne s’ha de presentar a una prova d’avaluació final \\(PAF\\) \\*\\*presencial i obligatòria\\*\\* per aprovar cada UF \\(consulteu dates clau a la taula::table:T10:\\)\\.\n \nPer poder presentar-se, \\*\\*cal confirmar\\*\\* l'assistència en el període establert\\.\n\nLa prova d'avaluació final \\(PAF\\):\n\n  \\* té una durada d'\\*\\*\\{##duradaPAF##\\}\\*\\*\n  \\* consistirà en diversos exercicis: frases V\\/F, preguntes obertes, resolució d'un cas pràctic, etc\\.\n  \\* s'avalua numèricament del 0 al 10, amb dos decimals\\.\n  \\* NO inclou retroacció\\.\n  \\* té una ponderació  a la Qualificació Final \\(QF\\) de la UF \\(vegeu l'apartat \\<WIOCCL:IF condition\\=\"''mòdul''\\=\\=\\{##tipusBlocModul##\\}\"\\>\"Qualificació final QF\"\\<\\/WIOCCL:IF\\>\\<WIOCCL:IF condition\\=\"''mòdul''\\!\\=\\{##tipusBlocModul##\\}\"\\>\"Qualificació final de cada UF\"\\<\\/WIOCCL:IF\\>\\)\\.\n  \\* s'ha d'obtenir una \\*\\*nota mínima de \\{##notaMinimaPAF##\\},00 sense arrodoniment\\*\\* per poder aplicar el càlcul de la QF\\.\n\nS'ofereixen dues convocatòries ordinàries cada semestre: PAF1 i PAF2\\. \\(consulteu dates clau a la taula::table:T10:\\)\\. L'alumne pot:\n\n  \\* Presentar-se a la PAF1 i en cas de no superar-la, presentar-se a la PAF2 del mateix semestre \\(2 setmanes després de la PAF1\\)\\. En cap cas es pot presentar a la PAF2  per pujar nota tenint la PAF1 superada\\.\n  \\* Presentar-se directament a la PAF2\\.\n\nLa convocatòria corresponent a la PAF1 s'esgota tant si l'estudiant s'hi presenta com si no \\(exceptuant  que l'alumne hagi anul·lat la matrícula\\)\\. La convocatòria corresponent a PAF2 només s'esgota si l'estudiant s'hi presenta\\.",
                        "Al final de cada semestre, l’alumne s’ha de presentar a una prova d’avaluació final (PAF) **presencial i obligatòria** per aprovar cada UF (consulteu dates clau a la taula::table:T10:).\n \nPer poder presentar-s'hi, **cal confirmar** l'assistència en el període establert.\n\nLa prova d'avaluació final (PAF):\n\n  * Té una durada d'**{##duradaPAF##}**\n  * Consistirà en diversos exercicis: frases V/F, preguntes obertes, resolució d'un cas pràctic, etc.\n  * S'avalua numèricament del 0 al 10, amb dos decimals.\n  * NO inclou retroacció.\n  * Té una ponderació  a la qualificació final (QF) de la UF (vegeu l'apartat <WIOCCL:IF condition=\"''mòdul''=={##tipusBlocModul##}\">\"Qualificació final QF\"</WIOCCL:IF><WIOCCL:IF condition=\"''mòdul''!={##tipusBlocModul##}\">\"Qualificació final de cada UF\"</WIOCCL:IF>).\n  * S'ha d'obtenir una **nota mínima de {##notaMinimaPAF##},00 sense arrodoniment** per poder aplicar el càlcul de la QF.\n\nS'ofereixen dues convocatòries ordinàries cada semestre: PAF 1 i PAF 2 (consulteu dates clau a la taula::table:T10:). L'alumne pot:\n\n  * Presentar-se a la PAF 1 i en cas de no superar-la, presentar-se a la PAF 2 del mateix semestre (2 setmanes després de la PAF 1). En cap cas es pot presentar a la PAF 2  per pujar nota tenint la PAF 1 superada.\n  * Presentar-se directament a la PAF 2.\n\nLa convocatòria corresponent a la PAF 1 s'esgota tant si l'estudiant s'hi presenta com si no (exceptuant  que l'alumne hagi anul·lat la matrícula). La convocatòria corresponent a PAF 2 només s'esgota si l'estudiant s'hi presenta."
                    ],
                    [
                        "Si la qualificació de la PAF és inferior a \\{##notaMinimaPAF##\\},00, el càlcul de la QF serà el \\<WIOCCL:SUBSET subsetvar=\"filtered\" array=\"\\{##dadesQualificacioUFs##\\}\" arrayitem=\"itemsub\" filter=\"\\{##itemsub\\[tipus qualificació\\]##\\}==''PAF''\"\\>\\{#_FIRST\\(\\{##filtered##\\}, ''FIRST\\[ponderació\\]''\\)_#\\}\\<\\/WIOCCL:SUBSET\\> de la nota obtinguda a la PAF\\.",
                        "Si la qualificació de la PAF és inferior a {##notaMinimaPAF##},00, el càlcul de la QF serà el <WIOCCL:SUBSET subsetvar=\"filtered\" array=\"{##dadesQualificacioUFs##}\" arrayitem=\"itemsub\" filter=\"{##itemsub[tipus qualificació]##}==''PAF''\">{#_FIRST({##filtered##}, ''FIRST[ponderació]'')_#}</WIOCCL:SUBSET>% de la nota obtinguda a la PAF."
                    ],
                    [
                        "En cas de no superar la UF, el següent semestre s'han de tornar a realitzar totes les activitats \\(PAF i AC\\)\\. No es guardarà cap nota parcial de la UF pel següent semestre\\.",
                        "En cas de no superar la UF, el següent semestre s'han de tornar a fer totes les activitats (PAF i AC). No es guardarà cap nota parcial de la UF per el següent semestre."
                    ],
                    [
                        "La planificació establerta per a la UF\\{##itemUf\\[unitat formativa\\]##\\} és la següent: \\(veure ?:table:T11\\-\\{##itemUf\\[unitat formativa\\]##\\}:\\)\n::table:T11-\\{##itemUf\\[unitat formativa\\]##\\}\n  :title:Planificació UF\\{##itemUf\\[unitat formativa\\]##\\}\\.\n  :footer: \\<sup\\>\\*\\<\\/sup\\> Atenció: podeu mirar la data màxima de lliurament de les activitats qualificables a l'apartat de les dates clau",
                        "La planificació establerta per a la UF{##itemUf[unitat formativa]##} és la següent (veure :table:T11-{##itemUf[unitat formativa]##}:):\n::table:T11-{##itemUf[unitat formativa]##}\n  :title:Planificació UF{##itemUf[unitat formativa]##}\n  :footer: <sup>*</sup> Atenció: podeu mirar la data màxima de lliurament de les activitats qualificables a l'apartat de les dates clau."
                    ],
                    [
                        "\\| \\<WIOCCL:FOREACH  var\\=\"item_act\" array\\=\"\\{##activitatsAprenentatge##\\}\" filter\\=\"\\{##item_act\\[unitat\\]##\\}\\=\\=\\{##item_per\\[unitat\\]##\\}\\&\\&\\{##item_act\\[període\\]##\\}\\=\\=\\{##item_per\\[període\\]##\\}\"\\>\\{##item_act\\[eina\\]##\\}: \\{##item_act\\[descripció\\]##\\}",
                        "| <WIOCCL:FOREACH  var=\"item_act\" array=\"{##activitatsAprenentatge##}\" filter=\"{##item_act[unitat]##}=={##item_per[unitat]##}&&{##item_act[període]##}=={##item_per[període]##}\">- {##item_act[descripció]##}"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($status = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $status;
    }

}

<?php
/**
 * upgrader_5: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 4 a la estructura de la versión 5
 * @culpable rafael 21-06-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_5 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename = NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }// cerquem les dades de la paf1 i paf 2 i les qualificacions son de l'any 2019 i canviar-les per la mateixa data però 2020

                $dataProject['itinerarisRecomanats'] = [
                    [
                        "crèdit" => $dataProject['credit'],
                        "itinerariRecomanatS1" => $dataProject['itinerariRecomanatS1'],
                        "itinerariRecomanatS2" => $dataProject['itinerariRecomanatS2'],
                    ]
                ];

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                if ($filename===NULL) { //se supone que $filename se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                //Modificación del fichero de proyecto (el .txt que está en data/pages/ y que, originalmente, proviene de una plantilla)
                // Replace
                $aTokRep = [
                    ["PAF1",
                     "PAF 1",
                     "s"
                    ],
                    ["PAF2",
                     "PAF 2",
                     "s"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                // Replace
                $aTokRep = [
                    ["(Té una assignació de {##durada##} h de les {##duradaCicle##})(h del cicle de {##cicle##}\. El)( )( coodinador del crèdit és {##coordinador##}, els professors són: {##professors##}\.\n)",
                     "$1 $2$4",
                     "s"
                    ],
                    ["(Es recomana cursar-lo el)( <WIOCCL:IF condition=\"{##semestre##}==1\">{##itinerariRecomanatS1##}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##itinerariRecomanatS2##}<\/WIOCCL:IF>)( semestre)( de l'itinerari formatiu i suposa una \*\*dedicació setmanal mínima)( )( de {##dedicacio##})(h\.\*\*\n)",
                     "$1 semestre$2$4$6 $7",
                     "s"
                    ],
                    ["(Es segueix una metodologia)( )( basada en l’aprenentatge significatiu)( mitjançant el seguiment de les eines d'aprenentatge que se us proposen al següent apartat per assolir els objectius terminals \(OT\)\.\n)",
                     "$1$3,$4",
                     "s"
                    ],
                    ["(És recomanable realitzar totes les activitats proposades, tant les que avalua el professor i per tant ponderen a l'avalu)(ció contínua \(AC\), com les altres\. Les activitats i exercicis no avaluats disposen de solució)( que us permetrà portar el propi control del vostre aprenentatge\.\n)",
                     "$1a$2, fet$3",
                     "s"
                    ],
                    ["(Aquest <WIOCCL:IF condition=\"''crèdit'')(!)(={##tipusBlocCredit##}\">{##tipusBlocCredit##}<\/WIOCCL:IF>)( disposa també de jornades tècniques \(JT\) per ajudar-vos a consolidar parts pràctiques específiques\.)",
                     "$1=$3<WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF>$4",
                     "s"
                    ],
                    ["(A banda de les PAF)( caldrà )(realitzar)( EAF durant el semestre \(vegeu l'apartat d'avaluació\)\.\n)",
                     "$1,$2fer$4",
                     "s"
                    ],
                    ["\| {##item\[tipus\]##} \| {##item\[eina\]##} \| {##item\[opcionalitat\]##} \| <WIOCCL:IF condition=\"{##item\[puntuable\]##}==true\">si<\/WIOCCL:IF><WIOCCL:IF condition=\"{##item\[puntuable\]##}==false\">no<\/WIOCCL:IF> \|",
                     "| {#_UCFIRST(\"{##item[tipus]##}\")_#} | {##item[eina]##} | {##item[opcionalitat]##} | <WIOCCL:IF condition=\"{##item[puntuable]##}==true\">Sí</WIOCCL:IF><WIOCCL:IF condition=\"{##item[puntuable]##}==false\">No</WIOCCL:IF> |",
                     "s"
                    ],
                    ["En aquest {##tipusBlocCredit##} es descriuen els següents objectius terminals::",
                     "En aquest <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">{##tipusBlocCredit##}</WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc</WIOCCL:IF> es descriuen els següents objectius terminals:",
                     "s"
                    ],
                    ["(Cada )(U)(nitat )(D)(idàctica es divideix en diferents nuclis d'activitat:\n)",
                     "$1u$3d$5",
                     "s"
                    ],
                    ["(\^ Id \^  )(u)(nitat didàctica  .*?\^  )(d)(ata de publicació de l'enunciat  .*?\^ )(d)(ata de publicació del lliurament <WIOCCL:IF condition=\")({##hiHaSolucioPer.+?##})(==true\">\^ )(d)(ata de publicació de la solució <\/WIOCCL:IF>\^ )(d)(ata de publicació de la qualificació \^)",
                     "$1U$3D$5D$7$8$9D$11D$13",
                     "s"
                    ],
                    ["(\^ Id \^  )(i)(nscripció  \^  )(p)(ublicació llista provisional  \^ )(p)(ublicació llista definitiva \^ )(d)(ata de la jornada tècnica \(JT\) \^ )(d)(ata publicació de la qualificació \^)",
                     "$1I$3P$5P$7D$9D$11",
                     "s"
                    ],
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                // Replace
                $aTokRep = [
                    ["(\^  PAF  \^  Data)( de realització)(  \^  Publicació qualificació \^\n)",
                     "$1$3",
                     "s"
                    ],
                    ["(L'AC es )(realitza)( a distància, es concreta en:\n)",
                     "$1fa$3",
                     "s"
                    ],
                    ["(  \* El seguiment correcte de l’AC compromet l’alumne a )(realitzar)( les activitats proposades de manera individual, original i seguint les indicacions del professor\. Si no es respecten aquestes condicions, s’obtindrà una qualificació negativa\. Els treballs o tasques d'AC que siguin còpia literal \(total o parcial\) d'altres treballs, exercicis o fonts es consideraran suspesos amb una qualificació de 0\.\n)",
                     "$1fer$3",
                     "s"
                    ],
                    ["(  \* La qualificació de l'AC es té en compte tant a la PAF 1 com a la PAF 2 del mateix semestre)( però no es guarda d'un semestre a l'altre\.\n)",
                     "$1,$2",
                     "s"
                    ],
                    ["(  \* )(é)(s)(\*\*)( )(no presencial\*\*, es realitza a distància al llarg del <WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">{##tipusBlocCredit##}<\/WIOCCL:IF>)(\.)( \(consulteu)( )( dates clau a les taules :table:T06: i :table:T07:\)\.\n)",
                     "$1É$3 $4$6$8$10",
                     "s"
                    ],
                    ["(  \* )(la seva realització és \*\*obligatòria\*\*)( per aprovar el <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">{##tipusBlocCredit##}<\/WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">bloc<\/WIOCCL:IF>\.\n)",
                     "$1L'EAF és **obligatori**$3",
                     "s"
                    ],
                    ["(  \* )(é)(s de caràcter \[##TODO: escolliu \"individual\" o \"grupal\" segons sigui el cas##\]\.\n)",
                     "$1É$3",
                     "s"
                    ],
                    ["(  \* )(t)(é una ponderació en la \*\*qualificació final\*\*\.\n)",
                     "$1T$3",
                     "s"
                    ],
                    ["(  \* )(s)('ha d'obtenir una qualificació \*\*mínima de {##notaMinimaEAF##},00 sense arrodoniment\*\* per poder aplicar el càlcul de la qualificació final\.\n)",
                     "$1S$3",
                     "s"
                    ],
                    ["(L'EAF ha de ser lliurat dins els terminis fixats; el termini de lliurament és improrrogable\. El sistema no permet lliurar cap exercici passades les 23\.55 hores de la data prevista per al lliurament \(consulteu dates clau a les taules :table:T06: i :table:T07:\)\. Es recomana no esperar a)( darrer moment per evitar imprevistos\.\n)",
                     "$1l$2",
                     "s"
                    ],
                    ["(En ser)( part de l'avaluació final, l'EAF té dues convocatòries ordinàries cada semestre:)( )( EAF i recuperació EAF\. L'alumne pot:\n)",
                     "Com que és$2$4",
                     "s"
                    ],
                    ["(  \* Presentar-se a l'EAF i)( en cas de no superar-lo, presentar-se a la recuperació )(EAF del mateix semestre\. En cap cas es pot presentar a la recuperació )(EAF per pujar nota tenint l'EAF superat\.\n)",
                     "$1,$2de l'$3de l'$4",
                     "s"
                    ],
                    ["(  \* Presentar-se directament a la recuperació )(EAF\.\n)",
                     "$1de l'$2",
                     "s"
                    ],
                    ["(  \* La recuperació implica a tots els )(components)( de l'equip)( que han de lliurar de nou i conjuntament una mateixa tasca\.\n)",
                     "$1membres$3,$4",
                     "s"
                    ],
                    ["(  \* La recuperació individual fa referència al contingut de l'exercici i no a la recuperació de les competències de treball en equip que només es poden superar )(de nou, )(treballant novament en grup\.\n)",
                     "$1$3",
                     "s"
                    ],
                    ["(Jornad)(es)( presencial)(s)( procedimental)(s)( del <WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">{##tipusBlocCredit##}<\/WIOCCL:IF>:\n)",
                     "$1a$3$5$7",
                     "s"
                    ],
                    ["(  \* )(é)(s presencial\.\n)",
                     "$1É$3",
                     "s"
                    ],
                    ["(  \* )(é)(s obligatòria per aprovar la unitat didàctica\.\n)",
                     "$1É$3",
                     "s"
                    ],
                    ["(  \* )(es realitza)( al llarg del semestre \(consulteu dates clau)( )( a les taules :table:T08: i :table:T09:\)\.\n)",
                     "$1Es fa$3$5",
                     "s"
                    ],
                    ["(  \* )(e)(s confirma la identitat de l'alumne que la )(realitza)(\.\n)",
                     "$1E$3fa$5",
                     "s"
                    ],
                    ["(  \* )(t)(é una durada màxima de \[##TODO: X dies\. Cada dia té una durada màxima de X h\. \(si ho considereu oportú\)##\]\.\n)",
                     "$1T$3",
                     "s"
                    ],
                    ["(  \* )(s)('avalua numèricament )( de )(entre el 0 i el 10, amb dos decimals\.\n)",
                     "$1S$3$5",
                     "s"
                    ],
                    ["(  \* )(t)(é una ponderació en la \*\*qualificació final\*\* <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">del crèdit \(vegeu l'apartat \"Qualificació final QF\"<\/WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">de la UD \(vegeu l'apartat \"Qualificació final de cada UD\"<\/WIOCCL:IF>\)\.\n)",
                     "$1T$3",
                     "s"
                    ],
                    ["(  \* )(e)(s necessita una nota mínima de {##notaMinimaJT##},00 sense arrodoniment per poder aplicar el càlcul de la QF\.\n)",
                     "$1E$3",
                     "s"
                    ],
                    ["(  \* )(a)(puntar-se a l'espai indicat pel professor dins dels terminis establerts \(si ho considereu\)\n)",
                     "$1A$3",
                     "s"
                    ],
                    ["(Al final de cada semestre)( l’alumne s’ha de presentar a una prova d’avaluació final \(PAF\) \*\*presencial i obligatòria\*\* per aprovar el {##tipusBlocCredit##} \(consulteu dates clau a la taula::table:T10:\)\.\n)",
                     "$1,$2",
                     "s"
                    ],
                    ["(Per poder presentar-s)(e)(, \*\*cal confirmar\*\* l'assistència en el període establert\.\n)",
                     "$1'hi$3",
                     "s"
                    ],
                    ["(  \* )(t)(é una durada d'\*\*{##duradaPAF##}\*\*\n)",
                     "$1T$3",
                     "s"
                    ],
                    ["(  \* )(c)(onsistirà en diversos exercicis: frases V\/F, preguntes obertes, resolució d'un cas pràctic, etc\.\n)",
                     "$1C$3",
                     "s"
                    ],
                    ["(  \* )(s)('avalua numèricament del 0 al 10, amb dos decimals\.\n)",
                     "$1S$3",
                     "s"
                    ],
                    ["(  \* )(t)(é una ponderació a la )(Q)(ualificació )(F)(inal \(QF\) \(vegeu l'apartat <WIOCCL:IF condition=\"''crèdit''=={##tipusBlocCredit##}\">\"Qualificació final QF\"<\/WIOCCL:IF><WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">\"Qualificació final de cada bloc\"<\/WIOCCL:IF>\)\.\n)",
                     "$1T$3q$5f$7",
                     "s"
                    ],
                    ["(  \* )(s)('ha d'obtenir una \*\*nota mínima de {##notaMinimaPAF##},00 sense arrodoniment\*\* per poder aplicar el càlcul de la QF\.\n)",
                     "$1S$3",
                     "s"
                    ],
                    [
                     "(S'ofereixen dues convocatòries ordinàries cada semestre: PAF 1 i PAF 2)(\.)( \(consulteu dates clau a la taula::table:T10:\)\. L'alumne pot:\n)",
                     "$1$3",
                     "s"
                    ],
                    ["(  \* Presentar-se a la PAF 1 i)( en cas de no superar-la, presentar-se a la PAF 2 del mateix semestre \(2 setmanes després de la PAF 1\)\. En cap cas es pot presentar a la PAF 2)( )( per pujar nota tenint la PAF 1 superada\.\n)",
                     "$1,$2$4",
                     "s"
                    ],
                    ["(La convocatòria corresponent a la PAF 1 s'esgota tant si l'estudiant s'hi presenta com si no \(exceptuant)( )( que l'alumne hagi anul·lat la matrícula\)\. La convocatòria corresponent a PAF 2 només s'esgota si l'estudiant s'hi presenta\.\n)",
                     "$1$3",
                     "s"
                    ],
                    ["(La QF del bloc és numèrica de l'1 al 10, sense decimals; és la mitjana ponderada \(segons les hores)( de cada bloc)(\) de la qualificació de)(ls dos blocs \(B1 i B2\), )( sempre i quan estiguin superats cadascun d'ells sense arrodoniment amb un 5,00 com a mínim\.\n)",
                     "$1$3 cada bloc,$5",
                     "s"
                    ],
                    ["(La planificació establerta per a la UD{#_SUMA\({##ind##}, 1\)_#} és la següent)(:)( \(veure:table:T11-{##itemUD\[unitat didàctica\]##}:\))",
                     "$1$3:",
                     "s"
                    ],
                    ["(  :title:Planificació UD{##itemUD\[unitat didàctica\]##})(\.)",
                     "$1",
                     "s"
                    ],
                    ["(  :footer: <sup>\*<\/sup> Atenció: podeu mirar la data màxima de lliurament de les activitats qualificables a l'apartat de les dates clau)",
                     "$1.",
                     "s"
                    ],
                    ["(\| <WIOCCL:FOREACH  var=\"item_act\" array=\"{##activitatsPerUD##}\" filter=\"{##item_act\[nucli activitat\]##}=={##itemu\[nucli activitat\]##}&&{##item_act\[període\]##}=={##itemu\[període\]##}\">)({##item_act\[eina\]##}\: )({##item_act\[descripció\]##} .*? <\/WIOCCL:FOREACH>     \|\|\n)",
                     "$1-$3",
                     "s"
                    ]
                ];

                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                $status = !empty($doc);
        }
        return $status;
    }

}

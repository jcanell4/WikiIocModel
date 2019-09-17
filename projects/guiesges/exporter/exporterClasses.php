<?php
/**
 * projecte 'guiesges'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");
require_once DOKU_PLUGIN."wikiiocmodel/exporter/BasicExporterClasses.php";

class renderObject extends BasicRenderObject {

    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->cfgExport->rendererPath = dirname(realpath(__FILE__));
    }

    public function cocinandoLaPlantillaConDatos($param) {
        return $param;
    }
}

/**
 * class IocTcPdf
 */
require_once (DOKU_INC.'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends TCPDF {
    private $header_logo_height = 10;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->header_logo_width = 8;
        $this->SetMargins(20, 20);
        $this->head =20;
        $this->header_font = "helvetica";
    }

    //Page header
    public function Header() {
        if ($this->PageNo() === 1) {
            return;
        }

        $margins = $this->getMargins();

        // Logo
        $image_file = K_PATH_IMAGES.$this->header_logo;
        $this->Image($image_file, $margins['left'], 5, $this->header_logo_width, $this->header_logo_height, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $headerfont = $this->getHeaderFont();
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        $header_x = $margins['left'] + $margins['padding_left'] + ($this->header_logo_width * 1.1);
        $header_w = 105 - $header_x;

        $this->SetTextColorArray($this->header_text_color);
        // header title
        $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
        $this->SetX($header_x);
        $this->MultiCell($header_w, $cell_height, $this->header_title, 0, 'L', 0, 0, "", "", true);

        // header string
        $this->MultiCell(0, $cell_height, $this->header_string, 0, 'R', 0, 0, "", "", true);
        $this->Line($margins['left'], 19, $this->getPageWidth()-$margins['right'], 19);
    }

    // Page footer
    public function Footer() {
        if ($this->PageNo() === 1) {
            return;
        }

        $margins = $this->getMargins();
        $footerfont = $this->getFooterFont();
        $cell_height = $this->getCellHeight($footerfont[2]) / 2;
        $y_position = -($cell_height*2 + 15);

        $this->SetFont($footerfont[0], $footerfont[1], $footerfont[2]);
        $this->SetY($y_position);   //Position from bottom

        $codi = " codi: ".$this->peu['codi'];
        $versio = " versió: ".$this->peu['versio'];
        $w1 = max(10, strlen($codi), strlen($versio)) * 2;
        $w1 = min(30, $w1);
        $w2 = 28;

        $this->MultiCell($w1, $cell_height, $codi, 1, 'L', 0, 1, "", "", true, 0, false, true, $cell_height, 'M');
        $this->MultiCell($w1, $cell_height, $versio, 1, 'L', 0, 0, "", "", true, 0, false, true, $cell_height, 'M');
        $this->SetY($y_position);
        $titol_w = $this->getPageWidth()-$margins['right']-($w1+$w2-13);
        $this->MultiCell($titol_w, $cell_height*2, $this->peu['titol'], 1, 'C', 0, 0, "", "", true, 0, false, true, $cell_height*2, 'M');
        $page_number = 'pàgina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages();
        $this->Cell($w2, $cell_height*2, $page_number, 1, 0, 'R', false, '', 0, false, 'T', 'M'); // Page number
    }

    public function setHeaderData($ln='', $lw=0, $lh=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_height = $lh;
    }

    public function setFooterData($data, $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setFooterData($tc, $lc);
        $this->peu = $data;
    }
}

class StaticPdfRenderer extends BasicStaticPdfRenderer {

    /**
     * params = hashArray:{
     *      id: string  //id del projecte
     *      path_templates:string,  // directori on es troben les plantilles latex usades per crear el pdf
     *      tmp_dir: string,    //directori temporal on crear el pdf
     *      lang: string  // idioma usat (CA, EN, ES, ...)
     *      mode: string  // pdf o zip
     *      data: hashArray:{
     *              titol:array os string    // linies de títol del document (cada ítem és una línia)
     *              contingut: string   //contingut latex ja rendaritzat
     */
    public static function renderDocument($params, $output_filename="") {
        StaticPdfRenderer::resetStaticDataRender();
        if (empty($output_filename)){
            $output_filename = str_replace(":", "_", $params["id"]);
        }

        $iocTcPdf = new IocTcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false);
        $iocTcPdf->SetCreator("DOKUWIKI IOC");

        $iocTcPdf->setHeaderData($params["data"]["header"]["logo"], $params["data"]["header"]["wlogo"], $params["data"]["header"]["hlogo"], $params["data"]["header"]["ltext"], $params["data"]["header"]["rtext"]);
        $iocTcPdf->setFooterData($params["data"]["peu"]);

        $iocTcPdf->setStartingPageNumber(0);

        // set header and footer fonts
        $iocTcPdf->setHeaderFont(Array(self::$headerFont, '', self::$headerFontSize));
        $iocTcPdf->setFooterFont(Array(self::$footerFont, '', self::$footerFontSize));

        $iocTcPdf->SetDefaultMonospacedFont("Courier");
        $iocTcPdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $iocTcPdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set margins
        $iocTcPdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $iocTcPdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $iocTcPdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //primera pàgina
        $iocTcPdf->AddPage();
        $iocTcPdf->SetX(100);
        $iocTcPdf->SetY($y=100);

        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 35);
        for ($i=0; $i<2; $i++){
            $iocTcPdf->Cell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }
        $iocTcPdf->SetY($y+=100);

        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 20);
        for ($i=2; $i<count($params["data"]["titol"]); $i++){
            $iocTcPdf->Cell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }

        $iocTcPdf->AddPage();

        $len = count($params["data"]["contingut"]);
        for ($i=0; $i<$len; $i++){
            self::resolveReferences($params["data"]["contingut"][$i]);
        }
        for ($i=0; $i<$len; $i++){
            self::renderHeader($params["data"]["contingut"][$i], $iocTcPdf);
        }

        // add a new page for TOC
        $iocTcPdf->addTOCPage();

        // write the TOC title
        $iocTcPdf->SetFont('Times', 'B', 16);
        $iocTcPdf->MultiCell(0, 0, 'Índex', 0, 'C', 0, 1, '', '', true, 0);
        $iocTcPdf->Ln();

        // add a simple Table Of Content at first page
        $iocTcPdf->SetFont('Times', '', 12);
        $iocTcPdf->addTOC(2, 'courier', '.', 'INDEX', 'B', array(128,0,0));

        // end of TOC page
        $iocTcPdf->endTOCPage();

        $iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        return TRUE;
    }

}

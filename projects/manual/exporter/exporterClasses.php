<?php
/**
 * projecte 'manual'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class renderObject extends BasicRenderObject {

    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->cfgExport->rendererPath = dirname(realpath(__FILE__));
    }

}

/**
 * class IocTcPdf
 */
require_once (DOKU_INC.'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends TCPDF {
    private $header_logo_height = 10;

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->header_logo_width = 8;
        $this->SetMargins(20, 20);
        $this->head = 20;
        $this->header_font = "helvetica";
    }

    //Page header
    public function Header() {
        if ($this->PageNo() == 1) return;

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
        if ($this->PageNo() == 1) return;

        $this->SetY(-15);   // Position at 15 mm from bottom
        $this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
        $this->Cell(0, 10, 'pàgina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M'); // Page number
    }

    public function setHeaderData($ln='', $lw=0, $lh=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_height = $lh;
    }
}

class StaticPdfRenderer extends BasicStaticPdfRenderer {

    /**
     * params = hashArray:{
     *      string 'id'             //id del projecte
     *      string 'path_templates' //directori on es troben les plantilles latex usades per crear el pdf
     *      string 'tmp_dir'        //directori temporal on crear el pdf
     *      string 'lang'           //idioma usat (CA, EN, ES, ...)
     *      string 'mode'           //pdf o zip
     *      int 'max_img_size'
     *      hashArray 'data': [
     *              array d'strings 'titol'     // linies de títol del document (cada ítem és una línia)
     *              string          'contingut' //contingut latex ja rendaritzat
     */
    public static function renderDocument($params, $output_filename="") {
        if (empty($output_filename)) {
            $output_filename = str_replace(":", "_", $params["id"]);
        }

        $iocTcPdf = new IocTcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false);
        $iocTcPdf->SetCreator("DOKUWIKI IOC");
        $iocTcPdf->setHeaderData($params["data"]["header"]["logo"], $params["data"]["header"]["wlogo"], $params["data"]["header"]["hlogo"], $params["data"]["header"]["ltext"], $params["data"]["header"]["rtext"]);
        self::setMaxImgSize($params['max_img_size']);

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
        $iocTcPdf->SetY($y=80);

        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 35);
        $iocTcPdf->MultiCell(0, 0, $params["data"]["titol"]["titol"], 0, 'L');
        $iocTcPdf->SetY($y+=30);
        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 25);
        $iocTcPdf->MultiCell(0, 0, $params["data"]["titol"]["subtitol"], 0, 'L');
        $iocTcPdf->SetY($y+=80);
        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 15);
        if(!empty($params["data"]["titol"]['autor'])){
            $iocTcPdf->Cell(0, 0, $params["data"]["titol"]['autor'], 0, 1);
        }
        if(!empty($params["data"]["titol"]['entitatResponsable'])){
            $iocTcPdf->Cell(0, 0, $params["data"]["titol"]['entitatResponsable'], 0, 1);
        }
        $iocTcPdf->Cell(0, 0, $params["data"]["titol"]['data'], 0, 1);

        //continguts
        $iocTcPdf->AddPage();
        if (!empty($params["data"]["contingut"])) {
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                self::resolveReferences($itemsDoc);
            }
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                self::renderHeader($itemsDoc, $iocTcPdf);
            }
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

<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class renderFile extends BasicRenderFile {

    public function process($data, $alias="") {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $startedHere = true;
        }
        $_SESSION['styletype'] = $this->cfgExport->styletype;
        $html = parent::process($data, $alias);
        return $html;
    }
}

/**
 * class IocTcPdf
 */
require_once (DOKU_INC.'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends BasicIocTcPdf {

    public function __construct(TcPdfStyle &$stile) {
        parent::__construct($stile);
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

class PdfRenderer extends BasicPdfRenderer {

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
    public function renderDocument($params, $output_filename="") {
        parent::renderDocument($params, $output_filename);
        
        //primera pàgina
        $this->iocTcPdf->AddPage();
        $this->iocTcPdf->SetX(100);
        $this->iocTcPdf->SetY($y=80);

        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 35);
        $this->iocTcPdf->MultiCell(0, 0, $params["data"]["titol"]["titol"], 0, 'L');
        $this->iocTcPdf->SetY($y+=30);
        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 25);
        $this->iocTcPdf->MultiCell(0, 0, $params["data"]["titol"]["subtitol"], 0, 'L');
        $this->iocTcPdf->SetY($y+=80);
        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 15);
        if(!empty($params["data"]["titol"]['autor'])){
            $this->iocTcPdf->Cell(0, 0, $params["data"]["titol"]['autor'], 0, 1);
        }
        if(!empty($params["data"]["titol"]['entitatResponsable'])){
            $this->iocTcPdf->Cell(0, 0, $params["data"]["titol"]['entitatResponsable'], 0, 1);
        }
        $this->iocTcPdf->Cell(0, 0, $params["data"]["titol"]['data'], 0, 1);

        //continguts
        $this->iocTcPdf->AddPage();
        if (!empty($params["data"]["contingut"])) {
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                $this->resolveReferences($itemsDoc);
            }
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                $this->renderHeader($itemsDoc, $this->iocTcPdf);
            }
        }

        // add a new page for TOC
        $this->renderToc();

        $this->iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        return TRUE;
    }
}

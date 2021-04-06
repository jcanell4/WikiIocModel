<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
/**
 * class IocTcPdf
 */
class IocTcPdf extends BasicIocTcPdf {
    private $peu = array();
    public function __construct(TcPdfStyle &$stile) {
        parent::__construct($stile);
    }

    //Page header
    public function Header() {
        $margins = $this->getMargins();

        // Logo
        $image_file = $this->header_logo;
        $this->Image($image_file, $margins['left'], 5, $this->header_logo_width, $this->header_logo_height, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $headerfont = $this->getHeaderFont();
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        $header_x = $margins['left'] + $margins['padding_left'] + ($this->header_logo_width * 1.1);
        $header_w = 95 - $header_x;

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
        //$margins = $this->getMargins();
        $footerfont = $this->getFooterFont();
        $cell_height = $this->getCellHeight($footerfont[2]) / 2;
        $y_position = -($cell_height*2 + 15);

        $this->SetFont($footerfont[0], $footerfont[1], $footerfont[2]);
        $this->SetY($y_position);   //Position from bottom

        $cicle = " cicle: ".$this->peu['cicle'];
        $modulId = " modulId: ".$this->peu['modulId'];
        $w1 = max(10, strlen($cicle), strlen($modulId)) * 2;
        $w1 = min(30, $w1);
        $w2 = 22;

        $this->MultiCell($w1, $cell_height, $cicle, 1, 'L', 0, 1, "", "", true, 0, false, true, $cell_height, 'M');
        $this->MultiCell($w1, $cell_height, $modulId, 1, 'L', 0, 0, "", "", true, 0, false, true, $cell_height, 'M');
        $this->SetY($y_position);
        $titol_w = $this->getPageWidth()-($w1+$w2);
        $this->MultiCell($titol_w, $cell_height*2, $this->peu['departament'], 1, 'C', 0, 0, "", "", true, 0, false, true, $cell_height*2, 'M');
        // codi de pàgina actual: $this->getAliasNumPage() = {:pnp:} -> problema: ocupa 7 caracters en el render
        // codi de total pàgines: $this->getAliasNbPages() = {:ptp:} -> es calcula l'espai ocupat abans d'obtenir el valor real
        $page_number = "pàgina ".$this->getPage()."/".$this->getAliasNbPages();
        $this->MultiCell($w2, $cell_height*2, $page_number, 1, 'R', 0, 1, "", "", true, 0, false, true, $cell_height*2, 'M');
    }

    public function setHeaderData($ln='', $lw=0, $lh=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_height = $lh;
    }

    public function setFooterDataLocal($data, $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setFooterData($tc, $lc);
        $this->peu = $data;
    }
 }

class PdfRenderer extends BasicPdfRenderer {

    /**
     * params = hashArray:{
     *      string 'id'             //id del projecte
     *      string 'tmp_dir'        //directori temporal on crear el pdf
     *      string 'lang'           //idioma usat (CA, EN, ES, ...)
     *      string 'mode'           //pdf o zip
     *      hashArray 'data': [
     *              array  'header'    //dades de la capçalera de pàgina
     *              array  'peu'       //dades del peu de pàgina
     *              string 'contingut' //contingut latex ja rendaritzat
     */
    public function renderDocument($params, $output_filename="") {
        parent::renderDocument($params, $output_filename);

        $this->iocTcPdf->setFooterDataLocal($params["data"]["peu"]);

        //pàgina de continguts
        $this->iocTcPdf->AddPage();
        if (!empty($params["data"]["contingut"])) {
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                $this->resolveReferences($itemsDoc);
            }
            foreach ($params["data"]["contingut"] as $itemsDoc) {
                $this->renderHeader($itemsDoc, $this->iocTcPdf);
            }
        }

        $this->iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        return TRUE;
    }

}

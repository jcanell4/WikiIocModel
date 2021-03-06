<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
class IocTcPdf extends BasicIocTcPdf {
    public function __construct(TcPdfStyle &$stile) {
        parent::__construct($stile);
    }

    //Page header
    public function Header() {
        if ($this->PageNo() === 1) {
            return;
        }

        $margins = $this->getMargins();

        // Logo
        $image_file = $this->header_logo;
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

    public function setFooterDataLocal($data, $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setFooterData($tc, $lc);
        $this->peu = $data;
    }
}

class PdfRenderer extends BasicPdfRenderer {

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
    public function renderDocument($params, $output_filename="") {
        $this->resetDataRender();
        parent::renderDocument($params, $output_filename);

        $this->iocTcPdf->setFooterDataLocal($params["data"]["peu"]);

        $this->iocTcPdf->setStartingPageNumber(0);

        //primera pàgina
        $this->iocTcPdf->AddPage();
        $this->iocTcPdf->SetX(100);
        $this->iocTcPdf->SetY($y=100);

        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 35);
        for ($i=0; $i<2; $i++){
            $this->iocTcPdf->Cell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }
        $this->iocTcPdf->SetY($y+=100);

        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 20);
        for ($i=2; $i<count($params["data"]["titol"]); $i++){
            $this->iocTcPdf->Cell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }

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


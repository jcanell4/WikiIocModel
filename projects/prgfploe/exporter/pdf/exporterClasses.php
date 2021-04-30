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
    private $titol = array();

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
        $footerfont = $this->getFooterFont();
        $cell_height = $this->getCellHeight($footerfont[2]) / 2;
        $y_position = -($cell_height*2 + 15);

        $this->SetFont($footerfont[0], $footerfont[1], $footerfont[2]);
        $this->SetY($y_position);   //Position from bottom

        // Logo
        $f = $this->peu['logo'];
        $w = $this->peu['wlogo'];
        $h = $this->peu['hlogo'];
        $w0 = $w+4;
        $x = $this->GetX()+1;
        $y = $this->GetY()+1;
        $this->SetTextColor(255,255,255);
        $this->MultiCell($w0, $cell_height*2, $this->Image($f,$x,$y,$w,$h,'JPG','','',true,300,'',false,false,0,['CM']), 0, 'L', 0, 0, "", "", true, 0, false, true, $cell_height*2, 'M');
        $this->SetTextColor(0);

        $codi = " codi: ".$this->peu['codi'];
        $versio = " versió: ".$this->peu['versió'];
        $w1 = max(10, strlen($codi), strlen($versio)) * 2;
        $w1 = min(30, $w1);
        $w2 = 22;
        $this->MultiCell($w1, $cell_height, $codi, 1, 'L', 0, 1, "", "", true, 0, false, true, $cell_height, 'M');
        $this->SetX($this->GetX()+$w0);
        $this->MultiCell($w1, $cell_height, $versio, 1, 'L', 0, 0, "", "", true, 0, false, true, $cell_height, 'M');

        $this->SetY($y_position);
        $titol_w = $this->getPageWidth()-($w0+$w1+$w2);
        $this->MultiCell($titol_w, $cell_height*2, "Programacions de cicles formatius (LOGSE)", 1, 'C', 0, 0, "", "", true, 0, false, true, $cell_height*2, 'M');

        // codi de pàgina actual: $this->getAliasNumPage() = {:pnp:} -> problema: ocupa 7 caracters en el render
        // codi de total pàgines: $this->getAliasNbPages() = {:ptp:} -> es calcula l'espai ocupat abans d'obtenir el valor real
        $page_number = "pàgina ".$this->getPage()."/".$this->getAliasNbPages();
        $this->MultiCell($w2, $cell_height*2, $page_number, 1, 'R', 0, 1, "", "", true, 0, false, true, $cell_height*2, 'M');
    }

    public function setHeaderData($ln='', $lw=0, $lh=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_height = $lh;
    }

    public function setFooterDataLocal($peu, $titol, $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setFooterData($tc, $lc);
        $this->peu = $peu;
        $this->titol = $titol;
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

        //primera pàgina
        $this->iocTcPdf->AddPage();
        $this->iocTcPdf->SetX(100);
        $this->iocTcPdf->SetY($y=80);
        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 25);
        $this->iocTcPdf->Write(25, "Programacions de cicles formatius", '', false, "L");

        $this->iocTcPdf->SetY($y+=60);
        $this->iocTcPdf->SetFont($this->firstPageFont, 'B', 15);
        $titol = $params['data']['titol'];
        $text = "Departament: {$titol['departament']}\n"
              . "Cicle formatiu: {$titol['cicle']}\n"
              . "Mòdul {$titol['modulId']}: {$titol['modul']}\n"
              . "Hores totals: {$titol['hores']}";
        $this->iocTcPdf->Write(8, $text, '', false, "L");

        //peu de pàgina
        $this->iocTcPdf->setFooterDataLocal($params["data"]["peu"], $params['data']['titol']);

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

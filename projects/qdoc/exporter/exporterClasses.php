<?php
/**
 * projecte 'qdoc'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");
require_once DOKU_PLUGIN."wikiiocmodel/exporter/BasicExporterClasses.php";

class renderObject extends BasicRenderObject {

}

/**
 * class IocTcPdf
 */
require_once (DOKU_INC.'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends TCPDF{
    private $header_logo_height = 10;
    private $peu = array();

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->header_logo_width = 8;
        $this->SetMargins(20, 20);
        $this->head = 20;
        $this->header_font = "helvetica";
    }

    //Page header
    public function Header() {
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
        $w2 = 22;

        $this->MultiCell($w1, $cell_height, $codi, 1, 'L', 0, 1, "", "", true, 0, false, true, $cell_height, 'M');
        $this->MultiCell($w1, $cell_height, $versio, 1, 'L', 0, 0, "", "", true, 0, false, true, $cell_height, 'M');
        $this->SetY($y_position);
        $titol_w = $this->getPageWidth()-$margins['right']-($w1+$w2-5);
        $this->MultiCell($titol_w, $cell_height*2, $this->peu['titol'], 1, 'C', 0, 0, "", "", true, 0, false, true, $cell_height*2, 'M');
        $page_number = "pàgina ".$this->getPage()."/".$this->getNumPages()." ";
        $this->MultiCell($w2, $cell_height*2, $page_number, 1, 'R', 0, 1, "", "", true, 0, false, true, $cell_height*2, 'M');
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

class StaticPdfRenderer{
    static $tableCounter = 0;
    static $tableReferences = array();
    static $figureCounter = 0;
    static $figureReferences = array();
    static $headerNum = array(0,0,0,0,0,0);
    static $headerFont = "helvetica";
    static $headerFontSize = 10;
    static $footerFont = "helvetica";
    static $footerFontSize = 8;
    static $firstPageFont = "Times";
    static $pagesFont = "helvetica";
    static $state = ["table" =>["type" => "table"]];

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
    public static function renderDocument($params, $output_filename="") {
        if (empty($output_filename)) {
            $output_filename = str_replace(":", "_", $params["id"]);
        }

        $iocTcPdf = new IocTcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false);
        $iocTcPdf->SetCreator("DOKUWIKI IOC");
        $iocTcPdf->setHeaderData($params["data"]["header"]["logo"], $params["data"]["header"]["wlogo"], $params["data"]["header"]["hlogo"], $params["data"]["header"]["ltext"], $params["data"]["header"]["rtext"]);
        $iocTcPdf->setFooterData($params["data"]["peu"]);

        // set header and footer fonts
        $iocTcPdf->setHeaderFont(Array(self::$headerFont, '', self::$headerFontSize));
        $iocTcPdf->setFooterFont(Array(self::$footerFont, '', self::$footerFontSize));

        // set default monospaced font
        $iocTcPdf->SetDefaultMonospacedFont("Courier");

        // set margins
        $iocTcPdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $iocTcPdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $iocTcPdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $iocTcPdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $iocTcPdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //pàgina de continguts
        $iocTcPdf->AddPage();

        $len = count($params["data"]["contingut"]);
        for ($i=0; $i<$len; $i++) {
            self::resolveReferences($params["data"]["contingut"][$i]);
        }
        for ($i=0; $i<$len; $i++) {
            self::renderHeader($params["data"]["contingut"][$i], $iocTcPdf);
        }

        $iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        return TRUE;
    }

    private static function getHeaderCounter($level) {
        $ret = "";
        for ($i=0; $i<=$level; $i++) {
            $ret .= self::$headerNum[$i].".";
        }
        return $ret." ";
    }

    private static function incHeaderCounter($level) {
        self::$headerNum[$level]++;
        for ($i=$level+1; $i<count(self::$headerNum); $i++) {
            self::$headerNum[$i]=0;
        }
        return self::getHeaderCounter($level);
    }

    private static function resolveReferences($content) {
        if ($content["type"]===TableFrame::TABLEFRAME_TYPE_TABLE || $content["type"]===TableFrame::TABLEFRAME_TYPE_ACCOUNTING) {
            self::$tableCounter++;
            self::$tableReferences[$content["id"]] = self::$tableCounter;
        }elseif ($content["type"]===FigureFrame::FRAME_TYPE_FIGURE) {
            self::$figureCounter++;
            self::$figureReferences[$content["id"]] = self::$figureCounter;
        }
        for ($i=0; $i<count($content["content"]); $i++) {
            self::resolveReferences($content["content"][$i]);
        }
        for ($i=0; $i<count($content["children"]); $i++) {
            self::resolveReferences($content["children"][$i]);
        }
    }

    private static function renderHeader($header, IocTcPdf &$iocTcPdf) {
        $level = $header["level"]-1;
        $iocTcPdf->SetFont('Times', 'B', 12);
        $title = self::incHeaderCounter($level).$header["title"];
        $iocTcPdf->Bookmark($title, $level, 0);
        $iocTcPdf->Ln(5);
        $iocTcPdf->Cell(0, 0, $title, 0,1, "L");
        $iocTcPdf->Ln(3);
        for ($i=0; $i<count($header["content"]); $i++) {
            self::renderContent($header["content"][$i], $iocTcPdf);
        }
        for ($i=0; $i<count($header["children"]); $i++) {
            self::renderHeader($header["children"][$i], $iocTcPdf);
        }
    }

    private static function renderContent($content, IocTcPdf &$iocTcPdf, $pre="", $post="") {
        $iocTcPdf->SetFont('helvetica', '', 10);
        if ($content['type'] === FigureFrame::FRAME_TYPE_FIGURE) {
            self::getFrameContent($content, $iocTcPdf);
        }
//        elseif ($content['type'] === StructuredNodeDoc::PARAGRAPH_TYPE && $content['content'][0]['type'] === ImageNodeDoc::IMAGE_TYPE) {
//            self::renderImage($content, $iocTcPdf);
//        }
//        elseif ($content['type'] === ImageNodeDoc::IMAGE_TYPE) {
//            self::renderImage($content, $iocTcPdf);
//        }
//        elseif ($content['type'] === SmileyNodeDoc::SMILEY_TYPE) {
//            self::renderSmiley($content, $iocTcPdf);
//        }
        else {
            $iocTcPdf->writeHTML(self::getContent($content), TRUE, FALSE);
        }

        if ($content["type"] == StructuredNodeDoc::ORDERED_LIST_TYPE
                || $content["type"] == StructuredNodeDoc::UNORDERED_LIST_TYPE
                || $content["type"] == StructuredNodeDoc::PARAGRAPH_TYPE) {
            $iocTcPdf->Ln(3);
        }
    }

    private static function getFrameContent($content, IocTcPdf &$iocTcPdf) {
        switch ($content['type']) {
            case ImageNodeDoc::IMAGE_TYPE:
                self::renderImage($content, $iocTcPdf);
                break;

            case FigureFrame::FRAME_TYPE_FIGURE:
                $center = "style=\"margin:auto; text-align:center;";
                if ($content["hasBorder"]) {
                    $style = $center . " border:1px solid gray;";
                }
                $ret = "<div $style nobr=\"true\">";
                if ($content['title']) {
                    $ret .= "<p $center font-weight:bold;\">Figura ".self::$figureReferences[$content['id']].". ".$content['title']."</p>";
                }
                $iocTcPdf->writeHTML($ret, TRUE, FALSE);
                $ret = self::getFrameStructuredContent($content, $iocTcPdf);
                if ($content['footer']) {
                    if ($content['title']) {
                        $ret .= "<p $center font-size:80%;\">".$content['footer']."</p>";
                    }else {
                        $ret .= "<p $center font-size:80%;\">Figura ".self::$figureReferences[$content['id']].". ".$content['footer']."</p>";
                    }
                }
                $ret .= "</div>";
                $iocTcPdf->writeHTML($ret, TRUE, FALSE);
                break;

            default:
                self::getFrameStructuredContent($content, $iocTcPdf);
                break;
        }
        return "";
    }

    private static function getFrameStructuredContent($content, IocTcPdf &$iocTcPdf) {
        $ret = "";
        $limit = count($content['content']);
        for ($i=0; $i<$limit; $i++) {
            $ret .= self::getFrameContent($content['content'][$i], $iocTcPdf);
        }
        return $ret;
    }

    private static function renderSmiley($content, IocTcPdf &$iocTcPdf) {
        preg_match('/\.(.+)$/', $content['src'], $match);
        $ext = ($match) ? $match[1] : "JPG";
        $iocTcPdf->Image($content['src'], '', '', 0, 0, $ext, '', 'T');
    }

    private static function renderImage($content, IocTcPdf &$iocTcPdf) {
        preg_match('/\.(.+)$/', $content['src'], $match);
        $ext = ($match) ? $match[1] : "JPG";
        //càlcul de les dimensions de la imatge
        list($w0, $h0) = getimagesize($content['src']);
        $w = ($content['width']) ? $content['width'] / 5 : 0;
        if ($w) $pcw = $w / $w0; //percentatge de tamany
        $h = ($content['height']) ? $content['height'] / 5 : $h0 * $pcw;
        //inserció de la imatge
        $iocTcPdf->Image($content['src'], '', '', $w, $h, $ext, '', 'T', '', '', 'C');
        $iocTcPdf->SetY($iocTcPdf->GetY() + $h); //correcció de la coordinada Y desprès de insertar la imatge
        //inserció del títol a sota de la imatge
        $center = "style=\"margin:auto; text-align:center;";
        $text = "<p $center font-size:80%;\">{$content['title']}</p>";
        $iocTcPdf->writeHTML($text, TRUE, FALSE);
    }

    private static function getContent($content) {
        $char = "";
        $ret = "";
        switch ($content["type"]) {
            case ListItemNodeDoc::LIST_ITEM_TYPE:
                $ret = '<li style="text-align:justify;">'.self::getStructuredContent($content)."</li>";
                break;
            case StructuredNodeDoc::DELETED_TYPE:
                $ret = "<del>".self::getStructuredContent($content)."</del>";
                break;
            case StructuredNodeDoc::EMPHASIS_TYPE:
                $ret = "<em>".self::getStructuredContent($content)."</em>";
                break;
            case StructuredNodeDoc::FOOT_NOTE_TYPE:
                break;
            case StructuredNodeDoc::LIST_CONTENT_TYPE:
                break;
            case StructuredNodeDoc::MONOSPACE_TYPE:
                $ret = "<code>".self::getStructuredContent($content)."</code>";
                break;
            case StructuredNodeDoc::ORDERED_LIST_TYPE:
                $ret = "<ol>".self::getStructuredContent($content)."</ol>";
                break;
            case StructuredNodeDoc::PARAGRAPH_TYPE:
                $ret = '<p style="text-align:justify;">'.self::getStructuredContent($content).'</p>';
                break;
            case StructuredNodeDoc::SINGLEQUOTE_TYPE:
                $char = "'";
            case StructuredNodeDoc::DOUBLEQUOTE_TYPE:
                $char = empty($char) ? "\"" : $char;
                $ret = $char.self::getStructuredContent($content).$char;
                break;
            case StructuredNodeDoc::QUOTE_TYPE:
                $ret = "<blockquote>".self::getStructuredContent($content)."</blockquote>";
                break;
            case StructuredNodeDoc::STRONG_TYPE:
                $ret = "<strong>".self::getStructuredContent($content)."</strong>";
                break;
            case StructuredNodeDoc::SUBSCRIPT_TYPE:
                $ret = "<sub>".self::getStructuredContent($content)."</sub>";
                break;
            case StructuredNodeDoc::SUPERSCRIPT_TYPE:
                $ret = "<sup>".self::getStructuredContent($content)."</sup>";
                break;
            case StructuredNodeDoc::UNDERLINE_TYPE:
                $ret = "<u>".self::getStructuredContent($content)."</u>";
                break;
            case StructuredNodeDoc::UNORDERED_LIST_TYPE:
                $ret = "<ul>".self::getStructuredContent($content)."</ul>";
                break;
            case SpecialBlockNodeDoc::HIDDENCONTAINER_TYPE:
                $ret = '<span style="color:gray; font-size:80%;">' . self::getStructuredContent($content) . '</span>';
                break;

            case ImageNodeDoc::IMAGE_TYPE:
                if (preg_match("|\.gif$|", $content["src"], $t)) {
                    //El formato GIF no está soportado
                    $ret = " {$content["title"]} ";
                }else {
                    preg_match("|.*".DOKU_BASE."(.*)|", $content["src"], $t);
                    $ret = ' <img src="'.DOKU_BASE.$t[1].'"';
                    if ($content["title"])
                        $ret.= ' alt="'.$content["title"].'"';
                    if ($content["width"])
                        $ret.= ' width="'.$content["width"].'"';
                    if ($content["height"])
                        $ret.= ' height="'.$content["height"].'"';
                    $ret.= '> ';
                }
                break;

            case SmileyNodeDoc::SMILEY_TYPE:
                preg_match("|.*".DOKU_BASE."(.*)|", $content["src"], $t);
                $ret = ' <img src="'.DOKU_BASE.$t[1].'" alt="smiley" height="8" width="8"> ';
                break;

            case SpecialBlockNodeDoc::NEWCONTENT_TYPE:
                //$ret = '<div style="border:1px solid red; padding:0 10px; margin:5px 0;">' . self::getStructuredContent($content) . "</div>";
            case SpecialBlockNodeDoc::BLOCVERD_TYPE:
                //$ret = '<span style="background-color:lightgreen;">' . self::getStructuredContent($content) . '</span>';
            case SpecialBlockNodeDoc::PROTECTED_TYPE:
            case SpecialBlockNodeDoc::SOL_TYPE:
            case SpecialBlockNodeDoc::SOLUCIO_TYPE:
            case SpecialBlockNodeDoc::VERD_TYPE:
                $ret = self::getStructuredContent($content);
                break;

            case TableFrame::TABLEFRAME_TYPE_TABLE:
            case TableFrame::TABLEFRAME_TYPE_ACCOUNTING:
                $ret = "<div nobr=\"true\">";
                if ($content["title"]) {
                    $ret .= "<h4 style=\"text-align:center;\"> Taula ".self::$tableReferences[$content["id"]].". ".$content["title"]."</h4>";
                }
                $ret .= self::getStructuredContent($content);
                if ($content["footer"]) {
                    if ($content["title"]) {
                        $ret .= "<p style=\"text-align:justify; font-size:80%;\">".$content["footer"]."</p>";
                    }else {
                        $ret .= "<p style=\"text-align:justify; font-size:80%;\"> Taula ".self::$tableReferences[$content["id"]].". ".$content["footer"]."</p>";
                    }
                }
                $ret .= "</div>";
                break;
            case TableNodeDoc::TABLE_TYPE:
                $ret = '<table cellpadding="5" nobr="true">'.self::getStructuredContent($content)."</table>";
                break;
            case StructuredNodeDoc::TABLEROW_TYPE:
                $ret = "<tr>".self::getStructuredContent($content)."</tr>";
                break;
            case CellNodeDoc::TABLEHEADER_TYPE:
                $align = $content["align"] ? "text-align:{$content["align"]};" : "text-align:center;";
                $style = $content["hasBorder"] ? ' style="border:1px solid black; border-collapse:collapse; '.$align.' font-weight:bold; background-color:#F0F0F0;"' : ' style="'.$align.' font-weight:bold; background-color:#F0F0F0;"';
                $colspan = $content["colspan"]>1 ? ' colspan="'.$content["colspan"].'"' : "";
                $rowspan = $content["rowspan"]>1 ? ' rowspan="'.$content["rowspan"].'"' : "";
                $ret = "<th$colspan$rowspan$style>".self::getStructuredContent($content)."</th>";
                break;
            case CellNodeDoc::TABLECELL_TYPE:
                $align = $content["align"] ? "text-align:{$content["align"]};" : "text-align:center;";
                $style = $content["hasBorder"] ? ' style="border:1px solid black; border-collapse:collapse; '.$align.'"' : " style=\"$align\"";
                $colspan = $content["colspan"]>1 ? ' colspan="'.$content["colspan"].'"' : "";
                $rowspan = $content["rowspan"]>1 ? ' rowspan="'.$content["rowspan"].'"' : "";
                $ret = "<td$colspan$rowspan$style>".self::getStructuredContent($content)."</td>";
                break;
            case CodeNodeDoc::CODE_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::HTML_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::PLAIN_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;

            case ReferenceNodeDoc::REFERENCE_TYPE:
                switch ($content["referenceType"]) {
                    case ReferenceNodeDoc::REF_TABLE_TYPE:
                        $id = trim($content["referenceId"]);
                        $ret = " <a href=\"#".$id."\"><em>Taula ".self::$tableReferences[$id]."</em></a> ";
                        break;
                    case ReferenceNodeDoc::REF_FIGURE_TYPE:
                        $id = trim($content["referenceId"]);
                        $ret = " <a href=\"#".$id."\"><em>Figura ".self::$figureReferences[$id]."</em></a> ";
                        break;
                    case ReferenceNodeDoc::REF_WIKI_LINK:
                        $file = $_SERVER['HTTP_REFERER']."?id=".$content["referenceId"];
                        $ret = " <a href=\"".$file."\">".$content["referenceTitle"]."</a> ";
                        break;
                    case ReferenceNodeDoc::REF_INTERNAL_LINK:
                        $ret = " <a href='".$content["referenceId"]."'>".$content["referenceTitle"]."</a> ";
                        break;
                    case ReferenceNodeDoc::REF_EXTERNAL_LINK:
                        $ret = " <a href=\"".$content["referenceId"]."\">".$content["referenceTitle"]."</a> ";
                        break;
                }
                break;

            case TextNodeDoc::PREFORMATED_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::UNFORMATED_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            default :
                $ret = self::getLeafContent($content);
        }
        return $ret;
    }

    private static function getStructuredContent($content) {
        $ret = "";
        $limit = count($content["content"]);
        for ($i=0; $i<$limit; $i++) {
            $ret .= self::getContent($content["content"][$i]);
        }
        return $ret;
    }

    private static function getTextContent($content) {
        if (!empty($content["text"]) && empty(trim($content["text"]))) {
            $ret = " ";
        }else {
            $ret = preg_replace("/\s\s+/", " ", $content["text"]);
        }
        return $ret;
    }

    private static function getLeafContent($content) {
        switch($content["type"]) {
            case LeafNodeDoc::HORIZONTAL_RULE_TYPE:
                $ret = "<hr>";
                break;
            case LeafNodeDoc::LINE_BREAK_TYPE:
                $ret = "<br>";
                break;
            case LeafNodeDoc::APOSTROPHE_TYPE:
                $ret = "'";
                break;
            case LeafNodeDoc::BACKSLASH_TYPE:
                $ret = "\\";
                break;
            case LeafNodeDoc::DOUBLEHYPHEN_TYPE:
                $ret = "&mdash;";
                break;
            case LeafNodeDoc::GRAVE_TYPE:
                $ret = "&#96;";
                break;
        }
        return $ret;
    }
}

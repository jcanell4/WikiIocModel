<?php
/**
 * projecte 'eoi'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC . "lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN . "tmp/latex/");
require_once DOKU_PLUGIN . "wikiiocmodel/exporter/BasicExporterClasses.php";
require_once DOKU_PLUGIN . "/wikiiocmodel/renderer/psdom.php";

class renderObject extends BasicRenderObject
{

    public function __construct($factory, $typedef, $renderdef)
    {
        parent::__construct($factory, $typedef, $renderdef);
        $this->cfgExport->rendererPath = dirname(realpath(__FILE__));
    }

}

require_once(DOKU_INC . 'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends TCPDF
{
    private $header_logo_hight = 10;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->header_logo_width = 8;
        $this->SetMargins(20, 20);
        $this->head = 20;
        $this->header_font = "helvetica";
    }

    //Page header
    public function Header()
    {
        $margins = $this->getMargins();

        // Logo
        $image_file = K_PATH_IMAGES . $this->header_logo;
        $this->Image($image_file, $margins['left'], 5, $this->header_logo_width, $this->header_logo_hight, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);

        $headerfont = $this->getHeaderFont();
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        $header_x = $margins['left'] + $margins['padding_left'] + ($this->header_logo_width * 1.1);
        $header_w = 105 - $header_x;

        $this->SetTextColorArray($this->header_text_color);
        // header title
        $this->SetFont($this->header_font[0], $this->header_font[1], $this->header_font[2]);
        $this->SetX($header_x);
        $this->MultiCell($header_w, $cell_height, $this->header_title, 0, 'L', 0, 0, "", "", true);

        // header string
        $this->MultiCell(65, $cell_height, $this->header_string, 0, 'R', 0, 0, "", "", true);
//        $this->Line(5, 19, 180, 19);
        $this->Line($margins['left'], 25, $this->getPageWidth()-$margins['right'], 25);
    }

    // Page footer
    public function Footer()
    {
        $this->SetY(-15);   //Position at 15 mm from bottom
        $this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');  //Page number
    }

    public function setHeaderData($ln = '', $lw = 0, $lh = 0, $ht = '', $hs = '', $tc = array(0, 0, 0), $lc = array(0, 0, 0))
    {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_hight = $lh;
    }
}

class StaticPdfRenderer extends BasicStaticPdfRenderer
{

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
    public static function renderDocument($params, $output_filename = "")
    {
        if (empty($output_filename)) {
            $output_filename = str_replace(":", "_", $params["id"]);
        }

        $iocTcPdf = new IocTcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $iocTcPdf->SetCreator("DOKUWIKI IOC");
        $iocTcPdf->setHeaderData($params["data"]["header_page_logo"], $params["data"]["header_page_wlogo"], $params["data"]["header_page_hlogo"], $params["data"]["header_ltext"], $params["data"]["header_rtext"]);

        // set header and footer fonts
        $iocTcPdf->setHeaderFont(Array(static::$headerFont, '', static::$headerFontSize));
        $iocTcPdf->setFooterFont(Array(static::$footerFont, '', static::$footerFontSize));

        $iocTcPdf->setStartingPageNumber(1);

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

        //primera pàgina
        $iocTcPdf->AddPage();


        $iocTcPdf->SetX(100);
        $iocTcPdf->SetY($y = 28);

        $iocTcPdf->SetFont(static::$firstPageFont, 'B', 16);
        $iocTcPdf->Cell(0, 0, html_entity_decode($params["data"]["titol"], ENT_QUOTES), 0, false, 'C');

        $iocTcPdf->SetY($y = 35);

        $len = count($params["data"]["contingut"]);
        for ($i = 0; $i < $len; $i++) {
            static::resolveReferences($params["data"]["contingut"][$i]);
        }
        for ($i = 0; $i < $len; $i++) {
            static::renderHeader($params["data"]["contingut"][$i], $iocTcPdf);
        }

        $iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        return TRUE;
    }

    // override
    protected static function getContent($content)
    {

        switch ($content["type"]) {
            case EoiBlockNodeDoc::BLOCK:
                $ret = "<table cellspacing=\"10\" style=\"border: 1px solid black;page-break-after: always\" ><tr><td>" . trim(static::getStructuredContent($content), " ") . "</td></tr></table>";
                return $ret;

            case EoiMapTableNodeDoc::MAP_TABLE:

                // Cel·la 1 Fila 1 (adreça)
                $content['content'][0]['content'][0]['content'][0]['align'] = "left";

                // Cel·la 2 Fila 1 (mapa)
                $content['content'][0]['content'][0]['content'][1]['align'] = "right";

                // Cel·la 2 Fila 2 (url)
                $content['content'][0]['content'][1]['content'][1]['align'] = "right";

                $aux = trim(static::getStructuredContent($content), " ");

                return $aux;

            case ImgResourcePrjNodeDoc::IMG_RESOURCE_PRJ:

                $ret = '<img src="' . $content['src'] . '" ';
                if ($content["title"]) {
                    $ret .= ' alt="' . $content["title"] . '"';
                }

                if ($content["width"]) {
                    $ret .= ' width="' . $content["width"] . '"';
                }

                if ($content["height"]) {
                    $ret .= ' height="' . $content["height"] . '"';
                }

                $ret .= '> ';

                
                return $ret;

            default;
                return parent::getContent($content);
        }
    }
}


class EoiBlockNodeDoc extends StructuredNodeDoc
{
    const BLOCK = "block";

    public function __construct($type)
    {
        parent::__construct($type);
    }

}

class EoiMapTableNodeDoc extends StructuredNodeDoc
{
    const MAP_TABLE = "map-table";

    public function __construct($type)
    {
        parent::__construct($type);
    }

}

class ImgResourcePrjNodeDoc extends ImageNodeDoc
{
    const IMG_RESOURCE_PRJ = "img-resource-prj";

    // TODO[Xavi]: En lloc de fer servir una mida fixa extreurela de la etiqueta <img-resource-prj>
    public function __construct($height = NULL, $width=NULL, $align=NULL)
    {
        parent::__construct('', NULL, $align, $width, $height, NULL, NULL);

        $this->type = self::IMG_RESOURCE_PRJ;
    }

    public function setSource($src)
    {
        $this->src = $src;
    }

}
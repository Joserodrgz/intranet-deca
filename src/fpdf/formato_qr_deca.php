<?php
require_once('fpdf.php');
require_once('qrcode/qrcode.class.php');

class QR extends FPDF
{
// private variables
var $colonnes;
var $format;
var $angle=0;
var $javascript;
var $n_js;

function IncludeJS($script) {
    $this->javascript=$script;
}
function _putjavascript() {
    $this->_newobj();
    $this->n_js=$this->n;
    $this->_out('<<');
    $this->_out('/Names [(EmbeddedJS) '.($this->n+1).' 0 R ]');
    $this->_out('>>');
    $this->_out('endobj');
    $this->_newobj();
    $this->_out('<<');
    $this->_out('/S /JavaScript');
    $this->_out('/JS '.$this->_textstring($this->javascript));
    $this->_out('>>');
    $this->_out('endobj');
}
function _putresources() {
    parent::_putresources();
    if (!empty($this->javascript)) {
    $this->_putjavascript();
    }
}
function _putcatalog() {
    parent::_putcatalog();
    if (isset($this->javascript)) {
    $this->_out('/Names <</JavaScript '.($this->n_js).' 0 R>>');
    }
}
function AutoPrint($dialog=false)
{
    $param=($dialog ? 'true' : 'false');
    $param = 'false';
    $script="print($param);";
    $this->IncludeJS($script);
}
function imprimir_codigoqr($msg1,$msg2,$qr,$eti_pie1,$eti_pie2)
{
    $r1  = 32;              // posicion X
    $y1  = 30;              // posicion Y
    $this->SetFillColor(0,0,0);
	$this->Image($qr,$r1,$y1,80);
    $this->SetTextColor(0,0,0);
    $r1  = 65;              // posicion X
    $y1  = 130;              // posicion Y
    $this->SetFont( "Arial", "B", 13);
    $this->SetXY( $r1, $y1 );
    $this->Cell(15,5, "$msg1", 0, 0, "C");
    $y1  = 145;              // posicion Y
    $this->SetXY( $r1, $y1 );
    $this->Cell(15,5, "$msg2", 0, 0, "C");
    $y1  = 180;              // posicion Y
    $this->SetTextColor(78,96,168);
    $this->SetFont( "Arial", "B", 7);
    $this->SetXY( $r1, $y1 );
    $this->Cell(15,5, "$eti_pie1 $eti_pie2", 0, 0, "C");
    $this->SetTextColor(0,0,0);
}
function imprimir_qr_deca($qr)
{
    $r1  = 60;              // posicion X
    $y1  = 60;              // posicion Y
    $this->SetFillColor(255,255,255);
	$this->Image($qr,$r1,$y1,100);
    $this->SetTextColor(0,0,0);
}

}
?>

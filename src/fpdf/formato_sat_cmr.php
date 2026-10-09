<?php
require('fpdf.php');
require_once('qrcode/qrcode.class.php');
class INVOICE extends FPDF
{
// private variables
var $colonnes;
var $format;

function EAN13($x,$y,$barcode,$h=16,$w=.35)
{
    $this->Barcode($x,$y,$barcode,$h,$w,13);
}
function UPC_A($x,$y,$barcode,$h=16,$w=.35)
{
	$this->Barcode($x,$y,$barcode,$h,$w,12);
}
function GetCheckDigit($barcode)
{
    $sum=0;
    for($i=1;$i<=11;$i+=2)
    $sum+=3*$barcode{$i};
	for($i=0;$i<=10;$i+=2)
    $sum+=$barcode{$i};
    $r=$sum%10;
    if($r>0)
    $r=10-$r;
    return $r;
}
function TestCheckDigit($barcode)
{
    $sum=0;
    for($i=1;$i<=11;$i+=2)
    $sum+=3*$barcode{$i};
    for($i=0;$i<=10;$i+=2)
    $sum+=$barcode{$i};
    return ($sum+$barcode{12})%10==0;
}
function Barcode($x,$y,$barcode,$h,$w,$len)
{
    $barcode=str_pad($barcode,$len-1,'0',STR_PAD_LEFT);
    if($len==12)
    $barcode='0'.$barcode;
	if(strlen($barcode)==12)
    $barcode.=$this->GetCheckDigit($barcode);
    elseif(!$this->TestCheckDigit($barcode))
    $this->Error('Incorrect check digit');
    $codes=array(
    'A'=>array(
    '0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
    '5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'),
    'B'=>array(
    '0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
    '5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'),
    'C'=>array(
    '0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
    '5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100')
     );
    $parities=array(
    '0'=>array('A','A','A','A','A','A'),
    '1'=>array('A','A','B','A','B','B'),
    '2'=>array('A','A','B','B','A','B'),
	'3'=>array('A','A','B','B','B','A'),
    '4'=>array('A','B','A','A','B','B'),
    '5'=>array('A','B','B','A','A','B'),
    '6'=>array('A','B','B','B','A','A'),
    '7'=>array('A','B','A','B','A','B'),
    '8'=>array('A','B','A','B','B','A'),
    '9'=>array('A','B','B','A','B','A')
    );
    $code='101';
    $p=$parities[$barcode{0}];
    for($i=1;$i<=6;$i++)
    $code.=$codes[$p[$i-1]][$barcode{$i}];
    $code.='01010';
    for($i=7;$i<=12;$i++)
    $code.=$codes['C'][$barcode{$i}];
    $code.='101';
    for($i=0;$i<strlen($code);$i++)
    {
    if($code{$i}=='1')
    $this->Rect($x+$i*$w,$y,$w,$h,'F');
    }
}
function Ellipse($pagina,$x,$y,$rx,$ry,$style='D')
{
    if ($pagina == 0)
	$this->SetDrawColor(0,0,80);		
    if ($pagina == 1)		
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0); 

    $this->SetLineWidth(0.5);
 	if($style=='F')
    $op='f';
 	elseif($style=='FD' or $style=='DF')
    $op='B';
 	else
    $op='S';
 	$lx=4/3*(M_SQRT2-1)*$rx;
 	$ly=4/3*(M_SQRT2-1)*$ry;
 	$k=$this->k;
 	$h=$this->h;
 	$this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
    ($x+$rx)*$k,($h-$y)*$k,
    ($x+$rx)*$k,($h-($y-$ly))*$k,
    ($x+$lx)*$k,($h-($y-$ry))*$k,
    $x*$k,($h-($y-$ry))*$k));
 	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
    ($x-$lx)*$k,($h-($y-$ry))*$k,
    ($x-$rx)*$k,($h-($y-$ly))*$k,
    ($x-$rx)*$k,($h-$y)*$k));
 	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
    ($x-$rx)*$k,($h-($y+$ly))*$k,
    ($x-$lx)*$k,($h-($y+$ry))*$k,
    $x*$k,($h-($y+$ry))*$k));
 	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
    ($x+$lx)*$k,($h-($y+$ry))*$k,
    ($x+$rx)*$k,($h-($y+$ly))*$k,
    ($x+$rx)*$k,($h-$y)*$k,
    $op));
}
function _endpage()
{
	if($this->angle!=0)
	{
	$this->angle=0;
	$this->_out('Q');
	}
	parent::_endpage();
}
function sizeOfText( $texte, $largeur )
{
	$index    = 0;
	$nb_lines = 0;
	$loop     = TRUE;
	while ( $loop )
	{
	$pos = strpos($texte, "\n");
	if (!$pos)
	{
	$loop  = FALSE;
	$ligne = $texte;
	}
	else
	{
	$ligne  = substr( $texte, $index, $pos);
	$texte = substr( $texte, $pos+1 );
	}
	$length = floor( $this->GetStringWidth( $ligne ) );
	$nb_lines += $res;
	}
	return $nb_lines;
}
function titulo($factura, $pagina)
{
    $r1  = 1;
    $y1  = 5;
	if ($pagina == 1) 
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 1, 6 );
    $this->SetFont( "Helvetica", "", 25);
    $this->Cell(20,4, $pagina, 0, 0, "L");
    $this->SetXY( 7, 5 );
    $this->SetFont( "Arial", "", 5);
    if ($pagina == 1)
    $this->Cell(20,4, "Copia para el remitente o expedidor", 0, 0, "L");
    if ($pagina == 2)
    $this->Cell(20,4, "Copia para el destinatario", 0, 0, "L");
    if ($pagina == 3)
    $this->Cell(20,4, "Copia para el transportista", 0, 0, "L");
    if ($pagina == 4)
    $this->Cell(20,4, "Copia para devolver firmada al remitente o expedidor", 0, 0, "L");
    $this->SetXY( 7, 7.5 );
    if ($pagina == 1)
    $this->Cell(20,4, "Exemplaire de l'expéditeur", 0, 0, "L");
    if ($pagina == 2)
    $this->Cell(20,4, "Exemplaire du destinataire", 0, 0, "L");
    if ($pagina == 3)
    $this->Cell(20,4, "Exemplaire du transporteur", 0, 0, "L");
    if ($pagina == 4)
    $this->Cell(20,4, "Signé copie à l'expéditeur", 0, 0, "L");
    $this->SetXY( 58 , $y1 );
    $this->SetFont( "Arial", "B", 5);
    $this->Cell(20,4, "CARTA DE PORTE INTERNACIONAL", 0, 0, "L");
    $this->SetXY( 90 , $y1 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "CMR", 0, 0, "C");
    $this->SetXY( 109 , $y1 );
    $this->SetFont( "Arial", "B", 5);
    $this->Cell(20,4, "LETTRE DE VOITURE INTERNATIONALE", 0, 0, "L");
    $this->SetXY( 150 , 5 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "ES", 0, 0, "L");
    $this->SetXY( 160 , 4 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "España", 0, 0, "L");
    $this->SetXY( 160 , 6 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Espagne", 0, 0, "L");
    $this->SetXY( 185 , 5 );
    $this->SetFont( "Helvetica", "B", 12);
    $this->Cell(20,4, "$factura", 0, 0, "R");
}
function titulo_deca($factura)
{
    $this->SetTextColor(0,0,80);
    $this->SetXY(4,5);
    $this->SetFont( "Arial", "B", 5);
    $this->Cell(20,4, "Documento de control electrónico conforme a la Orden FOM/2861/2012 y la Resolución de 5 de junio de 2026", 0, 0, "L");	
    $this->SetXY(115,4);
    $this->SetFont( "Arial", "B", 5);
    $this->Cell(20,4, "CARTA DE PORTE", 0, 0, "L");
    $this->SetXY(92,5);
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(30,4, "DeCA", 0, 0, "C");
    $this->SetXY(115,5);
    $this->SetFont( "Arial", "B", 5);
    $this->Cell(20,8, "Documento de control electrónico", 0, 0, "L");
    $this->SetXY(150,5);
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "ES", 0, 0, "L");
    $this->SetXY( 160 , 6 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "España", 0, 0, "L");
    $this->SetXY( 185 , 5 );
    $this->SetFont( "Helvetica", "B", 12);
    $this->Cell(20,4, "$factura", 0, 0, "R");
}
function marco_rojo($pagina)
{
    $r1  = 5;
    $r2  = $r1 + 200.3;
    $y1  = 15;
    $y2  = $y1+280;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
	$this->SetDrawColor(0,0,0);
 	$this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
}
function marco_deca()
{
    $r1  = 5;
    $r2  = $r1 + 200.3;
    $y1  = 15;
    $y2  = $y1+280;
    $this->SetDrawColor(11,11,74);
 	$this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
}
function marco_rojo_1($pagina)
{
    $r1  = 5;
    $r2  = $r1 + 110;
    $y1  = 15;
    $y2  = $y1+25;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
	$this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
}
function marco_deca_1()
{
    $r1  = 5;
    $r2  = $r1 + 110;
    $y1  = 15;
    $y2  = $y1+25;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
	$this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
}
function addSociete( $nom, $adresse )
{
    $this->SetXY( 7 , 16 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "1", 0, 0, "L");
    $this->SetXY( 10 , 15 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Remitente/Expedidor", 0, 0, "L");
    $this->SetXY( 10 , 16.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Expéditeur (nom, adresse, pays)", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $x1 = 12;
    $y1 = 22;
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',10);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( $x1, $y1 + 3 );
    $this->SetFont('Arial','',8);
    $length = $this->GetStringWidth( $adresse );
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 3.5, $adresse);
}
function addSociete_deca( $nom, $adresse )
{
    $this->SetXY(7,16);
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(20,4, "Remitente/Expedidor", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $x1 = 12;
    $y1 = 22;
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial','B',10);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( $x1, $y1 + 3 );
    $this->SetFont('Arial','',8);
    $length = $this->GetStringWidth( $adresse );
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 3.5, $adresse);
}
function marco_rojo_6($pagina, $matricula)
{
    $r1  = 110.7;
    $r2  = $r1 + 94;
    $y1  = 15.3;
    $y2  = $y1+24.7;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetXY( 112 , 16 );
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "6", 0, 0, "L");
    $this->SetXY( 116 , 15 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Transportista", 0, 0, "L");
    $this->SetXY( 116 , 16.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Transporteur (nom, adresse, pays, autres références)", 0, 0, "L");
    $this->SetXY( 147.5 , 35.21 );
    $this->SetFont( "Helvetica", "", 8);
    $this->Cell(10,4, "MATRICULA:", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 166 , 35.21 );
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(10,4, "$matricula", 0, 0, "L");
}
function marco_deca_6($matricula)
{
    $r1  = 110.7;
    $r2  = $r1 + 94;
    $y1  = 15.3;
    $y2  = $y1+24.7;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetXY( 112 , 16 );
    $this->SetTextColor(0,0,80);
    $this->SetXY( 116 , 16 );
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(20,4, "Transportista", 0, 0, "L");
    $this->SetXY( 147.5 , 35.21 );
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(10,4, "MATRICULA:", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 166 , 35.21 );
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(10,4, "$matricula", 0, 0, "L");
}
function addSociete_6( $nom, $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 118, 22);
    $this->SetFont('Arial','B',10);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( 118, 25 );
    $this->SetFont('Arial','',8);
    $length = $this->GetStringWidth( $adresse );
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 3.5, $adresse);
}
function addSociete_deca_6( $nom, $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 118, 22);
    $this->SetFont('Arial','B',10);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( 118, 25 );
    $this->SetFont('Arial','',8);
    $length = $this->GetStringWidth( $adresse );
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 3.5, $adresse);
}
function marco_rojo_7($pagina)
{
    $r1  = 110.7;
    $r2  = $r1 + 94;
    $y1  = 40;
    $y2  = $y1+25;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 112 , 41 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "7", 0, 0, "L");
    $this->SetXY( 116 , 40 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Transportistas sucesivos", 0, 0, "L");
    $this->SetXY( 116 , 41.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Transporteurs successifs", 0, 0, "L");
    $this->SetXY( 112 , 66 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "8", 0, 0, "L");
    $this->SetXY( 116 , 65 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Reservas y observaciones del transportista sobre la mercancía", 0, 0, "L");
    $this->SetXY( 116 , 66.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Réserves et observations du transporteur lors de la prise en charge de la marchandise", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function marco_deca_7()
{
    $r1  = 110.7;
    $r2  = $r1 + 94;
    $y1  = 40;
    $y2  = $y1+25;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY( 116 , 41 );
    $this->SetFont( "Helvetica", "B",8);
    $this->Cell(20,4, "Transportistas sucesivos", 0, 0, "L");
    $this->SetXY( 116 , 66 );
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(20,4, "Reservas y observaciones del transportista sobre la mercancía", 0, 0, "L");
	$this->SetTextColor(0,0,0);
}
function marco_rojo_678($pagina)
{
    $r1  = 110.3;
    $r2  = $r1 + 94.7;
    $y1  = 15.3;
    $y2  = $y1+79.2;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(1);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
}
function marco_deca_678()
{
    $r1  = 110.3;
    $r2  = $r1 + 94.7;
    $y1  = 15.3;
    $y2  = $y1+79.2;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(1);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
}
function marco_rojo_2($pagina)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 40;
    $y2  = $y1+25;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 41 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "2", 0, 0, "L");
    $this->SetXY( 10.5 , 40 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Destinatario", 0, 0, "L");
    $this->SetXY( 10.5 , 41.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Destinataire (nom, adresse, pays)", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function marco_deca_2()
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 40;
    $y2  = $y1+25;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,41);
    $this->SetFont( "Helvetica", "B", 8);	
    $this->Cell(20,4, "Destinatario", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function addSociete_2( $nom, $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 47);
    $this->SetFont('Arial','B',10);
    $length = $this->GetStringWidth( $nom );
    $this->Cell( $length, 2, $nom);
    $this->SetXY( 12, 50 );
    $this->SetFont('Arial','',8);
    $length = $this->GetStringWidth( $adresse );
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 3.5, $adresse);
}
function marco_rojo_3($pagina)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 65;
    $y2  = $y1+15;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 66 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "3", 0, 0, "L");
    $this->SetXY( 10.5 , 65 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Destino de la mercancía", 0, 0, "L");
    $this->SetXY( 10.5 , 66.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Prise en charge de la marchandise", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function marco_deca_3()
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 65;
    $y2  = $y1+15;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,66);
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(20,4, "Destino de la mercancía", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function addClientAdresse_3( $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 71);
    $this->SetFont( "Helvetica", "", 10);
    $this->MultiCell( 90, 4, $adresse);
    $this->SetFont( "Helvetica", "B", 8);
}
function marco_rojo_4($pagina)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 79.8;
    $y2  = $y1+15;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 81 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "4", 0, 0, "L");
    $this->SetXY( 10.5 , 80 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Lugar y fecha de carga", 0, 0, "L");
    $this->SetXY( 10.5 , 81.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Livraison de la marchandise", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function marco_deca_4()
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 79.8;
    $y2  = $y1+15;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,81 );
    $this->SetFont( "Helvetica", "B",8);
    $this->Cell(20,4, "Lugar y fecha de carga", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function addClientAdresse_4( $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 86);
    $this->SetFont( "Helvetica", "", 10);
    $this->MultiCell( 75, 4, $adresse);
    $this->SetFont( "Helvetica", "B", 8);
}
function marco_rojo_5($pagina, $instr_5)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 94.7;
    $y2  = $y1+18;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
	$this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 96 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "5", 0, 0, "L");
    $this->SetXY( 10.5 , 95 );
    $this->SetFont( "Helvetica", "", 5);
	$this->Cell(20,4, "Instrucciones del expedidor", 0, 0, "L");
    $this->SetXY( 10.5 , 96.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Instructions de l'expéditeur", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 102);
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(20,4, $instr_5, 0, 0, "L");
}
function marco_deca_5($instr_5)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 94.7;
    $y2  = $y1+18;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,96);
    $this->SetFont( "Helvetica", "B",8);
	$this->Cell(20,4, "Instrucciones del expedidor", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 102);
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(20,4, $instr_5, 0, 0, "L");
}
function marco_rojo_9($pagina, $cod_cmr)
{
    $r1  = 110.0;
    $r2  = $r1 + 95.3;
    $y1  = 94.7;
    $y2  = $y1+18;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
	$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 112 , 96 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "9", 0, 0, "L");
    $this->SetXY( 115.6 , 95 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Documentos entregados al transportista por el expedidor", 0, 0, "L");
    $this->SetXY( 115.6 , 96.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Documents remis au transporteur par l'expéditeur", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 117, 102);
    $this->SetFont( "Helvetica", "", 10);
	if ($cod_cmr == 'F') 
    $this->Cell(20,4, "FACTURA DE VENTA", 0, 0, "L");
    if ($cod_cmr == 'A')
    $this->Cell(20,4, "ALBARAN DE ENTREGA", 0, 0, "L");
}
function marco_deca_9($cod_cmr)
{
    $r1  = 110.0;
    $r2  = $r1 + 95.3;
    $y1  = 94.7;
    $y2  = $y1+18;
	$this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(115.6,96);
    $this->SetFont( "Helvetica", "B",8);
    $this->Cell(20,4, "Documentos entregados al transportista por el expedidor", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 117, 102);
    $this->SetFont( "Helvetica", "", 10);
	if ($cod_cmr == 'F') 
    $this->Cell(20,4, "FACTURA DE VENTA", 0, 0, "L");
    if ($cod_cmr == 'A')
    $this->Cell(20,4, "ALBARAN DE ENTREGA", 0, 0, "L");
}
function addClientAdresse_9( $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 170, 101.5);
    $this->SetFont( "Helvetica", "B", 14);
    $this->MultiCell( 75, 5, $adresse);
    $this->SetFont( "Helvetica", "B", 8);
}
function marco_rojo_16($pagina, $instr_16)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 190;
    $y2  = $y1+23;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 191 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "16", 0, 0, "L");
    $this->SetXY( 14 , 190 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Instrucciones del expedidor al transportista", 0, 0, "L");
    $this->SetXY( 14 , 191.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Conventions particulières entre l'expéditeur et le transporteur", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 200);
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(20,4, $instr_16, 0, 0, "L");
}
function marco_deca_16($instr_16)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 190;
    $y2  = $y1+23;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,191);
    $this->SetFont("Helvetica","B",8);
    $this->Cell(20,4, "Instrucciones del expedidor al transportista", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 200);
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(20,4, $instr_16, 0, 0, "L");
}
function marco_rojo_17($pagina, $en_destino)
{
    $r1  = 110;
    $r2  = $r1 + 95.3;
    $y1  = 190;
    $y2  = $y1+23;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 112 , 191 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "17", 0, 0, "L");
    $this->SetXY( 119 , 190 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Transporte a pagar en:", 0, 0, "L");
    $this->SetXY( 119 , 191.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Transport a payer par:", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 117, 200);
    $this->SetFont( "Helvetica", "", 10);
	if ($en_destino == 'S') 
    $this->Cell(20,4, "TRANSPORTE A PAGAR EN DESTINO", 0, 0, "L");
    if ($en_destino == 'N')
    $this->Cell(20,4, "TRANSPORTE A PAGAR EN ORIGEN", 0, 0, "L");
}
function marco_deca_17($en_destino)
{
    $r1  = 110;
    $r2  = $r1 + 95.3;
    $y1  = 190;
    $y2  = $y1+23;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(115.6,191);
    $this->SetFont("Helvetica","B",8);
    $this->Cell(20,4, "Transporte a pagar en:", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 117, 200);
    $this->SetFont( "Helvetica", "", 10);
	if ($en_destino == 'S') 
    $this->Cell(20,4, "TRANSPORTE A PAGAR EN DESTINO", 0, 0, "L");
    if ($en_destino == 'N')
    $this->Cell(20,4, "TRANSPORTE A PAGAR EN ORIGEN", 0, 0, "L");
}
function marco_rojo_18($pagina, $instr_18)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 213;
    $y2  = $y1+18;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 214 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "18", 0, 0, "L");
    $this->SetXY( 14 , 213 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Estipulaciones particulares", 0, 0, "L");
    $this->SetXY( 14 , 214.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Autres indications utiles", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 222);
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(20,4, $instr_18, 0, 0, "L");
}
function marco_deca_18($instr_18)
{
    $r1  = 5;
    $r2  = $r1 + 105;
    $y1  = 213;
    $y2  = $y1+27;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,214);
    $this->SetFont("Helvetica","B",8);
    $this->Cell(20,4, "Estipulaciones particulares", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 12, 222);
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(20,4, $instr_18, 0, 0, "L");
}
function marco_rojo_19($pagina)
{
    $r1  = 110;
    $r2  = $r1 + 95.3;
    $y1  = 213;
    $y2  = $y1+18;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 133 , 214 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(20,4, "DOCUMENTO DE CONTROL", 0, 0, "L");
    $this->SetXY( 112 , 217.7 );
    $this->SetFont( "Arial", "B", 6);
	$this->Cell(20,4, "Este transporte queda sometido, no obstante a toda clausula contraria al convenio sobre", 0, 0, "L");
    $this->SetXY( 115.4 , 220.7 );
	$this->Cell(20,4, "el contrato de transportes, segun la norma del B.O.E. 13/02/2003 - O.FOM 238/2003", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function marco_deca_19()
{
    $r1  = 110;
    $r2  = $r1 + 95.3;
    $y1  = 213;
    $y2  = $y1+27;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
 //   $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(115.6,214);
    $this->SetFont( "Helvetica", "B", 8);
    $this->Cell(20,4, "Seguimiento", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function cod_barras ($sscc)
{
    $r1 = 136.6;
    $r2 = $r1 + 60;
    $y1 = 223.9;
    $y2 = $y1 + 6;
    $this->SetFillColor(0,0,0);
    $this->EAN13($r1+3,$y1+1,$sscc,$h=4.2,$w=.38);
}
function marco_rojo_20($pagina)
{
    $r1  = 5;
    $r2  = $r1 + 200.3;
    $y1  = 231;
    $y2  = $y1+9;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 233.5 );
	$this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "20", 0, 0, "L");
    $this->SetXY( 14 , 232 );
    $this->SetFont( "Arial", "B", 7);
    $this->Cell(20,4, "Este transporte queda sometido al convenio sobre el Contrato de
	Transporte Internacional de Mercancias por Carretera (CMR)", 0, 0, "L");
    $this->SetXY( 14 , 234.7 );
    $this->SetFont( "Arial", "B", 7);
    $this->Cell(20,4, "Ce transport est soumis, nonobstant toute clause contraire, à la Convention
	relative au contrat de transport international de marchandises par route (CMR)", 0, 0, "L");
    $this->SetTextColor(0,0,0);
}
function marco_rojo_21($direccion2, $pagina)
{
    $r1  = 5.4;
    $r2  = $r1 + 135.1;
    $y1  = 240;
    $y2  = $y1+9;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(1);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 242.5 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "21", 0, 0, "L");
    $this->SetXY( 14 , 241.4 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Lugar y fecha del contrato", 0, 0, "L");
    $this->SetXY( 14 , 243.2 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Etablie à", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 40, 242.5);
    $this->SetFont( "Helvetica", "B", 12);
    $this->Cell(20,4, $direccion2, 0, 0, "L");
}
function marco_deca_21($direccion2)
{
    $r1  = 5.4;
    $r2  = $r1 + 135.1;
    $y1  = 240;
    $y2  = $y1+9;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(1);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(7,241);
    $this->SetFont( "Helvetica", "B",8);
    $this->Cell(20,4, "Lugar y fecha del contrato", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY(12,245);
    $this->SetFont("Helvetica","",9);
    $this->Cell(20,4, $direccion2, 0, 0, "L");
}
function addClientAdresse_21( $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 95, 242.5);
    $this->SetFont( "Helvetica", "B", 12);
    $this->MultiCell( 75, 5, $adresse);
    $this->SetFont( "Helvetica", "B", 8);
}
function addClientAdresse_deca_21( $adresse )
{
    $this->SetTextColor(0,0,0);
    $this->SetXY(95,245);
    $this->SetFont("Helvetica","",9);
    $this->MultiCell(20,4, $adresse);
}
function marco_rojo_22($pagina, $empresa)
{
    $r1  = 5;
    $r2  = $r1 + 70;
    $y1  = 249.4;
    $y2  = $y1+25;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 7 , 251 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "22", 0, 0, "L");
    $this->SetXY( 7 , 268 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Firma o sello del expedidor", 0, 0, "L");
    $this->SetXY( 7 , 269.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Signature ou timbre de l'expéditeur", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 35 , 265 );
    $this->SetFont( "Helvetica", "B", 7);
    $this->Cell(10,4, "$empresa", 0, 0, "C");
}
function marco_deca_22($empresa)
{
    $r1  = 5;
    $r2  = $r1 + 70;
    $y1  = 249.4;
    $y2  = $y1+25;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY( 7 , 268 );
    $this->SetFont("Helvetica","B",8);
    $this->Cell(20,4, "Firma o sello del expedidor", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 35 , 265 );
    $this->SetFont( "Helvetica", "B", 7);
    $this->Cell(10,4, "$empresa", 0, 0, "C");
}
function logo()
{
    $this->Image('../img_sat/logo_web.png',25,252,30);
}
function marco_rojo_23($pagina, $matricula)
{
    $r1  = 74.5;
    $r2  = $r1 + 65.9;
    $y1  = 249.1;
    $y2  = $y1+25;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(1);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 76 , 251 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "23", 0, 0, "L");
    $this->SetXY( 76 , 268 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Firma o sello del transportista", 0, 0, "L");
    $this->SetXY( 76 , 269.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Signature ou timbre du transporteur", 0, 0, "L");
    $this->SetXY( 76 , 265 );
    $this->SetFont( "Helvetica", "", 7);
    $this->Cell(10,4, "MATRICULA:", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 95 , 265 );
    $this->SetFont( "Helvetica", "B", 7);
    $this->Cell(10,4, "$matricula", 0, 0, "L");
}
function marco_deca_23($matricula)
{
    $r1  = 74.5;
    $r2  = $r1 + 65.9;
    $y1  = 249.1;
    $y2  = $y1+25;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(1);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY( 76 , 268 );
    $this->SetFont("Helvetica","B",8);
    $this->Cell(20,4, "Firma o sello del transportista", 0, 0, "L");
    $this->SetXY( 76 , 265 );
    $this->SetFont( "Helvetica", "", 7);
    $this->Cell(10,4, "MATRICULA:", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 95 , 265 );
    $this->SetFont( "Helvetica", "B", 7);
    $this->Cell(10,4, "$matricula", 0, 0, "L");
}
function addClientAdresse_23($adresse)
{
    $this->SetTextColor(0,0,0);
    $this->SetXY( 83, 251);
    $this->SetFont('Helvetica','B',7);
    $this->MultiCell( 75, 2.5, $adresse);
}
function marco_rojo_24($pagina, $empresa)
{
    $r1  = 140.9;
    $r2  = $r1 + 64.4;
    $y1  = 239.7;
    $y2  = $y1+34.7;
    if ($pagina == 1)
    $this->SetDrawColor(240,30,30);
    if ($pagina == 2)
    $this->SetDrawColor(97,140,255);
    if ($pagina == 3)
    $this->SetDrawColor(0,165,80);
    if ($pagina == 4)
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    $this->SetXY( 143 , 242.5 );
    $this->SetFont( "Helvetica", "B", 14);
    $this->Cell(20,4, "24", 0, 0, "L");
    $this->SetXY( 150 , 241.5 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Recibo de mercancía:", 0, 0, "L");
    $this->SetXY( 150 , 243.2 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Marchandises reçues:", 0, 0, "L");
    $this->SetXY( 143 , 246.2 );
    $this->SetFont( "Helvetica", "B", 6);
    $this->Cell(20,4, "Lugar, fecha y hora / Lieu et heure d'arrivée", 0, 0, "L");
    $this->SetXY( 143 , 268 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Firma y sello del destinatario", 0, 0, "L");
    $this->SetXY( 143 , 269.7 );
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Signature et timbre du destinataire", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY( 143 , 265 );
    $this->SetFont( "Helvetica", "B", 7);
    $this->Cell(10,4, "$empresa", 0, 0, "L");
}
function marco_deca_24($empresa)
{
    $r1  = 140.9;
    $r2  = $r1 + 64.4;
    $y1  = 259.7; //239
    $y2  = $y1+35;
    $this->SetDrawColor(0,0,80);
    $this->SetLineWidth(0.3);
    $this->SetFillColor(255,255,255);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'DF');
    $this->SetTextColor(0,0,80);
    $this->SetXY(143,261.5);
    $this->SetFont( "Helvetica", "B",8);
    $this->Cell(20,4, "Recibo de mercancía:", 0, 0, "L");
    $this->SetXY(143,265.2);
    $this->SetFont( "Helvetica", "B", 6);
    $this->Cell(20,4, "Lugar, fecha y hora", 0, 0, "L");
    $this->SetXY(143,289.7);
    $this->SetFont( "Helvetica", "", 5);
    $this->Cell(20,4, "Firma y sello del destinatario", 0, 0, "L");
    $this->SetTextColor(0,0,0);
    $this->SetXY(143,287);
    $this->SetFont( "Helvetica", "B", 7);
    $this->Cell(10,4, "$empresa", 0, 0, "L");
}
function addCols_venta( $tab, $pagina )
{
    global $colonnes;
    $r1  = 5;
    $r2  = 200.3 ;
    $y1  = 107.7;
    $y2  = $this->h - 107 - $y1;   //70
    $this->SetXY( $r1, $y1 );
    $this->SetFont( "Helvetica", "", 5);
	$this->SetFillColor(255,255,255);
    $this->Rect( $r1, $y1, $r2, $y2, "DF");
    $this->SetFillColor(255,255,255);
    $this->Rect( $r1, $y1+4, $r2, $y2-4, "DF");
    $colX = $r1;
    $colonnes = $tab;
    if ($pagina == 1)
    $this->SetTextColor(240,30,30);
    if ($pagina == 2)
    $this->SetTextColor(97,140,255);
    if ($pagina == 3)
    $this->SetTextColor(0,165,80);
    if ($pagina == 4)
    $this->SetTextColor(0,0,0);
    while ( list( $lib, $pos ) = each ($tab) )
    {
    $this->SetXY( $colX, $y1+2 );
    $this->Cell( $pos, 1, $lib, 0, 0, "L");
    $colX += $pos;
    $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}
function addCols_venta_deca($tab)
{
    global $colonnes;
    $r1  = 5;
    $r2  = 200.3 ;
    $y1  = 107.7;
    $y2  = $this->h - 107 - $y1;   //70
    $this->SetXY( $r1, $y1 );
    $this->SetFont( "Helvetica", "B", 5);
	$this->SetFillColor(255,255,255);
    $this->Rect( $r1, $y1, $r2, $y2, "DF");
    $this->SetFillColor(255,255,255);
    $this->Rect( $r1, $y1+4, $r2, $y2-4, "DF");
    $colX = $r1;
    $colonnes = $tab;
    $this->SetTextColor(0,0,80);
    while ( list( $lib, $pos ) = each ($tab) )
    {
    $this->SetXY( $colX, $y1+2 );
    $this->Cell( $pos, 1, $lib, 0, 0, "L");
    $colX += $pos;
    $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}
function lineVert( $tab )
{
	global $colonnes;
	reset( $colonnes );
	$maxSize=0;
	while ( list( $lib, $pos ) = each ($colonnes) )
	{
	$texte = $tab[ $lib ];
	$longCell  = $pos -2;
	$size = $this->sizeOfText( $texte, $longCell );
	if ($size > $maxSize)
	$maxSize = $size;
	}
	return $maxSize;
}
function addLineFormat( $tab )
{
    global $format, $colonnes;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
    if ( isset( $tab["$lib"] ) )
    $format[ $lib ] = $tab["$lib"];
    }
}
function addLine( $ligne, $tab )
{
	global $colonnes, $format;
	$ordonnee     = 5;
	$maxSize      = $this->$ligne;
    $this->SetTextColor(0,0,0);
    $this->SetFont( "Helvetica", "", 6);
	reset( $colonnes );
	while ( list( $lib, $pos ) = each ($colonnes) )
	{
	$longCell  = $pos -2;
	$texte     = $tab[ $lib ];
	$length    = $this->GetStringWidth( $texte );
	$tailleTexte = $this->sizeOfText( $texte, $length );
	$formText  = $format[ $lib ];
	$this->SetXY( $ordonnee, $ligne-1);
	$this->MultiCell( $longCell, 4 , $texte, 0, $formText);
	if ( $maxSize < ($this->GetY()  ) )
	$maxSize = $this->GetY() ;
	$ordonnee += $pos;
	}
	return ( $maxSize - $ligne );
}
function imprimir_qr_deca($qr)
{
    $this->Image($qr,152,216,40);
}
}
?>

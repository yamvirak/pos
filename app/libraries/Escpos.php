<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 *  ============================================================================== 
 *  Author	: Mian Saleem
 *  Email	: veasna.rin@sunfixconsulting.com 
 *  For		: ESC/POS Print Driver for PHP
 *  License	: MIT License
 *  ============================================================================== 
 */

require APPPATH . 'third_party/Escpos/autoload.php';
require APPPATH . 'third_party/phppdf/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\ImagickEscposImage;
use Mike42\Escpos\Devices\AuresCustomerDisplay;

use mikehaertl\wkhtmlto\Image;
use mikehaertl\wkhtmlto\Pdf;

class Escpos
{

    public $printer;
    public $char_per_line = 42;
	public $client;

    public function __construct() 
	{
        $this->load->helper('pos');
    }

    public function __get($var) 
	{
        return get_instance()->$var;
    }

    function load($printer) 
	{

        if ($printer->type == 'network') {
            $connector = new NetworkPrintConnector($printer->ip_address, $printer->port);
        } elseif ($printer->type == 'linux') {
            $connector = new FilePrintConnector($printer->path);
        } else {
            $connector = new WindowsPrintConnector($printer->path);
        }

        $this->char_per_line = $printer->char_per_line;
        $profile = CapabilityProfile::load($printer->profile);
        $this->printer = new Printer($connector, $profile);
    }
	
    public function print_receipt($data) 
	{

        if (isset($data->logo) && !empty($data->logo)) {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            //$logo = EscposImage::load(FCPATH.'assets/uploads/logos/'.DIRECTORY_SEPARATOR.$data->logo, false);
            //$this->printer->bitImage($logo);
        }

        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->setEmphasis(true);
        $this->printer->setTextSize(2, 2);
        $this->printer->text($data->text->store_name);
        $this->printer->setEmphasis(false);
        $this->printer->setTextSize(1, 1);
        $this->printer->feed();
        $this->printer->text($data->text->header);
        $this->printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->printer->text($data->text->info);
        $this->printer->text($data->text->items);
		
		
        if (isset($data->text->totals) && !empty($data->text->totals)) {
            $this->printer->text(drawLine($data->printer->char_per_line));
            $this->printer->text($data->text->totals);
        }

        if (isset($data->text->payments) && !empty($data->text->payments)) {
            $this->printer->text(drawLine($data->printer->char_per_line));
            $this->printer->text($data->text->payments);
            $this->printer->feed(2);
        }

        if (isset($data->text->footer) && !empty($data->text->footer)) {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text($data->text->footer);
        }

        $this->printer->feed(2);
        $this->printer->cut();

        if (isset($data->cash_drawer) && !empty($data->cash_drawer)) {
            $this->printer->pulse();
        }
        $this->printer->close();
    }
	
	public function print_order_html($data)
	{
		$hdtag = '<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
							<link href="'.FCPATH.'themes/default/assets/styles/theme.css" rel="stylesheet"/>
							<style type="text/css">
								#wrapper { max-width: 570px; }
								table td, table th{
									font-size:22px;
									font-family: "Titillium Web","Suwannaphum", sans-serif;
								}
							</style>
						</head>
						<body><div id="wrapper"> ';
						
		$fttag = '</div></body></html>';
		$cotag = $hdtag . $data->text->items . $fttag;
		$pdf = new Image($cotag);
		
		$pdf->binary = FCPATH.'assets\wkhtmltopdf\wkhtmltoimage.exe';
		
		$random = date("ymdhis");
		if (!$pdf->saveAs(FCPATH.'assets/uploads/orders/order_'.$random.'.png')) {
			echo $pdf->getError();
		}
		$items = EscposImage::load(FCPATH.'assets/uploads/orders/order_'.$random.'.png', false);
        $this->printer->bitImage($items);
        $this->printer->feed();
        $this->printer->cut();
        $this->printer->close();
	}
	
	public function print_bill_html($data)
	{
		$hdtag = '<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
							<link href="'.FCPATH.'themes/default/assets/styles/theme.css" rel="stylesheet"/>
							<style type="text/css">
								#wrapper { max-width: 570px; }
								table td, table th{
									font-size:22px;
									font-family: "Titillium Web","Suwannaphum", sans-serif;
								}
							</style>
						</head>
						<body><div id="wrapper"> ';
						
		$fttag = '</div></body></html>';
		$cotag = $hdtag . $data->text->items . $fttag;
		$pdf = new Image($cotag);
		$pdf->binary = FCPATH.'assets\wkhtmltopdf\wkhtmltoimage.exe';
		$random = date("ymdhis");
		if (!$pdf->saveAs(FCPATH.'assets/uploads/bills/bill_'.$random.'.png')) {
			echo $pdf->getError();
		}
		$items = EscposImage::load(FCPATH.'assets/uploads/bills/bill_'.$random.'.png', false);
        $this->printer->bitImage($items);
        $this->printer->feed();
        $this->printer->cut();
        $this->printer->close();
	}
	
	public function print_receipt_html($data)
	{
		$pdf = new Image($data);
		$pdf->binary = FCPATH.'assets\wkhtmltopdf\wkhtmltoimage.exe';
		$random = date("ymdhis");
		if (!$pdf->saveAs(FCPATH.'assets/uploads/receipts/receipt_'.$random.'.png')) {
			echo $pdf->getError();
		}
		$items = EscposImage::load(FCPATH.'assets/uploads/receipts/receipt_'.$random.'.png', false);
        $this->printer->bitImage($items);
        $this->printer->feed();
        $this->printer->cut();
        $this->printer->close();
	}
	
    function open_drawer() 
	{
        $this->printer->pulse();
        $this->printer->close();
    }

}

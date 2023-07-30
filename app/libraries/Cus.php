<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
 *  ==============================================================================
 *  Author      : Mian Saleem
 *  Email       : veasna.rin@sunfixconsulting.com
 *  For         : Stock Manager Advance
 *  Web         : www.sunfixconsulting.com
 *  ==============================================================================
 */

class Cus
{

    public function __construct()
    {

    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    private function _rglobRead($source, &$array = array())
    {
        if (!$source || trim($source) == "") {
            $source = ".";
        }
        foreach ((array) glob($source . "/*/") as $key => $value) {
            $this->_rglobRead(str_replace("//", "/", $value), $array);
        }
        $hidden_files = glob($source . ".*") and $htaccess = preg_grep('/\.htaccess$/', $hidden_files);
        $files = array_merge(glob($source . "*.*"), $htaccess);
        foreach ($files as $key => $value) {
            $array[] = str_replace("//", "/", $value);
        }
    }
    
    function convertUnit($product_id,$quantity,$price){
        $product = $this->site->getProductByID($product_id);
        $units = $this->site->getProductUnitByProduct($product_id);
        if($units){
            $unit_string = '';
            $operation = '';
            if($quantity < 0){
                $quantity = abs($quantity);
                $operation = '-';
            }
            foreach($units as $unit){
                if($quantity >= $unit->unit_qty && ($quantity % $unit->unit_qty) == 0){
                    $unit_qty = $quantity / $unit->unit_qty;
                    $unit_price = $price * $unit->unit_qty;
                    return array('quantity' => ($operation.$unit_qty),'unit_id'=>$unit->unit_id,'price'=>$unit_price);
                }
            }
        }else{
            return array('quantity' => $quantity,'unit_id'=>$product->unit,'price'=>$price);
        }
    }
    
    function convertQty($product_id,$quantity)
    {
        $units = $this->site->getProductUnitByProduct($product_id);
        if($units){
            $unit_string = '';
            $i=1;
            $operation = '';
            if($quantity < 0){
                $quantity = abs($quantity);
                $operation = '-';
            }
            if($quantity < 1){
                return $quantity;
            }
            foreach($units as $unit){
                if($quantity >= $unit->unit_qty){
                    if($i > 1){
                        $unit_string .=', ';
                    }
                    if($unit->unit_qty==1){
                        $quantity_unit = ($quantity / $unit->unit_qty);
                    }else{
                        $quantity_unit = (int) ($quantity / $unit->unit_qty);
                    }
                    
                    $unit_string .=  $this->formatQuantity($quantity_unit).' <span style="color:#357EBD;">'.$unit->name.'</span>';
                    $quantity = $quantity - ($quantity_unit * $unit->unit_qty);
                    $i++;
                }
            }
            return $operation.$unit_string;
        }else{
            return $quantity;
        }
    }

    private function _zip($array, $part, $destination, $output_name = 'cus')
    {
        $zip = new ZipArchive;
        @mkdir($destination, 0777, true);

        if ($zip->open(str_replace("//", "/", "{$destination}/{$output_name}" . ($part ? '_p' . $part : '') . ".zip"), ZipArchive::CREATE)) {
            foreach ((array) $array as $key => $value) {
                $zip->addFile($value, str_replace(array("../", "./"), null, $value));
            }
            $zip->close();
        }
    }
    
    public function formatOtherMoney($number, $display_symbol = "", $decimals = false)
    {       
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $display_symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? '<small style="font-size:10px;">'.$display_symbol.' </small>'  : '');
        }
        $decimals = $decimals?$decimals:$this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? ' <small style="font-size:10px;">'.$display_symbol.' </small>'  : '') .
        number_format($number, $decimals, $ds, $ts) .
        ($this->Settings->display_symbol == 2 ? ' <small style="font-size:10px;">'.$display_symbol.' </small>' : '');
    }
    public function formatKhMoney($amount, $rate = false){
        if($rate && $rate > 0){
            $kh_rate = $rate;
        }else{
            $currency =  $this->site->getCurrencyByCode("KHR");
            $kh_rate = $currency->rate;
        }
        $amount = $amount * $kh_rate;
        $amount = round($amount / 100) * 100;
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $display_symbol : '') .
            $this->formatSAC($this->formatDecimal($amount)) .
            ($this->Settings->display_symbol == 2 ? '<small style="font-size:10px;">'.$display_symbol.' </small>'  : '');
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($amount, 0, $ds, $ts);
        
    }

    
    public function formatMoney($number)
    {
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
        $decimals = $this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
        number_format($number, $decimals, $ds, $ts) .
        ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
    }
    
    public function formatMoneyKH($number = false)
    {
        $number = (number_format($number / 100) * 100);
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
        $decimals = $this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? '' : '') .
        number_format($number, 0, $ds, $ts) .
        ($this->Settings->display_symbol == 2 ? '' : '');
    }

        public function formatQuantityQty($number = false)
    {
       
        if ($this->Settings->sac) {
            return ($this->Settings->display_symbol == 1 ? $this->Settings->symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $this->Settings->symbol : '');
        }
        $decimals = $this->Settings->decimals;
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return ($this->Settings->display_symbol == 1 ? '' : '') .
        number_format($number, 0, $ds, $ts) .
        ($this->Settings->display_symbol == 0 ? '' : '');
    }


    public function formatQuantity($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->qty_decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatNumber($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatDecimalRaw($number)
    {
        if (!is_numeric($number)) {
            return null;
        }
        return $number;
    }

    public function formatDecimal($number, $decimals = null)
    {
        if (!is_numeric($number)) {
            return null;
        }
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        return number_format($number, $decimals, '.', '');
    }

    public function clear_tags($str)
    {
        return htmlentities(
            strip_tags($str,
                '<span><div><a><br><p><b><i><u><img><blockquote><small><ul><ol><li><hr><big><pre><code><strong><em><table><tr><td><th><tbody><thead><tfoot><h3><h4><h5><h6>'
            ),
            ENT_QUOTES | ENT_XHTML | ENT_HTML5,
            'UTF-8'
        );
    }

    public function decode_html($str)
    {
        return html_entity_decode($str, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8');
    }
    
    public function remove_tag($str)
    {
        return strip_tags(html_entity_decode($str));
    }

    public function roundMoney($num, $nearest = 0.05)
    {
        return round($num * (1 / $nearest)) * $nearest;
    }

    public function roundNumber($number, $toref = null)
    {
        switch ($toref) {
            case 1:
                $rn = round($number * 20) / 20;
                break;
            case 2:
                $rn = round($number * 2) / 2;
                break;
            case 3:
                $rn = round($number);
                break;
            case 4:
                $rn = ceil($number);
                break;
            default:
                $rn = $number;
        }
        return $rn;
    }

    public function unset_data($ud)
    {
        if ($this->session->userdata($ud)) {
            $this->session->unset_userdata($ud);
            return true;
        }
        return false;
    }

    public function hrsd($sdate)
    {
        if ($sdate) {
            return date($this->dateFormats['php_sdate'], strtotime($sdate));
        } else {
            return '0000-00-00';
        }
    }



    public function hrld($ldate, $with_time = false)
    {
        if ($this->Settings->date_with_time == 0 && $with_time == false) {
            return $this->hrsd($ldate);
        }else{
            if ($ldate) {
                return date($this->dateFormats['php_ldate'], strtotime($ldate));
            } else {
                return '0000-00-00 00:00:00';
            }
        }
    }

    public function fsd($inv_date)
    {
        if ($inv_date) {
            $jsd = $this->dateFormats['js_sdate'];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . "-" . substr($inv_date, 3, 2) . "-" . substr($inv_date, 0, 2);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . "-" . substr($inv_date, 0, 2) . "-" . substr($inv_date, 3, 2);
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000-00-00';
        }
    }

    public function fld($ldate, $with_time = false, $last_time = false)
    {
        if ($ldate) {
            $date = explode(' ', $ldate);
            $jsd = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            if($last_time && $this->Settings->date_with_time == 0){
                $time = '23:59:59';
            }else if($this->Settings->date_with_time == 0 && $with_time == false){
                $time = '';
            }else{
                $time = isset($date[1])? $date[1]: '';
            }
            
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                if ($this->Settings->date_with_time == 0 && $with_time == false && $last_time = false) {
                    $date = substr($inv_date, -4) . "-" . substr($inv_date, 3, 2) . "-" . substr($inv_date, 0, 2);
                }else{
                    $date = substr($inv_date, -4) . "-" . substr($inv_date, 3, 2) . "-" . substr($inv_date, 0, 2) . " " . $time;
                }
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                if ($this->Settings->date_with_time == 0 && $with_time == false && $last_time = false) {
                    $date = substr($inv_date, -4) . "-" . substr($inv_date, 0, 2) . "-" . substr($inv_date, 3, 2);
                }else{
                    $date = substr($inv_date, -4) . "-" . substr($inv_date, 0, 2) . "-" . substr($inv_date, 3, 2) . " " . $time;
                }
            } else {
                $date = $inv_date;
            }
            return $date;
        } else {
            return '0000-00-00 00:00:00';
        }
    }

    public function send_email($to, $subject, $message, $from = null, $from_name = null, $attachment = null, $cc = null, $bcc = null)
    {
        $this->load->library('email');
        $config['useragent'] = "Stock Manager Advance";
        $config['protocol'] = $this->Settings->protocol;
        $config['mailtype'] = "html";
        $config['crlf'] = "\r\n";
        $config['newline'] = "\r\n";
        if ($this->Settings->protocol == 'sendmail') {
            $config['mailpath'] = $this->Settings->mailpath;
        } elseif ($this->Settings->protocol == 'smtp') {
            $this->load->library('encrypt');
            $config['smtp_host'] = $this->Settings->smtp_host;
            $config['smtp_user'] = $this->Settings->smtp_user;
            $config['smtp_pass'] = $this->encrypt->decode($this->Settings->smtp_pass);
            $config['smtp_port'] = $this->Settings->smtp_port;
            if (!empty($this->Settings->smtp_crypto)) {
                $config['smtp_crypto'] = $this->Settings->smtp_crypto;
            }
        }

        $this->email->initialize($config);

        if ($from && $from_name) {
            $this->email->from($from, $from_name);
        } elseif ($from) {
            $this->email->from($from, $this->Settings->site_name);
        } else {
            $this->email->from($this->Settings->default_email, $this->Settings->site_name);
        }

        $this->email->to($to);
        if ($cc) {
            $this->email->cc($cc);
        }
        if ($bcc) {
            $this->email->bcc($bcc);
        }
        $this->email->subject($subject);
        $this->email->message($message);
        if ($attachment) {
            if (is_array($attachment)) {
                foreach ($attachment as $file) {
                    $this->email->attach($file);
                }
            } else {
                $this->email->attach($attachment);
            }
        }

        if ($this->email->send()) {
            //echo $this->email->print_debugger(); die();
            return true;
        } else {
            //echo $this->email->print_debugger(); die();
            return false;
        }
    }

    public function checkPermissions($action = null, $js = null, $module = null)
    {
        if (!$this->actionPermissions($action, $module)) {
            $this->session->set_flashdata('error', lang("access_denied"));
            if ($js) {
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
            } else {
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }
        }
    }

    public function actionPermissions($action = null, $module = null)
    {
        if ($this->Owner || $this->Admin) {
            if ($this->Admin && stripos($action, 'delete') !== false) {
                return false;
            }
            return true;
        } elseif ($this->Customer || $this->Supplier) {
            return false;
        } else {
            if (!$module) {
                $module = $this->m;
            }
            if (!$action) {
                $action = $this->v;
            }
            //$gp = $this->site->checkPermissions();
            if ($this->GP[$module . '-' . $action] == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function save_barcode($text = null, $bcs = 'code128', $height = 56, $stext = 1, $get_be = false)
    {
        $drawText = ($stext != 1) ? false : true;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $text, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => ($get_be ? 2 : 1));
        if ($this->Settings->barcode_img && $get_be) {
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::draw($bcs, 'image', $barcodeOptions, $rendererOptions);
            ob_start();
            imagepng($imageResource);
            $imagedata = ob_get_contents();
            ob_end_clean();
            if ($get_be) {
                return 'data:image/png;base64,'.base64_encode($imagedata);
            }
            return "<img src='data:image/png;base64,".base64_encode($imagedata)."' alt='{$text}' class='bcimg' />";
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            // $imageResource = Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            // return $imageResource;
            ob_start();
            Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            $imagedata = ob_get_contents();
            ob_end_clean();
            return "<img src='data:image/svg+xml;base64,".base64_encode($imagedata)."' alt='{$text}' class='bcimg' />";
        }
        return FALSE;
    }
    
    public function qrcode($type = 'text', $text = 'http://sunfixconsulting.com.kh', $size = 2, $level = 'H', $sq = null)
    {
        $file_name = 'assets/uploads/qrcode' . $this->session->userdata('user_id') . ($sq ? $sq : '') . ($this->Settings->barcode_img ? '.png' : '.svg');
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('phpqrcode');
        $config = array('data' => $text, 'size' => $size, 'level' => $level, 'savename' => $file_name);
        if (!$this->Settings->barcode_img) {
            $config['svg'] = 1;
        }
        $this->phpqrcode->generate($config);
        if ($this->Settings->barcode_img) {
            $imagedata = file_get_contents($file_name);
            return "<img src='data:image/png;base64,".base64_encode($imagedata)."' alt='{$text}' class='qrimg' style='float:right;' />";
        }
        $imagedata = file_get_contents($file_name);
        return "<img src='data:image/svg+xml;base64,".base64_encode($imagedata)."' alt='{$text}' class='qrimg' style='float:right;' />";
    }

    public function generate_pdf($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'L')
    {
        if (!$output_type) {
            $output_type = 'D';
        }
        if (!$margin_bottom) {
            $margin_bottom = 10;
        }
        if (!$margin_top) {
            $margin_top = 20;
        }
        $this->load->library('pdf');
        $pdf = new mPDF('utf-8', 'A4-' . $orientation, '13', '', 10, 10, $margin_top, $margin_bottom, 9, 9);
        $pdf->debug = false;
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        // if you need to add protection to pdf files, please uncomment the line below or modify as you need.
        // $pdf->SetProtection(array('print')); // You pass 2nd arg for user password (open) and 3rd for owner password (edit)
        // $pdf->SetProtection(array('print', 'copy')); // Comment above line and uncomment this to allow copying of content
        $pdf->SetTitle($this->Settings->site_name);
        $pdf->SetAuthor($this->Settings->site_name);
        $pdf->SetCreator($this->Settings->site_name);
        $pdf->SetDisplayMode('fullpage');
        $stylesheet = file_get_contents('assets/bs/bootstrap.min.css');
        $pdf->WriteHTML($stylesheet, 1);
        // $pdf->SetFooter($this->Settings->site_name.'||{PAGENO}/{nbpg}', '', TRUE); // For simple text footer

        if (is_array($content)) {
            $pdf->SetHeader($this->Settings->site_name.'||{PAGENO}/{nbpg}', '', TRUE); // For simple text header
            $as = sizeof($content);
            $r = 1;
            foreach ($content as $page) {
                $pdf->WriteHTML($page['content']);
                if (!empty($page['footer'])) {
                    $pdf->SetHTMLFooter('<p class="text-center">' . $page['footer'] . '</p>', '', true);
                }
                if ($as != $r) {
                    $pdf->AddPage();
                }
                $r++;
            }

        } else {

            $pdf->WriteHTML($content);
            if ($header != '') {
                $pdf->SetHTMLHeader('<p class="text-center">' . $header . '</p>', '', true);
            }
            if ($footer != '') {
                $pdf->SetHTMLFooter('<p class="text-center">' . $footer . '</p>', '', true);
            }

        }

        if ($output_type == 'S') {
            $file_content = $pdf->Output('', 'S');
            write_file('assets/uploads/' . $name, $file_content);
            return 'assets/uploads/' . $name;
        } else {
            $pdf->Output($name, $output_type);
        }
    }

    public function print_arrays()
    {
        $args = func_get_args();
        echo "<pre>";
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo "</pre>";
        die();
    }

    public function logged_in()
    {
        return (bool) $this->session->userdata('identity');
    }

    public function in_group($check_group, $id = false)
    {
        if ( ! $this->logged_in()) {
            return false;
        }
        $id || $id = $this->session->userdata('user_id');
        $group = $this->site->getUserGroup($id);
        if ($group && $group->name === $check_group) {
            return true;
        }
        return false;
    }

    public function log_payment($msg, $val = null)
    {
        $this->load->library('logs');
        return (bool) $this->logs->write('payments', $msg, $val);
    }

    public function update_award_points($total, $customer =0, $user=0, $scope = null)
    {
        if($customer > 0){
            if($this->config->item("member_card")==true){
                $member_card = $this->site->getMemberCardByCustomerID($customer);
                if($member_card && (!$member_card->expiry || $member_card->expiry > date('Y-m-d'))){
                    $company = $this->site->getCompanyByID($customer);
                    if(!empty($member_card->each_spent) OR !empty($member_card->ca_point)){
                        $dpos = strpos($member_card->ca_point, '%');
                        if ($dpos !== false) {
                            $pds = explode("%", $member_card->ca_point);
                            $points = (double)($total * $pds[0]) / 100;
                            $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
                            $this->db->update('companies', array('award_points' => $total_points), array('id' => $customer));
                        } else if(!$dpos && !empty($member_card->each_spent) && $total >= $member_card->each_spent){
                            $points = floor(($total / $member_card->each_spent) * $member_card->ca_point);
                            $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
                            $this->db->update('companies', array('award_points' => $total_points), array('id' => $customer));
                        }
                    }else {
                        if(!empty($this->Settings->each_spent) && $total >= $this->Settings->each_spent) {
                            $points = floor(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
                            $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
                            $this->db->update('companies', array('award_points' => $total_points), array('id' => $customer));
                        }
                    }
                }
            }else{
                $company = $this->site->getCompanyByID($customer);
                if (!empty($this->Settings->each_spent) && $total >= $this->Settings->each_spent) {
                    $points = floor(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
                    $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
                    $this->db->update('companies', array('award_points' => $total_points), array('id' => $customer));
                }
            }
        }
        if($user > 0){
            if (!empty($this->Settings->each_sale) && !$this->Customer && $total >= $this->Settings->each_sale) {
                $staff = $this->site->getUser($user);
                if(!empty($staff->each_sale) OR !empty($staff->sa_point)){
                    $dpos = strpos($staff->sa_point, '%');
                    if ($dpos !== false) {
                        $pds = explode("%", $staff->sa_point);
                        $points = (double)($total * $pds[0]) / 100;
                        $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
                        $this->db->update('users', array('award_points' => $total_points), array('id' => $user));
                    }else{
                        $points = floor(($total / $staff->each_sale) * $staff->sa_point);
                        $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
                        $this->db->update('users', array('award_points' => $total_points), array('id' => $user));
                    }
                }else{
                    $points = floor(($total / $this->Settings->each_sale) * $this->Settings->sa_point);
                    $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
                    $this->db->update('users', array('award_points' => $total_points), array('id' => $user));
                }
            }
        }
        return true;
    }

    public function zip($source = null, $destination = "./", $output_name = 'cus', $limit = 5000)
    {
        if (!$destination || trim($destination) == "") {
            $destination = "./";
        }

        $this->_rglobRead($source, $input);
        $maxinput = count($input);
        $splitinto = (($maxinput / $limit) > round($maxinput / $limit, 0)) ? round($maxinput / $limit, 0) + 1 : round($maxinput / $limit, 0);

        for ($i = 0; $i < $splitinto; $i++) {
            $this->_zip(array_slice($input, ($i * $limit), $limit, true), $i, $destination, $output_name);
        }

        unset($input);
        return;
    }

    public function unzip($source, $destination = './')
    {

        // @chmod($destination, 0777);
        $zip = new ZipArchive;
        if ($zip->open(str_replace("//", "/", $source)) === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
        // @chmod($destination,0755);

        return true;
    }

    public function view_rights($check_id, $js = null)
    {
        if (!$this->Owner && !$this->Admin) {
            if ($check_id != $this->session->userdata('user_id')) {
                $this->session->set_flashdata('warning', $this->data['access_denied']);
                if ($js) {
                    die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome') . "'; }, 10);</script>");
                } else {
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
                }
            }
        }
        return true;
    }

    public function makecomma($input)
    {
        if (strlen($input) <= 2) {return $input;}
        $length = substr($input, 0, strlen($input) - 2);
        $formatted_input = $this->makecomma($length) . "," . substr($input, -2);
        return $formatted_input;
    }

    public function formatSAC($num)
    {
        $pos = strpos((string) $num, ".");
        if ($pos === false) {$decimalpart = "00";} else {
            $decimalpart = substr($num, $pos + 1, 2);
            $num = substr($num, 0, $pos);}

        if (strlen($num) > 3 & strlen($num) <= 12) {
            $last3digits = substr($num, -3);
            $numexceptlastdigits = substr($num, 0, -3);
            $formatted = $this->makecomma($numexceptlastdigits);
            $stringtoreturn = $formatted . "," . $last3digits . "." . $decimalpart;
        } elseif (strlen($num) <= 3) {
            $stringtoreturn = $num . "." . $decimalpart;
        } elseif (strlen($num) > 12) {
            $stringtoreturn = number_format($num, 2);
        }

        if (substr($stringtoreturn, 0, 2) == "-,") {$stringtoreturn = "-" . substr($stringtoreturn, 2);}

        return $stringtoreturn;
    }

    public function md($page = FALSE)
    {
        die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($page ? site_url($page) : (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome')) . "'; }, 10);</script>");
    }

    public function analyze_term($term)
    {
        $spos = strpos($term, $this->Settings->barcode_separator);
        if ($spos !== false) {
            $st = explode($this->Settings->barcode_separator, $term);
            $sr = trim($st[0]);
            $option_id = trim($st[1]);
        } else {
            $sr = $term;
            $option_id = false;
        }
        return array('term' => $sr, 'option_id' => $option_id);
    }

    public function cash_opts($paid_by = null, $deposit = false, $empty_opt = false, $gift_card = false){
        $opts = '';
        if(!$paid_by){
            $paid_by = $this->Settings->default_cash_account;
        }
        if ($empty_opt) {
            $opts .= '<option value="">'.lang('select').' '.lang('cash_account').'</option>';
        }
        $cash_accounts = $this->site->getCashAccounts();
        if($cash_accounts){
            foreach($cash_accounts as $cash_account){
                $opts .= '<option cash_type="'.$cash_account->type.'" value="'.$cash_account->id.'" '.($paid_by && $paid_by == $cash_account->id ? ' selected="selected"' : '').'>'.$cash_account->name.'</option>';
            }
        }
        if (!$deposit) {
            $opts .= '<option cash_type="deposit" value="deposit"'.($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '').'>'.lang("deposit").'</option>';
        }
        if($this->config->item("gift_card")){
            if (!$gift_card) {
                $opts .= '<option cash_type="gift_card" value="gift_card"'.($paid_by && $paid_by == 'gift_card' ? ' selected="selected"' : '').'>'.lang("gift_card").'</option>';
            }
        }
        return $opts;
    }

    public function paid_opts($paid_by = null, $purchase = false, $empty_opt = false, $gift_card = false, $credit_card=false)
    {
        $opts = '';
        if ($empty_opt) {
            $opts .= '<option value="">'.lang('select').'</option>';
        }
        $opts .= '
        <option value="cash"'.($paid_by && $paid_by == 'cash' ? ' selected="selected"' : '').'>'.lang("cash").'</option>
        <option value="Cheque"'.($paid_by && $paid_by == 'Cheque' ? ' selected="selected"' : '').'>'.lang("cheque").'</option>';
        if (!$credit_card) {
            $opts .= '<option value="CC"'.($paid_by && $paid_by == 'CC' ? ' selected="selected"' : '').'>'.lang("CC").'</option>';
        }
        if (!$purchase) {
            $opts .= '<option value="deposit"'.($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '').'>'.lang("deposit").'</option>';
        }
        
        if($this->config->item("gift_card")){
            if (!$gift_card) {
                $opts .= '<option value="gift_card"'.($paid_by && $paid_by == 'gift_card' ? ' selected="selected"' : '').'>'.lang("gift_card").'</option>';
            }
        }
        
        $opts .= '<option value="other"'.($paid_by && $paid_by == 'other' ? ' selected="selected"' : '').'>'.lang("other").'</option>';
        
        return $opts;
    }
    
        public function send_json($data)
    {
        header('Content-Type: application/json');
        die(json_encode($data));
        exit;
    }
    
    public function numberToKhmer($number)
    {
        $numbers = str_split($number);
        $khmer_numbers = array('0'=>'០','1'=>'១','2'=>'២','3'=>'៣','4'=>'៤','5'=>'៥','6'=>'៦','7'=>'៧','8'=>'៨','9'=>'៩');
        if($numbers){
            $khmer_number = '';
            foreach($numbers as $number){
                $khmer_number .= $khmer_numbers[$number];
            }
            return $khmer_number;
        }
        return false;
    }

    public function NumberToNumber($number)
    {
        $numbers = str_split($number);
        $khmer_numbers = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9');
        if($numbers){
            $khmer_number = '';
            foreach($numbers as $number){
                $khmer_number .= $khmer_numbers[$number];
            }
            return $khmer_number;
        }
        return false;
    }
    
    public function numberToMonth($number)
    {
        $khmer_months = array('01'=>lang('jan'),'02'=>lang('feb'),'03'=>lang('mar'),'04'=>lang('apr'),'05'=>lang('may'),'06'=>lang('jun'),'07'=>lang('jul'),'08'=>lang('aug'),'09'=>lang('sep'),'10'=>lang('oct'),'11'=>lang('nov'),'12'=>lang('dec'));
        if($khmer_months[$number]){
            return $khmer_months[$number];
        }
        return false;
    }
    
    public function numberToKhmerMonth($number)
    {
        $khmer_months = array('01'=>'មករា','02'=>'កុម្ភៈ','03'=>'មីនា','04'=>'មេសា','05'=>'ឧសភា','06'=>'មិថុនា','07'=>'កក្កដា','08'=>'សីហា','09'=>'កញ្ញា','10'=>'តុលា','11'=>'វិច្ឆិកា','12'=>'ធ្នូ');
        if($khmer_months[$number]){
            return $khmer_months[$number];
        }
        return false;
    }

    public function numberTonNumberMonth($number)
    {
        $khmer_months = array('01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12');
        if($khmer_months[$number]){
            return $khmer_months[$number];
        }
        return false;
    }

    public function numberToYearNumber($number)
    {
        $numbers = str_split($number);
        $khmer_numbers = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9');
        if($numbers){
            $khmer_number = '';
            foreach($numbers as $number){
                $khmer_number .= $khmer_numbers[$number];
            }
            return $khmer_number;
        }
        return false;
    }
    
    public function convertKhmerDate($date){
        $date = explode('-',$date);
        if($date){
            $day = $this->numberToKhmer($date[2]);
            $month = $this->numberToKhmerMonth($date[1]);
            $year = $this->numberToKhmer($date[0]);         
            $khmer_date = $day.' '.$month.' '.$year;
            return $khmer_date;
        }
        return false;   
    }

    public function convertStandardEnDate($date){
        $date = explode('-',$date);
        if($date){
            $day = $this->NumberToNumber($date[2]);
            $month = $this->numberToMonth($date[1]);
            $year = $this->NumberToNumber($date[0]);         
            $khmer_date = $day.'-'.$month.'-'.$year;
            return $khmer_date;
        }
        return false;   
    }
    
    public function dateToKhmerDate($date)
    {
        $date = explode('/',$date);
        if($date){
            $day = $this->numberToKhmer($date[0]);
            $month = $this->numberToKhmerMonth($date[1]);
            $year = $this->numberToKhmer($date[2]);         
            $khmer_date = 'ថ្ងៃទី'.$day.' ខែ'.$month.' ឆ្នាំ'.$year;
            return $khmer_date;
        }
        return false;   
    }

    public function dateToKhmerConvert($date)
    {
        $date = explode('/',$date);
        if($date){
            $day = $this->numberToKhmer($date[0]);
            $month = $this->numberToKhmer($date[1]);
            $year = $this->numberToKhmer($date[2]);         
            $khmer_date = ''.$day.'/'.$month.'/'.$year;
            return $khmer_date;
        }
        return false;   
    }

    public function dateToStandardDate($date)
    {
        $date = explode('/',$date);
        if($date){
            $day = $this->NumberToNumber($date[0]);
            $month = $this->numberTonNumberMonth($date[1]);
            $year = $this->numberToYearNumber($date[2]);         
            $khmer_date = 'ថ្ងៃទី'.$day.' ខែ'.$month.' ឆ្នាំ'.$year;
            return $khmer_date;
        }
        return false;   
    }
    
    public function numberToWords($number, $kh='')
    {
        if (($number < 0) || ($number > 999999999))
        {
            //throw new Exception("Number is out of range");
            return  "Number is out of range";
        }

        $Gn = floor($number / 1000000);  /* Millions (giga) */
        $number -= $Gn * 1000000;
        $kn = floor($number / 1000);     /* Thousands (kilo) */
        $number -= $kn * 1000;
        $Hn = floor($number / 100);      /* Hundreds (hecto) */
        $number -= $Hn * 100;
        $Dn = floor($number / 10);       /* Tens (deca) */
        $n = $number % 10;               /* Ones */

        $res = "";

        if ($Gn)
        {
            $res .= $this->numberToWords ($Gn,$kh) . ($kh==""?" Million":"លាន");
        }

        if ($kn)
        {
            $res .= (empty($res) ? "" : " ") .
                $this->numberToWords ($kn,$kh) . ($kh==""?" Thousand":"ពាន់");
        }

        if ($Hn)
        {
            $res .= (empty($res) ? "" : " ") .
                $this->numberToWords ($Hn,$kh) . ($kh==""?" Hundred":"រយ");
        }

        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
            "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
            "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",
            "Nineteen");
        $tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty",
            "Seventy", "Eighty", "Ninety");

        $oneskh = array("", "មួយ", "ពីរ", "បី", "បួន", "ប្រាំ", "ប្រាំមួយ",
            "ប្រាំពីរ", "ប្រាំបី", "ប្រាំបួន", "ដប់", "ដប់មួយ", "ដប់ពីរ", "ដប់បី",
            "ដប់បួន", "ដប់ប្រាំ", "ដប់ប្រាំមួយ", "ដប់ប្រាពីរ", "ដប់ប្រាំបី",
            "ដប់ប្រាំបួន");
        $tenskh = array("", "", "ម្ភៃ", "សាមសិប", "សែសិប", "ហាសិប", "ហុកសិប",
            "ចិតសិប", "ប៉ែតសិប", "កៅសិប");

        if ($Dn || $n)
        {
            if (!empty($res))
            {

                $res .= ($fpont>0?" ":($kh==""?" ":""));
            }

            if ($Dn < 2)
            {
                $res .= ($kh==""?$ones[$Dn * 10 + $n]:$oneskh[$Dn * 10 + $n]);
            }
            else
            {
                $res .= ($kh==""?$tens[$Dn]:$tenskh[$Dn]);

                if ($n)
                {
                    $res .= ($kh==""?"-".$ones[$n]:$oneskh[$n]);
                }
            }
        }

        if (empty($res))
        {
            $res = ($kh==""?"zero":"សូន្យ");
        }

        return $res;
    }

    public function numberToWordsCur($numberf, $kh='', $cur="USD", $cur_h = "Cents") 
    {
        $numberf = round($numberf,2);
        $arr = explode('.',$numberf);
        $number = $arr[0]-0;
        $fpont = $arr[1]-0;
        $f = '';
        if($fpont > 0){
            $fpont = str_pad($fpont, 2, '0',STR_PAD_RIGHT)-0;
            $f = ($kh==""?" and ": " ​ និង  "). $this->numberToWords($fpont,$kh).$cur_h;
        }
        $res = $this->numberToWords($number,$kh).' '.$cur;
        return $res.$f;
    }
    
    public function row_status($status = NULL) 
    {
        if($status == null) {
            return '';
        } else if($status == 'pending' || $status == 'assigned'  || $status == 'reservation' || $status == 'cleared' || $status == 'repairing' || $status == 'dirty') {
            return '<div class="text-center"><span class="row_status label label-warning">'.lang($status).'</span></div>';
    } else if($status == 'completed' || $status == 'paid' || $status == 'sent' || $status == 'received'  || $status == 'active' || $status == 'authorized' || $status == 'sent' || $status == 'checked_in' || $status == 'cleaned') {
            return '<div class="text-center"><span class="row_status label label-success">'.lang($status).'</span></div>';
        } else if($status == 'partial' || $status == 'transferring' || $status == 'ordered' || $status == 'approved' || $status == 'payoff' || $status == 'done' || $status == 'free') {
            return '<div class="text-center"><span class="row_status label label-info">'.lang($status).'</span></div>';
        } else if($status == 'due' || $status == 'returned' || $status == 'rejected' || $status == 'inactive' || $status == 'sold' || $status == 'unauthorize' || $status == 'deleted' || $status == 'not_done' || $status == 'maintenance') {
            return '<div class="text-center"><span class="row_status label label-danger">'.lang($status).'</span></div>';
        } else {
            return '<div class="text-center"><span class="row_status label label-default">'.$status.'</span></div>';
        }
    }

    public function cash_opts_report($paid_by = null, $deposit = false, $empty_opt = false, $gift_card = false){
        $opts = '';
        
        if ($empty_opt) {
            $opts .= '<option value="">'.lang('select').' '.lang('cash_account').'</option>';
        }
        $cash_accounts = $this->site->getCashAccounts();
        if($cash_accounts){
            foreach($cash_accounts as $cash_account){
                $opts .= '<option cash_type="'.$cash_account->type.'" value="'.$cash_account->id.'" '.($paid_by && $paid_by == $cash_account->id ? ' selected="selected"' : '').'>'.$cash_account->name.'</option>';
            }
        }
        if (!$deposit) {
            $opts .= '<option cash_type="deposit" value="deposit"'.($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '').'>'.lang("deposit").'</option>';
        }
        if($this->config->item("gift_card")){
            if (!$gift_card) {
                $opts .= '<option cash_type="gift_card" value="gift_card"'.($paid_by && $paid_by == 'gift_card' ? ' selected="selected"' : '').'>'.lang("gift_card").'</option>';
            }
        }
        return $opts;
    }
    
    function productFormulation($product_id,$width = 0,$height = 0,$square = 0,$quantity = 0)
    {
        $total_quantity = $square * $quantity;
        $formulations = $this->site->getProductFormulation($product_id);
        $formulation_products = array();
        if($formulations){
            foreach($formulations as $formulation){
                $for_width = $formulation->for_width;
                $for_height = $formulation->for_height;
                $for_square = $formulation->for_square;
                $for_qty = $formulation->for_qty;
                $continuse = 1;
                if($for_width  || $for_height  || $for_square  || $for_qty ){
                   
                    if($for_width != ''){
                        $for_width = eval('return '.strtr($for_width, array('x' => $width)).';');
                        if(!$for_width){
                            $continuse = 0;
                        }
                    }

                    if($for_height != ''){
                        $for_height = eval('return '.strtr($for_height, array('x' => $height)).';');
                        if(!$for_height){
                            $continuse = 0;
                        }
                    }

                    if($for_square != ''){
                        $for_square = eval('return '.strtr($for_square, array('x' => $square)).';');
                        if(!$for_square){
                            $continuse = 0;
                        }
                    }

                    if($for_qty != ''){
                        $for_qty = eval('return '.strtr($for_qty, array('x' => $quantity)).';');
                        if(!$for_qty){
                            $continuse = 0;
                        }
                    }
                    
                }
                
                if($continuse==1){
                    $for_product_id = $formulation->for_product_id;
                    $for_field = $formulation->for_field;
                    $for_operation = $formulation->for_operation;
                    $for_unit_id = $formulation->for_unit_id;
                    $for_caculation = $formulation->for_caculation;
                    if($for_field=='width'){
                        $field = $width;
                    }else if($for_field=='height'){
                        $field = $height;
                    }else if($for_field=='square'){
                        $field = $square;
                    }else if($for_field=='qty'){
                        $field = $quantity;
                    }else if($for_field=='total_qty'){
                        $field = $total_quantity;
                    }else{
                        $field = 0 ;
                    }
                    $extract_qty = 0;
                    if(strpos($for_caculation,'+') || strpos($for_caculation,'-') || strpos($for_caculation,'*') || strpos($for_caculation,'/') || strpos($for_caculation,'U') || strpos($for_caculation,'D')== true){
                        $split = str_split($for_caculation);
                        $last = count($split);
                        $number = '';
                        $round = '';
                        $operation = '';
                        $old_operation = '';
                        $i = 1;
                        $f = 0;
                        foreach($split as $row){
                            $row=trim($row);
                            if($row =='+' || $row == '-' || $row =='*' || $row == '/'){
                                if($row=='+'){
                                    $operation = '+';
                                }else if($row=='-'){
                                    $operation = '-';
                                }else if($row=='*'){
                                    $operation = '*';
                                }else{
                                    $operation = '/';
                                }
                                if($f==0 && $field != 0){
                                    $value = (float) $number;
                                    if($for_operation=='multiple'){
                                        $extract_qty = $field * $value;
                                    }else if($for_operation=='divide'){
                                        $extract_qty = $field / $value;
                                    }else if($for_operation=='add'){
                                        $extract_qty = $field + $value;
                                    }else if($for_operation=='subtraction'){
                                        $extract_qty = $field - $value;
                                    }
                                    $f = 1;
                                }else{
                                    $value = (float) $number;
                                    //=========customerize========//
                                        if($round != ''){
                                            if(($field%2)!=0 || strpos($field, '.')!== false){
                                                $value = $value+1;
                                            }
                                        }
                                    //=========customerize========//
                                    if($old_operation=='+'){
                                        $extract_qty = $extract_qty + $value;
                                    }else if($old_operation=='-'){
                                        $extract_qty = $extract_qty - $value;
                                    }else if($old_operation=='*'){
                                        $extract_qty = $extract_qty * $value;
                                    }else if($old_operation=='/'){
                                        $extract_qty = $extract_qty / $value;
                                    }else{
                                        $extract_qty = $value;
                                    }
                                }
                                $number = '';
                                $old_operation = $operation;
                                
                            }else if($row=='U' || $row=='D'){
                                if($row=='U'){
                                    $round = 'ceil';
                                }else{
                                    $round = 'floor';
                                }
                            }else if(is_numeric($row) || $row=='.'){
                                $number = $number.$row;
                            }
                            
                            if($i==$last){
                                $value = (float) $number;
                                if($old_operation=='+'){
                                    $extract_qty = $extract_qty + $value;
                                }else if($old_operation=='-'){
                                    $extract_qty = $extract_qty - $value;
                                }else if($old_operation=='*'){
                                    $extract_qty = $extract_qty * $value;
                                }else if($old_operation=='/'){
                                    $extract_qty = $extract_qty / $value;
                                }
                                
                                if($round == 'ceil'){
                                    $extract_qty = ceil($extract_qty);
                                }else if($round== 'floor'){
                                    $extract_qty = floor($extract_qty);
                                }
                            }
                            $i++;
                        }
                    }else{
                        $value = (float) $for_caculation;
                        if(($field == 0) || ($for_operation == 'equal')){
                            $extract_qty = $value;
                        }else if($for_operation=='multiple'){
                            $extract_qty = $field * $value;
                        }else if($for_operation=='divide'){
                            $extract_qty = $field / $value;
                        }else if($for_operation=='add'){
                            $extract_qty = $field + $value;
                        }else if($for_operation=='subtraction'){
                            $extract_qty = $field - $value;
                        }
                    }
                    $formulation_products[] = array( 
                                                    'for_product_id' => $for_product_id,
                                                    'for_quantity' => $extract_qty,
                                                    'for_unit_id' => $for_unit_id,
                                                    );
                }
            }
            return $formulation_products;
        }
        return false;
    }

    public function secTotime($number,$format = '%02d:%02d:%02d')
    {
        if($number > 0){
            $hour = floor($number / 3600);
            $minute = floor($number / 60 % 60);
            $second = floor($number % 60);
            return sprintf($format, $hour, $minute, $second);
        }
        return '';
    }
    
    public function round_time($round_min,$minimum_min,$actual_time)
    {
        if($round_min > 0 && $minimum_min > 0 && $actual_time > 0){
            $round_min = $round_min * 60;
            $minimum_min = $minimum_min * 60;
            $cut = ((int)($actual_time / $minimum_min)) * $minimum_min;
            $over = $actual_time - $cut;
            if($over >= $round_min){
                $round_time = $cut + $minimum_min;
            }else{
                $round_time =  $cut;
            }
            return $round_time;
            
        }
        return $actual_time;
    }
    
    function configNotification($link = false) 
    {
        $heading = array(
            "en" => $this->Settings->site_name
        );
        $content = array(
            "en" => ucfirst($this->session->userdata('username')).' - '.$this->session->userdata('message'),
        );
        $fields = array(
            'app_id' => "2515bcb1-78e5-4370-ac60-2c6f629bf694",
            'included_segments' => array('All'),
            'heading' => $heading,
            'contents' => $content,
            'url' => site_url($link),
            'chrome_web_image' => '',
        );
        $fields = json_encode($fields);
        print("\nJSON sent:\n");
        print($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic NWM4YWYwM2YtN2VlZC00YzVjLWJmYzItZDM1YWZmZTgyZTVl'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    
    function sendNotification($link = false)
    {
        $response = $this->configNotification($link);
        $return["allresponses"] = $response;
        $return = json_encode($return);
        $data = json_decode($response, true);
        print_r($data);
        $id = $data['id'];
        print_r($id);
        print("\n\nJSON received:\n");
        print($return);
        print("\n");
    }
    
    function numberToEngWord($number){
        $numbers = array(
                        '1' => 'First',
                        '2' => 'Second',
                        '3' => 'Third',
                        '4' => 'Fourth',
                        '5' => 'Fifth',
                        '6' => 'Sixth',
                        '7' => 'Seventh',
                        '8' => 'Eighth',
                        '9' => 'Ninth',
                    );
        if(isset($numbers[$number]) && $numbers[$number]){
            return $numbers[$number];
        }else{
            return $number;
        }       
        
    }

    function isValidDateTime($dateTime)
    {
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }

        return false;
    }

    function unitQty($quantity = false, $unit_id = false){
        $unit = $this->site->getUnitByID($unit_id);
        if($unit){
            return $this->formatQuantity($quantity).' <span style="color:#357EBD;">'.$unit->name.'</span>';
        }else{
            return $this->formatQuantity($quantity);
        }

    }

    
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->cus->md('login');
        }
        $this->lang->load('products', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('products_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'cus_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
    }
	
    function index($warehouse_id = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['product_units'] = json_encode($this->products_model->getProductUnits());
		$this->data['warehouse_id'] = $warehouse_id;
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : NULL;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('products'), 'bc' => $bc);
        $this->core_page('products/index', $meta, $this->data);
    }

    function getProducts($warehouse_id = NULL)
    {
        $this->cus->checkPermissions('index', TRUE);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $detail_link = anchor('products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' id='a__$1' href='" . site_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . "</a>";
        $single_barcode = anchor('products/print_barcodes/$1/'.$warehouse_id, '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
		<ul class="dropdown-menu pull-right" role="menu">
			<li>' . $detail_link . '</li>
			<li><a href="' . site_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
			<li><a href="' . site_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . site_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
			if($this->Settings->product_serial == 1){	
				$action .= '<li><a href="' . site_url('products/set_serials/$1/' . $warehouse_id.'') . '"><i class="fa fa-bars"></i> '
				. lang('set_serial') . '</a></li>';
			}
        }
        $action .= '<li><a href="' . site_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
			<li>' . $single_barcode . '</li>
			<li class="divider"></li>
			<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
		
		$warehouse_query = '';
		if ($this->Settings->show_warehouse_qty) {
			$warehouses = $this->site->getWarehouses();
			if($warehouses){
				foreach($warehouses as $warehouse){
					$warehouse_query .= 'convert_qty('.$this->db->dbprefix("products").'.id,IFNULL((IF('.$this->db->dbprefix("products").'.type="service" OR '.$this->db->dbprefix("products").'.type="bom","0",(SELECT IFNULL(quantity,0) as quantity from '.$this->db->dbprefix("warehouses_products").' WHERE '.$this->db->dbprefix("warehouses_products").'.product_id = '.$this->db->dbprefix("products").'.id and '.$this->db->dbprefix("warehouses_products").'.warehouse_id = "'.$warehouse->id.'" GROUP BY '.$this->db->dbprefix("warehouses_products").'.product_id))),0)) as qty_'.$warehouse->id.',';
				}
			}
		}
		$allow_category = $this->site->getCategoryByProject();
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
            ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type, {$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit, cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='service_rental' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(wp.quantity, 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, wp.rack as rack, alert_quantity", FALSE)
            ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT product_id, quantity, rack, warehouse_id from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                ->where('wp.warehouse_id', $warehouse_id)
                ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.type !=','problem')
            ->group_by("products.id,wp.warehouse_id");
	
        } else if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type,{$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit,  cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='service_rental' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(sum(".$this->db->dbprefix('warehouses_products').".quantity), 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, '' as rack, products.alert_quantity", FALSE)
                ->from('products')
				->join('warehouses_products', 'warehouses_products.product_id = products.id', 'inner')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.type !=','problem')
               // ->where('products.type !=','service_rental')
				->where_in('warehouses_products.warehouse_id',json_decode($this->session->userdata('warehouse_id')))
                ->group_by("products.id");
		} else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type,{$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit,  cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='service_rental' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(quantity, 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, '' as rack, alert_quantity", FALSE)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.type !=','problem')
                //->where('products.type !=','service_rental')
                ->group_by("products.id");
        }
		
		if($allow_category){
			$this->datatables->where_in("products.category_id",$allow_category);
		}
		
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column("cost");
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column("price");
            }
        }
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
            ->or_where('supplier2', $supplier)
            ->or_where('supplier3', $supplier)
            ->or_where('supplier4', $supplier)
            ->or_where('supplier5', $supplier);
        }
        $this->datatables->add_column("Actions", $action, "productid, image, code, name");
        echo $this->datatables->generate();
    }

    function set_rack($product_id = NULL, $warehouse_id = NULL)
    {
        $this->cus->checkPermissions('index', true);

        $this->form_validation->set_rules('rack', lang("rack_location"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = array('rack' => $this->input->post('rack'),
                'product_id' => $product_id,
                'warehouse_id' => $warehouse_id,
            );
        } elseif ($this->input->post('set_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("products");
        }

        if ($this->form_validation->run() == true && $this->products_model->setRack($data)) {
            $this->session->set_flashdata('message', lang("rack_set"));
            redirect("products/" . $warehouse_id);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product'] = $this->site->getProductByID($product_id);
            $wh_pr = $this->products_model->getProductQuantity($product_id, $warehouse_id);
            $this->data['rack'] = $wh_pr['rack'];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/set_rack', $this->data);

        }
    }

    function product_barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        if ($this->Settings->barcode_img) {
            return "<img src='" . site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height) . "' alt='{$product_code}' class='bcimg' />";
        } else {
			return "<iframe src='".site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height)."' style='border:0; width:200px;'></iframe>";
            //return $this->gen_barcode($product_code, $bcs, $height);
        }
    }

    function barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        return site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height);
    }

    function gen_barcode($product_code = NULL, $bcs = 'code128', $height = 60, $text = 1)
    {
        $drawText = ($text != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1.0);
        if ($this->Settings->barcode_img) { 
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
            return $imageResource;
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            header("Content-Type: image/svg+xml");
            echo $imageResource;
        }
    }
	
	function set_serials($product_id = NULL, $warehouse_id = NULL)
	{
		$this->cus->checkPermissions('serial');
		$this->form_validation->set_rules('product_id', lang("product_id"), 'required');
		if ($this->form_validation->run() == true) {
			$warehouse_id = $this->input->post('warehouse_id');
			$product_id = $this->input->post('product_id');
			$i = isset($_POST['serial_no']) ? sizeof($_POST['serial_no']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$serial_number = $_POST['serial_no'][$r];				
				if($serial_number!=''){							
					$cost = $_POST['cost'][$r];
					$price = $_POST['price'][$r];
					$color = $_POST['color'][$r];
					$description = $_POST['description'][$r];
					$supplier_id = $_POST['supplier_id'][$r];
					$supplier = $_POST['supplier'][$r];
					$purchase_id = $_POST['purchase_id'][$r];
					$receive_id = $_POST['receive_id'][$r];
					$transfer_id = $_POST['transfer_id'][$r];
					$pawn_id = $_POST['pawn_id'][$r];
					$adjustment_id = $_POST['adjustment_id'][$r];
					$date = $this->cus->fld($_POST['serial_date'][$r]);
					$data[] = array(
								'product_id' => $product_id,
								'warehouse_id' => $warehouse_id,
								'date' => $date,
								'serial' => $serial_number,
								'description' => $description,
								'color' => $color,
								'cost' => $cost,
								'price' => $price,
								'supplier_id' => $supplier_id,
								'supplier' => $supplier,
								'purchase_id' => $purchase_id,
								'receive_id' => $receive_id,
								'transfer_id' => $transfer_id,
								'pawn_id' => $pawn_id,
								'adjustment_id' => $adjustment_id,
							);
				}		
			}
		}
		if ($this->form_validation->run() == true && $this->products_model->addSerials($data,$product_id,$warehouse_id)) {
            $this->session->set_flashdata('message', lang("serial_set"));
            redirect('products/set_serials/'.$product_id.'/'.$warehouse_id);
        }else{
			if(($product = $this->site->getProductByID($product_id)) && $warehouse_id) {
				$productSerials = $this->products_model->getProductSerialDetailsByProductId($product_id,$warehouse_id);
				$warehouse_product = $this->site->getWarehouseProduct($warehouse_id, $product_id);
				$this->data['product'] = $product;
				$this->data['product_serials'] = $productSerials;
				$this->data['warehouse_product'] = $warehouse_product;
				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('set_serials')));
				$meta = array('page_title' => lang('set_serials'), 'bc' => $bc);
				$this->core_page('products/set_serials', $meta, $this->data);
            }else{
				redirect('products');
			}
		}
	}
	
	function set_serial_by_excel($product_id = NULL, $warehouse_id = NULL)
    {
		$this->cus->checkPermissions('serial');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		$this->form_validation->set_rules('product', lang("product"), 'required');
		$this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        if ($this->form_validation->run() == true) {
			$data = false;
			$product_id = $this->input->post('product');
			$warehouse_id = $this->input->post('warehouse');
			if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
						$supplier = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$date = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$serial = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$cost = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$price = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						$color = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
						$description = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
						
						if(trim($serial)!=''){
							if (strpos($date, '/') == false) {
								$date = PHPExcel_Shared_Date::ExcelToPHP($date);
								$date = date('d/m/Y',$date);
							}
							$final[] = array(
								'supplier'  => $supplier,
								'date'   => $date,
								'serial'   => $serial,
								'cost'    => $cost,
								'price'  => $price,
								'color'   => $color,
								'description'   => $description,
							);	
						}
					}
				}
				$warehouse_product = $this->products_model->getProductQuantity($product_id, $warehouse_id);
				$total_active_serial = $this->products_model->getActiveProductSerialID($product_id, $warehouse_id);
				$total_product = $warehouse_product['quantity'];
				$active_serial = count($total_active_serial);
				$import_serial = count($final);
				$unset_serial = $total_product - $active_serial;
				if($import_serial > $unset_serial){
					$error = lang('serial_is_more_than_product_qty');
					$this->session->set_flashdata('error', $error);
					redirect($_SERVER["HTTP_REFERER"]);
				}else{
					$rw = 2;
					$checkSerial = false;
					foreach ($final as $csv_pr) {
						if(isset($checkSerial[trim($csv_pr['serial'])]) && $checkSerial[trim($csv_pr['serial'])]){
							$this->session->set_flashdata('error', lang("serial_no") . " (" . $csv_pr['serial'] . "). " . lang("serial_exist") . " " . lang("line_no") . " " . $rw);
							redirect($_SERVER["HTTP_REFERER"]);
						}
						
						$product_serial = $this->products_model->getProductSerial(trim($csv_pr['serial']),$product_id,$warehouse_id);
						if($product_serial && $product_serial->inactive != 1){
							$this->session->set_flashdata('error', lang("serial_no") . " (" . $csv_pr['serial'] . "). " . lang("serial_exist") . " " . lang("line_no") . " " . $rw);
							redirect($_SERVER["HTTP_REFERER"]);
						}
						$checkSerial[trim($csv_pr['serial'])] = true;
						$supplier = $this->site->getCompanyByCode(trim($csv_pr['supplier']));
						$data[] = array(
								'product_id' => $product_id,
								'warehouse_id' => $warehouse_id,
								'date' => $this->cus->fsd(trim($csv_pr['date'])),
								'serial' => trim($csv_pr['serial']),
								'description' => trim($csv_pr['description']),
								'color' => trim($csv_pr['color']),
								'cost' => trim($csv_pr['cost']),
								'price' => trim($csv_pr['price']),
								'supplier_id' => ($supplier ? $supplier->id : ''),
								'supplier' => ($supplier ? $supplier->name : ''),
							);
						$rw++;
					}
				}

            }
			if(!$data){
				$this->form_validation->set_rules('serial', lang("serial"), 'required');
			}
        } elseif ($this->input->post('set_serial')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->products_model->importSerials($data)) {
            $this->session->set_flashdata('message', lang("serial_set"));
            redirect($_SERVER["HTTP_REFERER"]);
		}else {
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
			$this->data['product_id'] = $product_id;
			$this->data['warehouse_id'] = $warehouse_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'products/set_serial_by_excel', $this->data);

        }
    }
	
    function print_barcodes($product_id = NULL, $warehouse_id = NULL)
    {
        $this->cus->checkPermissions('barcode', true);

        $this->form_validation->set_rules('p_width', lang("paper_width"), 'required');
		$this->form_validation->set_rules('p_height', lang("paper_height"), 'required');
		$this->form_validation->set_rules('b_width', lang("barcode_width"), 'required');
		$this->form_validation->set_rules('b_height', lang("barcode_height"), 'required');

        if ($this->form_validation->run() == true) {

            $style = $this->input->post('style');
            $bci_size = $this->input->post('b_size');
            $currencies = $this->site->getAllCurrencies();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
							
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                redirect("products/print_barcodes");
            }
			
			$barcode_setting = array(
									'p_width' => $this->input->post('p_width'),
									'p_height' => $this->input->post('p_height'),
									'p_padding_top' => $this->input->post('p_padding_top'),
									'p_padding_bottom' => $this->input->post('p_padding_bottom'),
									'p_padding_left' => $this->input->post('p_padding_left'),
									'p_padding_right' => $this->input->post('p_padding_right'),
									'b_width' => $this->input->post('b_width'),
									'b_height' => $this->input->post('b_height'),
									'b_padding_top' => $this->input->post('b_padding_top'),
									'b_padding_bottom' => $this->input->post('b_padding_bottom'),
									'b_padding_left' => $this->input->post('b_padding_left'),
									'b_padding_right' => $this->input->post('b_padding_right'),
									'b_quantity' => $this->input->post('b_quantity'),
									'b_size' => $this->input->post('b_size'),
								);
			$this->products_model->updateBarcodeSetting($barcode_setting,$style);						
			
			$serial = $this->input->post('serial');
			$barcodes = array();
            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->products_model->getProductWithCategory($pid);
                $unit = $this->site->getUnitByID($product->unit);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                $product->unit  = $unit->name;
                if($serial){
					$serials = $this->products_model->getActiveProductSerialID($pid);
					$s_serial = $this->input->post('s_serial');

					foreach($serials as $row_serial){
						if($s_serial=='' OR $s_serial == $row_serial->serial){
							$barcodes[] = array(
								'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
								'name' => $this->input->post('product_name') ? $product->name : FALSE,
								'image' => $this->input->post('product_image') ? $product->image : FALSE,
								'barcode' => $this->product_barcode($row_serial->serial, $product->barcode_symbology, $bci_size),
								'price' => $this->input->post('price') ?  ($row_serial->price > 0 ? $this->cus->formatMoney($row_serial->price) : $this->cus->formatMoney($product->price)) : FALSE,
								'unit' => $this->input->post('unit') ? $product->unit : FALSE,
								'category' => $this->input->post('category') ? $product->category : FALSE,
								'currencies' => $this->input->post('currencies'),
								'variants' => FALSE,
								'quantity' => $quantity
							);
						}
					}
				}else if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_'.$product->id.'_'.$option->id)) {
							$quantity = $this->input->post('varaint_qty_'.$product->id.'_'.$option->id);
                            $barcodes[] = array(
                                'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                                'name' => $this->input->post('product_name') ? $product->name.' <br> '.$option->name : FALSE,
                                'image' => $this->input->post('product_image') ? $product->image : FALSE,
                                'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price' => $this->input->post('price') ?  $this->cus->formatMoney($option->price != 0 ? $option->price : $product->price) : FALSE,
                                'unit' => $this->input->post('unit') ? $product->unit : FALSE,
                                'category' => $this->input->post('category') ? $product->category : FALSE,
                                'currencies' => $this->input->post('currencies'),
                                'variants' => $this->input->post('variants') ? $variants : FALSE,
                                'quantity' => $quantity
                                );
                        }
                    }
                } else {
                    $barcodes[] = array(
                        'site' => $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                        'name' => $this->input->post('product_name') ? $product->name : FALSE,
                        'image' => $this->input->post('product_image') ? $product->image : FALSE,
                        'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'price' => $this->input->post('price') ?  $this->cus->formatMoney($product->price) : FALSE,
                        'unit' => $this->input->post('unit') ? $product->unit : FALSE,
                        'category' => $this->input->post('category') ? $product->category : FALSE,
                        'currencies' => $this->input->post('currencies'),
                        'variants' => FALSE,
                        'quantity' => $quantity
                        );
                }

            }
            $this->data['barcodes'] = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style'] = $style;
			$this->data['barcode_styles'] = $this->products_model->getBarcodeStyle();
			$this->data['barcode_setting'] = $this->products_model->getBarcodeSettingByID($style);
            $this->data['items'] = false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->core_page('products/print_barcodes', $meta, $this->data);

        } else {

            if ($this->input->get('purchase') || $this->input->get('transfer')) {
                if ($this->input->get('purchase')) {
                    $purchase_id = $this->input->get('purchase', TRUE);
                    $items = $this->products_model->getPurchaseItems($purchase_id);
                } elseif ($this->input->get('transfer')) {
                    $transfer_id = $this->input->get('transfer', TRUE);
                    $items = $this->products_model->getTransferItems($transfer_id);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $item->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }

            if ($product_id) {
                if ($row = $this->site->getProductByID($product_id)) {
					
					if($warehouse_id){
						$row1 = $this->site->getWarehouseProduct($warehouse_id, $product_id);
						$quantity = $row1->quantity;
					}else{
						$quantity = $row->quantity;
					}
					
                    $selected_variants = false;
                    if ($variants = $this->products_model->getProductOptions($row->id)) {
                        foreach ($variants as $variant) {
                            $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                        }
                    }
					$c = str_replace(".", "", microtime(true));
                    $pr[$row->id] = array('c' => $c, 'id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    $this->data['message'] = lang('product_added_to_list');
                }
            }

            if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }
			$barcode_styles = $this->products_model->getBarcodeStyle();
            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
			$this->data['barcode_styles'] = $barcode_styles;
			$this->data['barcode_setting'] = $this->products_model->getBarcodeSettingByID($barcode_styles[0]->id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('print_barcodes')));
			$meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->core_page('products/print_barcodes', $meta, $this->data);

        }
    }
	
	public function get_barcode_style()
	{
		$style = $this->input->get("style");
		$rows = $this->products_model->getBarcodeSettingByID($style);
		echo json_encode(array("result" => $rows));
	}

    function add($id = NULL)
    {
        $this->cus->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getWarehouses();
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
			if($this->Settings->accounting == 1){
				$this->form_validation->set_rules('stock_account', lang("stock_account"), 'required');
				$this->form_validation->set_rules('adjustment_account', lang("adjustment_account"), 'required');
				$this->form_validation->set_rules('usage_account', lang("usage_account"), 'required');
				$this->form_validation->set_rules('cost_of_sale_account', lang("cost_of_sale_account"), 'required');
				$this->form_validation->set_rules('sale_account', lang("sale_account"), 'required');
			}
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]|alpha_dash');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
            $service_type = $this->site->getServiceTypesByID($this->input->post('electricity'));
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),				
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->cus->formatDecimal($this->input->post('cost'),16),
                'price' => $this->cus->formatDecimal($this->input->post('price'),16),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->cus->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->cus->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->cus->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->cus->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->cus->formatDecimal($this->input->post('supplier_5_price')),
				'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->cus->formatDecimal($this->input->post('promo_price'),16),
                'start_date' => $this->input->post('start_date') ? $this->cus->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->cus->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file' => $this->input->post('file_link'),
				'adjustment_qty' => $this->input->post('adjustment_qty'),
                'electricity' => $this->input->post('electricity'),
                'service_code' => $service_type->code,
                'service_types' => $service_type->name,
				'accounting_method' => $this->input->post('accounting_method'),
                'seperate_qty' => $this->input->post('seperate_qty'),
				'product_additional' => $this->input->post('product_additional'),
            );
			
			if($this->config->item("concretes")){
				$data['stregth'] = $this->input->post('stregth');
			}
			
			if($this->Settings->cbm == 1){
				$data['p_length'] = $this->input->post('p_length');
				$data['p_width'] = $this->input->post('p_width');
				$data['p_height'] = $this->input->post('p_height');
				$data['p_weight'] = $this->input->post('p_weight');
			}
			
			if($this->config->item('product_currency')==true){
				$currency_code = $this->input->post('currency_code',true);
				$currency = $this->site->getCurrencyByCode($currency_code);
				$data['price'] = $this->cus->formatDecimal($this->input->post('price'),16) / $currency->rate;
				$data['currency_code'] = $currency->code;
				$data['currency_rate'] = $currency->rate;
			}
			
			if($this->Settings->accounting == 1){
				if($this->input->post('type')=='service'){
					$sale_acc = $this->input->post('sale_account_sv');
				}else{
					$sale_acc = $this->input->post('sale_account');
				}
				$product_account = array(
					'type' => $this->input->post('type'),
					'stock_acc' => $this->input->post('stock_account'),
					'adjustment_acc' => $this->input->post('adjustment_account'),
					'usage_acc' => $this->input->post('usage_account'),
					'convert_acc' => $this->input->post('convert_account'),
					'cost_acc' => $this->input->post('cost_of_sale_account'),
					'sale_acc' => $sale_acc,
					'pawn_acc' => $this->input->post('pawn_account'),
					);
				//$this->cus->print_arrays($product_account);	
			}					
			
			$i = isset($_POST['product_unit_id']) ? sizeof($_POST['product_unit_id']) : 0;
			if($i > 0){
				for ($r = 0; $r < $i; $r++) {
					 $product_unit_qty = $this->cus->formatDecimal($_POST['product_unit_qty'][$r]);
					 if($product_unit_qty > 0 && $product_unit_qty !=''){
						$product_units[] = array(
								'unit_id' => $_POST['product_unit_id'][$r],
								'unit_qty' => $product_unit_qty,
								'unit_price' => $_POST['product_unit_price'][$r],
						  ); 
					 }
					 
				}
			}else{
				$product_units[] = array(
						'unit_id' => $this->input->post('unit'),
						'unit_qty' => 1
					); 
			}
			
			$this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                        );
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = array(
                                'name' => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity' => $_POST['attr_quantity'][$r],
                                'price' => $_POST['attr_price'][$r],
                            );
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }
					
					if ($wh_total_quantity != $pv_total_quantity) {
						$this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
						$this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
					}
					
                } else {
                    $product_attributes = NULL;
                }                
				
            } else {
                $warehouse_qty = NULL;
                $product_attributes = NULL;
            }


            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
							'item_id' => $_POST['combo_item_id'][$r],
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
							'option_id' => $_POST['coption_id'][$r]
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                /*if ($this->cus->formatDecimal($total_price) != $this->cus->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }*/
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {

				$c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r])) {
                        $items2[] = array(
							'item_code' => $_POST['combo_item_code'][$r],
                            'product_id' => $_POST['combo_item_id'][$r],
							'option_id' => $_POST['poption_id'][$r]
                        );
                    }
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'bom') {	
				$c = sizeof($_POST['bom_item_id']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['bom_item_id'][$r]) && isset($_POST['bom_item_quantity'][$r])) {
                        $bom_items[] = array(
                            'bom_type' => $_POST['bom_type'][$r],
                            'product_id' => $_POST['bom_item_id'][$r],
                            'quantity' => $_POST['bom_item_quantity'][$r],
							'unit_id' => $_POST['bom_unit_id'][$r]
                        );
                    } 
                }
				$data['track_quantity'] = 0;
			}
			
			if($this->Settings->product_formulation == 1 && $this->input->post('formulation')){
				$d = sizeof($_POST['for_caculation']) - 1;
				for ($r = 0; $r <= $d; $r++) {
                    if (isset($_POST['for_caculation'][$r])) {
                        $formulation_items[] = array(
                            'for_width' => $_POST['for_width'][$r],
							'for_height' => $_POST['for_height'][$r],
							'for_square' => $_POST['for_square'][$r],
							'for_qty' => $_POST['for_qty'][$r],
							'for_field' => $_POST['for_field'][$r],
							'for_caculation' => $_POST['for_caculation'][$r],
							'for_operation' => $_POST['for_operation'][$r],
                            'for_unit_id' => $this->input->post('unit'),
                        );
                    } 
                }
			}

			if (!isset($bom_items)) {
                $bom_items = NULL;
            }
            if (!isset($items)) {
                $items = NULL;
            }
			if (!isset($items2)) {
                $items2 = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/add");
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("products/add");
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'right';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
			
			$convert = false;
			$convert_item = false;
			
			if($this->config->item('convert') && ($this->input->post('type')=='standard' || $this->input->post('type')=='raw_material' || $this->input->post('type')=='asset')){
				$i = isset($_POST['convert_item_id']) ? sizeof($_POST['convert_item_id']) : 0;		
				if($i > 0){
					for ($r = 0; $r < $i; $r++) {
						$convert_item_id = $_POST['convert_item_id'][$r];
						$convert_item_unit = $_POST['convert_item_unit'][$r];
						$convert_item_qty = $_POST['convert_item_qty'][$r];
						$convert_unit_info = $this->site->getProductUnit($convert_item_id,$convert_item_unit);
						$convert_item[] = array(
									'product_id'=>$convert_item_id,
									'quantity'=>$convert_item_qty * $convert_unit_info->unit_qty,
									'unit_id'=>$convert_item_unit,
									'unit_qty'=>$convert_item_qty,
									'type'=>"raw_material",
								);		
					}
					$convert = array(
						'name' => $this->input->post('code').' - '.$this->input->post('name'),	
						'created_by' => $this->session->userdata('user_id'),	
					);
				}
								
			}
        }

        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $product_units, $items2, $product_account , $bom_items, $formulation_items, $convert, $convert_item)) {
            $this->session->set_flashdata('message', lang("product_added") ." - ".$data["code"]." - ".$data["name"]);
            redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->load->model('pos_model');
			if($id){
				$old_product_info = $this->products_model->getProductByID($id);
				$this->data['subunits'] = $this->site->getUnitsByBUID($old_product_info->unit);
				$this->data['product_unit'] = $this->products_model->getUnitbyProduct($old_product_info->id,$old_product_info->unit);
				if($this->config->item('convert') && ($old_product_info->type=='standard' || $old_product_info->type=='raw_material' || $old_product_info->type=='asset')){
					$this->data['convert_items'] = $this->products_model->getBomItemByProductID($id);
				}
				if($this->Settings->product_formulation == 1){
					$this->data['formulation_items'] = $this->products_model->getProductFormulation($id);
				}
				if($this->Settings->accounting == 1){
					$productAccount = $this->products_model->getProductAccByProductId($old_product_info->id);
					$this->data['stock_accounts'] = $this->site->getAccount(array('AS'),$productAccount->stock_acc);
					$this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->adjustment_acc);
					$this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->usage_acc);
					$this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->convert_acc);
					$this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$productAccount->cost_acc);
					$this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->sale_acc);
					if($this->config->item("pawn")){
						$this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->pawn_acc);
					}
				}
			}else{
				$old_product_info = NULL;
				$this->data['subunits'] = NULL;
				$this->data['product_unit'] = NULL;
				if($this->Settings->accounting == 1){
					$this->data['stock_accounts'] = $this->site->getAccount(array('AS'));
					$this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'));
					$this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'));
					$this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'));
					$this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'));
					$this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
					if($this->config->item("pawn")){
						$this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
					}
				}
			}
			

			$this->data['pos_settings'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['service_types'] = $this->site->getAllServiceTypes();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : NULL;
            $this->data['product'] = $old_product_info;
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['combo_items'] = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_product')));
            $meta = array('page_title' => lang('add_product'), 'bc' => $bc);
            $this->core_page('products/add', $meta, $this->data);
        }
    }
	
	function raw_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getRawProductNames($term);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            foreach ($rows as $row) {
				$options = $this->products_model->getUnitbyProduct($row->id,$row->unit);
				$pr[] = array('row_id' => $c, 'id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'cost' => $row->cost, 'options' => $options,'bom_type'=>'' );
			}
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
				$options = $this->products_model->getUnitbyProduct($row->id,$row->unit);
				$variants = $this->products_model->getProductOptions($row->id);
				$pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'cost' => $row->cost, 'options' => $options , 'variants' => $variants, 'product_id' => $row->id, 'unit' => $row->unit);
			}
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function get_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
			$c = str_replace(".", "", microtime(true));
			$rw = 0;
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = array('c' => ($c+$rw), 'id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'variants' => $variants);
				$rw++;
			}
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	function get_suggestion_categories()
    {
        $term = $this->input->get('term', TRUE);
		$quantity = $this->input->get('quantity', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getCategoriesForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $quantity, 'variants' => $variants);
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(array('msg' => lang('access_denied'))));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_is_required'))));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(array('msg' => lang('product_name_is_required'))));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(array('msg' => lang('product_category_is_required'))));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(array('msg' => lang('product_unit_is_required'))));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(array('msg' => lang('product_price_is_required'))));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(array('msg' => lang('product_cost_is_required'))));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_already_exist'))));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0');
                $this->cus->send_json(array('msg' => 'success', 'result' => $pr));
            } else {
                exit(json_encode(array('msg' => lang('failed_to_add_product'))));
            }
        } else {
            json_encode(array('msg' => 'Invalid token'));
        }

    }

    function edit($id = NULL)
    {
        $this->cus->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses = $this->site->getWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product = $this->site->getProductByID($id);

        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
			if($this->Settings->accounting == 1){
				$this->form_validation->set_rules('stock_account', lang("stock_account"), 'required');
				$this->form_validation->set_rules('adjustment_account', lang("adjustment_account"), 'required');
				$this->form_validation->set_rules('usage_account', lang("usage_account"), 'required');
				$this->form_validation->set_rules('cost_of_sale_account', lang("cost_of_sale_account"), 'required');
				$this->form_validation->set_rules('sale_account', lang("sale_account"), 'required');
			}
        }
		
        $this->form_validation->set_rules('code', lang("product_code"), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');

        if ($this->form_validation->run('products/add') == true) {
              $service_type = $this->site->getServiceTypesByID($this->input->post('electricity'));
        		
            $data = array('code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),				
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->cus->formatDecimal($this->input->post('cost'),16),
                'price' => $this->cus->formatDecimal($this->input->post('price'),16),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->cus->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->cus->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->cus->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->cus->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->cus->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->cus->formatDecimal($this->input->post('promo_price'),16),
                'start_date' => $this->input->post('start_date') ? $this->cus->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->cus->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
				'adjustment_qty' => $this->input->post('adjustment_qty'),
                'electricity' => $this->input->post('electricity'),
                'service_code' => $service_type->code,
                'service_types' => $service_type->name,
				'accounting_method' => $this->input->post('accounting_method'),
                'seperate_qty' => $this->input->post('seperate_qty'),
				'product_additional' => $this->input->post('product_additional'),
				'inactive' => $this->input->post('inactive'),
            );
			
			if($this->config->item("concretes")){
				$data['stregth'] = $this->input->post('stregth');
			}
			
			if($this->Settings->cbm == 1){
				$data['p_length'] = $this->input->post('p_length');
				$data['p_width'] = $this->input->post('p_width');
				$data['p_height'] = $this->input->post('p_height');
				$data['p_weight'] = $this->input->post('p_weight');
			}
			
			if($this->config->item('product_currency')==true){
				$currency_code = $this->input->post('currency_code',true);
				$currency = $this->site->getCurrencyByCode($currency_code);
				$data['price'] = $this->cus->formatDecimal($this->input->post('price'),16) / $currency->rate;
				$data['currency_code'] = $currency->code;
				$data['currency_rate'] = $currency->rate;
			}
			
			if($this->Settings->accounting == 1){
				if($this->input->post('type')=='service'){
					$sale_acc = $this->input->post('sale_account_sv');
					$pawn_acc = $this->input->post('pawn_account_sv');
				}else{
					$sale_acc = $this->input->post('sale_account');
					$pawn_acc = $this->input->post('pawn_account');
				}
				$product_account = array(
					'type' => $this->input->post('type'),
					'stock_acc' => $this->input->post('stock_account'),
					'adjustment_acc' => $this->input->post('adjustment_account'),
					'usage_acc' => $this->input->post('usage_account'),
					'convert_acc' => $this->input->post('convert_account'),
					'cost_acc' => $this->input->post('cost_of_sale_account'),
					'sale_acc' => $sale_acc,
					'pawn_acc' => $pawn_acc,
					);
			}else{
				if($product->cost != $this->input->post('cost')){
					$stockmoves= array(
						'transaction' => 'CostAdjustment',
						'transaction_id' => '0',
                        'product_id' => $product->id,
						'product_code' => $product->code,
                        'warehouse_id' => 0,
                        'date' => date('Y-m-d H:i:s'),
						'real_unit_cost' => $this->input->post('cost'),
						'user_id' => $this->session->userdata('user_id'),
                    );	
				}
			}
			
			$i = isset($_POST['product_unit_id']) ? sizeof($_POST['product_unit_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				 $product_unit_qty = $this->cus->formatDecimal($_POST['product_unit_qty'][$r]);
				 if($product_unit_qty > 0 && $product_unit_qty !=''){
					$product_units[] = array(
							'unit_id' => $_POST['product_unit_id'][$r],
							'product_id' => $id,
							'unit_qty' => $product_unit_qty,
							'unit_price' => $_POST['product_unit_price'][$r],
					  ); 
				 }
				 
			}
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = array(
                            'id' => $this->input->post('variant_id_'.$pv->id),
                            'name' => $this->input->post('variant_name_'.$pv->id),
                            'cost' => $this->input->post('variant_cost_'.$pv->id),
                            'price' => $this->input->post('variant_price_'.$pv->id),
                        );
                    }
                } else {
                    $update_variants = NULL;
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = array(
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                    );
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang("product_already_has_variant").' ('.$_POST['attr_name'][$r].')');
                                $this->form_validation->set_rules('new_product_variant', lang("new_product_variant"), 'required');
                            } else {
                                $product_attributes[] = array(
                                    'name' => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'price' => $_POST['attr_price'][$r],
                                );
                            }
                        }
                    }

                } else {
                    $product_attributes = NULL;
                }

            } else {
                $warehouse_qty = NULL;
                $product_attributes = NULL;
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
				$total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
							'item_id' => $_POST['combo_item_id'][$r],
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
							'option_id' => $_POST['coption_id'][$r]
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
				$c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r])) {
                        $items2[] = array(
							'item_code' => $_POST['combo_item_code'][$r],
                            'product_id' => $_POST['combo_item_id'][$r],
							'option_id' => $_POST['poption_id'][$r]
                        );
                    }
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'bom') {	
				$c = sizeof($_POST['bom_item_id']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['bom_item_id'][$r]) && isset($_POST['bom_item_quantity'][$r])) {
                        $bom_items[] = array(
                            'product_id' => $_POST['bom_item_id'][$r],
                            'bom_type' => $_POST['bom_type'][$r],
                            'quantity' => $_POST['bom_item_quantity'][$r],
							'unit_id' => $_POST['bom_unit_id'][$r]
                        );
                    } 
                }
				$data['track_quantity'] = 0;
			}
			
			if($this->Settings->product_formulation == 1 && $this->input->post('formulation')){
				$d = sizeof($_POST['for_caculation']) - 1;
				for ($r = 0; $r <= $d; $r++) {
                    if (isset($_POST['for_caculation'][$r])) {
                        $formulation_items[] = array(
							'for_width' => $_POST['for_width'][$r],
							'for_height' => $_POST['for_height'][$r],
							'for_square' => $_POST['for_square'][$r],
							'for_qty' => $_POST['for_qty'][$r],
                            'for_field' => $_POST['for_field'][$r],
                            'for_unit_id' => $this->input->post('unit'),
							'for_caculation' => $_POST['for_caculation'][$r],
							'for_operation' => $_POST['for_operation'][$r],
                        );
                    } 
                }
			}
			if (!isset($formulation_items)) {
                $formulation_items = NULL;
            }
			if (!isset($bom_items)) {
                $bom_items = NULL;
            }
            if (!isset($items)) {
                $items = NULL;
            }
			if (!isset($items2)) {
                $items2 = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/edit/" . $id);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("products/edit/" . $id);
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'right';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
			
			
			if($this->config->item('convert') && ($this->input->post('type')=='standard' || $this->input->post('type')=='raw_material' || $this->input->post('type')=='asset')){
				$i = isset($_POST['convert_item_id']) ? sizeof($_POST['convert_item_id']) : 0;		
				if($i > 0){
					for ($r = 0; $r < $i; $r++) {
						$convert_item_id = $_POST['convert_item_id'][$r];
						$convert_item_unit = $_POST['convert_item_unit'][$r];
						$convert_item_qty = $_POST['convert_item_qty'][$r];
						$convert_unit_info = $this->site->getProductUnit($convert_item_id,$convert_item_unit);
						$convert_item[] = array(
									'product_id'=>$convert_item_id,
									'quantity'=>$convert_item_qty * $convert_unit_info->unit_qty,
									'unit_id'=>$convert_item_unit,
									'unit_qty'=>$convert_item_qty,
									'type'=>"raw_material",
								);
					}
					$convert = array(
						'name' => $this->input->post('code').' - '.$this->input->post('name'),	
						'product_id' => $id,		
						'updated_by' => $this->session->userdata('user_id'),	
					);
				}
								
			}
        }
		
		
        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $product_units, $items2,  $product_account, $stockmoves, $bom_items, $formulation_items, $convert, $convert_item)) {
			$this->session->set_flashdata('message', lang("product_updated")." - ".$product->code." - ".$product->name);
            redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->load->model('pos_model');
			$this->data['pos_settings'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['service_types'] = $this->site->getAllServiceTypes();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product'] = $product;
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['product_unit'] = $this->products_model->getUnitbyProduct($product->id,$product->unit);
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['subunits'] = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants'] = $this->products_model->getProductOptions($id);
            $this->data['combo_items'] = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : NULL;
			$this->data['bom_items'] = $product->type == 'bom' ? $this->products_model->getProductBomItems($product->id) : NULL;
			$this->data['digital_items'] = $product->type == 'digital' ? $this->products_model->getProductDigitalItems($product->id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;

			$productAccount = $this->products_model->getProductAccByProductId($product->id);
			if($this->Settings->accounting == 1){
				$this->data['stock_accounts'] = $this->site->getAccount(array('AS'),$productAccount->stock_acc);
				$this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->adjustment_acc);
				$this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->usage_acc);
				$this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->convert_acc);
				$this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$productAccount->cost_acc);
				$this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->sale_acc);
				if($this->config->item("pawn")){
					$this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->pawn_acc);
				}
			}
			
			if($this->config->item('convert') && ($product->type=='standard' || $product->type=='raw_material' || $product->type=='asset')){
				$this->data['convert_items'] = $this->products_model->getBomItemByProductID($product->id);
			}
			
			if($this->Settings->product_formulation == 1){
				$this->data['formulation_items'] = $this->products_model->getProductFormulation($product->id);
			}
			$this->data['customers'] = $this->site->getAllCompanies("customer");
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_product')));
			$meta = array('page_title' => lang('edit_product'), 'bc' => $bc);
            $this->core_page('products/edit', $meta, $this->data);
        }
    }

    function import_csv()
    {
        $this->cus->checkPermissions('import');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {
				
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
						$name = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$code = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$brand = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$category_code = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$unit = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						$unit_qtys = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
						$sale_unit = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
						$purchase_unit = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
						$cost = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
						$price = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
						$alert_quantity = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
						$subcategory_code = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
						$variants = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
						$cf1s = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
						$cf2s = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
						$cf3s = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
						$cf4s = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
						$cf5s = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
						$cf6s = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
						if(trim($code)!=''){
							$final[] = array(
							  'name'  => $name,
							  'code'   => $code,
							  'barcode_symbology'    => "code128",
							  'brand'  => $brand,
							  'category_code'   => $category_code,
							  'unit'   => $unit,
							  'unit_qtys'   => $unit_qtys,
							  'sale_unit'   => $sale_unit,
							  'purchase_unit'   => $purchase_unit,
							  'cost'   => $cost,
							  'price'   => $price,
							  'alert_quantity'   => $alert_quantity,
							  'tax_rate'   => "No Tax",
							  'tax_method'   => "exclusive",
							  'image'   => "no_image.png",
							  'subcategory_code'   => $subcategory_code,
							  'variants'   => $variants,
							  'cf1'   => $cf1s,
							  'cf2'   => $cf2s,
							  'cf3'   => $cf3s,
							  'cf4'   => $cf4s,
							  'cf5'   => $cf5s,
							  'cf6'   => $cf6s,
							);	
						}
						
					}
				}
			
                $rw = 2;
				$checkCode = false;
                foreach ($final as $csv_pr) {
                    if(!$this->products_model->getProductByCode(trim($csv_pr['code']))) {
						if ($catd = $this->products_model->getCategoryByCode(trim($csv_pr['category_code']))) {
							if(isset($checkCode[trim($csv_pr['code'])]) && $checkCode[trim($csv_pr['code'])]){
								$this->session->set_flashdata('error', lang("product_code") . " (" . $csv_pr['code'] . "). " . lang("code__exist") . " " . lang("line_no") . " " . $rw);
								redirect("products/import_csv");
							}
							$checkCode[trim($csv_pr['code'])] = true;
							$brand = $this->products_model->getBrandByCode(trim($csv_pr['brand']));
							$unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));
							$base_unit = $unit ? $unit->id : NULL;
							$sale_unit = $base_unit;
							$purcahse_unit = $base_unit;
							$unit_qtys = trim($csv_pr['unit_qtys']);
							if ($base_unit) {
								$units = $this->site->getUnitsByBUID($base_unit);

								foreach ($units as $u) {
									if ($u->code == trim($csv_pr['sale_unit'])) {
										$sale_unit = $u->id;
									}
									if ($u->code == trim($csv_pr['purchase_unit'])) {
										$purcahse_unit = $u->id;
									}
									if(trim($csv_pr['unit_qtys'])==''){
										if($u->operation_value==''){
											$unit_qtys .= $u->code.'=1|';
										}else{
											$unit_qtys .= $u->code.'='.$u->operation_value.'|';
										}
										
									}
								}
							} else {
								$this->session->set_flashdata('error', lang("check_unit") . " (" . $csv_pr['unit'] . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
								redirect("products/import_csv");
							}
							$pr_code[] = trim($csv_pr['code']);
							$pr_name[] = trim($csv_pr['name']);
							$pr_cat[] = $catd->id;
							$pr_variants[] = trim($csv_pr['variants']);
							$pr_brand[] = $brand ? $brand->id : NULL;
							$pr_unit[] = $base_unit;
							$pr_unit_qty[] = $unit_qtys;
							$pr_image[] = trim($csv_pr['image']);
							$sale_units[] = $sale_unit;
							$purcahse_units[] = $purcahse_unit;
							$tax_method[] = $csv_pr['tax_method'] == 'exclusive' ? 1 : 0;
							$prsubcat = $this->products_model->getCategoryByCode(trim($csv_pr['subcategory_code']));
							$pr_subcat[] = $prsubcat ? $prsubcat->id : NULL;
							$pr_cost[] = trim($csv_pr['cost']);
							$pr_price[] = trim($csv_pr['price']);
							$pr_aq[] = trim($csv_pr['alert_quantity']);
							$tax_details = $this->products_model->getTaxRateByName(trim($csv_pr['tax_rate']));
							$pr_tax[] = $tax_details ? $tax_details->id : NULL;
							$bs[] = mb_strtolower(trim($csv_pr['barcode_symbology']), 'UTF-8');
							$cf1[] = trim($csv_pr['cf1']);
							$cf2[] = trim($csv_pr['cf2']);
							$cf3[] = trim($csv_pr['cf3']);
							$cf4[] = trim($csv_pr['cf4']);
							$cf5[] = trim($csv_pr['cf5']);
							$cf6[] = trim($csv_pr['cf6']);
						} else {
							$this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_pr['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
							redirect("products/import_csv");
						}
                    }else{
                        $this->session->set_flashdata('error', lang("product_code") . " (" . $csv_pr['code'] . "). " . lang("code__exist") . " " . lang("line_no") . " " . $rw);
						redirect("products/import_csv");
                    }

                    $rw++;
                }
            }

            $ikeys = array('code', 'barcode_symbology', 'name', 'brand', 'category_id', 'unit', 'unit_qtys', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'subcategory_id', 'variants', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'image');

            $items = array();
            foreach (array_map(null, $pr_code, $bs, $pr_name, $pr_brand, $pr_cat, $pr_unit, $pr_unit_qty, $sale_units, $purcahse_units, $pr_cost, $pr_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $pr_image) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }

            //$this->cus->print_arrays($items);
        }

        if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items)) {
            $this->session->set_flashdata('message', sprintf(lang("products_added"), $prs));
            redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('import_products_by_csv')));
			$meta = array('page_title' => lang('import_products_by_csv'), 'bc' => $bc);
            $this->core_page('products/import_csv', $meta, $this->data);

        }
    }

    function update_price()
    {
        $this->cus->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'price');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (!$this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        $this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
                        redirect("products");
                    }
                    $rw++;
                }
            }

        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/group_product_prices/".$group_id);
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            $this->products_model->updatePrice($final);
            $this->session->set_flashdata('message', lang("price_updated"));
            redirect('products');
        } else {

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'products/update_price', $this->data);

        }
    }

    function delete($id = NULL)
    {
        $this->cus->checkPermissions(NULL, TRUE);
		$row = $this->products_model->getProductByID($id);
		if($row->quantity <> 0){
            $this->session->set_flashdata('error', lang('product_has_quantity'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("product_deleted")." - ". $row->code . " - " . $row->name;
				die();
            }
			$this->session->set_flashdata('message', lang('product_deleted')." - ". $row->code . " - " . $row->name);
            redirect('products');
        }

    }
    
    function quantity_adjustments($warehouse_id = NULL, $biller_id = NULL)
	{
        $this->cus->checkPermissions('adjustments');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('quantity_adjustments')));
		$meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->core_page('products/quantity_adjustments', $meta, $this->data);
    }

    function getadjustments($warehouse_id = NULL, $biller_id = NULL)
    {
        $this->cus->checkPermissions('adjustments');
		$approve_link = '';
		$edit_link = anchor('products/edit_adjustment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_adjustment'), ' class="edit_adjustment" ');
		$delete_link = "<a href='#' class='po delete_adjustment' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_adjustment/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_adjustment') . "</a>";
		if($this->Admin || $this->Owner || $this->GP['products-approve_adjustment']){
			$approve_link = "<a href='#' class='po approve_adjustment' title='<b>" . $this->lang->line("approve_adjustment") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('products/approve_adjustment/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_adjustment') . "</a>";
		}
		$action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $edit_link . '</li>
						<li>' . $approve_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';


        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('adjustments')}.id as id, date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, note,adjustments.status, attachment")
            ->from('adjustments')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left')
			->join('companies', 'companies.id=adjustments.biller_id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->group_by("adjustments.id");
            if ($warehouse_id) {
                $this->datatables->where('adjustments.warehouse_id', $warehouse_id);
            }
			if ($biller_id) {
                $this->datatables->where('adjustments.biller_id', $biller_id);
            }
		
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('adjustments.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
				$this->datatables->where_in('adjustments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('adjustments.created_by', $this->session->userdata('user_id'));
			}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();

    }

    public function view_adjustment($id)
    {
        $this->cus->checkPermissions('adjustments', TRUE);
        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->cus->md();
        }
        $this->data['inv'] = $adjustment;
		$this->data['biller'] = $this->site->getCompanyByID($adjustment->biller_id);
        $this->data['rows'] = $this->products_model->getAdjustmentItems($id);
        $this->data['created_by'] = $this->site->getUser($adjustment->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($adjustment->warehouse_id);
		$this->data['project'] = ($adjustment->project_id ? $this->site->getProjectByID($adjustment->project_id) : false);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Quantity Adjustment',$adjustment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Quantity Adjustment',$adjustment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme.'products/view_adjustment', $this->data);
    }
	
	public function supplier_suggestions()
    {
        $term = $this->input->get('term', true); 
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->products_model->getAllSupplier($sr);
        if ($rows) {
            foreach ($rows as $row) {              			
                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->company . ")",
                    'row' => $row);
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function ca_allsuggestions()
    {
        $term = $this->input->get('term', true); 
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows = $this->products_model->getAllProductNames($sr);
        if ($rows) {
            foreach ($rows as $row) {
			   $options = $this->products_model->getProductOptions($row->id);
			   $units = $this->site->getUnitbyProduct($row->id, isset($row->base_unit)? $row->base_unit: '');				
               $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'units'=> $units);
			}
			$this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	function cost_adjustments($biller_id = NULL)
    {
        $this->cus->checkPermissions('cost_adjustments');
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('cost_adjustments')));
		$meta = array('page_title' => lang('cost_adjustments'), 'bc' => $bc);
        $this->core_page('products/cost_adjustments', $meta, $this->data);
    }

    function getcostadjustments($biller_id = NULL)
    {
        $this->cus->checkPermissions('cost_adjustments');
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('cost_adjustments')}.id as id, date, reference_no, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, note, attachment")
            ->from('cost_adjustments')
			->join('companies', 'companies.id=cost_adjustments.biller_id', 'left')
            ->join('users', 'users.id=cost_adjustments.created_by', 'left')
            ->group_by("cost_adjustments.id");
		
			if ($biller_id) {
                $this->datatables->where('cost_adjustments.biller_id', $biller_id);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('cost_adjustments.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('cost_adjustments.created_by', $this->session->userdata('user_id'));
			}
        //$this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('products/edit_cost_adjustment/$1') . "' class='tip' title='" . lang("edit_cost_adjustment") . "'><i class='fa fa-edit'></i></a> " . "" . "</div>", "id");
        echo $this->datatables->generate();

    }

    public function view_cost_adjustment($id)
    {
        $this->cus->checkPermissions('cost_adjustments', TRUE);
        $adjustment = $this->products_model->getCostAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('cost_adjustment_not_found'));
            $this->cus->md();
        }
        $this->data['inv'] = $adjustment;
		$this->data['biller'] = $this->site->getCompanyByID($adjustment->biller_id);
        $this->data['rows'] = $this->products_model->getCostAdjustmentItems($id);
        $this->data['created_by'] = $this->site->getUser($adjustment->created_by);
		$this->data['project'] = ($adjustment->project_id ? $this->site->getProjectByID($adjustment->project_id) : false);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Cost Adjustment',$adjustment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Cost Adjustment',$adjustment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme.'products/view_cost_adjustment', $this->data);
    }
	
	function add_cost_adjustment_excel($group_id = NULL)
    {
		$this->cus->checkPermissions('cost_adjustments-add',true);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$products = false;
			if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++)
					{
						$code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$cost = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$final[] = array(
							'code'  => trim($code),
							'cost'   => (float) $cost,
						);
					}
				}
				foreach($final as $row){
					if($row['code']!=''){
						if($product_details = $this->products_model->getProductByCode($row['code'])){
							if($row['cost'] > 0){
								$new_cost = $row['cost'];
								$old_cost = $product_details->cost;
								$products[] = array(
													'product_id' => $product_details->id,
													'old_cost' =>$old_cost,
													'new_cost' =>$new_cost,
													);	
													
								$warehouse_products = $this->products_model->getAllWarehouseProducts($product_details->id); 
								$unit = $this->site->getProductUnit($product_id,$product_details->unit);
								if($warehouse_products){
									foreach($warehouse_products as $warehouse_product){
										if($warehouse_product->quantity > 0 || $warehouse_product->quantity < 0){
											$stockmoves[] = array(
												'transaction' => 'CostAdjustment',
												'product_id' => $product_details->id,
												'product_code' => $product_details->code,
												'warehouse_id' => $warehouse_product->warehouse_id,
												'quantity' => $warehouse_product->quantity * (-1),
												'date' => $date,
												'unit_id' => $product_details->unit,
												'unit_code' => $unit->code,
												'unit_quantity' => $unit->unit_qty,
												'real_unit_cost' => $old_cost,
												'user_id' => $this->session->userdata('user_id'),
											);
											$stockmoves[] = array(
												'transaction' => 'CostAdjustment',
												'product_id' => $product_details->id,
												'product_code' => $product_details->code,
												'warehouse_id' => $warehouse_product->warehouse_id,
												'quantity' => $warehouse_product->quantity,
												'date' => $date,
												'unit_id' => $product_details->unit,
												'unit_code' => $unit->code,
												'unit_quantity' => $unit->unit_qty,
												'real_unit_cost' => $new_cost,
												'user_id' => $this->session->userdata('user_id'),
											);
										}
									}
								}							

									
								if($this->Settings->accounting == 1 && $product_details->quantity > 0){	
									$productAcc = $this->site->getProductAccByProductId($product_details->id);
									$amount = ($new_cost - $old_cost) * $product_details->quantity ;
									$accTrans[] = array(
										'transaction' => 'CostAdjustment',
										'transaction_date' => $date,
										'account' => $productAcc->stock_acc,
										'amount' => $amount,
										'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$product_details->quantity.'#'.'Cost: '.$new_cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);
									$accTrans[] = array(
										'transaction' => 'CostAdjustment',
										'transaction_date' => $date,
										'account' => $productAcc->adjustment_acc,
										'amount' => -$amount,
										'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$product_details->quantity.'#'.'Cost: '.$new_cost,
										'description' => $note,
										'biller_id' => $biller_id,
										'project_id' => $project_id,
										'user_id' => $this->session->userdata('user_id'),
									);
									
								}			
							}else{
								$products = false;
								$error = lang('product_cost_required').' '.$row['code'];
								$this->session->set_flashdata('error', $error);
								redirect($_SERVER["HTTP_REFERER"]);
							}
						}else{
							$products = false;
							$error = lang('no_product_found').' '.$row['code'];
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
				}
            }
			if($products){
				$reference_no = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ca',$biller_id);
				$data = array(
					'date' => $date,
					'reference_no' => $reference_no,
					'biller_id' => $biller_id,
					'project_id' => $project_id,
					'created_by' => $this->session->userdata('user_id'),
					);

				if ($_FILES['document']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload('document')) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}
			}else{
				$this->form_validation->set_rules('product', lang("products"), 'required');
			}
        } elseif ($this->input->post('add_cost_adjustment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("products/cost_adjustments/");
        }

        if ($this->form_validation->run() == true && $this->products_model->addCostAdjustment($data, $products, $accTrans, $stockmoves)) {
            $this->session->set_userdata('remove_cals', 1);
            $this->session->set_flashdata('message', lang("cost_adjusted")." - ".$data['reference_no']);
            redirect('products/cost_adjustments');
		}else {
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'products/cost_adjustment_excel', $this->data);

        }
    }
	
	function add_cost_adjustment()
    {
		$this->cus->checkPermissions('cost_adjustments-add',true);	
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ca',$biller_id);
            $note = $this->cus->clear_tags($this->input->post('note'));
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
			
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
				$old_cost = $_POST['old_cost'][$r];
				$new_cost = $_POST['new_cost'][$r];
				$product_details = $this->products_model->getProductByID($product_id); 
				
                $products[] = array(
                    'product_id' => $product_id,
					'old_cost' =>$old_cost,
					'new_cost' =>$new_cost,
                    );
				$warehouse_products = $this->products_model->getAllWarehouseProducts($product_id); 
				$unit = $this->site->getProductUnit($product_id,$product_details->unit);
				if($warehouse_products){
					foreach($warehouse_products as $warehouse_product){
						if($warehouse_product->quantity > 0 || $warehouse_product->quantity < 0){
							$stockmoves[] = array(
								'transaction' => 'CostAdjustment',
								'product_id' => $product_id,
								'product_code' => $product_details->code,
								'warehouse_id' => $warehouse_product->warehouse_id,
								'quantity' => $warehouse_product->quantity * (-1),
								'date' => $date,
								'unit_id' => $product_details->unit,
								'unit_code' => $unit->code,
								'unit_quantity' => $unit->unit_qty,
								'real_unit_cost' => $old_cost,
								'reference_no' => $reference_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							$stockmoves[] = array(
								'transaction' => 'CostAdjustment',
								'product_id' => $product_id,
								'product_code' => $product_details->code,
								'warehouse_id' => $warehouse_product->warehouse_id,
								'quantity' => $warehouse_product->quantity,
								'date' => $date,
								'unit_id' => $product_details->unit,
								'unit_code' => $unit->code,
								'unit_quantity' => $unit->unit_qty,
								'real_unit_cost' => $new_cost,
								'reference_no' => $reference_no,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					}
				}

				if($this->Settings->accounting == 1 && ($product_details->quantity > 0 || $product_details->quantity < 0)){	
					$productAcc = $this->site->getProductAccByProductId($product_details->id);
					$amount = ($new_cost - $old_cost) * $product_details->quantity ;
					$accTrans[] = array(
						'transaction' => 'CostAdjustment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => $amount,
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$product_details->quantity.'#'.'Cost: '.$new_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'CostAdjustment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->adjustment_acc,
						'amount' => $amount * (-1),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$product_details->quantity.'#'.'Cost: '.$new_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
				}	

            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        }
        if ($this->form_validation->run() == true && $this->products_model->addCostAdjustment($data, $products, $accTrans, $stockmoves)) {
            $this->session->set_userdata('remove_cals', 1);
            $this->session->set_flashdata('message', lang("cost_adjusted")." - ".$data['reference_no']);
            redirect('products/cost_adjustments');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/cost_adjustments'), 'page' => lang('cost_adjustments')), array('link' => '#', 'page' => lang('add_cost_adjustment')));
			$meta = array('page_title' => lang('add_cost_adjustment'), 'bc' => $bc);
            $this->core_page('products/add_cost_adjustment', $meta, $this->data);

        }
    }
	
	function edit_cost_adjustment($id)
    {
		$this->cus->checkPermissions('cost_adjustments-add',true);
        $adjustment = $this->products_model->getCostAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('cost_adjustments_not_found'));
            $this->cus->md();
        }
        $this->form_validation->set_rules('biller', lang("biller"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = $adjustment->date;
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ca',$biller_id);
            $note = $this->cus->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
				$old_cost = $_POST['old_cost'][$r];
				$new_cost = $_POST['new_cost'][$r];
				$product_details = $this->products_model->getProductByID($product_id); 
				
                $products[] = array(
                    'product_id' => $product_id,
					'old_cost' =>$old_cost,
					'new_cost' =>$new_cost,
                    );
				
				$warehouse_products = $this->products_model->getAllWarehouseProducts($product_id); 
				if($warehouse_products){
					foreach($warehouse_products as $warehouse_product){
						if($warehouse_product->quantity > 0 || $warehouse_product->quantity < 0){
							$stockmoves[] = array(
								'transaction' => 'CostAdjustment',
								'transaction_id' => $id,
								'product_id' => $product_id,
								'product_code' => $product_details->code,
								'warehouse_id' => $warehouse_product->warehouse_id,
								'quantity' => $warehouse_product->quantity * (-1),
								'date' => $date,
								'real_unit_cost' => $old_cost,
								'reference_no' => $reference_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							$stockmoves[] = array(
								'transaction' => 'CostAdjustment',
								'transaction_id' => $id,
								'product_id' => $product_id,
								'product_code' => $product_details->code,
								'warehouse_id' => $warehouse_product->warehouse_id,
								'quantity' => $warehouse_product->quantity,
								'date' => $date,
								'real_unit_cost' => $new_cost,
								'reference_no' => $reference_no,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					}
				}				

				if($this->Settings->accounting == 1 && $product_details->quantity > 0){	
					$productAcc = $this->site->getProductAccByProductId($product_details->id);
					$amount = ($new_cost - $old_cost) * $product_details->quantity ;
					$accTrans[] = array(
						'transaction' => 'CostAdjustment',
						'transaction_date' => $date,
						'transaction_id' => $id,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => $amount,
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$product_details->quantity.'#'.'Cost: '.$new_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'CostAdjustment',
						'transaction_date' => $date,
						'transaction_id' => $id,
						'reference' => $reference_no,
						'account' => $productAcc->adjustment_acc,
						'amount' => -$amount,
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$product_details->quantity.'#'.'Cost: '.$new_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					
				}	

            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
                'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
                );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->products_model->updateCostAdjustment($id, $data, $products, $accTrans, $stockmoves)) {
            $this->session->set_userdata('remove_cals', 1);
            $this->session->set_flashdata('message', lang("cost_edited")." - ".$data['reference_no']);
            redirect('products/cost_adjustments');
        } else {

            $inv_items = $this->products_model->getCostAdjustmentItems($id);
			
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->new_cost = $item->new_cost;
                $row->old_cost = $item->old_cost;
				$row->cost = $product->cost;
				
                $pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row);
                $c++;
            }
			
            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_items'] = json_encode($pr);
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/cost_adjustments'), 'page' => lang('cost_adjustments')), array('link' => '#', 'page' => lang('edit_cost_adjustment')));
			$meta = array('page_title' => lang('edit_cost_adjustment'), 'bc' => $bc);
            $this->core_page('products/edit_cost_adjustment', $meta, $this->data);

        }
    }
	
    function add_adjustment($count_id = NULL)
    {
        $this->cus->checkPermissions('adjustments-add',true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
			$status = 'pending';
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $serial = $_POST['serial'][$r];
				$expired_data = isset($_POST['expiry'][$r]) ? $this->cus->fsd($_POST['expiry'][$r]) : NULL;
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;
				$item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$unit = $this->site->getProductUnit($product_id,$item_unit);
				$product_details = $this->products_model->getProductByID($product_id); 
				$real_unit_cost = $product_details->cost;
				if($type == 'subtraction'){
					$item_quantity = $item_quantity * (-1);
				}
                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $item_quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    'serial_no' => $serial,
					'product_unit_id' => $item_unit,
					'product_unit_code' => $unit->code,
					'unit_quantity' => $quantity,
					'expiry' => $expired_data,
					'real_unit_cost' => $real_unit_cost,
                );	

            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => $this->input->post('count_id') ? $this->input->post('count_id') : NULL,
				'status' => $status
                );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("quantity_adjusted")." - ".$data['reference_no']);
            redirect('products/quantity_adjustments');
        } else {
            if ($count_id) {
                $stock_count = $this->products_model->getStouckCountByID($count_id);
                $items = $this->products_model->getStockCountItems($count_id);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    if ($item->counted != $item->expected) {
						if($this->Settings->product_expiry == '1'){
							$product_qty = $this->products_model->getProductQuantityExpiry($item->product_id,$stock_count->warehouse_id,$item->product_expiry);
						}else{
							$product_qty = $this->products_model->getProductQuantity($item->product_id,$stock_count->warehouse_id);
						}
						
                        $product = $this->site->getProductByID($item->product_id);
                        $row = json_decode('{}');
                        $row->id = $item->product_id;
                        $row->code = $product->code;
                        $row->name = $product->name;
						$row->real_unit_cost = $product->cost;
                        $row->qty = $item->counted-$item->expected;
                        $row->type = $row->qty > 0 ? 'addition' : 'subtraction';
                        $row->qty = $row->qty > 0 ? $row->qty : (0-$row->qty);
                        $options = $this->products_model->getProductOptions($product->id);
                        $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                        $row->serial = '';
                        
						$row->base_quantity = $row->qty > 0 ? $row->qty : (0-$row->qty);
						$row->base_unit = $product->unit;
						$row->base_unit_cost = $product->cost;
						$row->expiry = (($item->product_expiry != '0000-00-00' && $item->product_expiry!= '') ? $this->cus->hrsd($item->product_expiry) : '');
						$row->unit = $product->unit;
						$row->qohunit = $row->unit;
						$row->quantity = $product_qty['quantity'];
						$row->qoh = $this->cus->convertQty($row->id, $row->quantity);
						if($row->type=='addition'){
							$row->new_qoh = $product_qty['quantity'] + $row->qty; 
						}else{
							$row->new_qoh = $product_qty['quantity'] - $row->qty; 
						}
						$ri = $this->Settings->item_addition ? $product->id : $c;
						$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'options' => $options, 'units' => $units);
                        $c++;
                    }
                }
            }
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : FALSE;
            $this->data['warehouse_id'] = $count_id ? $stock_count->warehouse_id : FALSE;
            $this->data['count_id'] = $count_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/quantity_adjustments'), 'page' => lang('quantity_adjustments')), array('link' => '#', 'page' => lang('add_adjustment')));
			$meta = array('page_title' => lang('add_adjustment'), 'bc' => $bc);
            $this->core_page('products/add_adjustment', $meta, $this->data);

        }
    }

	public function approve_adjustment($id = null)
    {
		$this->cus->checkPermissions('approve_adjustment', true);
		$adjustment = $this->products_model->getAdjustmentByID($id);
		$adjustment_items = $this->products_model->getAdjustmentItems($id);
		if($adjustment && $adjustment_items){
			$date = $adjustment->date;
			$warehouse_id = $adjustment->warehouse_id;
			$reference_no = $adjustment->reference_no;
			$biller_id = $adjustment->biller_id;
			$project_id = $adjustment->project_id;
			$note = $adjustment->note;
			
			foreach($adjustment_items as $adjustment_item){
				$product_details = $this->products_model->getProductByID($adjustment_item->product_id); 
				$product_id = $product_details->id;
                $type = $adjustment_item->type;
                $serial = $adjustment_item->serial_no;
				$expired_data = $adjustment_item->expiry;
                $variant = $adjustment_item->option_id;
				$item_unit = $adjustment_item->product_unit_id;
                $item_quantity = abs($adjustment_item->quantity);
				$unit = $this->site->getProductUnit($product_id,$item_unit);
				$real_unit_cost = $product_details->cost;
				
				if($this->Settings->accounting_method == '0' && $type == 'subtraction'){
					$costs = $this->site->getFifoCost($product_id,$item_quantity,$stockmoves);
				}else if($this->Settings->accounting_method == '1' && $type == 'subtraction'){
					$costs = $this->site->getLifoCost($product_id,$item_quantity,$stockmoves);
				}else if($this->Settings->accounting_method == '3' && $type == 'subtraction'){
					$costs = $this->site->getProductMethod($product_id,$item_quantity,$stockmoves);
				}else{
					$costs = false;
				}
				if($type == 'subtraction'){
					$item_quantity = $item_quantity * (-1);
				}
				if($costs && $serial==''){
					$item_cost_qty  = 0;
					$item_cost_total = 0;
					$productAcc = $this->site->getProductAccByProductId($product_details->id);
					foreach($costs as $cost_item){
						$item_cost_qty += $cost_item['quantity'];
						$item_cost_total += $cost_item['cost'] * $cost_item['quantity'];
						$stockmoves[] = array(
							'transaction_id' => $id,
							'transaction' => 'QuantityAdjustment',
							'product_id' => $product_id,
							'product_code' => $product_details->code,
							'option_id' => $variant,
							'quantity' => $cost_item['quantity'] * (-1),
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'expiry' => $expired_data,
							'unit_id' => $item_unit,
							'warehouse_id' => $warehouse_id,
							'date' => $date,
							'real_unit_cost' => $cost_item['cost'],
							'serial_no' => $serial,
							'reference_no' => $reference_no,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($this->Settings->accounting == 1){		
							$accTrans[] = array(
								'transaction_id' => $id,
								'transaction' => 'QuantityAdjustment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->stock_acc,
								'amount' => -($cost_item['cost'] * abs($cost_item['quantity'])),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction_id' => $id,
								'transaction' => 'QuantityAdjustment',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->adjustment_acc,
								'amount' => ($cost_item['cost'] * abs($cost_item['quantity'])),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
					}
					$real_unit_cost += ($item_cost_total / $item_cost_qty);
				}else{
					
					if($serial!='' && $type!='subtraction'){
						$serial_detail = $this->products_model->getProductSerial($serial,$product_details->id,$warehouse_id);
						if($serial_detail){
							$product_details->cost = $serial_detail->cost;
						}
						if($item_quantity > 0){
							$reactive = 0;
							if($serial_detail){
								if($serial_detail->inactive==0){
									$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial.') ');
									redirect($_SERVER["HTTP_REFERER"]);
								}else {
									$reactive = 1;
								}
							}else{
								$product_serials[] = array(
									'adjustment_id' => $id,
									'product_id' => $product_details->id,
									'cost' => $product_details->cost,
									'price' => $product_details->price,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'serial' => $serial,
								);
							}
						}
					}else{
						$reactive = 1;
					}
					
					$stockmoves[] = array(
						'transaction_id' => $id,
						'transaction' => 'QuantityAdjustment',
                        'product_id' => $product_id,
						'product_code' => $product_details->code,
                        'option_id' => $variant,
                        'quantity' => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $item_unit,
                        'warehouse_id' => $warehouse_id,
                        'date' => $date,
						'expiry' => $expired_data,
						'real_unit_cost' => $product_details->cost,
						'serial_no' => $serial,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
						'reactive' => $reactive,
                    );	
					if($this->Settings->accounting == 1){		
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'QuantityAdjustment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($product_details->cost * $item_quantity),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction_id' => $id,
							'transaction' => 'QuantityAdjustment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->adjustment_acc,
							'amount' => ($product_details->cost * $item_quantity) * (-1),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
					}
				}
			}
			$data = array(
							'status' =>'approved',
							'approved_by' => $this->session->userdata('user_id'),
							'approved_at' => date('Y-m-d H:i:s')
						);
			if ($this->products_model->approveAdjustment($id, $data, $stockmoves, $accTrans)) {
				if ($this->input->is_ajax_request()) {
					echo lang("quantity_adjustment_approved");die();
				}else{
					$this->session->set_flashdata('error', lang("quantity_adjustment_cannot_approved"));
					die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
				}
			}				
		}else{
			$this->session->set_flashdata('error', lang("quantity_adjustment_cannot_approved"));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
		}
	

    }

    function edit_adjustment($id)
    {
        $this->cus->checkPermissions('adjustments-edit', true);
		if(!$this->Admin && !$this->Owner && !$this->GP['products-approve_adjustment']){
			$this->session->set_flashdata('error', lang('access_denied'));
            $this->cus->md();
		}
        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->cus->md();
        }
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = $adjustment->date;
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {

                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $serial = $_POST['serial'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;
				$expired_data = isset($_POST['expiry'][$r]) ? $this->cus->fsd($_POST['expiry'][$r]) : NULL;
				$item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$unit = $this->site->getProductUnit($product_id,$item_unit);
				$product_details = $this->products_model->getProductByID($product_id);
				$real_unit_cost = $_POST['real_unit_cost'][$r];

				if($this->Settings->accounting_method == '0' && $type == 'subtraction'){
					$costs = $this->site->getFifoCost($product_id,$item_quantity,$stockmoves,'QuantityAdjustment',$id);
				}else if($this->Settings->accounting_method == '1' && $type == 'subtraction'){
					$costs = $this->site->getLifoCost($product_id,$item_quantity,$stockmoves,'QuantityAdjustment',$id);;
				}else if($this->Settings->accounting_method == '3' && $type == 'subtraction'){
					$costs = $this->site->getProductMethod($product_id,$item_quantity,$stockmoves,'QuantityAdjustment',$id);;
				}else{
					$costs = false;
				}
				if($type == 'subtraction'){
					$item_quantity = $item_quantity * (-1);
				}
				
				if($costs && $serial==''){
					$item_cost_qty  = 0;
					$item_cost_total = 0;
					$productAcc = $this->site->getProductAccByProductId($product_details->id);
					foreach($costs as $item_cost){
						$item_cost_qty += $item_cost['quantity'];
						$item_cost_total += $item_cost['cost'] * $item_cost['quantity'];
						$stockmoves[] = array(
							'transaction' => 'QuantityAdjustment',
							'product_id' => $product_id,
							'product_code' => $product_details->code,
							'option_id' => $variant,
							'quantity' => $item_cost['quantity'] * (-1),
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'expiry' => $expired_data,
							'warehouse_id' => $warehouse_id,
							'date' => $date,
							'real_unit_cost' => $item_cost['cost'],
							'serial_no' => $serial,
							'reference_no' => $reference_no,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($this->Settings->accounting == 1){		
							$accTrans[] = array(
								'transaction' => 'QuantityAdjustment',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->stock_acc,
								'amount' => -($item_cost['cost'] * abs($item_cost['quantity'])),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_cost['quantity'].'#'.'Cost: '.$item_cost['cost'],
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction' => 'QuantityAdjustment',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->adjustment_acc,
								'amount' => ($item_cost['cost'] * abs($item_cost['quantity'])),
								'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_cost['quantity'].'#'.'Cost: '.$item_cost['cost'],
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							
						}
					}
					$real_unit_cost += ($item_cost_total / $item_cost_qty);
				}else{
					
					if($serial!='' && $type!='subtraction'){
						$serial_detail = $this->products_model->getProductSerial($serial,$product_details->id,$warehouse_id,$id);
						if($serial_detail){
							$product_details->cost = $serial_detail->cost;
						}
						
						if($item_quantity > 0){	
							$reactive = 0;
							if($serial_detail){
								if($serial_detail->inactive==0){
									if($this->products_model->getAdjustmentItemSerial($product_details->id,$id,$serial)){
										$reactive = 1;
									}else{
										$this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial.') ');
										redirect($_SERVER["HTTP_REFERER"]);
									}

								}else {
									$reactive = 1;
								}
							}else{
								$product_serials[] = array(
									'product_id' => $product_details->id,
									'cost' => $product_details->cost,
									'price' => $product_details->price,
									'warehouse_id' => $warehouse_id,
									'date' => $date,
									'serial' => $serial,
								);
							}
						}
					}else{
						$reactive = 1;
					}
					
					$stockmoves[] = array(
							'transaction' => 'QuantityAdjustment',
							'product_id' => $product_id,
							'product_code' => $product_details->code,
							'option_id' => $variant,
							'quantity' => $item_quantity,
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'expiry' => $expired_data,
							'warehouse_id' => $warehouse_id,
							'date' => $date,
							'real_unit_cost' => $real_unit_cost,
							'serial_no' => $serial,
							'reference_no' => $reference_no,
							'user_id' => $this->session->userdata('user_id'),
							'reactive' => $reactive,
						);		
					if($this->Settings->accounting == 1){		
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						$accTrans[] = array(
							'transaction' => 'QuantityAdjustment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->stock_acc,
							'amount' => ($real_unit_cost * $item_quantity),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$real_unit_cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'QuantityAdjustment',
							'transaction_id' => $id,
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $productAcc->adjustment_acc,
							'amount' => ($real_unit_cost * $item_quantity) * (-1),
							'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$real_unit_cost,
							'description' => $note,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
					}
				}
				
                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $item_quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    'serial_no' => $serial,
					'product_unit_id' => $item_unit,
					'product_unit_code' => $unit->code,
					'unit_quantity' => $quantity,
					'expiry' => $expired_data,
					'real_unit_cost' => $real_unit_cost,
                    );
					
				
				
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
                );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if($adjustment->status == 'pending'){
				$stockmoves = false;
				$accTrans = false;
				$product_serials = false;
			}
        }

        if ($this->form_validation->run() == true && $this->products_model->updateAdjustment($id, $data, $products, $stockmoves, $accTrans, $product_serials)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("quantity_adjusted")." - ".$data['reference_no']);
            redirect('products/quantity_adjustments');
        } else {

            $inv_items = $this->products_model->getAdjustmentItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                
				$row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->unit_quantity;
				$row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->cus->hrsd($item->expiry) : '');
                $row->type = $item->type;
                $options = $this->products_model->getProductOptions($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;			
				$product_qty = $this->products_model->getProductQuantity($row->id,$adjustment->warehouse_id);
				$row->quantity = $product_qty['quantity'] - $item->quantity;
				$row->qoh = $this->cus->convertQty($row->id, $row->quantity);	
				$row->new_qoh = $product_qty['quantity'];
				$row->base_quantity = $item->quantity;
				$row->base_unit_cost = $product->cost;
                $row->base_unit = $product->unit ? $product->unit : $item->product_unit_id;
				$row->unit = $item->product_unit_id;
				$row->real_unit_cost = $item->real_unit_cost;
				
				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'units'=> $units);
                $c++;
            }

            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['billers'] = $this->site->getBillers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/quantity_adjustments'), 'page' => lang('quantity_adjustments')), array('link' => '#', 'page' => lang('edit_adjustment')));
			$meta = array('page_title' => lang('edit_adjustment'), 'bc' => $bc);
            $this->core_page('products/edit_adjustment', $meta, $this->data);

        }
    }

    function add_adjustment_by_csv()
    {
        $this->cus->checkPermissions('adjustments-add', true);
		if(!$this->Admin && !$this->Owner && !$this->GP['products-approve_adjustment']){
			$this->session->set_flashdata('error', lang('access_denied'));
            $this->cus->md();
		}
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => NULL,
                );

            if ($_FILES['csv_file']['size'] > 0) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $csv = $this->upload->file_name;
                $data['attachment'] = $csv;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'quantity', 'variant');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->cus->print_arrays($final);
                $rw = 2;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['code']))) {
                        $csv_variant = trim($pr['variant']);
                        $variant = !empty($csv_variant) ? $this->products_model->getProductVariantID($product->id, $csv_variant) : FALSE;

                        $csv_quantity = trim($pr['quantity']);
                        $type = $csv_quantity > 0 ? 'addition' : 'subtraction';
                        $quantity = $csv_quantity > 0 ? $csv_quantity : (0-$csv_quantity);

                        if (!$this->Settings->overselling && $type == 'subtraction') {
                            if ($variant) {
                                if($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                                    if ($op_wh_qty->quantity < $quantity) {
                                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            }
                            if($wh_qty = $this->products_model->getProductQuantity($product->id, $warehouse_id)) {
                                if ($wh_qty['quantity'] < $quantity) {
                                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        
                        $products[] = array(
                            'product_id' => $product->id,
                            'type' => $type,
                            'quantity' => $quantity,
                            'warehouse_id' => $warehouse_id,
                            'option_id' => $variant,
                            );

                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $rw++;
                }

            } else {
                $this->form_validation->set_rules('csv_file', lang("upload_file"), 'required');
            }

            // $this->cus->print_arrays($data, $products);

        }

        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            redirect('products/quantity_adjustments');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_adjustment')));
            $meta = array('page_title' => lang('add_adjustment_by_csv'), 'bc' => $bc);
            $this->core_page('products/add_adjustment_by_csv', $meta, $this->data);

        }
    }

    function delete_adjustment($id = NULL)
    {
        $this->cus->checkPermissions('adjustments-delete', TRUE);
		$row = $this->products_model->getAdjustmentByID($id);
		if ($this->products_model->deleteAdjustment($id)) {
			if($this->input->is_ajax_request()) {
				echo lang("adjustment_deleted");
				die();
			}
			$this->session->set_flashdata('message', lang('adjustment_deleted')." - ". $row->reference_no);
		}
		redirect('products/quantity_adjustments');
    } 
	
	function price_list($id)
	{
		$this->cus->checkPermissions('price_list', TRUE);
		$product_id = explode('ProductID',$id);
		$products = $this->products_model->getProductsByID($product_id);
        if (!$id || !$products) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->cus->md();
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$biller_id = $this->session->userdata('biller_id');
		}else{
			$company = $this->site->getAllCompanies('biller');
			$biller_id = $company[0]->id;
		}
		$this->data['user'] = $this->site->getUser($this->session->userdata('user_id'));
		$this->data['biller'] = $this->site->getCompanyByID($biller_id);
		$this->data['products'] = $products;
		$this->load->view($this->theme.'products/price_list', $this->data);
	}
    
    function modal_view($id = NULL)
    {
        $this->cus->checkPermissions('index', TRUE);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->cus->md();
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
		$this->data['price_groups'] = $this->products_model->getProductGroupPrices($id);
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
		if($this->Settings->product_expiry == '1'){
			$this->data['expiries'] = $this->site->getExpiryQuantityByProduct($id);
		}
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->load->view($this->theme.'products/modal_view', $this->data);
    }

    function view($id = NULL)
    {
        $this->cus->checkPermissions('index');
        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => $pr_details->name));
        $meta = array('page_title' => $pr_details->name, 'bc' => $bc);
        $this->core_page('products/view', $meta, $this->data);
    }

    function pdf($id = NULL, $view = NULL)
    {
        $this->cus->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . ".pdf";
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, TRUE);
            if (! $this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->cus->generate_pdf($html, $name);
        }
    }

    function getSubCategories($category_id = NULL)
    {
        if ($rows = $this->products_model->getSubCategories($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    function product_actions($wh = NULL)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete');
					$product_details = "";
                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
                        if($row->quantity <> 0){
                            $this->session->set_flashdata('error', lang('product_has_quantity').' '.( ". $row->code .' - '. $row->name ." ));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
						$product_details .= " ( ". $row->code .' - '. $row->name ." ) ,";
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted") . " - " . $product_details);
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'labels') {

                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
						
						if($wh){
							$row1 = $this->site->getWarehouseProduct($wh, $id);
							$quantity = $row1->quantity;
						}else{
							$quantity = $row->quantity;
						}
						
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
					$barcode_styles = $this->products_model->getBarcodeStyle();
					$this->data['barcode_styles'] = $barcode_styles;
					$this->data['barcode_setting'] = $this->products_model->getBarcodeSettingByID($barcode_styles[0]->id);
                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->core_page('products/print_barcodes', $meta, $this->data);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('barcode_symbology'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('sale').' '.lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('purchase').' '.lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('alert_quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('tax_method'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('subcategory_code'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('product_variants'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('pcf1'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('pcf2'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('pcf3'));
                    $this->excel->getActiveSheet()->SetCellValue('T1', lang('pcf4'));
                    $this->excel->getActiveSheet()->SetCellValue('U1', lang('pcf5'));
                    $this->excel->getActiveSheet()->SetCellValue('V1', lang('pcf6'));
                    $this->excel->getActiveSheet()->SetCellValue('W1', lang('quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $brand = $this->site->getBrandByID($product->brand);
                        if($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        } else {
                            $base_unit = '';
                            $sale_unit = '';
                            $purchase_unit = '';
                        }
                        $variants = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
						
						/* if(file_exists('assets/uploads/thumbs/'.$product->image))
						{
							$objDrawing = new PHPExcel_Worksheet_Drawing();
							$objDrawing->setPath('assets/uploads/thumbs/'.$product->image);
							$objDrawing->setCoordinates('A'.$row);
							$objDrawing->setWorksheet($this->excel->getActiveSheet());
							$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(50);
						}
						else
						{
							$this->excel->getActiveSheet()->setCellValue('A'.$row, '');
						} */

						$product->name = ltrim($product->name, '=');
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $product->barcode_symbology);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($brand ? $brand->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale_unit);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase_unit);
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_price')) {
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->price);
                        }
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->alert_quantity);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $product->tax_rate_name);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $product->tax_method ? lang('exclusive') : lang('inclusive'));
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $product->image);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $product->subcategory_code);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $product_variants);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $product->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $product->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $product->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $product->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('U' . $row, $product->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('V' . $row, $product->cf6);
                        $this->excel->getActiveSheet()->SetCellValue('W' . $row, $quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_image($id = NULL)
    {
        $this->cus->checkPermissions('edit', true);
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $id || die(json_encode(array('error' => 1, 'msg' => lang('no_image_selected'))));
            $this->db->delete('product_photos', array('id' => $id));
            die(json_encode(array('error' => 0, 'msg' => lang('image_deleted'))));
        }
        die(json_encode(array('error' => 1, 'msg' => lang('ajax_error'))));
    }

    public function getSubUnits($unit_id)
    {
        $unit = $this->site->getUnitByID($unit_id);
        if (!$units = $this->site->getUnitsByBUID($unit_id)) {
            $units = array($unit);
        } 
        $this->cus->send_json($units);
    }

    public function qa_suggestions()
    {
        $term = $this->input->get('term', true);
       
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->products_model->getQASuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';
				
				$unit = $this->products_model->getUnitbyProduct($row->id,$row->unit);
                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'unit'=> $unit);

            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	/*=======UPDATE 10-02-2018=======*/
	
	public function qa_allsuggestions()
    {
        $term = $this->input->get('term', true);
		$warehouse_id = $this->input->get('warehouse_id', TRUE);
		
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->cus->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->products_model->getAllProductNames($sr);
        if ($rows) {
            foreach ($rows as $row) {
				$product_qty = $this->products_model->getProductQuantity($row->id,$warehouse_id);
                if($product_qty['quantity']== ''){
					$product_qty['quantity'] = 0;
				}
				$row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';
				$row->expiry = '';				
				$row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
				$row->quantity = $product_qty['quantity'];
				$row->qoh = $this->cus->convertQty($row->id, $row->quantity);
                $row->qohunit = $row->unit;
				$row->new_qoh = $product_qty['quantity'] - 1; 
				$row->type = 'subtraction';
				$row->real_unit_cost = $row->cost;

				$units = $this->site->getUnitbyProduct($row->id,$row->base_unit);				
                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'units'=> $units);
            }
            $this->cus->send_json($pr);
        } else {
            $this->cus->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	function cost_adjustment_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('cost_adjustments');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $adjustment = $this->products_model->getCostAdjustmentByID($id);
						
                        $created_by = $this->site->getUser($adjustment->created_by);
						$biller = $this->site->getCompanyByID($adjustment->biller_id);
						$items = $this->products_model->getCostAdjustmentItems($id);  
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name.' ('.lang('old_cost').' = '.$item->old_cost.' '.lang('new_cost').' = '.$item->new_cost.')'."\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($adjustment->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $adjustment->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $biller->company);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->decode_html($adjustment->note));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'cost_adjustments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
    function adjustment_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->cus->checkPermissions('adjustments-delete', TRUE);
					$adjustment_no = "";
                    foreach ($_POST['val'] as $id) {
						$row = $this->products_model->getAdjustmentByID($id);
						$adjustment_no .= $row->reference_no.", ";
                        $this->products_model->deleteAdjustment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("adjustment_deleted")." - ".$adjustment_no);
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('quantity_adjustments');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $adjustment = $this->products_model->getAdjustmentByID($id);
                        $created_by = $this->site->getUser($adjustment->created_by);
                        $warehouse = $this->site->getWarehouseByID($adjustment->warehouse_id);
                        $items = $this->products_model->getAdjustmentItems($id);  
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name.'('.$this->cus->formatQuantity($item->type == 'subtraction' ? -$item->quantity : $item->quantity).')'."\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($adjustment->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $adjustment->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->cus->decode_html($adjustment->note));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($adjustment->status));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quantity_adjustments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	
	
    function stock_counts($warehouse_id = NULL)
    {
        $this->cus->checkPermissions('stock_count');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse_id'] = $warehouse_id;
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('stock_counts')));
		$meta = array('page_title' => lang('stock_counts'), 'bc' => $bc);
        $this->core_page('products/stock_counts', $meta, $this->data);
    }

    function getCounts($warehouse_id = NULL)
    {
        $this->cus->checkPermissions('stock_count', TRUE);
        $detail_link = anchor('products/view_count/$1', '<label class="label label-primary pointer">'.lang('details').'</label>', 'class="tip" title="'.lang('details').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('stock_counts')}.id as id, date, reference_no, {$this->db->dbprefix('warehouses')}.name as wh_name, type, brand_names, category_names, initial_file, final_file")
            ->from('stock_counts')
            ->join('warehouses', 'warehouses.id=stock_counts.warehouse_id', 'left');
        if ($warehouse_id) {
            $this->datatables->where('warehouse_id', $warehouse_id);
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('stock_counts.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('stock_counts.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('stock_counts.created_by', $this->session->userdata('user_id'));
		}
		
        $this->datatables->add_column('Actions', '<div class="text-center">'.$detail_link.'</div>', "id");
        echo $this->datatables->generate();
    }

    function view_count($id)
    {
        $this->cus->checkPermissions('stock_count', TRUE);
        $stock_count = $this->products_model->getStouckCountByID($id);
        if ( ! $stock_count->finalized) {
            $this->cus->md('products/finalize_count/'.$id);
        }

        $this->data['stock_count'] = $stock_count;
		$this->data['biller'] = $this->site->getCompanyByID($stock_count->biller_id);
        $this->data['stock_count_items'] = $this->products_model->getStockCountItems($id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
        $this->data['adjustment'] = $this->products_model->getAdjustmentByCountID($id);
        $this->load->view($this->theme.'products/view_count', $this->data);
    }

    function count_stock($page = NULL){
        $this->cus->checkPermissions('stock_count');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $this->form_validation->set_rules('type', lang("type"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cou',$biller_id);
            $date = $this->cus->fsd($this->input->post('date'));
            $warehouse_id = $this->input->post('warehouse');
            $type = $this->input->post('type');
            $categories = $this->input->post('category') ? $this->input->post('category') : NULL;
            $brands = $this->input->post('brand') ? $this->input->post('brand') : NULL;
            $this->load->helper('string');
            $name = random_string('md5').'.xls';
            $products = $this->products_model->getStockCountProductByEnding($date,$warehouse_id, $type, $categories, $brands);
          
			$pr = 0; $rw = 0;
            foreach ($products as $product) {
				if($this->Settings->product_expiry == '1'){
					$product_expiries = $this->site->getProductExpiredByEnding($date,$product->id,$warehouse_id);
					if($product_expiries){
						foreach($product_expiries as $product_expiry){
							$items[] = array(
								'product_code' => $product->code,
								'product_name' => $product->name,
								'expiry' => $product_expiry->expiry,
								'variant' => '',
								'expected' => ($product_expiry->quantity ? $product_expiry->quantity : 0),
								'counted' => ''
								);
							$rw++;
						}
					}
					
				}else if ($variants = $this->products_model->getStockCountProductVariantsByEnding($date, $warehouse_id, $product->id)) {
                    foreach ($variants as $variant) {
                        $items[] = array(
                            'product_code' => $product->code,
                            'product_name' => $product->name,
							'expiry' => '',
                            'variant' => $variant->name,
                            'expected' => ($variant->quantity ? $variant->quantity : 0),
                            'counted' => ''
                            );
                        $rw++;
                    }
                } else {
                    $items[] = array(
                        'product_code' => $product->code,
                        'product_name' => $product->name,
						'expiry' => '',
                        'variant' => '',
                        'expected' => ($product->quantity ? $product->quantity : 0),
                        'counted' => ''
                        );
                    $rw++;
                }
                $pr++;
            }
			
            if ( ! empty($items)) {
				$this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);

                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('expiry'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('variant'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('expected'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('counted'));
				$row = 2;
				
				foreach ($items as $item) {
                    $this->excel->getActiveSheet()->setCellValueExplicit('A' . $row, $item['product_code'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValueExplicit('B' . $row, $item['product_name'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValueExplicit('C' . $row, $item['expiry'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValueExplicit('D' . $row, $item['variant'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $item['expected']);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $item['counted']);
					$row++;
                }

				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $name . '.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				$objWriter->save('./files/'.$name, 'w');
            } else {
                $this->session->set_flashdata('error', lang('no_product_found'));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            
            $category_ids = '';
            $brand_ids = '';
            $category_names = '';
            $brand_names = '';
            if ($categories) {
                $r = 1; $s = sizeof($categories);
                foreach ($categories as $category_id) {
                    $category = $this->site->getCategoryByID($category_id);
                    if ($r == $s) {
                        $category_names .= $category->name;
                        $category_ids .= $category->id;
                    } else {
                        $category_names .= $category->name.', ';
                        $category_ids .= $category->id.', ';
                    }
                    $r++;
                }
            }
            if ($brands) {
                $r = 1; $s = sizeof($brands);
                foreach ($brands as $brand_id) {
                    $brand = $this->site->getBrandByID($brand_id);
                    if ($r == $s) {
                        $brand_names .= $brand->name;
                        $brand_ids .= $brand->id;
                    } else {
                        $brand_names .= $brand->name.', ';
                        $brand_ids .= $brand->id.', ';
                    }
                    $r++;
                }
            }
            $data = array(
                'date' => $date,
                'warehouse_id' => $warehouse_id,
				'biller_id' => $biller_id,
                'reference_no' => $reference,
                'type' => $type,
                'categories' => $category_ids,
                'category_names' => $category_names,
                'brands' => $brand_ids,
                'brand_names' => $brand_names,
                'initial_file' => $name,
                'products' => $pr,
                'rows' => $rw,
                'created_by' => $this->session->userdata('user_id')
            );

        }
        
        if ($this->form_validation->run() == true && $this->products_model->addStockCount($data)) {
            $this->session->set_flashdata('message', lang("stock_count_intiated")." - ".$data['reference_no']);
            redirect('products/stock_counts');

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
			$this->data['billers'] = $this->site->getBillers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/stock_counts'), 'page' => lang('stock_counts')), array('link' => '#', 'page' => lang('count_stock')));
			$meta = array('page_title' => lang('count_stock'), 'bc' => $bc);
            $this->core_page('products/count_stock', $meta, $this->data);

        }

    }

    function finalize_count($id)
    {
        $this->cus->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if ( ! $stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang("stock_count_finalized"));
            redirect('products/stock_counts');
        }

        $this->form_validation->set_rules('count_id', lang("count_stock"), 'required');

        if ($this->form_validation->run() == true) {
            if ($_FILES['userfile']['size'] > 0) {
                $note = $this->cus->clear_tags($this->input->post('note'));
                $data = array(
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'note' => $note
                );
				$csv = $this->upload->file_name;
				
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$product_code = $worksheet->getCellByColumnAndRow(0, $row)->getFormattedValue();
						$product_name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$product_expiry =trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
						if (strpos($product_expiry, '/') == false) {
							$product_expiry = PHPExcel_Shared_Date::ExcelToPHP($product_expiry);
							$product_expiry = date('d/m/Y',$product_expiry);
						}
						$product_expiry = $this->cus->fsd($product_expiry);
						$product_variant = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$expected = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						if($expected==''){
							$expected = 0;
						}
						$counted = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
						$final[] = array(
							'product_code'  => $product_code,
							'product_name'   => $product_name,
							'product_expiry'   => $product_expiry,
							'product_variant'    => $product_variant,
							'expected'  => $expected,
							'counted'   => $counted,
						);
					}
				}

                $rw = 2; $differences = 0; $matches = 0;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['product_code']))) {
                        $pr['counted'] = !empty($pr['counted']) ? $pr['counted'] : 0;
                        if ($pr['expected'] == $pr['counted']) {
                            $matches++;
                        } else {
                            $pr['stock_count_id'] = $id;
                            $pr['product_id'] = $product->id;
                            $pr['cost'] = $product->cost;
							$pr['product_expiry'] = empty($pr['product_expiry']) ? NULL : $pr['product_expiry'];
                            $pr['product_variant_id'] = empty($pr['product_variant']) ? NULL : $this->products_model->getProductVariantID($pr['product_id'], $pr['product_variant']);
                            $products[] = $pr;
                            $differences++;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['product_code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        redirect('products/finalize_count/'.$id);
                    }
                    $rw++;
                }
				
				$this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('userfile')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $file_name = $this->upload->file_name;

                $data['final_file'] = $file_name;
                $data['differences'] = $differences;
                $data['matches'] = $matches;
                $data['missing'] = $stock_count->rows-($rw-2);
                $data['finalized'] = 1;
            }
        }
        
        if ($this->form_validation->run() == true && $this->products_model->finalizeStockCount($id, $data, $products)) {
            $this->session->set_flashdata('message', lang("stock_count_finalized"));
            redirect('products/stock_counts');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stock_count'] = $stock_count;
            $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => site_url('products/stock_counts'), 'page' => lang('stock_counts')), array('link' => '#', 'page' => lang('finalize_count')));
            $meta = array('page_title' => lang('finalize_count'), 'bc' => $bc);
            $this->core_page('products/finalize_count', $meta, $this->data);

        }

    }
	
	/**********Using Stock*************/
	
	function add_using_stock()
    {        
		$this->cus->checkPermissions('using_stocks-add',true);
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));        
        $this->form_validation->set_rules('warehouse_id', lang("warehouse_id"), 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
			
			$biller_id = $this->input->post('biller');			
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('us',$biller_id);
			if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $customer_id = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $warehouse_id = $this->input->post('warehouse_id');
			$project_id = $this->input->post('project');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $warehouse_details = $this->site->getWarehouseByID($warehouse_id);
            $warehouse_code = $warehouse_details->code;
            $warehouse_name = $warehouse_details->name;
			$using_by = $this->input->post('staff');
			$return_date = $this->cus->fsd(trim($this->input->post('return_date')));

            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_unit_quantity = $_POST['quantity'][$r];
				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$item_expiry = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$item_expiry = null;
				}
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_quantity = $_POST['product_base_quantity'][$r];
                if (isset($item_code) && isset($item_quantity)) {
                    $product_details = $this->products_model->getProductByCode($item_code);
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
					
					if($this->Settings->accounting_method == '0'){
						$costs = $this->site->getFifoCost($product_details->id,$item_quantity,$stockmoves);
					}else if($this->Settings->accounting_method == '1'){
						$costs = $this->site->getLifoCost($product_details->id,$item_quantity,$stockmoves);
					}else if($this->Settings->accounting_method == '3'){
						$costs = $this->site->getProductMethod($product_details->id,$item_quantity,$stockmoves);
					}
					
					if($costs && $item_serial==''){
						$productAcc = $this->site->getProductAccByProductId($item_id);
						foreach($costs as $cost_item){
							
							$products[] = array(
								'product_id' => $product_details->id,
								'product_code' => $item_code,
								'product_name' => $product_details->name,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'],
								'product_unit_id' => $item_unit,
								'product_unit_code' => $unit->code,
								'unit_quantity' => ($unit->unit_qty > 1 ? ($cost_item['quantity'] / $unit->unit_qty) : $cost_item['quantity']),
								'expiry' => $item_expiry,
								'serial_no' => $item_serial,
								'product_cost' => $cost_item['cost'],

							);
							$stockmoves[] = array(
								'transaction' => 'UsingStock',
								'product_id' => $product_details->id,
								'product_code' => $item_code,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'] * (-1),
								'unit_quantity' => $unit->unit_qty,
								'unit_code' => $unit->code,
								'unit_id' => $item_unit,
								'warehouse_id' => $warehouse_id,
								'expiry' => $item_expiry,
								'date' => $date,
								'real_unit_cost' => $cost_item['cost'],
								'serial_no' => $item_serial,
								'reference_no' => $reference_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							if($this->Settings->accounting == 1){		
								
								$accTrans[] = array(
									'transaction' => 'UsingStock',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $productAcc->stock_acc,
									'amount' => -($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'customer_id' => $customer_id,
									'user_id' => $this->session->userdata('user_id'),
								);
								$accTrans[] = array(
									'transaction' => 'UsingStock',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $productAcc->usage_acc,
									'amount' => ($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'customer_id' => $customer_id,
									'user_id' => $this->session->userdata('user_id'),
								);
								
							}
						}
					}else{
						if($item_serial!=''){
							$serial_detail = $this->products_model->getProductSerial($item_serial,$product_details->id,$warehouse_id);
							if($serial_detail){
								$product_details->cost = $serial_detail->cost;
							}
							
						}
						$stockmoves[] = array(
							'transaction' => 'UsingStock',
							'product_id' => $product_details->id,
							'product_code' => $item_code,
							'option_id' => $item_option,
							'quantity' => (-1)*$item_quantity,
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'warehouse_id' => $warehouse_id,
							'expiry' => $item_expiry,
							'date' => $date,
							'real_unit_cost' => $product_details->cost,
							'serial_no' => $item_serial,
							'reference_no' => $reference_no,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($this->Settings->accounting == 1){		
							$productAcc = $this->site->getProductAccByProductId($product_details->id);
							$accTrans[] = array(
								'transaction' => 'UsingStock',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->stock_acc,
								'amount' => -($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'customer_id' => $customer_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction' => 'UsingStock',
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->usage_acc,
								'amount' => ($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'customer_id' => $customer_id,
								'user_id' => $this->session->userdata('user_id'),
							);
						}
						$products[] = array(
							'product_id' => $product_details->id,
							'product_code' => $item_code,
							'product_name' => $product_details->name,
							'option_id' => $item_option,
							'quantity' => $item_quantity,
							'product_unit_id' => $item_unit,
							'product_unit_code' => $unit->code,
							'unit_quantity' => $item_unit_quantity,
							'expiry' => $item_expiry,
							'serial_no' => $item_serial,
							'product_cost' => $product_details->cost,

						);
					}
                }
            }
			
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

    
            $data = array('reference_no' => $reference_no,
                'date' => $date,
                'warehouse_id' => $warehouse_id,
                'warehouse_code' => $warehouse_code,
				'warehouse_name' => $warehouse_name,
				'biller_id' => $biller_id, 
				'using_by' => $using_by,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'project_id' => $project_id,
				'status' => 'pending',				
                'note' => $note,
				'return_date' => $return_date,
                'created_by' => $this->session->userdata('user_id')
            );
			
			if($this->config->item("vehicle")){
				$data['vehicle_id'] = $this->input->post("vehicle");
			}
			
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if($this->Settings->product_expiry == '1' && $stockmoves && $products){
				$checkExpiry = $this->site->checkExpiry($stockmoves, $products,'UsingStock');
				$stockmoves = $checkExpiry['expiry_stockmoves'];
				$products = $checkExpiry['expiry_items'];
			}

        }

        if ($this->form_validation->run() == true && $this->products_model->addUsingStock($data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_using', 1);
            $this->session->set_flashdata('message', lang("using_stock_added")." - ".$data['reference_no']);
            redirect("products/using_stocks");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );
			$this->data['vehicles'] = $this->site->getAllVehicles();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['users'] = $this->site->getAllUsers();
            $this->data['rnumber'] = ''; 
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/using_stocks'), 'page' => lang('using_stocks')), array('link' => '#', 'page' => lang('add_using_stock')));
			$meta = array('page_title' => lang('add_using_stock'), 'bc' => $bc);
            $this->core_page('products/add_using_stock', $meta, $this->data);
        }
    }
	
	function edit_using_stock($id = NULL)
    {  
		$this->cus->checkPermissions('using_stocks-edit',true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $using_stock = $this->products_model->getUsingStockByID($id);
		if($using_stock->status != 'pending'){
			$this->session->set_flashdata('error', $using_stock->reference_no.' '.lang("using_stock_cannot_edit_x_return"));
            redirect($_SERVER["HTTP_REFERER"]);
		}
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));        
        $this->form_validation->set_rules('warehouse_id', lang("warehouse_id"), 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
			
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('us',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$return_date = $this->cus->fsd(trim($this->input->post('return_date')));
			$customer_id = $this->input->post('customer');
			$customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $warehouse_id = $this->input->post('warehouse_id');
			$using_by = $this->input->post('staff');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $warehouse_details = $this->site->getWarehouseByID($warehouse_id);
            $warehouse_code = $warehouse_details->code;
            $warehouse_name = $warehouse_details->name;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_unit_quantity = $_POST['quantity'][$r];
                if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$item_expiry = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$item_expiry = null;
				}
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                if (isset($item_code) && isset($item_quantity)) {
                    $product_details = $this->products_model->getProductByCode($item_code);
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
                    
					if($this->Settings->accounting_method == '0'){
						$costs = $this->site->getFifoCost($product_details->id,$item_quantity,$stockmoves,'UsingStock',$id);
					}else if($this->Settings->accounting_method == '1'){
						$costs = $this->site->getLifoCost($product_details->id,$item_quantity,$stockmoves,'UsingStock',$id);;
					}else if($this->Settings->accounting_method == '3'){
						$costs = $this->site->getProductMethod($product_details->id,$item_quantity,$stockmoves,'UsingStock',$id);;
					}
					
					if($costs && $item_serial==''){
						$productAcc = $this->site->getProductAccByProductId($product_details->id);
						foreach($costs as $cost_item){
							
							$products[] = array(
								'product_id' => $product_details->id,
								'product_code' => $item_code,
								'product_name' => $product_details->name,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'],
								'product_unit_id' => $item_unit,
								'product_unit_code' => $unit->code,
								'unit_quantity' => ($unit->unit_qty > 1 ? ($cost_item['quantity'] / $unit->unit_qty) : $cost_item['quantity']),
								'expiry' => $item_expiry,
								'serial_no' => $item_serial,
								'product_cost' => $cost_item['cost']
							);
							
							$stockmoves[] = array(
								'transaction' => 'UsingStock',
								'product_id' => $product_details->id,
								'product_code' => $item_code,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'] * (-1),
								'unit_quantity' => $unit->unit_qty,
								'unit_code' => $unit->code,
								'unit_id' => $item_unit,
								'warehouse_id' => $warehouse_id,
								'expiry' => $item_expiry,
								'date' => $date,
								'real_unit_cost' => $cost_item['cost'],
								'serial_no' => $item_serial,
								'reference_no' => $reference_no,
								'user_id' => $this->session->userdata('user_id'),
							);
							
							if($this->Settings->accounting == 1){		
								
								$accTrans[] = array(
									'transaction' => 'UsingStock',
									'transaction_id' => $id,
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $productAcc->stock_acc,
									'amount' => -($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'customer_id' => $customer_id,
									'user_id' => $this->session->userdata('user_id'),
								);
								$accTrans[] = array(
									'transaction' => 'UsingStock',
									'transaction_id' => $id,
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $productAcc->cost_acc,
									'amount' => ($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'customer_id' => $customer_id,
									'user_id' => $this->session->userdata('user_id'),
								);
								
							}
						}
						
					}else{
						
						if($item_serial!=''){
							$serial_detail = $this->products_model->getProductSerial($item_serial,$product_details->id,$warehouse_id);
							if($serial_detail){
								$product_details->cost = $serial_detail->cost;
							}
							
						}
						
						$stockmoves[] = array(
							'transaction' => 'UsingStock',
							'product_id' => $product_details->id,
							'product_code' => $item_code,
							'option_id' => $item_option,
							'quantity' => (-1)*$item_quantity,
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'warehouse_id' => $warehouse_id,
							'expiry' => $item_expiry,
							'date' => $date,
							'real_unit_cost' => $product_details->cost,
							'serial_no' => $item_serial,
							'reference_no' => $reference_no,
							'user_id' => $this->session->userdata('user_id'),
						);
						
						if($this->Settings->accounting == 1){		
							$productAcc = $this->site->getProductAccByProductId($product_details->id);
							$accTrans[] = array(
								'transaction' => 'UsingStock',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->stock_acc,
								'amount' => -($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'customer_id' => $customer_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							$accTrans[] = array(
								'transaction' => 'UsingStock',
								'transaction_id' => $id,
								'transaction_date' => $date,
								'reference' => $reference_no,
								'account' => $productAcc->cost_acc,
								'amount' => ($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'customer_id' => $customer_id,
								'user_id' => $this->session->userdata('user_id'),
							);
							
						}
						
						$products[] = array(
							'product_id' => $product_details->id,
							'product_code' => $item_code,
							'product_name' => $product_details->name,
							'option_id' => $item_option,
							'quantity' => $item_quantity,
							'product_unit_id' => $item_unit,
							'product_unit_code' => $unit->code,
							'unit_quantity' => $item_unit_quantity,
							'expiry' => $item_expiry,
							'serial_no' => $item_serial,
							'product_cost' => $product_details->cost
						);
					}
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            
            $data = array('reference_no' => $reference_no,
                'date' => $date,
                'warehouse_id' => $warehouse_id,
                'warehouse_code' => $warehouse_code,
				'warehouse_name' => $warehouse_name,
				'biller_id' => $biller_id,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'project_id' => $project_id,
				'using_by' => $using_by,
                'note' => $note,
				'return_date' => $return_date,
                'created_by' => $this->session->userdata('user_id')
            );
			
			if($this->config->item("vehicle")){
				$data['vehicle_id'] = $this->input->post("vehicle");
			}

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->products_model->updateUsingStock($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_using', 1);
            $this->session->set_flashdata('message', lang("using_stock_updated")." - ".$data['reference_no']);
            redirect("products/using_stocks");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['using_stock'] = $this->products_model->getUsingStockByID($id);
            $using_stock_items = $this->products_model->getAllUsingStockItems($id);			
            krsort($using_stock_items);
            $c = rand(100000, 9999999);
            foreach ($using_stock_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                } else {
                    unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
				$product_qty = $this->products_model->getProductQuantity($item->product_id,$this->data['using_stock']->warehouse_id);
				
				if($this->Settings->product_expiry == '1'){
					$product_expiries = $this->site->getProductExpiredByProductID($row->id, $this->data['using_stock']->warehouse_id, 'UsingStock' , $id);
				}else{
					$product_expiries = false;
				}
				
				$row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->cus->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->expired = $this->cus->hrsd($item->expiry);
				$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->quantity_balance = $product_qty['quantity'];
                $row->ordered_quantity = $item->quantity;
                $row->quantity = $product_qty['quantity'];
                $row->option = $item->option_id;
				$row->serial = $item->serial_no;
                $options = $this->site->getProductOptions($row->id);
				$product_serials = $this->products_model->getActiveProductSerialID($row->id,$this->data['using_stock']->warehouse_id, $item->serial_no);
				if ($options) {
                    foreach ($options as $option) {
						$ops = $this->site->getWarehouseProductsOptionByID($row->id,$this->data['using_stock']->warehouse_id,$option->id);
						$option->quantity = $ops->quantity;
						if($option->id == $item->option_id){
							$option->quantity += $item->quantity;
						}
                    }
                }
                $row->quantity += $item->quantity;
				
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                                
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'units' => $units, 'options' => $options, 'options' => $options,'product_serials' => $product_serials,'product_expiries' => $product_expiries);
                $c++;
            }

            $this->data['using_stock_items'] = json_encode($pr);
            $this->data['id'] = $id;
			$this->data['vehicles'] = $this->site->getAllVehicles();
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['users'] = $this->site->getAllUsers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/using_stocks'), 'page' => lang('using_stocks')), array('link' => '#', 'page' => lang('edit_using_stock')));
			$meta = array('page_title' => lang('edit_using_stock'), 'bc' => $bc);
            $this->core_page('products/edit_using_stock', $meta, $this->data);
        }
    }
	
	function return_using_stock($using_id)
	{
		$this->cus->checkPermissions('using_stocks-add',true);
		$using_stock = $this->products_model->getUsingStockByID($using_id);
		if($using_stock->status == 'completed'){
			$this->session->set_flashdata('error', lang("using_stock_is_already_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($this->input->post('return_using_stock')) {
			$i = ($this->input->post('product_id')) ? sizeof($this->input->post('product_id')) : 0;
			if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$note = $this->cus->clear_tags($this->input->post('note'));
            $products = false;
			for ($r = 0; $r < $i; $r++) {
				
				$product_id = $this->input->post('product_id')[$r];
				$product_code = $this->input->post('product_code')[$r];
				$product_name = $this->input->post('product_name')[$r];
				$product_expiry = $this->input->post('product_expiry')[$r];
				$product_serial = $this->input->post('product_serial')[$r];
				$product_unit_id = $this->input->post('product_unit_id')[$r];
				$product_unit_code = $this->input->post('product_unit_code')[$r];
				$option_id = $this->input->post('option_id')[$r];
				$product_cost = $this->input->post('product_cost')[$r];
				$return_quantity = $this->input->post('return_quantity')[$r] * (-1);
				$quantity = $return_quantity;
				if($return_quantity < 0){
					
					$unit = $this->site->getProductUnit($product_id,$product_unit_id);
					if($unit->unit_qty > 1){
						$quantity = $quantity * $unit->unit_qty;
					}
					$stockmoves[] = array(
						'transaction' => 'UsingStock',
						'product_id' => $product_id,
						'product_code' => $product_code,
						'option_id' => $option_id,
						'quantity' => (-1)*$quantity,
						'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $product_unit_id,
						'warehouse_id' => $using_stock->warehouse_id,
						'expiry' => $product_expiry,
						'date' => $date,
						'real_unit_cost' => $product_cost,
						'serial_no' => $product_serial,
						'user_id' => $this->session->userdata('user_id'),
					);

					
					if($this->Settings->accounting == 1){		
						$productAcc = $this->site->getProductAccByProductId($product_id);
						$accTrans[] = array(
							'transaction' => 'UsingStock',
							'transaction_date' => $date,
							'account' => $productAcc->stock_acc,
							'amount' => ($product_cost * abs($quantity)),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$product_cost,
							'description' => $note,
							'biller_id' => $using_stock->biller_id,
							'project_id' => $using_stock->project_id,
							'customer_id' => $using_stock->customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						$accTrans[] = array(
							'transaction' => 'UsingStock',
							'transaction_date' => $date,
							'account' => $productAcc->cost_acc,
							'amount' => -($product_cost * abs($quantity)),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$product_cost,
							'description' => $note,
							'biller_id' => $using_stock->biller_id,
							'project_id' => $using_stock->project_id,
							'customer_id' => $using_stock->customer_id,
							'user_id' => $this->session->userdata('user_id'),
						);
						
					}
					
					$products[] = array(
									'product_id' => $product_id,
									'product_code' => $product_code,
									'product_name' => $product_name,
									'option_id' => $option_id,
									'expiry' => $product_expiry,
									'serial_no' => $product_serial,
									'product_unit_id' => $product_unit_id,
									'product_unit_code' => $product_unit_code,
									'product_cost' => $product_cost,
									'quantity' => $quantity,
									'unit_quantity' => $return_quantity,
								);
				}
			}
			
			if($products){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rus',$using_stock->biller_id);
				$data = array(
					'using_id' => $using_id,
					'reference_no' => $reference_no,
					'date' => $date,
					'warehouse_id' => $using_stock->warehouse_id,
					'warehouse_code' => $using_stock->warehouse_code,
					'warehouse_name' => $using_stock->warehouse_name,
					'biller_id' => $using_stock->biller_id,
					'using_by' => $using_stock->using_by,
					'project_id' => $using_stock->project_id,
					'vehicle_id' => $using_stock->vehicle_id,
					'customer_id' => $using_stock->customer_id,
					'customer' => $using_stock->customer,
					'status' => 'return',	
					'note' => $note,
					'created_by' => $this->session->userdata('user_id')
				);
				if ($_FILES['document']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = FALSE;
					$config['encrypt_name'] = TRUE;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload('document')) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}
				if ($this->products_model->addUsingStock($data, $products, $stockmoves, $accTrans)) {
					$this->session->set_flashdata('message', lang("using_stock_returned")." - ".$data['reference_no']);
					redirect("products/using_stocks");
				}
			}else{
				$this->session->set_flashdata('error', lang("product_return_qty_is_required"));
                redirect($_SERVER["HTTP_REFERER"]);
			}
	
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$using_items = $this->products_model->getUsingStockItemWithReturn($using_id);
			$this->data['using_stock'] = $using_stock;
			$this->data['using_items'] = $using_items;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/using_stocks'), 'page' => lang('using_stocks')), array('link' => '#', 'page' => lang('return_using_stock')));
			$meta = array('page_title' => lang('return_using_stock'), 'bc' => $bc);
			$this->core_page('products/return_using_stock', $meta, $this->data);
		}
		
	}
	
	function using_stocks($warehouse_id = NULL, $biller_id = NULL)
    {      
		$this->cus->checkPermissions();
		$this->data['status'] = ($this->input->get("status") ? $this->input->get("status") : false);
		$this->data['billers'] = $this->site->getBillers();
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('using_stocks')));
		$meta = array('page_title' => lang('using_stocks'), 'bc' => $bc);
        $this->core_page('products/using_stocks', $meta, $this->data);
    }
	
	public function using_stock_returns($id = null)
    {
		$this->cus->checkPermissions('using_stocks',null);
		$this->data['using'] = $this->products_model->getUsingStockByID($id);
        $this->data['returns'] = $this->products_model->getUsingStockByUsingID($id);
        $this->load->view($this->theme . 'products/using_stock_returns', $this->data);
    }

	function getUsingStocks($warehouse_id = NULL, $biller_id = null, $status = NULL)
    {
		$this->cus->checkPermissions('using_stocks');
		$current_date = date("Y-m-d");
		$delete_link = "<a href='#' class='po' title='<b>" . lang("delete_using_stock") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_using_stock/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_using_stock') . "</a>";
		$edit_link = anchor('products/edit_using_stock/$1', '<i class="fa fa-edit"></i> ' . lang('edit_using_stock'), 'class="sledit"');
		$return_link = anchor('products/return_using_stock/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_using_stock'));
		$view_return_link = anchor('products/using_stock_returns/$1', '<i class="fa fa-file-text-o"></i>' . lang('view_using_stock_return'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
					. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
					. lang('actions') . ' <span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>' . $view_return_link . '</li>
							<li>' . $return_link . '</li>
							<li>' . $edit_link . '</li>
							<li>' . $delete_link . '</li>
						</ul>
					</div></div>';
		$this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('using_stocks')}.id as id, 
						date, 
						reference_no, 
						warehouses.name as wh_name, 
						CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as staff,
						using_stocks.customer, 
						using_stocks.return_date,
						using_stocks.note,
						IF(".$this->db->dbprefix('using_stocks').".status IN('pending','partial')  AND IFNULL(".$this->db->dbprefix('using_stocks').".return_date,'') != '' AND ".$this->db->dbprefix('using_stocks').".return_date <= '".$current_date."','due',".$this->db->dbprefix('using_stocks').".status) as status,
						using_stocks.attachment")
            ->from('using_stocks')
            ->join('warehouses', 'warehouses.id=using_stocks.warehouse_id', 'left')
			->join('companies', 'companies.id=using_stocks.biller_id', 'left')
            ->join('users', 'users.id=using_stocks.using_by', 'left')
            ->group_by("using_stocks.id");
            if ($warehouse_id && $warehouse_id != '0') {
                $this->datatables->where('using_stocks.warehouse_id', $warehouse_id);
            }		
			if ($biller_id) {
                $this->datatables->where('using_stocks.biller_id', $biller_id);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('using_stocks.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
				$this->datatables->where_in('using_stocks.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('using_stocks.created_by', $this->session->userdata('user_id'));
			}
			if($status){
				$this->datatables->where("(IF(".$this->db->dbprefix('using_stocks').".status IN('pending','partial')  AND IFNULL(".$this->db->dbprefix('using_stocks').".return_date,'') != '' AND ".$this->db->dbprefix('using_stocks').".return_date <= '".$current_date."','due',".$this->db->dbprefix('using_stocks').".status)) = 'due'");
			}
			$this->datatables->where('using_stocks.status !=','return');
			$this->datatables->add_column("Actions", $action, "id");
			echo $this->datatables->generate();
    }
	
	function delete_using_stock($id = NULL)
    {
		$this->cus->checkPermissions('using_stocks-delete',true);
		$row = $this->products_model->getUsingStockByID($id);
        if ($this->products_model->deleteUsingStock($id)) {
			if($this->input->is_ajax_request()) {
				echo lang("using_stock_deleted")." - ". $row->reference_no;
				die();
            }
			if($row->using_id > 0){
				$this->products_model->syncUsingStockStatus($row->using_id);
			}
			$this->session->set_flashdata('message', lang('using_stock_deleted')." - ". $row->reference_no);
        }
        redirect('products/using_stocks');
    }

	public function view_using_stock($id)
    {
		$this->cus->checkPermissions('using_stocks',true);
        $using_stock = $this->products_model->getUsingStockByID($id);
        if (!$id || !$using_stock) {
            $this->session->set_flashdata('error', lang('using_stock_not_found'));
            $this->cus->md();
        }
        $this->data['inv'] = $using_stock;
		$this->data['biller'] = $this->site->getCompanyByID($using_stock->biller_id);
        $this->data['rows'] = $this->products_model->getUsingStockItems($id);
        $this->data['created_by'] = $this->site->getUser($using_stock->created_by);
		$this->data['using_by'] = $this->site->getUser($using_stock->using_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($using_stock->warehouse_id);
		
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Using Stock',$using_stock->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Using Stock',$using_stock->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme.'products/view_using_stock', $this->data);
    }
	function using_stock_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->cus->checkPermissions('using_stocks-delete');
					$using_stock_no = "";
                    foreach ($_POST['val'] as $id) {
						$row = $this->products_model->getUsingStockByID($id);
						$using_stock_no .= $row->reference_no.", ";
                        $this->products_model->deleteUsingStock($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("using_stock_deleted")." - ".$using_stock_no);
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('using_stocks');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('staff'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $using_stock = $this->products_model->getUsingStockByID($id);
                        $staff = $this->site->getUser($using_stock->using_by);
                        $warehouse = $this->site->getWarehouseByID($using_stock->warehouse_id);
						$company = $this->site->getCompanyByID($using_stock->biller_id);
                        $items = $this->products_model->getUsingStockItems($id);  
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name.'('.$this->cus->formatQuantity($item->quantity).')'."\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($using_stock->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $using_stock->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $company->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, ($staff ? $staff->last_name.' ' .$staff->first_name : ''));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $using_stock->customer);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->cus->remove_tag($this->cus->decode_html($using_stock->note)));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'using_stock_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	

	public function add_convert() {
        $this->cus->checkPermissions('converts-add', true);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
		$this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		$this->form_validation->set_rules('bom', lang("bom"), 'required');
		$this->form_validation->set_rules('bom_quantity', lang("quality"), 'required');
        if ($this->form_validation->run() == true){
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('con',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
			$bom_id = $this->input->post('bom');
			$bom_qty = $this->input->post('bom_quantity');
			$bom_finish_qty = $this->products_model->getFinishGoodBomQty($bom_id);
            $note = $this->cus->clear_tags($this->input->post('note'));
			if ($this->Owner || $this->Admin || $this->cus->GP['products-converts-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$raw_materials = false;
			$finished_goods = false;
			$convert_cost = 0;
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r]; 
				$unit_qty = $_POST['unit_qty'][$r]; 
				$unit_id = $_POST['unit_id'][$r]; 
				$type = $_POST['type'][$r];
				$product_details = $this->products_model->getProductByID($product_id);
				$unit = $this->site->getProductUnit($product_id,$unit_id);
				$cost = $_POST['cost'][$r];
				$convert_cost += (($cost * $quantity) / $bom_qty);
				$raw_materials[] = array(
										"product_id" => $product_id,
										"quantity" => $quantity,
										"unit_qty" => $unit_qty,
										"unit_id" => $unit_id,
										"cost" => $cost,
										"type" => $type,
									);
				$stockmoves[] = array(
					'transaction' => 'Convert',
					'product_id' => $product_id,
					'product_code' => $product_details->code,
					'product_type' => $product_details->type,
					'quantity' => $quantity * (-1),
					'unit_quantity' => $unit->unit_qty,
					'unit_code' => $unit->code,
					'unit_id' => $unit_id,
					'warehouse_id' => $warehouse_id,
					'date' => $date,
					'real_unit_cost' => $cost,
					'reference_no' => $reference_no,
					'user_id' => $this->session->userdata('user_id'),
				);
				if($this->Settings->accounting == 1){		
					$productAcc = $this->site->getProductAccByProductId($product_id);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => -($cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->convert_acc,
						'amount' => ($cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
			$finish_cost = $convert_cost / $bom_finish_qty->quantity;
			$f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r]; 
				$unit_qty = $_POST['funit_qty'][$r]; 
				$unit_id = $_POST['funit_id'][$r]; 
				$type = $_POST['ftype'][$r];
				$product_details = $this->products_model->getProductByID($product_id);
				$unit = $this->site->getProductUnit($product_id,$unit_id);
				$finished_goods[] = array(
										"product_id" => $product_id,
										"quantity" => $quantity,
										"unit_qty" => $unit_qty,
										"unit_id" => $unit_id,
										"cost" => $finish_cost,
										"type" => $type,
									);
				$stockmoves[] = array(
					'transaction' => 'Convert',
					'product_id' => $product_id,
					'product_code' => $product_details->code,
					'product_type' => $product_details->type,
					'quantity' => $quantity,
					'unit_quantity' => $unit->unit_qty,
					'unit_code' => $unit->code,
					'unit_id' => $unit_id,
					'warehouse_id' => $warehouse_id,
					'date' => $date,
					'real_unit_cost' => $finish_cost,
					'reference_no' => $reference_no,
					'user_id' => $this->session->userdata('user_id'),
				);
				if($this->Settings->accounting == 1){		
					$productAcc = $this->site->getProductAccByProductId($product_id);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => ($finish_cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->convert_acc,
						'amount' => -($finish_cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
			if (empty($raw_materials) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($raw_materials);
				krsort($finished_goods);
            }
            $data = array(
				'reference_no' => $reference_no,
                'date' => $date,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
				'bom_id' => $bom_id,
				'quantity' => $bom_qty,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
            );
        }
		if ($this->form_validation->run() == true && $this->products_model->addConvert($data, $raw_materials, $finished_goods, $stockmoves, $accTrans)){
			$this->session->set_userdata('remove_cvls', 1);
			$this->session->set_flashdata('message', lang("convert_added")." - ".$data['reference_no']);
			redirect('products/converts');
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));			            
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['boms'] = $this->products_model->getBoms();		
			$this->data['billers'] = $this->site->getBillers();			
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/converts'), 'page' => lang('converts')), array('link' => '#', 'page' => lang('add_convert')));
			$meta = array('page_title' => lang('add_convert'), 'bc' => $bc);
			$this->core_page('products/add_convert', $meta, $this->data);
		}
    }		
	
	public function edit_convert($id) {
        $this->cus->checkPermissions('converts-add', true);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
		$this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		$this->form_validation->set_rules('bom', lang("bom"), 'required');
		$this->form_validation->set_rules('bom_quantity', lang("quality"), 'required');
        if ($this->form_validation->run() == true){
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('con',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
			$bom_id = $this->input->post('bom');
			$bom_qty = $this->input->post('bom_quantity');
			$bom_finish_qty = $this->products_model->getFinishGoodBomQty($bom_id);
            $note = $this->cus->clear_tags($this->input->post('note'));
			if ($this->Owner || $this->Admin || $this->cus->GP['products-converts-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$raw_materials = false;
			$finished_goods = false;
			$convert_cost = 0;
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r]; 
				$unit_qty = $_POST['unit_qty'][$r]; 
				$unit_id = $_POST['unit_id'][$r]; 
				$type = $_POST['type'][$r];
				$product_details = $this->products_model->getProductByID($product_id);
				$unit = $this->site->getProductUnit($product_id,$unit_id);
				$cost = $_POST['cost'][$r];
				$convert_cost += (($cost * $quantity) / $bom_qty);
				$raw_materials[] = array(
										"convert_id" => $id,
										"product_id" => $product_id,
										"quantity" => $quantity,
										"unit_qty" => $unit_qty,
										"unit_id" => $unit_id,
										"cost" => $cost,
										"type" => $type,
									);
				$stockmoves[] = array(
					'transaction' => 'Convert',
					'transaction_id' => $id,
					'product_id' => $product_id,
					'product_code' => $product_details->code,
					'product_type' => $product_details->type,
					'quantity' => $quantity * (-1),
					'unit_quantity' => $unit->unit_qty,
					'unit_code' => $unit->code,
					'unit_id' => $unit_id,
					'warehouse_id' => $warehouse_id,
					'date' => $date,
					'real_unit_cost' => $cost,
					'reference_no' => $reference_no,
					'user_id' => $this->session->userdata('user_id'),
				);
				if($this->Settings->accounting == 1){		
					$productAcc = $this->site->getProductAccByProductId($product_id);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => -($cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->convert_acc,
						'amount' => ($cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
			$finish_cost = $convert_cost / $bom_finish_qty->quantity;
			$f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r]; 
				$unit_qty = $_POST['funit_qty'][$r]; 
				$unit_id = $_POST['funit_id'][$r]; 
				$type = $_POST['ftype'][$r];
				$product_details = $this->products_model->getProductByID($product_id);
				$unit = $this->site->getProductUnit($product_id,$unit_id);
				$finished_goods[] = array(
										"convert_id" => $id,
										"product_id" => $product_id,
										"quantity" => $quantity,
										"unit_qty" => $unit_qty,
										"unit_id" => $unit_id,
										"cost" => $finish_cost,
										"type" => $type,
									);
				$stockmoves[] = array(
					'transaction' => 'Convert',
					'transaction_id' => $id,
					'product_id' => $product_id,
					'product_code' => $product_details->code,
					'product_type' => $product_details->type,
					'quantity' => $quantity,
					'unit_quantity' => $unit->unit_qty,
					'unit_code' => $unit->code,
					'unit_id' => $unit_id,
					'warehouse_id' => $warehouse_id,
					'date' => $date,
					'real_unit_cost' => $finish_cost,
					'reference_no' => $reference_no,
					'user_id' => $this->session->userdata('user_id'),
				);
				if($this->Settings->accounting == 1){		
					$productAcc = $this->site->getProductAccByProductId($product_id);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->stock_acc,
						'amount' => ($finish_cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
					$accTrans[] = array(
						'transaction' => 'Convert',
						'transaction_id' => $id,
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $productAcc->convert_acc,
						'amount' => -($finish_cost * $quantity),
						'narrative' => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
						'description' => $note,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
			}
			if (empty($raw_materials) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($raw_materials);
				krsort($finished_goods);
            }
            $data = array(
				'reference_no' => $reference_no,
                'date' => $date,
				'biller_id' => $biller_id,
				'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
				'bom_id' => $bom_id,
				'quantity' => $bom_qty,
                'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
        }
		if ($this->form_validation->run() == true && $this->products_model->updateConvert($id, $data, $raw_materials, $finished_goods, $stockmoves, $accTrans)){
			$this->session->set_userdata('remove_cvls', 1);
			$this->session->set_flashdata('message', lang("convert_edited")." - ".$data['reference_no']);
			redirect('products/converts');
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));			            
			$this->data['warehouses'] = $this->site->getWarehouses();
			$this->data['boms'] = $this->products_model->getBoms();		
			$this->data['billers'] = $this->site->getBillers();		
			$this->data['convert'] = $this->products_model->getConvertByID($id);
			$this->session->set_userdata('remove_cvls', 1);			
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/converts'), 'page' => lang('converts')), array('link' => '#', 'page' => lang('edit_convert')));
			$meta = array('page_title' => lang('edit_convert'), 'bc' => $bc);
			$this->core_page('products/edit_convert', $meta, $this->data);
		}
    }
	
	function delete_convert($id = NULL)
    {
		$this->cus->checkPermissions('converts-delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->products_model->deleteConvert($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("convert_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('convert_deleted')." - ". $row->reference_no);
        }
		redirect('converts');
    }
	
	public function converts($warehouse_id = NULL, $biller_id = NULL)
	{
		$this->cus->checkPermissions('converts');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('converts')));
		$meta = array('page_title' => lang('converts'), 'bc' => $bc);
        $this->core_page('products/converts', $meta, $this->data);
	}
	
	function getConvertItems($warehouse_id = NULL, $biller_id = NULL)
    {        
		$this->cus->checkPermissions('converts');
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_convert") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' href='" . site_url('products/delete_convert/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fonts fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('converts')}.id as id, 
			date, 
			reference_no, 
			boms.name as bom_name,
           
			warehouses.name as wh_name, 
			CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, 
			note")
            ->from('converts')
            ->join('warehouses', 'warehouses.id=converts.warehouse_id', 'left')
			->join('boms', 'boms.id=converts.bom_id', 'left')
            ->join('users', 'users.id=converts.created_by', 'left')
            ->group_by("converts.id");
			
		if ($warehouse_id) {
			$this->datatables->where('converts.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('converts.biller_id', $biller_id);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('converts.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('converts.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->datatables->where('converts.created_by', $this->session->userdata('user_id'));
		}
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('products/edit_convert/$1') . "' class='tip' title='" . lang("edit_convert") . "'><i class='fonts fa fa-edit'></i></a> " . $delete_link . "</div>", "id");
        echo $this->datatables->generate();
    }
	
	public function view_convert($id)
    {
		$this->cus->checkPermissions('converts');
        $convert = $this->products_model->getConvertByID($id);
        $this->data['convert'] = $convert;
		$this->data['bom'] = $this->products_model->getBomByID($convert->bom_id);;
		$this->data['biller'] = $this->site->getCompanyByID($convert->biller_id);
        $this->data['convert_items'] = $this->products_model->getConvertItems($id);
        $this->data['created_by'] = $this->site->getUser($convert->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($convert->warehouse_id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Convert',$convert->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Convert',$convert->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme.'products/view_convert', $this->data);
    }
	
	function convert_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
					$this->cus->checkPermissions('converts-delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteConvert($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("convert_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
					
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('converts');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('bom'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $convert = $this->products_model->getConvertByID($id);
						$bom = $this->products_model->getBomByID($convert->bom_id);
                        $created_by = $this->site->getUser($convert->created_by);
                        $warehouse = $this->site->getWarehouseByID($convert->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($convert->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $convert->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $bom->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->cus->decode_html($convert->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'convert_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

	public function delete_variant()
	{
		$id = $this->input->get("id");		
		if($id){			
			$row = $this->products_model->getVariantByID($id);			
			if($row->quantity <= 0 || $row->quantity == NULL){
				$this->products_model->deleteVariant($id);
				echo lang("product_variant_deleted");				
			}
			else {
				header('HTTP/1.0 400 Bad error');					
				echo lang("qty_variant_cannot_delete");
				die();
			}								
		}		
	}
	
	/*************CHECK STOCK**************/
	
	function scan_stocks($warehouse_id = NULL)
    {      
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('scan_stocks')));
        $meta = array('page_title' => lang('scan_stocks'), 'bc' => $bc);
        $this->core_page('products/scan_stocks', $meta, $this->data);
    }
	
	function getScanStocks($warehouse_id = NULL)
    {        
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_scan_stock") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_scan_stock/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('scan_stocks')}.id as id, date, reference_no, companies.company, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, note, attachment")
            ->from('scan_stocks')
            ->join('warehouses', 'warehouses.id=scan_stocks.warehouse_id', 'left')
			->join('companies', 'companies.id=scan_stocks.biller_id', 'left')
            ->join('users', 'users.id=scan_stocks.created_by', 'left')
            ->group_by("scan_stocks.id");
            if ($warehouse_id) {
                $this->datatables->where('scan_stocks.warehouse_id', $warehouse_id);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('scan_stocks.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
				$this->db->where_in('scan_stocks.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
			}
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('products/edit_scan_stock/$1') . "' class='tip' title='" . lang("edit_scan_stock") . "'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "id");

        echo $this->datatables->generate();

    }
	
	function add_scan_stock($sale_order_id = false)
    {        
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));        
        $this->form_validation->set_rules('warehouse_id', lang("warehouse_id"), 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
			
			$biller_id = $this->input->post('biller');			
			$sale_order = $this->products_model->getSaleOrderByID($sale_order_id);
            $reference_no = $sale_order->reference_no;//$this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('us',$biller_id);
			if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            
            $warehouse_id = $this->input->post('warehouse_id');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $shipping = 0;
            $status = 'completed';
            $warehouse_details = $this->site->getWarehouseByID($warehouse_id);
            $warehouse_code = $warehouse_details->code;
            $warehouse_name = $warehouse_details->name;
           
            $total = 0;
            $product_tax = 0;

            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_net_cost = $this->cus->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->cus->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->cus->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_expiry = isset($_POST['expiry'][$r]) ? $this->cus->fsd($_POST['expiry'][$r]) : NULL;
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->products_model->getProductByCode($item_code);
                  
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";

                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->cus->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->cus->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = ($product_details && $product_details->tax_method == 1) ? $this->cus->formatDecimal($unit_cost) : $this->cus->formatDecimal($unit_cost-$item_tax, 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->cus->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->cus->formatDecimal($item_net_cost + $item_tax, 4),
                        'quantity' => (-1)*$item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => (-1)*$item_quantity,
                        'warehouse_id' => $to_warehouse,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'subtotal' => $this->cus->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date))
                    );
										
                    $total += $this->cus->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $grand_total = $this->cus->formatDecimal(($total + $shipping + $product_tax), 4);
            $data = array(
				'reference_no' => $reference_no,
				'sale_order_id' => $sale_order_id,
                'date' => $date,
                'warehouse_id' => $warehouse_id,
                'warehouse_code' => $warehouse_code,
				'warehouse_name' => $warehouse_name,
				'biller_id' => $biller_id,                
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        }

        if ($this->form_validation->run() == true && $this->products_model->addScanStock($data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("using_stock_added"));
            redirect("products/scan_stocks/");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['quantity'] = array('name' => 'quantity',
                'id' => 'quantity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            );
			
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['rnumber'] = ''; 
			$this->data['id'] = $sale_order_id;
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_scan_stock')));
            $meta = array('page_title' => lang('add_scan_stock'), 'bc' => $bc);
            $this->core_page('products/add_scan_stock', $meta, $this->data);
        }
    }
	
	function edit_scan_stock($id = NULL)
    {        
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $using_stock = $this->products_model->getUsingStockByID($id);        
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));        
        $this->form_validation->set_rules('warehouse_id', lang("warehouse_id"), 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
			
			$biller_id = $this->input->post('biller');
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('us',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['products-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $warehouse_id = $this->input->post('warehouse_id');
            $note = $this->cus->clear_tags($this->input->post('note'));
            $shipping = 0;
            $status = 'completed';
            $warehouse_details = $this->site->getWarehouseByID($warehouse_id);
            $warehouse_code = $warehouse_details->code;
            $warehouse_name = $warehouse_details->name;

            $total = 0;
            $product_tax = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];
                $item_net_cost = $this->cus->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->cus->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->cus->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = $_POST['ordered_quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_expiry = isset($_POST['expiry'][$r]) ? $this->cus->fsd($_POST['expiry'][$r]) : NULL;
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->products_model->getProductByCode($item_code);

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->cus->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->cus->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {
                            $item_tax = $this->cus->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->cus->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = ($product_details && $product_details->tax_method == 1) ? $this->cus->formatDecimal($unit_cost) : $this->cus->formatDecimal($unit_cost-$item_tax, 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->cus->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);
                    //$balance_qty =  ($status != 'completed') ? $item_quantity : ($item_quantity - ($ordered_quantity - $quantity_balance));
                    
					$item_quantity = ($item_quantity > 0 ? (-1)*$item_quantity  : $item_quantity);
					
					$products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->cus->formatDecimal(($item_net_cost + $item_tax), 4),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $to_warehouse,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'subtotal' => $this->cus->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                    );
					
                    $total += $this->cus->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $grand_total = $this->cus->formatDecimal(($total + $shipping + $product_tax), 4);
            $data = array('reference_no' => $reference_no,
				'date' => $date,
                'warehouse_id' => $warehouse_id,
                'warehouse_code' => $warehouse_code,
				'warehouse_name' => $warehouse_name,
				'biller_id' => $biller_id,                
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->products_model->updateScanStock($id, $data, $products)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang("scan_stock_updated"));
            redirect("products/scan_stocks");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['scan_stock'] = $this->products_model->getScanStockByID($id);
            $scan_stock_items = $this->products_model->getAllScanStockItems($id, $this->data['scan_stock']->status);			
            krsort($scan_stock_items);
            $c = rand(100000, 9999999);
            foreach ($scan_stock_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                } else {
                    unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $row->quantity = 0;
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->cus->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->quantity_balance = $item->quantity_balance;
                $row->ordered_quantity = $item->quantity;
                $row->quantity += $item->quantity_balance;
                $row->cost = $item->net_unit_cost;
                $row->unit_cost = $item->net_unit_cost+($item->item_tax/$item->quantity);
                $row->real_unit_cost = $item->real_unit_cost;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
                $options = $this->products_model->getProductOptions($row->id, $this->data['scan_stock']->warehouse_id, FALSE);
                $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id);
                if($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->quantity += $item->quantity;
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
                        if($pis){
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        $option_quantity += $item->quantity;
                        if($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
              
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                                
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }

            $this->data['scan_stock_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_scan_stock')));
            $meta = array('page_title' => lang('edit_scan_stock'), 'bc' => $bc);
            $this->core_page('products/edit_scan_stock', $meta, $this->data);
        }
    }
	
	function delete_scan_stock($id = NULL)
    {
        if ($this->products_model->deleteScanStock($id)) {
            echo lang("using_stock_deleted");
        }

    }
	
	function getCategoryAccounts($category_id = NULL)
    {
        if ($rows = $this->products_model->getCategoryByID($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }
	
	public function get_project()
	{
		$id = $this->input->get("biller");
		$project_id = $this->input->get("project");
		$rows = $this->site->getAllProjectByBillerID($id);
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$project = json_decode($user->project_ids);
			
		$pl = array(lang('select')." ".lang('project'));
		if ($this->Owner || $this->Admin || $project[0] === 'all') {
			foreach($rows as $row){
				$pl[$row->id] = $row->name;
			}
		}else{
			if($project){
				foreach($rows as $row){
					if(in_array($row->id, $project)){
						$pl[$row->id] = $row->name;
					}
				}
			}
			
		}
		$opt = form_dropdown('project', $pl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="project" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function rate($id = false)
	{
		if($this->input->get("id")){
			$id = $this->input->get("id");
		}
		if($this->products_model->rateProductItem($id)){
			$product = $this->products_model->getProductByID($id);
			$this->cus->send_json(array("rate"=>$product->rate));
		}
		return false;
	}
	
	function add_barcode_style()
    {

        $this->form_validation->set_rules('code', lang("barcode_code"), 'trim|is_unique[barcode_settings.barcode_code]|required');
        $this->form_validation->set_rules('name', lang("barcode_name"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'barcode_name' => $this->input->post('name'),
                'barcode_code' => $this->input->post('code')
            );

        } elseif ($this->input->post('add_barcode_style')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->products_model->addBarcodeStyle($data)) {
            $this->session->set_flashdata('message', lang("barcode_style_added")." ".$data['barcode_code']." - ".$data['barcode_name']);
		   redirect("products/print_barcodes");	
		   
        } else {

            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/add_barcode_style', $this->data);

        }
    }
	

	public function consignments($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['warehouses'] = $this->site->getWarehouses();
		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('consignments')));
		$meta = array('page_title' => lang('consignments'), 'bc' => $bc);
        $this->core_page('products/consignments', $meta, $this->data);

    }
	
	
	public function getConsignments($warehouse_id = null, $biller_id = NULL)
    {
        $this->cus->checkPermissions('consignments');
        $create_sale = '';
		if(($this->Admin || $this->Owner) || $this->GP['sales-add']){
			$create_sale = anchor('sales/add/?consignment_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_sale'), ' class="consignment-create_sale" ​');
		}
		$edit_link = anchor('products/edit_consignment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_consignment'), ' class="edit_consignment" ');
		$view_return_link = anchor('products/view_consignment_return/$1', '<i class="fa fa-file-text-o"></i>' . lang('view_consignment_return'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$return_link = anchor('products/return_consignment/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_consignment'));
		$delete_link = "<a href='#' class='po delete_consignment' title='<b>" . $this->lang->line("delete_consignment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('products/delete_consignment/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_consignment') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $view_return_link . '</li>
						<li>' . $return_link . '</li>
						<li>' . $create_sale . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
       
        $this->load->library('datatables');

		$this->datatables
			->select("id, date, reference_no, customer, grand_total, status, attachment")
			->where("consignments.status !=",'returned')
			->from("consignments");
	    if ($warehouse_id) {
			$this->datatables->where('consignments.warehouse_id', $warehouse_id);
		}
		if ($biller_id) {
			$this->datatables->where('consignments.biller_id', $biller_id);
		}	
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('consignments.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
			$this->datatables->where_in('consignments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	
	
	
	public function add_consignment()
    {
        $this->cus->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csm',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['consignments-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$valid_day = $this->input->post('valid_day');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $status = 'pending';
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));
            $total = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			$consignmentAcc = $this->site->getAccountSettingByBiller($biller_id);
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$item_expiry = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$item_expiry = null;
				}

                if (isset($item_code) && isset($item_quantity)) {
					$product_details = $item_type != 'manual' ? $this->products_model->getProductByCode($item_code) : null;
                    $unit_price = $this->cus->formatDecimalRaw($unit_price);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($item_unit_quantity);
                    $subtotal = ($item_net_price * $item_unit_quantity);
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
					
					if($this->Settings->accounting_method == '0'){
						$costs = $this->site->getFifoCost($product_details->id,$item_quantity,$stockmoves);
					}else if($this->Settings->accounting_method == '1'){
						$costs = $this->site->getLifoCost($product_details->id,$item_quantity,$stockmoves);
					}else if($this->Settings->accounting_method == '3'){
						$costs = $this->site->getProductMethod($product_details->id,$item_quantity,$stockmoves);
					}
					
					if($costs && $item_serial==''){
						$productAcc = $this->site->getProductAccByProductId($item_id);
						foreach($costs as $cost_item){
							$stockmoves[] = array(
								'transaction' => 'Consignment',
								'product_id' => $product_details->id,
								'product_code' => $item_code,
								'product_type' => $item_type,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'] * (-1),
								'unit_quantity' => $unit->unit_qty,
								'unit_code' => $unit->code,
								'unit_id' => $item_unit,
								'warehouse_id' => $warehouse_id,
								'date' => $date,
								'expiry' => $item_expiry,
								'real_unit_cost' => $cost_item['cost'],
								'reference_no' => $reference,
								'user_id' => $this->session->userdata('user_id'),
							);
							if($this->Settings->accounting == 1){		
								$accTrans[] = array(
									'transaction' => 'Consignment',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $productAcc->stock_acc,
									'amount' => -($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
								$accTrans[] = array(
									'transaction' => 'Consignment',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $consignmentAcc->consignment_acc,
									'amount' => ($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
								
							}
						}
					}else{
						if($item_serial!=''){
							$serial_detail = $this->products_model->getProductSerial($item_serial,$product_details->id,$warehouse_id);
							if($serial_detail){
								$product_details->cost = $serial_detail->cost;
							}
							
						}
						$stockmoves[] = array(
							'transaction' => 'Consignment',
							'product_id' => $product_details->id,
							'product_code' => $item_code,
							'product_type' => $item_type,
							'option_id' => $item_option,
							'quantity' => (-1)*$item_quantity,
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'warehouse_id' => $warehouse_id,
							'date' => $date,
							'expiry' => $item_expiry,
							'serial_no' => $item_serial,
							'real_unit_cost' => $product_details->cost,
							'reference_no' => $reference,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($this->Settings->accounting == 1){		
							$productAcc = $this->site->getProductAccByProductId($product_details->id);
							$accTrans[] = array(
								'transaction' => 'Consignment',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->stock_acc,
								'amount' => -($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							$accTrans[] = array(
								'transaction' => 'Consignment',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $consignmentAcc->consignment_acc,
								'amount' => ($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							
						}
					}
                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->cus->formatDecimalRaw($item_net_price),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
						'expiry' => $item_expiry,
						'serial_no' => $item_serial,
						'comment' => $item_comment
                    );
                    $total += $this->cus->formatDecimalRaw($item_net_price * $item_unit_quantity);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $this->cus->formatDecimalRaw($total);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'grand_total' => $grand_total,
				'valid_day' => $valid_day,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if($this->Settings->product_expiry == '1' && $stockmoves && $products){
				$checkExpiry = $this->site->checkExpiry($stockmoves, $products,'Consignment');
				$stockmoves = $checkExpiry['expiry_stockmoves'];
				$products = $checkExpiry['expiry_items'];
			}
        }
        if ($this->form_validation->run() == true && $this->products_model->addConsignment($data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_csmls', 1);
            $this->session->set_flashdata('message', $this->lang->line("consignment_added") ." ". $reference);
            if($this->input->post('add_consignment_next')){
				redirect('products/add_consignment');
			}else{
				redirect('products/consignments');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/consignments'), 'page' => lang('consignments')), array('link' => '#', 'page' => lang('add_consignment')));
			$meta = array('page_title' => lang('add_consignment'), 'bc' => $bc);
            $this->core_page('products/add_consignment', $meta, $this->data);
        }
    }
	
	
	
	public function edit_consignment($id = false)
    {
		$this->cus->checkPermissions();
		$consignment = $this->products_model->getConsignmentByID($id);
        if($consignment->status == 'partial'){
			$this->session->set_flashdata('error', lang("consignment_is_in_process"));
            redirect($_SERVER["HTTP_REFERER"]);
		}else if($consignment->status == 'completed'){
			$this->session->set_flashdata('error', lang("consignment_is_already_completed"));
            redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csm',$biller_id);
            if ($this->Owner || $this->Admin || $this->cus->GP['consignments-date'] ) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$valid_day = $this->input->post('valid_day');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->cus->clear_tags($this->input->post('note'));
            $total = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			$consignmentAcc = $this->site->getAccountSettingByBiller($biller_id);
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
				$item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->cus->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price = $this->cus->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
				if($_POST['expired_data'][$r] && $_POST['expired_data'][$r] != '00/00/0000'){
					$item_expiry = $this->cus->fsd($_POST['expired_data'][$r]);
				}else{
					$item_expiry = null;
				}
                if (isset($item_code) && isset($item_quantity)) {
					$product_details = $item_type != 'manual' ? $this->products_model->getProductByCode($item_code) : null;
                    $unit_price = $this->cus->formatDecimalRaw($unit_price);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->cus->formatDecimalRaw($item_unit_quantity);
                    $subtotal = ($item_net_price * $item_unit_quantity);
					$unit = $this->site->getProductUnit($product_details->id,$item_unit);
					
					if($this->Settings->accounting_method == '0'){
						$costs = $this->site->getFifoCost($product_details->id,$item_quantity,$stockmoves,'Consignment',$id);
					}else if($this->Settings->accounting_method == '1'){
						$costs = $this->site->getLifoCost($product_details->id,$item_quantity,$stockmoves,'Consignment',$id);
					}else if($this->Settings->accounting_method == '3'){
						$costs = $this->site->getProductMethod($product_details->id,$item_quantity,$stockmoves,'Consignment',$id);
					}
					
					if($costs && $item_serial == ""){
						$productAcc = $this->site->getProductAccByProductId($item_id);
						foreach($costs as $cost_item){
							$stockmoves[] = array(
								'transaction_id' => $id,
								'transaction' => 'Consignment',
								'product_id' => $product_details->id,
								'product_code' => $item_code,
								'product_type' => $item_type,
								'option_id' => $item_option,
								'quantity' => $cost_item['quantity'] * (-1),
								'unit_quantity' => $unit->unit_qty,
								'unit_code' => $unit->code,
								'unit_id' => $item_unit,
								'warehouse_id' => $warehouse_id,
								'date' => $date,
								'expiry' => $item_expiry,
								'real_unit_cost' => $cost_item['cost'],
								'reference_no' => $reference,
								'user_id' => $this->session->userdata('user_id'),
							);
							if($this->Settings->accounting == 1){		
								$accTrans[] = array(
									'transaction_id' => $id,
									'transaction' => 'Consignment',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $productAcc->stock_acc,
									'amount' => -($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
								$accTrans[] = array(
									'transaction' => 'Consignment',
									'transaction_date' => $date,
									'reference' => $reference,
									'account' => $consignmentAcc->consignment_acc,
									'amount' => ($cost_item['cost'] * abs($cost_item['quantity'])),
									'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
									'description' => $note,
									'biller_id' => $biller_id,
									'project_id' => $project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' => $customer_id,
								);
								
							}
						}
					}else{
						$stockmoves[] = array(
							'transaction_id' => $id,
							'transaction' => 'Consignment',
							'product_id' => $product_details->id,
							'product_code' => $item_code,
							'product_type' => $item_type,
							'option_id' => $item_option,
							'quantity' => (-1)*$item_quantity,
							'unit_quantity' => $unit->unit_qty,
							'unit_code' => $unit->code,
							'unit_id' => $item_unit,
							'warehouse_id' => $warehouse_id,
							'date' => $date,
							'expiry' => $item_expiry,
							'serial_no' => $item_serial,
							'real_unit_cost' => $product_details->cost,
							'reference_no' => $reference,
							'user_id' => $this->session->userdata('user_id'),
						);
						if($this->Settings->accounting == 1){		
							$productAcc = $this->site->getProductAccByProductId($product_details->id);
							$accTrans[] = array(
								'transaction_id' => $id,
								'transaction' => 'Consignment',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $productAcc->stock_acc,
								'amount' => -($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							$accTrans[] = array(
								'transaction_id' => $id,
								'transaction' => 'Consignment',
								'transaction_date' => $date,
								'reference' => $reference,
								'account' => $consignmentAcc->consignment_acc,
								'amount' => ($product_details->cost * abs($item_quantity)),
								'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
								'description' => $note,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'user_id' => $this->session->userdata('user_id'),
								'customer_id' => $customer_id,
							);
							
						}
					}

                    $products[] = array(
						'consignment_id' => $id,
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->cus->formatDecimalRaw($item_net_price),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'subtotal' => $this->cus->formatDecimalRaw($subtotal),
                        'real_unit_price' => $real_unit_price,
						'comment' => $item_comment,
						'expiry' => $item_expiry,
						'serial_no' => $item_serial,
                    );
                    $total += $this->cus->formatDecimalRaw($item_net_price * $item_unit_quantity);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $this->cus->formatDecimalRaw($total);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
				'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'grand_total' => $grand_total,
				'valid_day' => $valid_day,
				'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->products_model->updateConsignment($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_csmls', 1);
            $this->session->set_flashdata('message', $this->lang->line("consignment_updated") ." ". $reference);
			redirect('products/consignments');
        } else {
			
			$consingment_items = $this->products_model->getConsigmentItems($id);
			krsort($consingment_items);
			$c = rand(100000, 9999999);
			foreach ($consingment_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                }
                $row->quantity = 0;
                $pis = $this->site->getStockmoves($item->product_id, $item->warehouse_id, $item->option_id, 'Consignment' , $id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
				if($this->Settings->product_expiry == '1'){
					$product_expiries = $this->site->getProductExpiredByProductID($item->product_id, $consignment->warehouse_id, 'Consignment' , $id);
				}else{
					$product_expiries = false;
				}
				$product_serials = $this->products_model->getActiveProductSerialID($item->product_id,$consignment->warehouse_id, $item->serial_no);
				$row->fup = 1;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->price = $this->cus->formatDecimalRaw($item->net_unit_price);
                $row->unit_price = $item->unit_price ;
                $row->real_unit_price = $item->real_unit_price;
                $row->option = $item->option_id;
				$row->comment = $item->comment;
				$row->serial = $item->serial_no;
				$row->expired = $this->cus->hrsd($item->expiry);
				$options = $this->products_model->getProductOptions($row->id, $item->warehouse_id);
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockmoves($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
						if($option->id == $item->option_id){
							$option->quantity += $item->quantity;
						}
                    }
                }

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->products_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'units' => $units, 'options' => $options,'product_expiries' => $product_expiries, 'product_serials'=> $product_serials);
                $c++;
            }
            
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['consignment'] = $consignment;
			$this->data['consingment_items'] = json_encode($pr);
			$this->data['billers'] =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
			$this->session->set_userdata('remove_csmls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/consignments'), 'page' => lang('consignments')), array('link' => '#', 'page' => lang('edit_consignment')));
			$meta = array('page_title' => lang('edit_consignment'), 'bc' => $bc);
            $this->core_page('products/edit_consignment', $meta, $this->data);
        }
    }
	
	
	
	public function delete_consignment($id = null)
    {
        $this->cus->checkPermissions(NULL, true);
		$consignment = $this->products_model->getConsignmentByID($id);
		if($consignment->status == 'partial'){
			$this->session->set_flashdata('error', lang("consignment_is_in_process"));
            $this->cus->md();
		}else if($consignment->status == 'completed'){
			$this->session->set_flashdata('error', lang("consignment_is_already_completed"));
            $this->cus->md();
		}else{
			if ($this->input->get('id')) {
				$id = $this->input->get('id');
			}
			if ($this->products_model->deleteConsignment($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("consignment_deleted");
					die();
				}
				$this->session->set_flashdata('message', lang('consignment_deleted'));
				redirect('products/consignments');
			}
		}   
    }
	
	
	public function modal_view_consignment($id = null)
    {
        $this->cus->checkPermissions('consignments', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $consignment = $this->products_model->getConsignmentByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->cus->view_rights($consignment->created_by, true);
        }
        $this->data['rows'] = $this->products_model->getConsigmentItems($id);
        $this->data['customer'] = $this->site->getCompanyByID($consignment->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($consignment->biller_id);
        $this->data['created_by'] = $this->site->getUser($consignment->created_by);
        $this->data['updated_by'] = $consignment->updated_by ? $this->site->getUser($consignment->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($consignment->warehouse_id);
        $this->data['consignment'] = $consignment;
		$this->data['project'] = $this->site->getProjectByID($consignment->project_id);
		if($this->Owner || $this->Admin || $this->cus->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Consignment',$consignment->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Consignment',$consignment->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme . 'products/modal_view_consignment', $this->data);

    }
	
	
	public function consignment_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->cus->checkPermissions('delete_consignment', true);
                    foreach ($_POST['val'] as $id) {
						$consignment = $this->products_model->getConsignmentByID($id);
						if($consignment->status == 'partial'){
							$this->session->set_flashdata('error', lang("consignment_is_in_process").' '.$consignment->reference_no);
							redirect($_SERVER["HTTP_REFERER"]);
						}else if($consignment->status == 'completed'){
							$this->session->set_flashdata('error', lang("consignment_is_already_completed").' '.$consignment->reference_no);
							redirect($_SERVER["HTTP_REFERER"]);
						}
                        $this->products_model->deleteConsignment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("consignment_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('consignment'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $consignment = $this->products_model->getConsignmentByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->cus->hrld($consignment->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $consignment->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $consignment->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $consignment->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $consignment->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $consignment->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'consignments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	
	function return_consignment($consignment_id = false)
	{
		$this->cus->checkPermissions('add_consignment');
		$consignment = $this->products_model->getConsignmentByID($consignment_id);
		if($consignment->status == 'completed'){
			$this->session->set_flashdata('error', lang("consignment_is_already_completed"));
            redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->form_validation->set_rules('consignment', $this->lang->line("consignment"), 'required');
		if ($this->form_validation->run() == true) {
			$i = ($this->input->post('product_id')) ? sizeof($this->input->post('product_id')) : 0;
			if ($this->Owner || $this->Admin || $this->cus->GP['consignments-date']) {
                $date = $this->cus->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rcsm',$consignment->biller_id);
			$note = $this->cus->clear_tags($this->input->post('note'));
			$consignmentAcc = $this->site->getAccountSettingByBiller($consignment->biller_id);
            $products = false;
			$grand_total = 0;
			for ($r = 0; $r < $i; $r++) {
				$consignment_item_id = $this->input->post('consignment_item_id')[$r];
				$product_id = $this->input->post('product_id')[$r];
				$product_code = $this->input->post('product_code')[$r];
				$product_name = $this->input->post('product_name')[$r];
				$product_expiry = $this->input->post('product_expiry')[$r];
				$product_serial = $this->input->post('product_serial')[$r];
				$product_cost = $this->input->post('product_cost')[$r];
				$product_type = $this->input->post('product_type')[$r];
				$option_id = $this->input->post('option_id')[$r];
				$product_unit_id = $_POST['unit'][$r];
				$return_quantity = $this->input->post('return_quantity')[$r] * (-1);
				$quantity = $return_quantity;
				$real_unit_price = $this->input->post('real_unit_price')[$r];
				if($return_quantity < 0){
					$unit = $this->site->getProductUnit($product_id,$product_unit_id);
					if($unit->unit_qty > 1){
						$quantity = $quantity * $unit->unit_qty;
					}
					$unit_price = $real_unit_price *  $unit->unit_qty;
					$stockmoves[] = array(
						'transaction' => 'Consignment',
						'product_id' => $product_id,
						'product_code' => $product_code,
						'product_type' => $product_type,
						'option_id' => $option_id,
						'quantity' => (-1)*$quantity,
						'unit_quantity' => $unit->unit_qty,
						'unit_code' => $unit->code,
						'unit_id' => $product_unit_id,
						'warehouse_id' => $consignment->warehouse_id,
						'expiry' => $product_expiry,
						'date' => $date,
						'real_unit_cost' => $product_cost,
						'serial_no' => $product_serial,
						'reference_no' => $reference_no,
						'user_id' => $this->session->userdata('user_id'),
					);

					
					if($this->Settings->accounting == 1){		
						$productAcc = $this->site->getProductAccByProductId($product_id);
						$accTrans[] = array(
							'transaction' => 'Consignment',
							'transaction_date' => $date,
							'account' => $productAcc->stock_acc,
							'amount' => ($product_cost * abs($quantity)),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.abs($quantity).'#'.'Cost: '.$product_cost,
							'description' => $note,
							'biller_id' => $consignment->biller_id,
							'project_id' => $consignment->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $consignment->customer_id,
							'reference' => $reference_no,
						);
						$accTrans[] = array(
							'transaction' => 'Consignment',
							'transaction_date' => $date,
							'account' => $consignmentAcc->consignment_acc,
							'amount' => -($product_cost * abs($quantity)),
							'narrative' => 'Product Code: '.$product_code.'#'.'Qty: '.abs($quantity).'#'.'Cost: '.$product_cost,
							'description' => $note,
							'biller_id' => $consignment->biller_id,
							'project_id' => $consignment->project_id,
							'user_id' => $this->session->userdata('user_id'),
							'customer_id' => $consignment->customer_id,
							'reference' => $reference_no,
						);
						
					}
					
					$products[] = array(
									'consignment_item_id' => $consignment_item_id,
									'product_id' => $product_id,
									'product_code' => $product_code,
									'product_name' => $product_name,
									'product_type' => $product_type,
									'option_id' => $option_id,
									'expiry' => $product_expiry,
									'serial_no' => $product_serial,
									'product_unit_id' => $product_unit_id,
									'product_unit_code' => $unit->code,
									'warehouse_id' => $consignment->warehouse_id,
									'quantity' => $quantity,
									'unit_quantity' => $return_quantity,
									'real_unit_price' => $real_unit_price,
									'unit_price' => $unit_price,
									'net_unit_price' => $unit_price,
									'subtotal' => $real_unit_price * $quantity,
								);
					$grand_total += $real_unit_price * $quantity;		
				}
			}
			
			if($products){
				$data = array(
					'consignment_id' => $consignment_id,
					'reference_no' => $reference_no,
					'date' => $date,
					'warehouse_id' => $consignment->warehouse_id,
					'biller_id' => $consignment->biller_id,
					'biller' => $consignment->biller,
					'customer' => $consignment->customer,
					'customer_id' => $consignment->customer_id,
					'project_id' => $consignment->project_id,
					'grand_total' => $grand_total,	
					'total' => $grand_total,	
					'status' => 'returned',	
					'note' => $note,
					'created_by' => $this->session->userdata('user_id')
				);
				if ($_FILES['document']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = FALSE;
					$config['encrypt_name'] = TRUE;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload('document')) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}
				if ($this->products_model->addConsignment($data, $products, $stockmoves, $accTrans)) {
					$this->session->set_flashdata('message', lang("consignment_returned")." - ".$data['reference_no']);
					redirect("products/consignments");
				}
			}else{
				$this->session->set_flashdata('error', lang("product_return_qty_is_required"));
                redirect($_SERVER["HTTP_REFERER"]);
			}
	
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$consignment_items = $this->products_model->getConsigmentItems($consignment_id);
			$this->data['consignment'] = $consignment;
			$this->data['consignment_items'] = $consignment_items;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/consignments'), 'page' => lang('consignments')), array('link' => '#', 'page' => lang('return_consignment')));
			$meta = array('page_title' => lang('return_consignment'), 'bc' => $bc);
			$this->core_page('products/return_consignment', $meta, $this->data);
		}
		
	}
	
	public function view_consignment_return($id = null)
    {
		$this->data['consignment'] = $this->products_model->getConsignmentByID($id);
        $this->data['returns'] = $this->products_model->getConsignmentByConsignID($id);
        $this->load->view($this->theme . 'products/view_consignment_return', $this->data);
    }
	
	public function getDocuments($product_id = false)
	{
		$this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_document") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_document/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_document') . "</a>";
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.site_url('products/edit_document/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_document').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					product_documents.id as id,
					product_documents.created_date,
					product_documents.name,
					product_documents.description,
					CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
					product_documents.attachment")
            ->from("product_documents")
			->join("users","users.id = product_documents.created_by","left")
			->where("product_id",$product_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_document($product_id = false){	
		$this->cus->checkPermissions('add',true);
		$this->form_validation->set_rules('d_name', lang("name"), 'required');
		if ($this->form_validation->run() == true) {
			$d_name			= $this->input->post('d_name');
			$d_description	= $this->input->post('d_description');
			$data = array(
					'name'			=> $d_name,
					'description'	=> $d_description,
					'created_by'	=> $this->session->userdata("user_id"),
					'created_date'	=> date("Y-m-d H:i"),
					'product_id'	=> $product_id,
				);
			if ($_FILES['d_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('d_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		if ($this->form_validation->run() == true && $this->products_model->addDocument($data)) {
			$this->session->set_flashdata('message', $this->lang->line("document_added"));
            redirect("products/view/".$product_id."/#document");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['product_id'] = $product_id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'products/add_document', $this->data);	
		}	
	}
	
	public function edit_document($id = false)
	{
		$this->cus->checkPermissions('edit',true);
		$this->form_validation->set_rules('d_name', lang("name"), 'required');
		$document = $this->products_model->getDocumentByID($id);
		if ($this->form_validation->run() == true) {	
			$d_name			= $this->input->post('d_name');
			$d_description	= $this->input->post('d_description');
			$data = array(
					'name'			=> $d_name,
					'description'	=> $d_description,
					'created_by'	=> $this->session->userdata("user_id"),
				);
			if ($_FILES['d_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('d_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		if ($this->form_validation->run() == true && $this->products_model->updateDocument($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("document_added"));
            redirect("products/view/".$document->product_id."/#document");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $document;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'products/edit_document', $this->data);	
		}	
	}
	
	public function delete_document($id = null)
    {		
		$this->cus->checkPermissions('delete',true);
        if (isset($id) || $id != null){
        	 if ($this->products_model->deleteDocument($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("document_deleted");
					die();
				}
				$this->session->set_flashdata('message', lang('document_deleted'));
				redirect('welcome');
			}
        }
    }
	
	
	public function getLicense($product_id = false)
	{
		$this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_license") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_license/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_license') . "</a>";
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.site_url('products/edit_license/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_license').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					product_licenses.id as id,
					product_licenses.issued_date,
					product_licenses.valid_date,
					product_licenses.description,
					CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
					product_licenses.attachment")
            ->from("product_licenses")
			->join("users","users.id = product_licenses.created_by","left")
			->where("product_id",$product_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_license($product_id = false){	
		$this->cus->checkPermissions('add',true);
		$this->form_validation->set_rules('l_issued_date', lang("issued_date"), 'required');
		$this->form_validation->set_rules('l_valid_date', lang("valid_date"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
					'issued_date'	=> $this->cus->fsd($this->input->post('l_issued_date')),
					'valid_date'	=> $this->cus->fsd($this->input->post('l_valid_date')),
					'description'	=> $this->input->post('l_description'),
					'created_by'	=> $this->session->userdata("user_id"),
					'product_id'	=> $product_id,
				);
			if ($_FILES['l_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('l_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		if ($this->form_validation->run() == true && $this->products_model->addLicense($data)) {
			$this->session->set_flashdata('message', $this->lang->line("license_added"));
            redirect("products/view/".$product_id."/#licenses");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['product_id'] = $product_id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'products/add_license', $this->data);	
		}	
	}
	
	public function edit_license($id = false)
	{
		$this->cus->checkPermissions('edit',true);
		$this->form_validation->set_rules('l_issued_date', lang("issued_date"), 'required');
		$this->form_validation->set_rules('l_valid_date', lang("valid_date"), 'required');
		$license = $this->products_model->getLicenseByID($id);
		if ($this->form_validation->run() == true) {	
			$data = array(
					'issued_date'	=> $this->cus->fsd($this->input->post('l_issued_date')),
					'valid_date'	=> $this->cus->fsd($this->input->post('l_valid_date')),
					'description'	=> $this->input->post('l_description'),
					'created_by'	=> $this->session->userdata("user_id")
				);
			if ($_FILES['l_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('l_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		if ($this->form_validation->run() == true && $this->products_model->updateLicense($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("licenset_added"));
            redirect("products/view/".$license->product_id."/#licenses");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $license;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'products/edit_license', $this->data);	
		}	
	}
	
	public function delete_license($id = null)
    {		
		$this->cus->checkPermissions('delete',true);
        if (isset($id) || $id != null){
        	 if ($this->products_model->deleteLicense($id)) {
				if ($this->input->is_ajax_request()) {
					echo lang("license_deleted");
					die();
				}
				$this->session->set_flashdata('message', lang('license_deleted'));
				redirect('welcome');
			}
        }
    }
	
	public function get_bom_items(){
		$bom_id = $this->input->get('bom_id');
		$warehouse_id = $this->input->get('warehouse_id');
		$quantity = $this->input->get('quantity');
		$boms_items = $this->products_model->getBomItems($bom_id);
		$raw_materials = false;
		$finish_products = false;
		if($boms_items){
			foreach($boms_items as $boms_item){
				$unit = $this->site->getProductUnit($boms_item->product_id,$boms_item->unit_id);
				$boms_item->unit_name = $unit->name;
				$boms_item->quantity = $boms_item->quantity * $quantity;
				$boms_item->unit_qty = $boms_item->unit_qty * $quantity;
				if($boms_item->type=='raw_material'){
					$product_qty = $this->products_model->getProductQuantity($boms_item->product_id,$warehouse_id);
					$boms_item->qoh = $product_qty['quantity'];
					$raw_materials[] = $boms_item;
				}else{
					$boms_item->qoh = 0;
					$finish_products[] = $boms_item;
				}
			}
		}
		echo json_encode(array("raw_materials"=>$raw_materials,"finish_products"=>$finish_products));
	}
/*=======================================================>NON_STOCK*/
function non_stock($warehouse_id = NULL)
{
    $this->cus->checkPermissions();
    $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    $this->data['warehouses'] = $this->site->getWarehouses();
    $this->data['product_units'] = json_encode($this->products_model->getProductUnits());
    $this->data['warehouse_id'] = $warehouse_id;
    $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
    $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : NULL;
    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('products')));
    $meta = array('page_title' => lang('products'), 'bc' => $bc);
    $this->core_page('products/non_stocks', $meta, $this->data);
}
function getProductsNonStock($warehouse_id = NULL)
{
    $this->cus->checkPermissions('index', TRUE);
    $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
    $detail_link = anchor('products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
    $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_non_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' id='a__$1' href='" . site_url('products/delete_non_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('Delete_Non_Stock') . "</a>";
    $single_barcode = anchor('products/print_barcodes/$1/'.$warehouse_id, '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
    // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
    $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li><a href="' . site_url('products/add_non_stock/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
        <li><a href="' . site_url('products/edit_non_stock/$1') . '"><i class="fa fa-edit"></i> ' . lang('Edit_Non_Stock') . '</a></li>';
    if ($warehouse_id) {
        $action .= '<li><a href="' . site_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa-bars"></i> '
            . lang('set_rack') . '</a></li>';
        if($this->Settings->product_serial == 1){	
            $action .= '<li><a href="' . site_url('products/set_serials/$1/' . $warehouse_id.'') . '"><i class="fa fa-bars"></i> '
            . lang('set_serial') . '</a></li>';
        }
    }
    $action .= '<li><a href="' . site_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
        . lang('view_image') . '</a></li>
        <li>' . $single_barcode . '</li>
        <li class="divider"></li>
        <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
    
    $warehouse_query = '';
    if ($this->Settings->show_warehouse_qty) {
        $warehouses = $this->site->getWarehouses();
        if($warehouses){
            foreach($warehouses as $warehouse){
                $warehouse_query .= 'convert_qty('.$this->db->dbprefix("products").'.id,IFNULL((IF('.$this->db->dbprefix("products").'.type="service" OR '.$this->db->dbprefix("products").'.type="bom","0",(SELECT IFNULL(quantity,0) as quantity from '.$this->db->dbprefix("warehouses_products").' WHERE '.$this->db->dbprefix("warehouses_products").'.product_id = '.$this->db->dbprefix("products").'.id and '.$this->db->dbprefix("warehouses_products").'.warehouse_id = "'.$warehouse->id.'" GROUP BY '.$this->db->dbprefix("warehouses_products").'.product_id))),0)) as qty_'.$warehouse->id.',';
            }
        }
    }
    $allow_category = $this->site->getCategoryByProject();
    $this->load->library('datatables');
    if ($warehouse_id) {
        $this->datatables
        ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type, {$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit, cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='service_rental' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(wp.quantity, 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, wp.rack as rack, alert_quantity", FALSE)
        ->from('products');
        if ($this->Settings->display_all_products) {
            $this->datatables->join("( SELECT product_id, quantity, rack, warehouse_id from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
        } else {
            $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
            ->where('wp.warehouse_id', $warehouse_id)
            ->where('wp.quantity !=', 0);
        }
        $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
        ->join('units', 'products.unit=units.id', 'left')
        ->join('brands', 'products.brand=brands.id', 'left')
        ->where('products.type =','asset')
        ->group_by("products.id,wp.warehouse_id");

    } else if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
        $this->datatables
            ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type,{$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit,  cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='service_rental' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(sum(".$this->db->dbprefix('warehouses_products').".quantity), 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, '' as rack, products.alert_quantity", FALSE)
            ->from('products')
            ->join('warehouses_products', 'warehouses_products.product_id = products.id', 'inner')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.type =','asset')
           // ->where('products.type !=','service_rental')
            ->where_in('warehouses_products.warehouse_id',json_decode($this->session->userdata('warehouse_id')))
            ->group_by("products.id");
    } else {
        $this->datatables
            ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type,{$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit,  cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='service_rental' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(quantity, 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, '' as rack, alert_quantity", FALSE)
            ->from('products')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.type =','asset')
            ->group_by("products.id");
    }
    
    if($allow_category){
        $this->datatables->where_in("products.category_id",$allow_category);
    }
    
    if (!$this->Owner && !$this->Admin) {
        if (!$this->session->userdata('show_cost')) {
            $this->datatables->unset_column("cost");
        }
        if (!$this->session->userdata('show_price')) {
            $this->datatables->unset_column("price");
        }
    }
    if ($supplier) {
        $this->datatables->where('supplier1', $supplier)
        ->or_where('supplier2', $supplier)
        ->or_where('supplier3', $supplier)
        ->or_where('supplier4', $supplier)
        ->or_where('supplier5', $supplier);
    }
    $this->datatables->add_column("Actions", $action, "productid, image, code, name");
    echo $this->datatables->generate();
}
/*=====================================================> Delete Non_Stock*/
    function delete_non_stock($id = NULL)
    {
        $this->cus->checkPermissions(NULL, TRUE);
        $row = $this->products_model->getProductByID($id);
        if($row->quantity <> 0){
            $this->session->set_flashdata('error', lang('product_has_quantity'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("product_deleted")." - ". $row->code . " - " . $row->name;
                die();
            }
            $this->session->set_flashdata('message', lang('product_deleted')." - ". $row->code . " - " . $row->name);
            redirect('products/non_stock');
        }

    }
/*=====================================================> Edit NonStock */
function edit_non_stock($id = NULL)
    {
        $this->cus->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses = $this->site->getWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product = $this->site->getProductByID($id);

        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
            if($this->Settings->accounting == 1){
                $this->form_validation->set_rules('stock_account', lang("stock_account"), 'required');
                $this->form_validation->set_rules('adjustment_account', lang("adjustment_account"), 'required');
                $this->form_validation->set_rules('usage_account', lang("usage_account"), 'required');
                $this->form_validation->set_rules('cost_of_sale_account', lang("cost_of_sale_account"), 'required');
                $this->form_validation->set_rules('sale_account', lang("sale_account"), 'required');
            }
        }
        
        $this->form_validation->set_rules('code', lang("product_code"), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');

        if ($this->form_validation->run('products/add') == true) {
            $service_type = $this->site->getServiceTypesByID($this->input->post('electricity'));
                
            $data = array('code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),				
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->cus->formatDecimal($this->input->post('cost'),16),
                'price' => $this->cus->formatDecimal($this->input->post('price'),16),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->cus->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->cus->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->cus->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->cus->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->cus->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->cus->formatDecimal($this->input->post('promo_price'),16),
                'start_date' => $this->input->post('start_date') ? $this->cus->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->cus->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'adjustment_qty' => $this->input->post('adjustment_qty'),
                'electricity' => $this->input->post('electricity'),
                'service_code' => $service_type->code,
                'service_types' => $service_type->name,
                'accounting_method' => $this->input->post('accounting_method'),
                'seperate_qty' => $this->input->post('seperate_qty'),
                'product_additional' => $this->input->post('product_additional'),
                'inactive' => $this->input->post('inactive'),
            );
            
            if($this->config->item("concretes")){
                $data['stregth'] = $this->input->post('stregth');
            }
            
            if($this->Settings->cbm == 1){
                $data['p_length'] = $this->input->post('p_length');
                $data['p_width'] = $this->input->post('p_width');
                $data['p_height'] = $this->input->post('p_height');
                $data['p_weight'] = $this->input->post('p_weight');
            }
            
            if($this->config->item('product_currency')==true){
                $currency_code = $this->input->post('currency_code',true);
                $currency = $this->site->getCurrencyByCode($currency_code);
                $data['price'] = $this->cus->formatDecimal($this->input->post('price'),16) / $currency->rate;
                $data['currency_code'] = $currency->code;
                $data['currency_rate'] = $currency->rate;
            }
            
            if($this->Settings->accounting == 1){
                if($this->input->post('type')=='service'){
                    $sale_acc = $this->input->post('sale_account_sv');
                    $pawn_acc = $this->input->post('pawn_account_sv');
                }else{
                    $sale_acc = $this->input->post('sale_account');
                    $pawn_acc = $this->input->post('pawn_account');
                }
                $product_account = array(
                    'type' => $this->input->post('type'),
                    'stock_acc' => $this->input->post('stock_account'),
                    'adjustment_acc' => $this->input->post('adjustment_account'),
                    'usage_acc' => $this->input->post('usage_account'),
                    'convert_acc' => $this->input->post('convert_account'),
                    'cost_acc' => $this->input->post('cost_of_sale_account'),
                    'sale_acc' => $sale_acc,
                    'pawn_acc' => $pawn_acc,
                    );
            }else{
                if($product->cost != $this->input->post('cost')){
                    $stockmoves= array(
                        'transaction' => 'CostAdjustment',
                        'transaction_id' => '0',
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'warehouse_id' => 0,
                        'date' => date('Y-m-d H:i:s'),
                        'real_unit_cost' => $this->input->post('cost'),
                        'user_id' => $this->session->userdata('user_id'),
                    );	
                }
            }
            
            $i = isset($_POST['product_unit_id']) ? sizeof($_POST['product_unit_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_unit_qty = $this->cus->formatDecimal($_POST['product_unit_qty'][$r]);
                if($product_unit_qty > 0 && $product_unit_qty !=''){
                    $product_units[] = array(
                            'unit_id' => $_POST['product_unit_id'][$r],
                            'product_id' => $id,
                            'unit_qty' => $product_unit_qty,
                            'unit_price' => $_POST['product_unit_price'][$r],
                    ); 
                }
                
            }
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = array(
                            'id' => $this->input->post('variant_id_'.$pv->id),
                            'name' => $this->input->post('variant_name_'.$pv->id),
                            'cost' => $this->input->post('variant_cost_'.$pv->id),
                            'price' => $this->input->post('variant_price_'.$pv->id),
                        );
                    }
                } else {
                    $update_variants = NULL;
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = array(
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                    );
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang("product_already_has_variant").' ('.$_POST['attr_name'][$r].')');
                                $this->form_validation->set_rules('new_product_variant', lang("new_product_variant"), 'required');
                            } else {
                                $product_attributes[] = array(
                                    'name' => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'price' => $_POST['attr_price'][$r],
                                );
                            }
                        }
                    }

                } else {
                    $product_attributes = NULL;
                }

            } else {
                $warehouse_qty = NULL;
                $product_attributes = NULL;
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
                            'item_id' => $_POST['combo_item_id'][$r],
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                            'option_id' => $_POST['coption_id'][$r]
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r])) {
                        $items2[] = array(
                            'item_code' => $_POST['combo_item_code'][$r],
                            'product_id' => $_POST['combo_item_id'][$r],
                            'option_id' => $_POST['poption_id'][$r]
                        );
                    }
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'bom') {	
                $c = sizeof($_POST['bom_item_id']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['bom_item_id'][$r]) && isset($_POST['bom_item_quantity'][$r])) {
                        $bom_items[] = array(
                            'product_id' => $_POST['bom_item_id'][$r],
                            'bom_type' => $_POST['bom_type'][$r],
                            'quantity' => $_POST['bom_item_quantity'][$r],
                            'unit_id' => $_POST['bom_unit_id'][$r]
                        );
                    } 
                }
                $data['track_quantity'] = 0;
            }
            
            if($this->Settings->product_formulation == 1 && $this->input->post('formulation')){
                $d = sizeof($_POST['for_caculation']) - 1;
                for ($r = 0; $r <= $d; $r++) {
                    if (isset($_POST['for_caculation'][$r])) {
                        $formulation_items[] = array(
                            'for_width' => $_POST['for_width'][$r],
                            'for_height' => $_POST['for_height'][$r],
                            'for_square' => $_POST['for_square'][$r],
                            'for_qty' => $_POST['for_qty'][$r],
                            'for_field' => $_POST['for_field'][$r],
                            'for_unit_id' => $this->input->post('unit'),
                            'for_caculation' => $_POST['for_caculation'][$r],
                            'for_operation' => $_POST['for_operation'][$r],
                        );
                    } 
                }
            }
            if (!isset($formulation_items)) {
                $formulation_items = NULL;
            }
            if (!isset($bom_items)) {
                $bom_items = NULL;
            }
            if (!isset($items)) {
                $items = NULL;
            }
            if (!isset($items2)) {
                $items2 = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/edit/" . $id);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("products/edit/" . $id);
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'right';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            
            
            if($this->config->item('convert') && ($this->input->post('type')=='standard' || $this->input->post('type')=='raw_material' || $this->input->post('type')=='asset')){
                $i = isset($_POST['convert_item_id']) ? sizeof($_POST['convert_item_id']) : 0;		
                if($i > 0){
                    for ($r = 0; $r < $i; $r++) {
                        $convert_item_id = $_POST['convert_item_id'][$r];
                        $convert_item_unit = $_POST['convert_item_unit'][$r];
                        $convert_item_qty = $_POST['convert_item_qty'][$r];
                        $convert_unit_info = $this->site->getProductUnit($convert_item_id,$convert_item_unit);
                        $convert_item[] = array(
                                    'product_id'=>$convert_item_id,
                                    'quantity'=>$convert_item_qty * $convert_unit_info->unit_qty,
                                    'unit_id'=>$convert_item_unit,
                                    'unit_qty'=>$convert_item_qty,
                                    'type'=>"raw_material",
                                );
                    }
                    $convert = array(
                        'name' => $this->input->post('code').' - '.$this->input->post('name'),	
                        'product_id' => $id,		
                        'updated_by' => $this->session->userdata('user_id'),	
                    );
                }
                                
            }
        }
        
        
        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $product_units, $items2,  $product_account, $stockmoves, $bom_items, $formulation_items, $convert, $convert_item)) {
            $this->session->set_flashdata('message', lang("product_updated")." - ".$product->code." - ".$product->name);
            redirect('products/non_stock');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->load->model('pos_model');
            $this->data['pos_settings'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['service_types'] = $this->site->getAllServiceTypes();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product'] = $product;
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['product_unit'] = $this->products_model->getUnitbyProduct($product->id,$product->unit);
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['subunits'] = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants'] = $this->products_model->getProductOptions($id);
            $this->data['combo_items'] = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : NULL;
            $this->data['bom_items'] = $product->type == 'bom' ? $this->products_model->getProductBomItems($product->id) : NULL;
            $this->data['digital_items'] = $product->type == 'digital' ? $this->products_model->getProductDigitalItems($product->id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;

            $productAccount = $this->products_model->getProductAccByProductId($product->id);
            if($this->Settings->accounting == 1){
                $this->data['stock_accounts'] = $this->site->getAccount(array('AS'),$productAccount->stock_acc);
                $this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->adjustment_acc);
                $this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->usage_acc);
                $this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->convert_acc);
                $this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$productAccount->cost_acc);
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->sale_acc);
                if($this->config->item("pawn")){
                    $this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->pawn_acc);
                }
            }
            
            if($this->config->item('convert') && ($product->type=='standard' || $product->type=='raw_material' || $product->type=='asset')){
                $this->data['convert_items'] = $this->products_model->getBomItemByProductID($product->id);
            }
            
            if($this->Settings->product_formulation == 1){
                $this->data['formulation_items'] = $this->products_model->getProductFormulation($product->id);
            }
            $this->data['customers'] = $this->site->getAllCompanies("customer");
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_product')));
            $meta = array('page_title' => lang('edit_product'), 'bc' => $bc);
            $this->core_page('products/edit_non_stock', $meta, $this->data);
        }
    }
/*=====================================================> Add NonStock */
function add_non_stock($id = NULL)
{
    $this->cus->checkPermissions();
    $this->load->helper('security');
    $warehouses = $this->site->getWarehouses();
    $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
    if ($this->input->post('type') == 'standard') {
        $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
        $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
        if($this->Settings->accounting == 1){
            $this->form_validation->set_rules('stock_account', lang("stock_account"), 'required');
            $this->form_validation->set_rules('adjustment_account', lang("adjustment_account"), 'required');
            $this->form_validation->set_rules('usage_account', lang("usage_account"), 'required');
            $this->form_validation->set_rules('cost_of_sale_account', lang("cost_of_sale_account"), 'required');
            $this->form_validation->set_rules('sale_account', lang("sale_account"), 'required');
        }
    }
    if ($this->input->post('barcode_symbology') == 'ean13') {
        $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
    }
    $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]|alpha_dash');
    $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
    $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
    $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
    if ($this->form_validation->run() == true) {
        $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
        $service_type = $this->site->getServiceTypesByID($this->input->post('electricity'));
        $data = array(
            'code' => $this->input->post('code'),
            'barcode_symbology' => $this->input->post('barcode_symbology'),
            'name' => $this->input->post('name'),
            'type' => $this->input->post('type'),
            'brand' => $this->input->post('brand'),				
            'category_id' => $this->input->post('category'),
            'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
            'cost' => $this->cus->formatDecimal($this->input->post('cost'),16),
            'price' => $this->cus->formatDecimal($this->input->post('price'),16),
            'unit' => $this->input->post('unit'),
            'sale_unit' => $this->input->post('default_sale_unit'),
            'purchase_unit' => $this->input->post('default_purchase_unit'),
            'tax_rate' => $this->input->post('tax_rate'),
            'tax_method' => $this->input->post('tax_method'),
            'alert_quantity' => $this->input->post('alert_quantity'),
            'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
            'details' => $this->input->post('details'),
            'product_details' => $this->input->post('product_details'),
            'supplier1' => $this->input->post('supplier'),
            'supplier1price' => $this->cus->formatDecimal($this->input->post('supplier_price')),
            'supplier2' => $this->input->post('supplier_2'),
            'supplier2price' => $this->cus->formatDecimal($this->input->post('supplier_2_price')),
            'supplier3' => $this->input->post('supplier_3'),
            'supplier3price' => $this->cus->formatDecimal($this->input->post('supplier_3_price')),
            'supplier4' => $this->input->post('supplier_4'),
            'supplier4price' => $this->cus->formatDecimal($this->input->post('supplier_4_price')),
            'supplier5' => $this->input->post('supplier_5'),
            'supplier5price' => $this->cus->formatDecimal($this->input->post('supplier_5_price')),
            'cf1' => $this->input->post('cf1'),
            'cf2' => $this->input->post('cf2'),
            'cf3' => $this->input->post('cf3'),
            'cf4' => $this->input->post('cf4'),
            'cf5' => $this->input->post('cf5'),
            'cf6' => $this->input->post('cf6'),
            'promotion' => $this->input->post('promotion'),
            'promo_price' => $this->cus->formatDecimal($this->input->post('promo_price'),16),
            'start_date' => $this->input->post('start_date') ? $this->cus->fsd($this->input->post('start_date')) : NULL,
            'end_date' => $this->input->post('end_date') ? $this->cus->fsd($this->input->post('end_date')) : NULL,
            'supplier1_part_no' => $this->input->post('supplier_part_no'),
            'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
            'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
            'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
            'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
            'file' => $this->input->post('file_link'),
            'adjustment_qty' => $this->input->post('adjustment_qty'),
            'electricity' => $this->input->post('electricity'),
            'service_code' => $service_type->code,
            'service_types' => $service_type->name,
            'accounting_method' => $this->input->post('accounting_method'),
            'seperate_qty' => $this->input->post('seperate_qty'),
            'product_additional' => $this->input->post('product_additional'),
        );
        
        if($this->config->item("concretes")){
            $data['stregth'] = $this->input->post('stregth');
        }
        
        if($this->Settings->cbm == 1){
            $data['p_length'] = $this->input->post('p_length');
            $data['p_width'] = $this->input->post('p_width');
            $data['p_height'] = $this->input->post('p_height');
            $data['p_weight'] = $this->input->post('p_weight');
        }
        
        if($this->config->item('product_currency')==true){
            $currency_code = $this->input->post('currency_code',true);
            $currency = $this->site->getCurrencyByCode($currency_code);
            $data['price'] = $this->cus->formatDecimal($this->input->post('price'),16) / $currency->rate;
            $data['currency_code'] = $currency->code;
            $data['currency_rate'] = $currency->rate;
        }
        
        if($this->Settings->accounting == 1){
            if($this->input->post('type')=='service'){
                $sale_acc = $this->input->post('sale_account_sv');
            }else{
                $sale_acc = $this->input->post('sale_account');
            }
            $product_account = array(
                'type' => $this->input->post('type'),
                'stock_acc' => $this->input->post('stock_account'),
                'adjustment_acc' => $this->input->post('adjustment_account'),
                'usage_acc' => $this->input->post('usage_account'),
                'convert_acc' => $this->input->post('convert_account'),
                'cost_acc' => $this->input->post('cost_of_sale_account'),
                'sale_acc' => $sale_acc,
                'pawn_acc' => $this->input->post('pawn_account'),
                );
            //$this->cus->print_arrays($product_account);	
        }					
        
        $i = isset($_POST['product_unit_id']) ? sizeof($_POST['product_unit_id']) : 0;
        if($i > 0){
            for ($r = 0; $r < $i; $r++) {
                 $product_unit_qty = $this->cus->formatDecimal($_POST['product_unit_qty'][$r]);
                 if($product_unit_qty > 0 && $product_unit_qty !=''){
                    $product_units[] = array(
                            'unit_id' => $_POST['product_unit_id'][$r],
                            'unit_qty' => $product_unit_qty,
                            'unit_price' => $_POST['product_unit_price'][$r],
                      ); 
                 }
                 
            }
        }else{
            $product_units[] = array(
                    'unit_id' => $this->input->post('unit'),
                    'unit_qty' => 1
                ); 
        }
        
        $this->load->library('upload');
        if ($this->input->post('type') == 'standard') {
            $wh_total_quantity = 0;
            $pv_total_quantity = 0;
            for ($s = 2; $s > 5; $s++) {
                $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
            }
            foreach ($warehouses as $warehouse) {
                if ($this->input->post('wh_qty_' . $warehouse->id)) {
                    $warehouse_qty[] = array(
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                    );
                    $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                }
            }

            if ($this->input->post('attributes')) {
                $a = sizeof($_POST['attr_name']);
                for ($r = 0; $r <= $a; $r++) {
                    if (isset($_POST['attr_name'][$r])) {
                        $product_attributes[] = array(
                            'name' => $_POST['attr_name'][$r],
                            'warehouse_id' => $_POST['attr_warehouse'][$r],
                            'quantity' => $_POST['attr_quantity'][$r],
                            'price' => $_POST['attr_price'][$r],
                        );
                        $pv_total_quantity += $_POST['attr_quantity'][$r];
                    }
                }
                
                if ($wh_total_quantity != $pv_total_quantity) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }
                
            } else {
                $product_attributes = NULL;
            }                
            
        } else {
            $warehouse_qty = NULL;
            $product_attributes = NULL;
        }


        if ($this->input->post('type') == 'service') {
            $data['track_quantity'] = 0;
        } elseif ($this->input->post('type') == 'combo') {
            $total_price = 0;
            $c = sizeof($_POST['combo_item_code']) - 1;
            for ($r = 0; $r <= $c; $r++) {
                if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                    $items[] = array(
                        'item_id' => $_POST['combo_item_id'][$r],
                        'item_code' => $_POST['combo_item_code'][$r],
                        'quantity' => $_POST['combo_item_quantity'][$r],
                        'unit_price' => $_POST['combo_item_price'][$r],
                        'option_id' => $_POST['coption_id'][$r]
                    );
                }
                $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
            }
            /*if ($this->cus->formatDecimal($total_price) != $this->cus->formatDecimal($this->input->post('price'))) {
                $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
            }*/
            $data['track_quantity'] = 0;
        } elseif ($this->input->post('type') == 'digital') {

            $c = sizeof($_POST['combo_item_code']) - 1;
            for ($r = 0; $r <= $c; $r++) {
                if (isset($_POST['combo_item_code'][$r])) {
                    $items2[] = array(
                        'item_code' => $_POST['combo_item_code'][$r],
                        'product_id' => $_POST['combo_item_id'][$r],
                        'option_id' => $_POST['poption_id'][$r]
                    );
                }
            }
            $config = NULL;
            $data['track_quantity'] = 0;
        } elseif ($this->input->post('type') == 'bom') {	
            $c = sizeof($_POST['bom_item_id']) - 1;
            for ($r = 0; $r <= $c; $r++) {
                if (isset($_POST['bom_item_id'][$r]) && isset($_POST['bom_item_quantity'][$r])) {
                    $bom_items[] = array(
                        'bom_type' => $_POST['bom_type'][$r],
                        'product_id' => $_POST['bom_item_id'][$r],
                        'quantity' => $_POST['bom_item_quantity'][$r],
                        'unit_id' => $_POST['bom_unit_id'][$r]
                    );
                } 
            }
            $data['track_quantity'] = 0;
        }
        
        if($this->Settings->product_formulation == 1 && $this->input->post('formulation')){
            $d = sizeof($_POST['for_caculation']) - 1;
            for ($r = 0; $r <= $d; $r++) {
                if (isset($_POST['for_caculation'][$r])) {
                    $formulation_items[] = array(
                        'for_width' => $_POST['for_width'][$r],
                        'for_height' => $_POST['for_height'][$r],
                        'for_square' => $_POST['for_square'][$r],
                        'for_qty' => $_POST['for_qty'][$r],
                        'for_field' => $_POST['for_field'][$r],
                        'for_caculation' => $_POST['for_caculation'][$r],
                        'for_operation' => $_POST['for_operation'][$r],
                        'for_unit_id' => $this->input->post('unit'),
                    );
                } 
            }
        }

        if (!isset($bom_items)) {
            $bom_items = NULL;
        }
        if (!isset($items)) {
            $items = NULL;
        }
        if (!isset($items2)) {
            $items2 = NULL;
        }
        if ($_FILES['product_image']['size'] > 0) {

            $config['upload_path'] = $this->upload_path;
            $config['allowed_types'] = $this->image_types;
            $config['max_size'] = $this->allowed_file_size;
            $config['max_width'] = $this->Settings->iwidth;
            $config['max_height'] = $this->Settings->iheight;
            $config['overwrite'] = FALSE;
            $config['max_filename'] = 25;
            $config['encrypt_name'] = TRUE;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('product_image')) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                redirect("products/add");
            }
            $photo = $this->upload->file_name;
            $data['image'] = $photo;
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = $this->upload_path . $photo;
            $config['new_image'] = $this->thumbs_path . $photo;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = $this->Settings->twidth;
            $config['height'] = $this->Settings->theight;
            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            if (!$this->image_lib->resize()) {
                echo $this->image_lib->display_errors();
            }
            if ($this->Settings->watermark) {
                $this->image_lib->clear();
                $wm['source_image'] = $this->upload_path . $photo;
                $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                $wm['wm_type'] = 'text';
                $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                $wm['quality'] = '100';
                $wm['wm_font_size'] = '16';
                $wm['wm_font_color'] = '999999';
                $wm['wm_shadow_color'] = 'CCCCCC';
                $wm['wm_vrt_alignment'] = 'top';
                $wm['wm_hor_alignment'] = 'right';
                $wm['wm_padding'] = '10';
                $this->image_lib->initialize($wm);
                $this->image_lib->watermark();
            }
            $this->image_lib->clear();
            $config = NULL;
        }

        if ($_FILES['userfile']['name'][0] != "") {

            $config['upload_path'] = $this->upload_path;
            $config['allowed_types'] = $this->image_types;
            $config['max_size'] = $this->allowed_file_size;
            $config['max_width'] = $this->Settings->iwidth;
            $config['max_height'] = $this->Settings->iheight;
            $config['overwrite'] = FALSE;
            $config['encrypt_name'] = TRUE;
            $config['max_filename'] = 25;
            $files = $_FILES;
            $cpt = count($_FILES['userfile']['name']);
            for ($i = 0; $i < $cpt; $i++) {

                $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/add");
                } else {

                    $pho = $this->upload->file_name;

                    $photos[] = $pho;

                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $pho;
                    $config['new_image'] = $this->thumbs_path . $pho;
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;

                    $this->image_lib->initialize($config);

                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }

                    if ($this->Settings->watermark) {
                        $this->image_lib->clear();
                        $wm['source_image'] = $this->upload_path . $pho;
                        $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                        $wm['wm_type'] = 'text';
                        $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                        $wm['quality'] = '100';
                        $wm['wm_font_size'] = '16';
                        $wm['wm_font_color'] = '999999';
                        $wm['wm_shadow_color'] = 'CCCCCC';
                        $wm['wm_vrt_alignment'] = 'top';
                        $wm['wm_hor_alignment'] = 'right';
                        $wm['wm_padding'] = '10';
                        $this->image_lib->initialize($wm);
                        $this->image_lib->watermark();
                    }

                    $this->image_lib->clear();
                }
            }
            $config = NULL;
        } else {
            $photos = NULL;
        }
        $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
        
        $convert = false;
        $convert_item = false;
        
        if($this->config->item('convert') && ($this->input->post('type')=='standard' || $this->input->post('type')=='raw_material' || $this->input->post('type')=='asset')){
            $i = isset($_POST['convert_item_id']) ? sizeof($_POST['convert_item_id']) : 0;		
            if($i > 0){
                for ($r = 0; $r < $i; $r++) {
                    $convert_item_id = $_POST['convert_item_id'][$r];
                    $convert_item_unit = $_POST['convert_item_unit'][$r];
                    $convert_item_qty = $_POST['convert_item_qty'][$r];
                    $convert_unit_info = $this->site->getProductUnit($convert_item_id,$convert_item_unit);
                    $convert_item[] = array(
                                'product_id'=>$convert_item_id,
                                'quantity'=>$convert_item_qty * $convert_unit_info->unit_qty,
                                'unit_id'=>$convert_item_unit,
                                'unit_qty'=>$convert_item_qty,
                                'type'=>"raw_material",
                            );		
                }
                $convert = array(
                    'name' => $this->input->post('code').' - '.$this->input->post('name'),	
                    'created_by' => $this->session->userdata('user_id'),	
                );
            }
                            
        }
    }

    if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $product_units, $items2, $product_account , $bom_items, $formulation_items, $convert, $convert_item)) {
        $this->session->set_flashdata('message', lang("product_added") ." - ".$data["code"]." - ".$data["name"]);
        redirect('products/non_stock');
    } else {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->load->model('pos_model');
        if($id){
            $old_product_info = $this->products_model->getProductByID($id);
            $this->data['subunits'] = $this->site->getUnitsByBUID($old_product_info->unit);
            $this->data['product_unit'] = $this->products_model->getUnitbyProduct($old_product_info->id,$old_product_info->unit);
            if($this->config->item('convert') && ($old_product_info->type=='standard' || $old_product_info->type=='raw_material' || $old_product_info->type=='asset')){
                $this->data['convert_items'] = $this->products_model->getBomItemByProductID($id);
            }
            if($this->Settings->product_formulation == 1){
                $this->data['formulation_items'] = $this->products_model->getProductFormulation($id);
            }
            if($this->Settings->accounting == 1){
                $productAccount = $this->products_model->getProductAccByProductId($old_product_info->id);
                $this->data['stock_accounts'] = $this->site->getAccount(array('AS'),$productAccount->stock_acc);
                $this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->adjustment_acc);
                $this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->usage_acc);
                $this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'),$productAccount->convert_acc);
                $this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'),$productAccount->cost_acc);
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->sale_acc);
                if($this->config->item("pawn")){
                    $this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'),$productAccount->pawn_acc);
                }
            }
        }else{
            $old_product_info = NULL;
            $this->data['subunits'] = NULL;
            $this->data['product_unit'] = NULL;
            if($this->Settings->accounting == 1){
                $this->data['stock_accounts'] = $this->site->getAccount(array('AS'));
                $this->data['adjustment_accounts'] = $this->site->getAccount(array('CO','EX'));
                $this->data['usage_accounts'] = $this->site->getAccount(array('CO','EX'));
                $this->data['convert_accounts'] = $this->site->getAccount(array('CO','EX'));
                $this->data['cost_accounts'] = $this->site->getAccount(array('CO','EX','OX','GL','AS'));
                $this->data['sale_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
                if($this->config->item("pawn")){
                    $this->data['pawn_accounts'] = $this->site->getAccount(array('RE','EX','OI','GL','LI'));
                }
            }
        }
        

        $this->data['pos_settings'] = $this->pos_model->getSetting();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['service_types'] = $this->site->getAllServiceTypes();
        $this->data['base_units'] = $this->site->getAllBaseUnits();
        $this->data['currencies'] = $this->site->getAllCurrencies();
        $this->data['warehouses'] = $warehouses;
        $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : NULL;
        $this->data['product'] = $old_product_info;
        $this->data['variants'] = $this->products_model->getAllVariants();
        $this->data['combo_items'] = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : NULL;
        $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_product')));
        $meta = array('page_title' => lang('add_product'), 'bc' => $bc);
        $this->core_page('products/add_non_stock', $meta, $this->data);
    }
}


}

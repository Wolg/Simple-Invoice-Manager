<?
	$data = array();
	if($_GET['mode']=='save_invoice') {

		$data['number'] 	= clean($_GET['invoice_number']);
		$data['ticket'] 	= clean($_GET['invoice_ticket']);
		$data['note'] 		= clean($_GET['note']);
		$data['date'] 		= clean($_GET['date']);
		$data['tax'] 		= clean($_GET['tax']);
		$data['customer'] 	= clean($_GET['customer_number']);
		$data['logo'] 		= clean($_GET['logo']);
		$data['last-mod'] 	= time();
		$temp_arr 		= json_to_array($_GET['content']);
		$data['products'] 	= $temp_arr['product'];

		$content = array_to_xml($data, 'invoice')->asXML();
		$content = format_xml($content);

		//if the folder not exist create it
		if (!file_exists($path['invoice'].DIRECTORY_SEPARATOR.get_last_year())) {
			mkdir($path['invoice'].DIRECTORY_SEPARATOR.get_last_year());
			file_put_contents($path['invoice'].DIRECTORY_SEPARATOR.get_last_year().DIRECTORY_SEPARATOR.'index.php','');
		}

		//Check year of invoice
		if(isset($_GET['year'])){
			$year_invoice = $_GET['year'];
		} else {
			$year_invoice = get_last_year();
		}

		//If changed the number of invoice remove the old number invoice
		if(isset($_GET['old_date'])){

		//Read the info of invoice for various check
		$inv_info = read_invoice_info(clean($_GET['invoice_number'],true),$year_invoice);

			if($_GET['old_date']==clean($_GET['date']) && $_GET['old_number']!=clean($_GET['invoice_number'])){
				$inv_info = read_invoice_info($_GET['old_number']);
				unlink($path['invoice'].DIRECTORY_SEPARATOR.$year_invoice.DIRECTORY_SEPARATOR.$_GET['old_number'].'.xml');
			}

			//Check if invoice is in the history of customer
			if ($inv_info['customer']!=$data['customer']) {
				//Remove info of old customer
				$file_history = $path['customers'].DIRECTORY_SEPARATOR.$data['customer'].'_history.xml';
				$add_history = true;
				if(!file_exists($file_history)){
					$add_history=true;
				} else {
					$check = xml2array($file_history);
					foreach($check as $key => $value){
						//if there is in the history not add
						if($check[$key]['number']==$data['number'] && $check[$key]['year']== $year_invoice) {
							$add_history = false;
							break;
						}
					}
				}

				//Add to history
				if($add_history){
					$data_h['invoice']['number'] = $data['number'];
					$data_h['invoice']['year'] = $year_invoice;
					$history['history'] = $data_h['invoice'];

					$content_h = array_to_xml($history, 'customer-history')->asXML();
					$content_h = format_xml($content_h);
					file_put_contents($file_history,$content_h);
				}
			}
		}

		file_put_contents($path['invoice'].DIRECTORY_SEPARATOR.$year_invoice.DIRECTORY_SEPARATOR.clean($_GET['invoice_number'],true).'.xml',$content);
	} elseif($_GET['mode']=='save_draft_invoice') {
		//Save draft
		$number_drafts = get_last_element('draft');
		$number_drafts++;

		$data['number'] 	= clean($_GET['invoice_number']);
		$data['ticket'] 	= clean($_GET['invoice_ticket']);
		$data['note'] 		= clean($_GET['note']);
		$data['date'] 		= clean($_GET['date']);
		$data['tax'] 		= clean($_GET['tax']);
		$data['customer'] 	= clean($_GET['customer_number']);
		$data['logo'] 		= clean($_GET['logo']);
		$data['last-mod'] 	= time();
		$temp_arr = json_to_array($_GET['content']);
		$data['products'] 	= $temp_arr['product'];

		$content = array_to_xml($data, 'invoice')->asXML();
		$content = format_xml($content);

		file_put_contents($path['draft'].DIRECTORY_SEPARATOR.$number_drafts.'.xml',$content);
	} elseif($_GET['mode']=='invoice_option') {
		//Save invoice option in invoice or draft

		$data['payment_date'] 		= clean($_GET['date']);
		$data['payment_capture'] 	= clean($_GET['capture']);

		$content = array_to_xml($data, 'invoice')->asXML();
		$content = format_xml($content);

		if(isset($_GET['is_invoice'])&&$_GET['is_invoice']==true){
			$path = $path['invoice'].DIRECTORY_SEPARATOR.get_last_year().DIRECTORY_SEPARATOR.clean($_GET['invoice_number'],true).'.xml';
		} else {
			$path = $path['draft'].DIRECTORY_SEPARATOR.clean($_GET['invoice_number'],true).'.xml';
		}

		$file = new SimpleXMLElement(file_get_contents($path));
		$file->addChild("payment_capture",$data['payment_capture']);
		$file->addChild("payment_date",$data['payment_date']);

		$content = format_xml($file->asXML());

		file_put_contents($path,$content);

	}  elseif($_GET['mode']=='new_customer') {
		//Add new customer
		$number_customers = get_last_element('customer');
		$number_customers++;

		$data['name'] 		= clean($_GET['name']);
		$data['vat'] 		= clean($_GET['vat']);
		$data['address'] 	= clean($_GET['address']);
		$data['zipcode'] 	= clean($_GET['zipcode']);
		$data['city'] 		= clean($_GET['city']);
		$data['region'] 	= clean($_GET['region']);
		$data['phone'] 		= clean($_GET['phone']);
		$data['email'] 		= clean($_GET['email']);

		$content = array_to_xml($data, 'customer')->asXML();
		$content = format_xml($content);

		file_put_contents($path['customers'].DIRECTORY_SEPARATOR.$number_customers.'.xml',$content);
	} elseif($_GET['mode']=='mod_customer') {
		//Edit the customer
		$data['name'] 		= clean($_GET['name']);
		$data['vat'] 		= clean($_GET['vat']);
		$data['address'] 	= clean($_GET['address']);
		$data['zipcode'] 	= clean($_GET['zipcode']);
		$data['city'] 		= clean($_GET['city']);
		$data['region'] 	= clean($_GET['region']);
		$data['phone'] 		= clean($_GET['phone']);
		$data['email'] 		= clean($_GET['email']);

		$content = array_to_xml($data, 'customer')->asXML();
		$content = format_xml($content);

		file_put_contents($path['customers'].DIRECTORY_SEPARATOR.$_GET['customer'].'.xml',$content);
	} elseif($_GET['mode']=='new_note') {
		//Save note
		$number_notes = get_last_element('note');
		$number_notes++;

		$data['name'] 		= clean($_GET['name']);
		$data['text'] 		= clean($_GET['text']);

		$content = array_to_xml($data, 'note')->asXML();

		file_put_contents($path['notes'].DIRECTORY_SEPARATOR.$number_notes.'.xml',$content);
	} elseif($_GET['mode']=='mod_note') {
		//Edit the note
		$data['name'] 		= clean($_GET['name']);
		$data['text'] 		= clean($_GET['text']);

		$content = array_to_xml($data, 'note')->asXML();
		$content = format_xml($content);

		file_put_contents($path['notes'].DIRECTORY_SEPARATOR.$_GET['note'].'.xml',$content);
	}

?>

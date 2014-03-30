<?php
class ControllerModuleSeomage extends Controller {
	private $error = array(); 

	public function install() {
		$this->load->model('module/seomage');
		$this->model_module_seomage->install();
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('seomage', array("seomage_checkupdate" => 1));
	}

	public function uninstall() {
		$this->load->model('module/seomage');
		$this->model_module_seomage->uninstall();
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('seomage', array("seomage_status" => 0));
	}

	private function convertFURL($s) {
		$cyrillic = array("а","б","в","г","д","е","ж", "з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц", "ч", "ш", "щ",  "ъ","ь","ю", "я", "А","Б","В","Г","Д","Е","Ж", "З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц", "Ч", "Ш", "Щ",  "Ъ","Ь","Ю", "Я", "Ї","ї","Є", "є", "Ы","ы","Ё", "ё", "ı","İ","ğ","Ğ","ü","Ü","ş","Ş","ö","Ö","ç","Ç","Á","á","Â","â","Ã","ã","À","à","Ç","ç","É","é","Ê","ê","Í","í","Ó","ó","Ô","ô","Õ","õ","Ú","ú");
		$latin =    array("a","b","v","g","d","e","zh","z","i","y","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","sht","a","y","yu","ya","A","B","V","G","D","E","Zh","Z","I","Y","K","L","M","N","O","P","R","S","T","U","F","H","Ts","Ch","Sh","Sht","A","Y","Yu","Ya","I","i","Ye","ye","I","i","Yo","yo","i","I","g","G","u","U","s","S","o","O","c","C","A","a","A","a","A","a","A","a","C","c","E","e","E","e","I","i","O","o","O","o","O","o","U","u");

		$res = '';
		$arr = preg_split('//u', $s, null, PREG_SPLIT_NO_EMPTY); 

		foreach ($arr as $value) {
			$ord = ord($value);
			// 0-9, a-z, A-Z
			if (($ord > 47 && $ord < 58) || ($ord > 96 && $ord < 123) || ($ord > 64 && $ord < 91)) {
				$res .= $value;
				continue;
			}
			$a = array_search($value, $cyrillic);
			if ($a === false) {
				$res .= '_';
			} else {
				$res .= $latin[$a];
			}
		}
		return $res;
	}

	public function clearlog() {
		$this->load->model('module/seomage');
		$this->model_module_seomage->clearLog();		
		$json['success'] = 'OK';
     	$this->response->setOutput(json_encode($json));	
	}
	
	public function generation() {
		$json = array();
		$language = $_POST['language'];
		$register_to_low = $_POST['register_to_low'];
		$report_type = $_POST['report'];
		if ($_POST['method'] == 'manual') {
			$template = $_POST['template'];
		} else {
			$template = $_POST['method'];
		}

		$report = '<table class="form">';
		$error_count = 0;
		$counter = 0;

		$this->load->model('module/seomage');
		$this->language->load('module/seomage');

		$keywords = $this->model_module_seomage->getAllKeywords();

		// Generation PRODUCTS links
		if ($_POST['id'] == 'gen_product') {
			$this->load->model('catalog/product');
			$prods = $this->model_catalog_product->getProducts();
			$counter = sizeof($prods);
			foreach ($prods as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();

				$desc = $this->model_catalog_product->getProductDescriptions($value['product_id']);
				$data['id'] = $value['product_id'];
				$data['name'] = $desc[$language]['name'];
				$data['model'] = $value['model'];
				$data['sku'] = $value['sku'];
				$data['manufacturer_id'] = $value['manufacturer_id'];
				$data['manufacturer'] = '';
				$data['category'] = '';

				if (strpos($template, '{manufacturer}') !== false) {
					$this->load->model('catalog/manufacturer');
					$m = $this->model_catalog_manufacturer->getManufacturer($data['manufacturer_id']);
					if (isset($m['name'])) {
						$data['manufacturer'] = $m['name'];
					} 
				}

				if (strpos($template, '{category}') !== false) {
					$c = $this->model_module_seomage->getCategoryByProduct($data['id'], $language);
					if (isset($c[0]['name'])) {
						$data['category'] = $c[0]['name'];
					}
				}

				$tpl = $template;
				$tpl = preg_replace('~{id}~Uis', $data['id'], $tpl);
				$tpl = preg_replace('~{name}~Uis', $data['name'], $tpl);
				$tpl = preg_replace('~{model}~Uis', $data['model'], $tpl);
				$tpl = preg_replace('~{sku}~Uis', $data['sku'], $tpl);
				$tpl = preg_replace('~{manufacturer}~Uis', $data['manufacturer'], $tpl);
				$tpl = preg_replace('~{category}~Uis', $data['category'], $tpl);
				$data['keyword'] = $this->convertFURL($tpl);
				if ($register_to_low == 'true') {
					$data['keyword'] = strtolower($data['keyword']);
				}

				$this->model_module_seomage->deleteProductURL($data['id']);
				$idx = array_search($data['keyword'], $keywords['default']);
				if ($idx !== false) {
					unset($keywords['default'][$idx]);
				}
				
				if (in_array($data['keyword'], $keywords['seomage'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_seomage');
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_default');
				} else {
					$this->model_module_seomage->setProductURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc[$language]['name'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}

		// Generation CATEGORIES links
		if ($_POST['id'] == 'gen_category') {
			$this->load->model('catalog/category');
			$categories = $this->model_catalog_category->getCategories(array());
			$counter = sizeof($categories);
			foreach ($categories as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();

				$desc = $this->model_catalog_category->getCategoryDescriptions($value['category_id']);
				$data['id'] = $value['category_id'];
				$data['name'] = $desc[$language]['name'];

				$tpl = $template;
				$tpl = preg_replace('~{name}~Uis', $data['name'], $tpl);
				$tpl = preg_replace('~{id}~Uis', $data['id'], $tpl);
				$data['keyword'] = $this->convertFURL($tpl);
				if ($register_to_low == 'true') {
					$data['keyword'] = strtolower($data['keyword']);
				}

				$this->model_module_seomage->deleteCategoryURL($data['id']);
				$idx = array_search($data['keyword'], $keywords['default']);
				if ($idx !== false) {
					unset($keywords['default'][$idx]);
				}

				if (in_array($data['keyword'], $keywords['seomage'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_seomage');
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_default');
				} else {
					$this->model_module_seomage->setCategoryURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc[$language]['name'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}

		// Generation INFORMATIONS links
		if ($_POST['id'] == 'gen_page') {
			$this->load->model('catalog/information');
			$informations = $this->model_catalog_information->getInformations(array());
			$counter = sizeof($informations);
			foreach ($informations as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();

				$desc = $this->model_catalog_information->getInformationDescriptions($value['information_id']);
				$data['id'] = $value['information_id'];
				$data['name'] = $desc[$language]['title'];

				$tpl = $template;
				$tpl = preg_replace('~{name}~Uis', $data['name'], $tpl);
				$tpl = preg_replace('~{id}~Uis', $data['id'], $tpl);
				$data['keyword'] = $this->convertFURL($tpl);
				if ($register_to_low == 'true') {
					$data['keyword'] = strtolower($data['keyword']);
				}

				$this->model_module_seomage->deleteInformationURL($data['id']);
				$idx = array_search($data['keyword'], $keywords['default']);
				if ($idx !== false) {
					unset($keywords['default'][$idx]);
				}

				if (in_array($data['keyword'], $keywords['seomage'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_seomage');
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_default');
				} else {
					$this->model_module_seomage->setInformationURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc[$language]['title'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}

		// Generation MANUFACTURERS links
		if ($_POST['id'] == 'gen_manufacturer') {
			$this->load->model('catalog/manufacturer');
			$manufacturers = $this->model_catalog_manufacturer->getManufacturers(array());
			$counter = sizeof($manufacturers);
			foreach ($manufacturers as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();

				$desc = $this->model_catalog_manufacturer->getManufacturer($value['manufacturer_id']);
				$data['id'] = $value['manufacturer_id'];
				$data['name'] = $desc['name'];

				$tpl = $template;
				$tpl = preg_replace('~{name}~Uis', $data['name'], $tpl);
				$tpl = preg_replace('~{id}~Uis', $data['id'], $tpl);
				$data['keyword'] = $this->convertFURL($tpl);
				if ($register_to_low == 'true') {
					$data['keyword'] = strtolower($data['keyword']);
				}

				$this->model_module_seomage->deleteManufacturerURL($data['id']);
				$idx = array_search($data['keyword'], $keywords['default']);
				if ($idx !== false) {
					unset($keywords['default'][$idx]);
				}

				if (in_array($data['keyword'], $keywords['seomage'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_seomage');
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = $this->language->get('text_find_dub_default');
				} else {
					$this->model_module_seomage->setManufacturerURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc['name'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}
		$report .= '</table>';

		
		if ($error_count > 0) {
			$json['fail'] = $this->language->get('text_error_dublicate') . $error_count . '. ' . $this->language->get('text_total_lines') . $counter;
		} else {
			$json['success'] = $this->language->get('text_gen_success') . $counter;
		}
		$json['report'] = $report;
		$json['error_count'] = $error_count;
     	$this->response->setOutput(json_encode($json));	
    }

	public function index() {
		// MODULE VERSION 
		$seomage_version = '1.0';

		$this->language->load('module/seomage');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('module/seomage');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (isset($this->request->post['keywords'])) {
				$this->model_module_seomage->saveKeywords($this->request->post['keywords']);
			} else {
				$this->model_module_seomage->clearKeywords();
			}
			
			$this->model_setting_setting->editSetting('seomage', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}


		$this->data['text_version'] = $this->language->get('text_version') . ' <b>' . $seomage_version . '</b>';


		$lang_vars = array(
			'heading_title',

			// Text
			'text_version_hint',
			'text_module',
			'text_success',
			'text_ajax_error',

			'button_add_keyword',
			'button_save',
			'button_cancel',
			'button_remove',
			'button_generate',

			'text_new_version_available',
			'text_check_version_fail',

			'tab_help',
			'tab_generation',
			'tab_generation_hint',
			'tab_log',
			'tab_log_hint',
			'tab_edit',
			'tab_edit_hint',

			'text_categories',
			'text_products',
			'text_manufacturers',
			'text_informations',
			'text_gen_categories',
			'text_gen_products',
			'text_gen_manufacturers',
			'text_gen_informations',
			'text_language',
			'text_template',
			'text_custom_template',
			'text_available_masks',
			'text_lowercase',
			'text_lowercase_hint',
			'text_report',
			'text_report_full',
			'text_report_error',
			'text_clear_log',
			'text_no_error',

			'text_mask_id',
			'text_mask_name',
			'text_mask_model',
			'text_mask_sku',
			'text_mask_manufacturer',
			'text_mask_category',

			// Entry
			'entry_status',
			'entry_checkupdate',
			'entry_debug',
			'entry_route',
			'entry_keyword',

			'text_help'

		);
		foreach ($lang_vars as $value) {
			$this->data[$value] = $this->language->get($value);
		}

		if ($this->config->get('seomage_checkupdate')) {
			// Check new version available
			$version_url = 'https://raw.githubusercontent.com/Negasus/oc.seomage/master/version.txt';
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $version_url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$version = curl_exec($curl);
			curl_close($curl);

			if ($version === false || substr($version, 0, 7) != 'SEOMAGE') {
				$this->data['text_version_hint'] = $this->language->get('text_check_version_fail');
			} elseif ($seomage_version !== substr($version, 7)) {
				$this->data['text_version_hint'] = $this->language->get('text_new_version_available') . substr($version, 7);
			}
		} else {
			$this->data['text_version_hint'] = '';
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/seomage', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('module/seomage', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['seomage_status'] = $this->config->get('seomage_status');
		$this->data['seomage_debug'] = $this->config->get('seomage_debug');
		$this->data['seomage_checkupdate'] = $this->config->get('seomage_checkupdate');

		$this->data['keywords'] = array();
		$this->data['keywords'] = $this->model_module_seomage->getKeywords();

		$this->data['logs'] = array();
		$this->data['logs'] = $this->model_module_seomage->getLogs();


		$this->template = 'module/seomage.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/seomage')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>
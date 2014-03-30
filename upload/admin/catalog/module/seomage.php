<?php
class ControllerModuleSeomage extends Controller {
	private $error = array(); 

	public function install() {
		$this->load->model('module/seomage');
		$this->model_module_seomage->install();
	}

	public function uninstall() {
		$this->load->model('module/seomage');
		$this->model_module_seomage->uninstall();
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('seomage', array("seomage_status" => 0));
	}

	private function convertFURL($s) {
		$cyrillic = array("а","б","в","г","д","е","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ь","ю","я","А","Б","В","Г","Д","Е","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ь","Ю","Я","Ї","ї","Є","є","Ы","ы","Ё","ё","ı","İ","ğ","Ğ","ü","Ü","ş","Ş","ö","Ö","ç","Ç","Á","á","Â","â","Ã","ã","À","à","Ç","ç","É","é","Ê","ê","Í","í","Ó","ó","Ô","ô","Õ","õ","Ú","ú");
		$latin = array("a","b","v","g","d","e","zh","z","i","y","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","sht","a","y","yu","ya","A","B","V","G","D","E","Zh","Z","I","Y","K","L","M","N","O","P","R","S","T","U","F","H","Ts","Ch","Sh","Sht","A","Y","Yu","Ya","I","i","Ye","ye","I","i","Yo","yo","i","I","g","G","u","U","s","S","o","O","c","C","A","a","A","a","A","a","A","a","C","c","E","e","E","e","I","i","O","o","O","o","O","o","U","u");

		$a = array();
		foreach ($cyrillic as $value) {
			$a[] = iconv('UTF-8//IGNORE', 'CP1251//IGNORE', $value);
		}
		$cyrillic = $a;

		$res = '';
		$arr = str_split(iconv('UTF-8', 'CP1251', $s));
		foreach ($arr as $value) {
			$ord = ord($value);
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
		$json['success'] = 'ok';
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

		$keywords = $this->model_module_seomage->getAllKeywords();

		if ($_POST['id'] == 'gen_product') {
			$this->load->model('catalog/product');
			$prods = $this->model_catalog_product->getProducts();
			$counter = sizeof($prods);
			foreach ($prods as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();
				// Наименование и id
				$desc = $this->model_catalog_product->getProductDescriptions($value['product_id']);
				$data['id'] = $value['product_id'];
				$data['name'] = $desc[$language]['name'];
				$data['model'] = $value['model'];
				$data['sku'] = $value['sku'];
				$data['manufacturer_id'] = $value['manufacturer_id'];
				$data['manufacturer'] = '';
				$data['category'] = '';

				// Производителя подгрузить
				if (strpos($template, '{manufacturer}') !== false) {
					$this->load->model('catalog/manufacturer');
					$m = $this->model_catalog_manufacturer->getManufacturer($data['manufacturer_id']);
					if (isset($m['name'])) {
						$data['manufacturer'] = $m['name'];
					} 
				}
				// Категорию подгрузить
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
					$error_text = 'Обнаружен дубликат, заданный в модуле SEO Mage. Ссылка не создана';
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = 'Обнаружен дубликат, ссылка не создана';
				} else {
					$this->model_module_seomage->setProductURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc[$language]['name'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}

		if ($_POST['id'] == 'gen_category') {
			$this->load->model('catalog/category');
			$categories = $this->model_catalog_category->getCategories(array());
			$counter = sizeof($categories);
			foreach ($categories as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();
				// Наименование и id
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
					$error_text = 'Обнаружен дубликат, заданный в модуле SEO Mage. Ссылка не создана';
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = 'Обнаружен дубликат, ссылка не создана';
				} else {
					$this->model_module_seomage->setCategoryURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc[$language]['name'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}

		if ($_POST['id'] == 'gen_page') {
			$this->load->model('catalog/information');
			$informations = $this->model_catalog_information->getInformations(array());
			$counter = sizeof($informations);
			foreach ($informations as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();
				// Наименование и id
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
					$error_text = 'Обнаружен дубликат, заданный в модуле SEO Mage. Ссылка не создана';
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = 'Обнаружен дубликат, ссылка не создана';
				} else {
					$this->model_module_seomage->setInformationURL($data);
					$keywords['default'][] = $data['keyword'];
				}

				if ($report_type == 'full' || $error_row) {
					$report .= '<tr><td>'.$desc[$language]['title'].'</td><td>'.$data['keyword'].'</td><td style="color:red">'.$error_text.'</td></tr>';
				}
			}
		}



		if ($_POST['id'] == 'gen_manufacturer') {
			$this->load->model('catalog/manufacturer');
			$manufacturers = $this->model_catalog_manufacturer->getManufacturers(array());
			$counter = sizeof($manufacturers);
			foreach ($manufacturers as $key => $value) {
				$error_row = false;
				$error_text = '';
				$data = array();
				// Наименование и id
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
					$error_text = 'Обнаружен дубликат, заданный в модуле SEO Mage. Ссылка не создана';
				} else
				if (in_array($data['keyword'], $keywords['default'])) {
					$error_row = true;
					$error_count++;
					$error_text = 'Обнаружен дубликат, ссылка не создана';
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
			$json['fail'] = 'Обнаружены дублирующие ссылки. Количество: ' . $error_count. '. Всего обработано строк: ' . $counter;
		} else {
			$json['success'] = 'Ссылки успешно сгенерированы. Количество: ' . $counter;
		}
		$json['report'] = $report;
		$json['error_count'] = $error_count;
     	$this->response->setOutput(json_encode($json));	
    }

	public function index() {   
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


		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['seomage_version'] = $this->language->get('text_version') . ' ' . $this->language->get('seomage_version');
		$this->data['text_version_hint'] = '';
		$this->data['text_ajax_error'] = $this->language->get('text_ajax_error');

		// Провека версии
		$version_url = 'https://raw.githubusercontent.com/Negasus/oc.seomage/master/version.txt';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $version_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$version = curl_exec($curl);
		curl_close($curl);

		if ($this->language->get('seomage_version') !== $version) {
			$this->data['text_version_hint'] = $this->language->get('text_new_version_available');
		}

		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_debug'] = $this->language->get('entry_debug');
		$this->data['entry_route'] = $this->language->get('entry_route');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword'); 

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_keyword'] = $this->language->get('button_add_keyword');
		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['tab_edit'] = $this->language->get('tab_edit');
		$this->data['tab_edit_hint'] = $this->language->get('tab_edit_hint');
		$this->data['tab_generation'] = $this->language->get('tab_generation');
		$this->data['tab_help'] = $this->language->get('tab_help');
		$this->data['tab_log'] = $this->language->get('tab_log');

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
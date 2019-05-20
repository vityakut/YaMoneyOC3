<?php
class ControllerExtensionPaymentYandexmoney extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/payment/yandexmoney');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('payment_yandexmoney', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true));
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['payment_wallet'])) {
			$data['error_payment_wallet'] = $this->error['payment_wallet'];
		} else {
			$data['error_payment_wallet'] = '';
		}

		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/yandexmoney', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['entry_wallet'] = $this->language->get('entry_wallet');
		$data['entry_result_url'] = $this->language->get('entry_result_url');
		$data['entry_success_url'] = $this->language->get('entry_success_url');
		$data['entry_fail_url'] = $this->language->get('entry_fail_url');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['help_wallet'] = $this->language->get('help_wallet');
		$data['action'] = $this->url->link('extension/payment/yandexmoney', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true);

		if (isset($this->request->post['payment_yandexmoney_wallet'])) {
			$data['payment_yandexmoney_wallet'] = $this->request->post['payment_yandexmoney_wallet'];
		} else {
			$data['payment_yandexmoney_wallet'] = $this->config->get('payment_yandexmoney_wallet');
		}

		
		$data['payment_yandexmoney_result_url'] 		= HTTP_CATALOG . 'index.php?route=extension/payment/yandexmoney/result';
		$data['payment_yandexmoney_success_url'] 	= HTTP_CATALOG . 'index.php?route=extension/payment/yandexmoney/success';
		$data['payment_yandexmoney_fail_url'] 		= HTTP_CATALOG . 'index.php?route=extension/payment/yandexmoney/fail';
		
		if (isset($this->request->post['payment_yandexmoney_test'])) {
			$data['payment_yandexmoney_test'] = $this->request->post['payment_yandexmoney_test'];
		} else {
			$data['payment_yandexmoney_test'] = $this->config->get('payment_yandexmoney_test');
		}

		if (isset($this->request->post['payment_yandexmoney_order_status_id'])) {
			$data['payment_yandexmoney_order_status_id'] = $this->request->post['payment_yandexmoney_order_status_id'];
		} else {
			$data['payment_yandexmoney_order_status_id'] = $this->config->get('payment_yandexmoney_order_status_id'); 
		}
		
		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_yandexmoney_geo_zone_id'])) {
			$data['payment_yandexmoney_geo_zone_id'] = $this->request->post['payment_yandexmoney_geo_zone_id'];
		} else {
			$data['payment_yandexmoney_geo_zone_id'] = $this->config->get('payment_yandexmoney_geo_zone_id'); 
		}
		
		$this->load->model('localisation/geo_zone');
		
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		
		
		if (isset($this->request->post['payment_yandexmoney_status'])) {
			$data['payment_yandexmoney_status'] = $this->request->post['payment_yandexmoney_status'];
		} else {
			$data['payment_yandexmoney_status'] = $this->config->get('payment_yandexmoney_status');
		}
		
		if (isset($this->request->post['payment_yandexmoney_sort_order'])) {
			$data['payment_yandexmoney_sort_order'] = $this->request->post['payment_yandexmoney_sort_order'];
		} else {
			$data['payment_yandexmoney_sort_order'] = $this->config->get('payment_yandexmoney_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/yandexmoney', $data));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/yandexmoney')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_yandexmoney_wallet']) {
			$this->error['payment_wallet'] = $this->language->get('error_payment_wallet');
		}
		

		
		return !$this->error;
	}
}
<?php

class ControllerExtensionPaymentYandexmoney extends Controller
{
	public function index()
	{

		$data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['payment_url'] = 'https://money.yandex.ru/quickpay/confirm.xml';

		$data['receiver'] = $this->config->get('payment_yandexmoney_wallet');

		$data['quickpay_form'] = 'shop';

		$data['targets'] = 'Заказ №' . $this->session->data['order_id'];

		$data['label'] = $this->session->data['order_id'];

		$data['paymentType'] = 'AC';

		$data['formcomment'] = 'Покупка в ' . $this->config->get('config_name');

		$data['short_dest'] = 'Покупка в ' . $this->config->get('config_name');

		$data['successURL'] = HTTP_SERVER . 'index.php?route=extension/payment/yandexmoney/success';

		$rur_code = 'RUB';

		$rur_order_total = $this->currency->convert($order_info['total'], $order_info['currency_code'], $rur_code);

		$data['sum'] = $this->currency->format($rur_order_total, $rur_code, $order_info['currency_value'], FALSE);



		return $this->load->view('extension/payment/yandexmoney', $data);
	}

	public function success()
	{


		if ($this->config->get('payment_yandexmoney_test')) {
			$password_1 = $this->config->get('payment_yandexmoney_test_password_1');
		} else {
			$password_1 = $this->config->get('payment_yandexmoney_password_1');
		}


		$out_summ = $this->request->post['OutSum'];
		$order_id = $this->request->post["InvId"];
		$crc = $this->request->post["SignatureValue"];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5($out_summ . ":" . $order_id . ":" . $password_1 . ":Shp_item=1"));

		if ($my_crc == $crc) {
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info['order_status_id'] == 0) {
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
			}

			$this->response->redirect($this->url->link('checkout/success', '', true));

		} else {

			$this->log->write('yandexmoney ошибка в заказе: ' . $order_id . 'Контрольные суммы не совпадают');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$this->response->redirect($this->url->link('error/error', '', true));

		}

		return true;
	}

	public function fail()
	{

		$this->response->redirect($this->url->link('checkout/checkout', '', true));

		return true;
	}

	public function result()
	{

		if ($this->config->get('payment_yandexmoney_test')) {
			$password_2 = $this->config->get('payment_yandexmoney_test_password_2');
		} else {
			$password_2 = $this->config->get('payment_yandexmoney_password_2');
		}

		$out_summ = $this->request->post['OutSum'];
		$order_id = $this->request->post["InvId"];
		$crc = $this->request->post["SignatureValue"];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5($out_summ . ":" . $order_id . ":" . $password_2 . ":Shp_item=1"));

		if ($my_crc == $crc) {
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);
			$new_order_status_id = $this->config->get('payment_yandexmoney_order_status_id');

			if ($order_info['order_status_id'] == 0) {
				$this->model_checkout_order->addOrderHistory($order_id, $new_order_status_id);
			}

			if ($order_info['order_status_id'] != $new_order_status_id) {
				$this->model_checkout_order->addOrderHistory($order_id, $new_order_status_id);

				if ($this->config->get('payment_yandexmoney_test')) {
					$this->log->write('yandexmoney в заказе: ' . $order_id . '. Статус заказа успешно изменен');
				}

			}


			return true;
		} else {

			if ($this->config->get('payment_yandexmoney_test')) {
				$this->log->write('yandexmoney ошибка в заказе: ' . $order_id . '. Контрольные суммы не совпадают');
			}

		}

	}
}
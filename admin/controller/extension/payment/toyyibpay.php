<?php

/**
 * toyyibPay OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author toyyibPay Team
 * @version 2.0.0
 */

class ControllerExtensionPaymentToyyibpay extends Controller
{

    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/toyyibpay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('toyyibpay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
			$status= 0;
			if($this->request->post['toyyibpay_status'] != ''){
				$status = $this->request->post['toyyibpay_status'];
			}
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` (`setting_id`, `store_id`, `code`, `key`, `value`, `serialized`) VALUES (NULL, '0', 'toyyibpay', 'payment_toyyibpay_status', '".$status."', '0')");
			
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['toyyibpay_api_key'] = $this->language->get('toyyibpay_api_key');
        $data['toyyibpay_category_code'] = $this->language->get('toyyibpay_category_code');
		$data['toyyibpay_api_environment'] = $this->language->get('toyyibpay_api_environment');
        $data['toyyibpay_payment_channel'] = $this->language->get('toyyibpay_payment_channel');
        $data['toyyibpay_payment_charge'] = $this->language->get('toyyibpay_payment_charge');
        $data['toyyibpay_company_email'] = $this->language->get('toyyibpay_company_email');
        $data['toyyibpay_company_phone'] = $this->language->get('toyyibpay_company_phone');
        $data['toyyibpay_extra_email'] = $this->language->get('toyyibpay_extra_email');
		
        $data['entry_minlimit'] = $this->language->get('entry_minlimit');
        $data['entry_completed_status'] = $this->language->get('entry_completed_status');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['help_api_key'] = $this->language->get('help_api_key');
		$data['help_api_environment'] = $this->language->get('help_api_environment');
        $data['help_payment_channel'] = $this->language->get('help_payment_channel');
        $data['help_payment_charge'] = $this->language->get('help_payment_charge');
        $data['help_company_email'] = $this->language->get('help_company_email');
        $data['help_company_phone'] = $this->language->get('help_company_phone');
        $data['help_extra_email'] = $this->language->get('help_extra_email');
		$data['help_category_code'] = $this->language->get('help_category_code');
        $data['help_minlimit'] = $this->language->get('help_minlimit');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['api_key'])) {
            $data['error_api_key'] = $this->error['api_key'];
        } else {
            $data['error_api_key'] = '';
        }

        if (isset($this->error['api_environment'])) {
            $data['error_api_environment'] = $this->error['api_environment'];
        } else {
            $data['error_api_environment'] = '';
        }

        if (isset($this->error['payment_channel'])) {
            $data['error_payment_channel'] = $this->error['payment_channel'];
        } else {
            $data['error_payment_channel'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/toyyibpay', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/toyyibpay', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['toyyibpay_api_key_value'])) {
            $data['toyyibpay_api_key_value'] = $this->request->post['toyyibpay_api_key_value'];
        } else {
            $data['toyyibpay_api_key_value'] = $this->config->get('toyyibpay_api_key_value');
        }

        if (isset($this->request->post['toyyibpay_category_code_value'])) {
            $data['toyyibpay_category_code_value'] = $this->request->post['toyyibpay_category_code_value'];
        } else {
            $data['toyyibpay_category_code_value'] = $this->config->get('toyyibpay_category_code_value');
        }

        if (isset($this->request->post['toyyibpay_api_environment_value'])) {
            $data['toyyibpay_api_environment_value'] = $this->request->post['toyyibpay_api_environment_value'];
        } else {
            $data['toyyibpay_api_environment_value'] = $this->config->get('toyyibpay_api_environment_value');
        }

        if (isset($this->request->post['toyyibpay_payment_channel_value'])) {
            $data['toyyibpay_payment_channel_value'] = $this->request->post['toyyibpay_payment_channel_value'];
        } else {
            $data['toyyibpay_payment_channel_value'] = $this->config->get('toyyibpay_payment_channel_value');
        }

        if (isset($this->request->post['toyyibpay_payment_charge_value'])) {
            $data['toyyibpay_payment_charge_value'] = $this->request->post['toyyibpay_payment_charge_value'];
        } else {
            $data['toyyibpay_payment_charge_value'] = $this->config->get('toyyibpay_payment_charge_value');
        }
		
		if (isset($this->request->post['toyyibpay_company_email_value'])) {
            $data['toyyibpay_company_email_value'] = $this->request->post['toyyibpay_company_email_value'];
        } else {
            $data['toyyibpay_company_email_value'] = $this->config->get('toyyibpay_company_email_value');
        }
		
		if (isset($this->request->post['toyyibpay_company_phone_value'])) {
            $data['toyyibpay_company_phone_value'] = $this->request->post['toyyibpay_company_phone_value'];
        } else {
            $data['toyyibpay_company_phone_value'] = $this->config->get('toyyibpay_company_phone_value');
        }
		
        if (isset($this->request->post['toyyibpay_extra_email_value'])) {
            $data['toyyibpay_extra_email_value'] = $this->request->post['toyyibpay_extra_email_value'];
        } else {
            $data['toyyibpay_extra_email_value'] = $this->config->get('toyyibpay_extra_email_value');
        }

        if (isset($this->request->post['toyyibpay_minlimit'])) {
            $data['toyyibpay_minlimit'] = $this->request->post['toyyibpay_minlimit'];
        } else {
            $data['toyyibpay_minlimit'] = $this->config->get('toyyibpay_minlimit');
        }

        if (isset($this->request->post['toyyibpay_completed_status_id'])) {
            $data['toyyibpay_completed_status_id'] = $this->request->post['toyyibpay_completed_status_id'];
        } else {
            $data['toyyibpay_completed_status_id'] = $this->config->get('toyyibpay_completed_status_id');
        }
		

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['toyyibpay_geo_zone_id'])) {
            $data['toyyibpay_geo_zone_id'] = $this->request->post['toyyibpay_geo_zone_id'];
        } else {
            $data['toyyibpay_geo_zone_id'] = $this->config->get('toyyibpay_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['toyyibpay_status'])) {
            $data['toyyibpay_status'] = $this->request->post['toyyibpay_status'];
        } else {
            $data['toyyibpay_status'] = $this->config->get('toyyibpay_status');
        }

        if (isset($this->request->post['toyyibpay_sort_order'])) {
            $data['toyyibpay_sort_order'] = $this->request->post['toyyibpay_sort_order'];
        } else {
            $data['toyyibpay_sort_order'] = $this->config->get('toyyibpay_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/toyyibpay', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/toyyibpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['toyyibpay_api_key_value']) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }
		
        if (!$this->request->post['toyyibpay_api_environment_value']) {
            $this->error['api_environment'] = $this->language->get('error_api_environment');
        }
		
        return !$this->error;
    }
}

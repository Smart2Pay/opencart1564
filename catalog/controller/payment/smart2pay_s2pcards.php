<?php

class ControllerPaymentSmart2payS2pcards extends Controller {
    /**
     * Index action
     *  Used within checkout flow
     */
    protected function index() {

        $this->load->model('payment/smart2pay');
        $this->load->model('payment/smart2pay_s2pcards');
        $this->load->model('account/address');
        $this->load->language('payment/smart2pay');

        /*
         * Get address
         */
        if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
            $payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
        } elseif (isset($this->session->data['guest'])) {
            $payment_address = $this->session->data['guest']['payment'];
        }

        /*
         * Set template data
         */
        $language = new Language(DIR_LANGUAGE);
        $translations = $language->load("payment/smart2pay");
        $this->data['trans'] = $translations;

        $this->data['methods']  = $this->model_payment_smart2pay->getActiveMethods($payment_address, true);

        /*
         * Set checkout method id
         *   - this might be set by s2p checkout helper in checkout step before last one
         */
        //$this->data['checkout_method_id'] = isset($_SESSION['smart2pay_checkout_method_id']) ? $_SESSION['smart2pay_checkout_method_id'] : null;
        $this->data['checkout_method_id'] = $this->model_payment_smart2pay_s2pcards->getMethodId();

        /*
         * Set base URL
         */
        $server_base = null;
        if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $server_base = HTTP_SERVER;
        } else {
            $server_base = HTTPS_SERVER;
        }

        if (is_dir(DIR_TEMPLATE . $this->config->get('config_template') . '/image/payment/smart2pay')) {
            $this->template = $this->config->get('config_template') . '/template/payment/smart2pay.tpl';
            $this->data['base_img_url'] = $server_base . 'catalog/view/theme/' . $this->config->get('config_template') . '/image/payment/smart2pay/methods/';
        } else {
            $this->data['base_img_url'] = $server_base . 'catalog/view/theme/default/image/payment/smart2pay/methods/';
        }

        /*
         * Prepare template
         */
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/smart2pay.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/smart2pay.tpl';
        } else {
            $this->template = 'default/template/payment/smart2pay.tpl';
        }

        $this->render();
    }
}

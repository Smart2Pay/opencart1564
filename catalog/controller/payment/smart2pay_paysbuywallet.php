<?php

class ControllerPaymentSmart2payPaysbuywallet extends Controller {
    /**
     * Index action
     *  Used within checkout flow
     */
    public function index() {

        $this->load->model('payment/smart2pay');
        $this->load->model('payment/smart2pay_paysbuywallet');
        $this->load->model('account/address');
        $this->load->language('payment/smart2pay');

        /*
         * Set template data
         */
        $language = new Language(DIR_LANGUAGE);
        $translations = $language->load("payment/smart2pay");
        $data['trans'] = $translations;

        /*
         * Set checkout method id
         *   - this might be set by s2p checkout helper in checkout step before last one
         */
        $data['checkout_method_id'] = $this->model_payment_smart2pay_paysbuywallet->getMethodId();

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
            $data['base_img_url'] = $server_base . 'catalog/view/theme/' . $this->config->get('config_template') . '/image/payment/smart2pay/methods/';
        } else {
            $data['base_img_url'] = $server_base . 'catalog/view/theme/default/image/payment/smart2pay/methods/';
        }

        /*
         * Prepare template
         */
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/smart2pay.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/smart2pay.tpl';
        } else {
            $this->template = 'default/template/payment/smart2pay.tpl';
        }

        return $this->load->view($this->template, $data);
    }
}

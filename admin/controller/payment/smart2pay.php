<?php

class ControllerPaymentSmart2pay extends Controller {

    private $error = array();

	public function index() {

        $this->load->language('payment/smart2pay');

        $this->load->model('setting/setting');

        $this->load->model('smart2pay/payment_method');

        $this->data['error'] = array();

        /*
         * Save POST data if valid
         */
        $this->data['form'] = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('smart2pay', array_merge($this->data['form'], $this->request->post));

            $this->session->data['success'] = 'Success: You have modified Smart2Pay settings!';

            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        /*
         * Set form elements
         */
        $formElements = $this->model_smart2pay_payment_method->getSettingsList();

        $savedSettings = $this->model_setting_setting->getSetting('smart2pay');
        if ($savedSettings) {
            foreach ($savedSettings as $key => $val) {
                if (isset($formElements[$key])) {
                    $formElements[$key]['value'] = $val;
                }
            }
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->request->post) {
                foreach ($this->request->post as $key => $val) {
                    if (isset($formElements[$key])) {
                        $formElements[$key]['value'] = $val;
                    }
                }
            }
        }

        $this->data['form_elements'] = $formElements;

        /*
         * Set logs
         */
        $this->data['logs'] = $this->model_smart2pay_payment_method->getLogs();

        /*
         * Set links
         */
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action'] = $this->url->link('payment/smart2pay', 'token=' . $this->session->data['token'], 'SSL');

        /*
         * Set validation errors and warnings
         */
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        /*
         * Set breadcrumbs
         */
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/smart2pay', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );


        /*
         * Prepare templates
         */
        $this->template = 'payment/smart2pay.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        /*
         * Render
         */
        $this->response->setOutput($this->render());
	}

    /**
     * Validate post data
     *
     * @return bool
     */
    private function validate() {

		if ( ! $this->user->hasPermission('modify', 'payment/pp_standard')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        $this->data['error'] = array();

        /*
         * Prevent case when user deselects all (checkboxes), when key in post is not set and settings keep previous state - which would be wrong
         */
        if ( ! isset($this->request->post['smart2pay_active_methods'])) {
            $this->data['form'] = array(
                'smart2pay_active_methods' => array()
            );
        }

        if ($this->request->post['smart2pay_status']) {
            switch ($this->request->post['smart2pay_env']) {
                /*
                 * Test
                 */
                case 0:
                    if (
                        ! isset($this->request->post['smart2pay_post_url_test']) ||
                        ! filter_var($this->request->post['smart2pay_post_url_test'], FILTER_VALIDATE_URL)
                    ) {
                        $this->data['error']['smart2pay_post_url_test'] = "Invalid Post URL";
                    }
                    if ( ! isset($this->request->post['smart2pay_signature_test']) || ! $this->request->post['smart2pay_signature_test']) {
                        $this->data['error']['smart2pay_signature_test'] = "Invalid Signature";
                    }
                    if (! isset($this->request->post['smart2pay_mid_test']) || ! $this->request->post['smart2pay_mid_test']) {
                        $this->data['error']['smart2pay_mid_test'] = "Invalid MID";
                    }
                    break;
                /*
                 * Live
                 */
                case 1:
                    if (
                        ! isset($this->request->post['smart2pay_post_url_live']) ||
                        ! filter_var($this->request->post['smart2pay_post_url_live'], FILTER_VALIDATE_URL)
                    ) {
                        $this->data['error']['smart2pay_post_url_live'] = "Invalid Post URL";
                    }
                    if ( ! isset($this->request->post['smart2pay_signature_live']) || ! $this->request->post['smart2pay_signature_live']) {
                        $this->data['error']['smart2pay_signature_live'] = "Invalid Signature";
                    }
                    if (! isset($this->request->post['smart2pay_mid_live']) || ! $this->request->post['smart2pay_mid_live']) {
                        $this->data['error']['smart2pay_mid_live'] = "Invalid MID";
                    }
                    break;
            }

            if (
                ! isset($this->request->post['smart2pay_return_url']) ||
                ! filter_var($this->request->post['smart2pay_return_url'], FILTER_VALIDATE_URL)
            ) {
                $this->data['error']['smart2pay_return_url'] = "Invalid Return URL";
            }
        }

		if ( ! $this->data['error']) {
			return true;
		} else {
            $this->error['warning'] = "There has been some problems saving your settings. Please check the form!";
			return false;
		}
	}

    /**
     * Install extension
     */
    public function install()
    {
        $this->load->model('smart2pay/payment_extension');
        $this->model_smart2pay_payment_extension->install();
    }

    /**
     * Uninstall extension
     */
    public function uninstall()
    {
        $this->load->model('smart2pay/payment_extension');
        $this->model_smart2pay_payment_extension->uninstall();
    }
}
?>
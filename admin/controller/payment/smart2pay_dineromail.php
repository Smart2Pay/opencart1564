<?php

class ControllerPaymentSmart2payDineromail extends Controller {

    private $error = array();

    protected $methodName = 'dineromail';

	public function index() {

        $this->load->model('setting/setting');

        $this->load->language('payment/smart2pay');

        $this->data['error'] = array();

        /*
         * Save POST data if valid
         */
        $this->data['form'] = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('smart2pay_' . $this->methodName, array_merge($this->data['form'], $this->request->post));

            $this->session->data['success'] = 'Success: You have modified Smart2Pay ' . ucfirst($this->methodName) . ' settings!';

            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        /*
         * Set form elements
         */
        $formElements = array(
            "smart2pay_" . $this->methodName . "_status" =>
                array(
                    "label"   => "Enabled",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                )
        );

        $savedSettings = $this->model_setting_setting->getSetting('smart2pay_' . $this->methodName);

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
         * Set links
         */
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action'] = $this->url->link('payment/smart2pay_' . $this->methodName, 'token=' . $this->session->data['token'], 'SSL');

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
            'href'      => $this->url->link('payment/smart2pay_' . $this->methodName, 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );


        /*
         * Prepare templates
         */
        $this->template = 'payment/smart2pay_payment_method.tpl';
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

		if ( ! $this->user->hasPermission('modify', 'payment/smart2pay_' . $this->methodName)) {
			$this->error['warning'] = $this->language->get('error_permission');
            return false;
		}

        $this->data['error'] = array();

        return true;
	}

    /**
     * Install extension
     */
    public function install(){}

    /**
     * Uninstall extension
     */
    public function uninstall(){}
}
?>
<?php

class ControllerPaymentSmart2payGiropay extends Controller {

    private $error = array();

    protected $methodName = 'giropay';

	public function index() {

        $this->load->model('setting/setting');

        $this->load->language('payment/smart2pay');

        $data['error'] = array();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit') . " (" . ucwords($this->methodName) . ")";
        $data['btn_text_save'] = $this->language->get('btn_text_save');
        $data['btn_text_cancel'] = $this->language->get('btn_text_cancel');

        /*
         * Save POST data if valid
         */
        $data['form'] = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('smart2pay_' . $this->methodName, array_merge($data['form'], $this->request->post));

            $this->session->data['success'] = 'Success: You have modified Smart2Pay ' . ucfirst($this->methodName) . ' settings!';

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
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

        $data['form_elements'] = $formElements;

        /*
         * Set links
         */
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
        $data['action'] = $this->url->link('payment/smart2pay_' . $this->methodName, 'token=' . $this->session->data['token'], 'SSL');

        /*
         * Set validation errors and warnings
         */
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        /*
         * Set breadcrumbs
         */
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/smart2pay_' . $this->methodName, 'token=' . $this->session->data['token'], 'SSL')
        );


        /*
         * Prepare templates
         */
        $this->template = 'payment/smart2pay_payment_method.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        /*
         * Render
         */
        $data['error'] = $this->error;
        $this->response->setOutput($this->load->view('payment/smart2pay_payment_method.tpl', $data));
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

        $this->error = array();

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
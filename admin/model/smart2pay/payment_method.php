<?php 
class ModelSmart2payPaymentMethod extends Model {

    /**
     * Get all methods
     *
     * @return array
     */
    public function getMethods(array $data = array())
    {
        $methods = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "smart2pay_method ORDER BY display_name");

        foreach ($query->rows as $method) {
            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * Get logs
     *
     * @return array
     */
    public function getLogs()
    {
        $logs = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "smart2pay_log ORDER BY log_created DESC");

        foreach ($query->rows as $method) {
            $logs[] = $method;
        }

        return $logs;
    }

    /**
     * Get module settings
     *
     * @return array
     */
    public function getSettingsList()
    {
    	$server_base = null;
    	if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$server_base = HTTP_CATALOG;
		} else 
		{
				$server_base = HTTPS_CATALOG;
		}
    	
    	
        $moduleSettings = array(
            "smart2pay_status" =>
                array(
                    "label"   => "Enabled",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_env" =>
                array(
                    "label"     => "Environment",
                    "type"      => "select",
                    "options"   =>
                        array(
                            0 => "Test",
                            1 => "Live"
                        ),
                    "value" => 0
                ),
            "smart2pay_post_url_live" =>
                array(
                    "label" => "Post URL Live",
                    "type"  => "text",
                    "value" => "https://api.smart2pay.com"
                ),
            "smart2pay_post_url_test" =>
                array(
                    "label" => "Post URL Test",
                    "type"  => "text",
                    "value" => "https://apitest.smart2pay.com"
                ),
            "smart2pay_signature_live" =>
                array(
                    "label" => "Signature Live",
                    "type"  => "text",
                    "value" => null
                ),
            "smart2pay_signature_test" =>
                array(
                    "label" => "Signature Test",
                    "type"  => "text",
                    "value" => null
                ),
            "smart2pay_mid_live" =>
                array(
                    "label" => "MID Live",
                    "type"  => "text",
                    "value" => null
                ),
            "smart2pay_mid_test" =>
                array(
                    "label" => "MID Test",
                    "type"  => "text",
                    "value" => null
                ),
            "smart2pay_return_url" =>
                array(
                    "label" => "Return URL",
                    "type"  => "text",
                    "value" => $server_base . "index.php?route=payment/smart2pay/feedback"
                ),
            /*"smart2pay_title" =>
                array(
                    "label" => "Title",
                    "type"  => "text",
                    "value" => "Smart2Pay"
                ),
            "smart2pay_pre_display_methods" =>
                array(
                    "label" => "Pre Display Methods",
                    "type"  => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value"    => 0
                ),
            "smart2pay_active_methods" =>
                array(
                    "label"   => "Active methods",
                    "type"    => "checkbox",
                    "options" =>
                        array(
                            0 => "Method 1",
                            1 => "Method 2"
                        ),
                    "value"    => null,
                    "multiple" => true
                ),
            "smart2pay_methods_display_mode" =>
                array(
                    "label"   => "Methods display mode",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Logo",
                            1 => "Text",
                            2 => "Logo and Text"
                        ),
                    "value" => 0
                ),
            "smart2pay_show_methods_in_grid" =>
                array(
                    "label"   => "Show methods in grid",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_grid_column_number" =>
                array(
                    "label" => "Grid column number",
                    "type"  => "text",
                    "value" => 2
                ),
            "smart2pay_auto_select" =>
                array(
                    "label"   => "Auto select Smart2Pay",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_send_payment_method" =>
                array(
                    "label"   => "Send payment method",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            */
            "smart2pay_send_order_number_as_product_description" =>
                array(
                    "label"   => "Send order number as product description",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_custom_product_description" =>
                array(
                    "label" => "Custom product description",
                    "type"  => "textarea",
                    "value" => null
                ),
            "smart2pay_notify_customer_by_email" =>
                array(
                    "label"   => "Notify customer by email",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
//            "smart2pay_create_invoice_on_success" =>
//                array(
//                    "label"   => "Create invoice on success",
//                    "type"    => "select",
//                    "options" =>
//                        array(
//                            0 => "No",
//                            1 => "Yes"
//                        ),
//                    "value" => 0
//                ),
//            "smart2pay_automate_shipping" =>
//                array(
//                    "label"   => "Automate shipping",
//                    "type"    => "select",
//                    "options" =>
//                        array(
//                            0 => "No",
//                            1 => "Yes"
//                        ),
//                    "value" => 0
//                ),
 			"smart2pay_order_confirm" =>
                array(
                    "label"   => "Confirm order",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Only when paid",
                            1 => "On final status",
                            2 => "On redirect",
                            3 => "On initiate"
                        ),
                    "value" => 0
                ),
            "smart2pay_order_status_new" =>
                array(
                    "label"   => "Order status when NEW",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Status 1",
                            1 => "Status 2"
                        ),
                    "value" => 1
                ),
            "smart2pay_order_status_success" =>
                array(
                    "label"   => "Order status when SUCCESS",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Status 1",
                            1 => "Status 2"
                        ),
                    "value" => 1
                ),
            "smart2pay_order_status_canceled" =>
                array(
                    "label"   => "Order status when CANCEL",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Status 1",
                            1 => "Status 2"
                        ),
                    "value" => 1
                ),
            "smart2pay_order_status_failed" =>
                array(
                    "label"   => "Order status when FAIL",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Status 1",
                            1 => "Status 2"
                        ),
                    "value" => 1
                ),
            "smart2pay_order_status_expired" =>
                array(
                    "label"   => "Order status on EXPIRED",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "Status 1",
                            1 => "Status 2"
                        ),
                    "value" => 1
                ),
            "smart2pay_skip_payment_page" =>
                array(
                    "label"   => "Skip Payment Page",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_redirect_in_iframe" =>
                array(
                    "label"   => "Redirect In IFrame",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_site_id" =>
                array(
                    "label" => "Site ID",
                    "type"  => "text",
                    "value" => null
                ),
			"smart2pay_skin_id" =>
                array(
                    "label" => "Skin ID",
                    "type"  => "text",
                    "value" => null
                ),
            "smart2pay_debug_form" =>
                array(
                    "label"   => "[Debug Form]",
                    "type"    => "select",
                    "options" =>
                        array(
                            0 => "No",
                            1 => "Yes"
                        ),
                    "value" => 0
                ),
            "smart2pay_sort_order" =>
                array(
                    "label"   => "Sort Order",
                    "type"    => "text",
                    "value"   => ""
                ),
        );

        $this->load->model('localisation/order_status');

        /*
         * Get order statuses
         */
        $orderStatuses = $this->model_localisation_order_status->getOrderStatuses();
        $orderStatusesIndexed = array();
        foreach($orderStatuses as $status) {
            $orderStatusesIndexed[$status['order_status_id']] = $status['name'];
        }

        $moduleSettings['smart2pay_order_status_new']['options']     = $orderStatusesIndexed;
        $moduleSettings['smart2pay_order_status_success']['options'] = $orderStatusesIndexed;
        $moduleSettings['smart2pay_order_status_canceled']['options']  = $orderStatusesIndexed;
        $moduleSettings['smart2pay_order_status_failed']['options']    = $orderStatusesIndexed;
        $moduleSettings['smart2pay_order_status_expired']['options'] = $orderStatusesIndexed;

        /*
         * Get payment methods
         *
        $methods = $this->getMethods();
        $moduleSettings['smart2pay_active_methods']['options'] = array();
        foreach ($methods as $method) {
            $moduleSettings['smart2pay_active_methods']['options'][$method['method_id']] = $method['display_name'];
        }
        $moduleSettings['smart2pay_active_methods']['value'] = array_keys($moduleSettings['smart2pay_active_methods']['options']);
        */

        return $moduleSettings;
    }
}
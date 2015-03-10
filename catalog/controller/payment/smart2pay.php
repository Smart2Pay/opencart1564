<?php
class ControllerPaymentSmart2pay extends Controller {
    /**
     * Index action
     *  Used within checkout flow
     */
    protected function index() {

        $this->load->model('payment/smart2pay');
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
        $data['trans'] = $translations;

        $data['methods']  = $this->model_payment_smart2pay->getActiveMethods($payment_address, true);

        /*
         * Set checkout method id
         *   - this might be set by s2p checkout helper in checkout step before last one
         */
        $data['checkout_method_id'] = isset($_SESSION['smart2pay_checkout_method_id']) ? $_SESSION['smart2pay_checkout_method_id'] : null;

        /*
         * Set base URL
         */
         
        $server_base = null;
    	if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$server_base = HTTP_SERVER;
		} else 
		{
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

    /**
     * Pay action
     *  It handles the flow after checkout is finished
     */
    public function pay()
    {
        $this->load->model('setting/setting');
        $this->load->model('payment/smart2pay');
        $this->load->model('account/address');
        $this->load->model('checkout/order');

        $this->load->language('payment/smart2pay');

        if ( ! empty($_SESSION['smart2pay_checkout_method_id'])) {
            unset($_SESSION['smart2pay_checkout_method_id']);
        }

        /*
         * Get address
         */
        $payment_address = null;

        if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
            $payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
        } elseif (isset($this->session->data['guest'])) {
            $payment_address = $this->session->data['guest']['payment'];
        }

        /*
         * Check pay request
         */
        $this->checkPayRequest($payment_address);

        /*
         * Set data
         */
        $settings = $this->model_setting_setting->getSetting('smart2pay');
        if ($settings['smart2pay_env']) {
            $settings['smart2pay_post_url']  = $settings['smart2pay_post_url_live'];
            $settings['smart2pay_mid']       = $settings['smart2pay_mid_live'];
            $settings['smart2pay_signature'] = $settings['smart2pay_signature_live'];
        } else {
            $settings['smart2pay_post_url']  = $settings['smart2pay_post_url_test'];
            $settings['smart2pay_mid']       = $settings['smart2pay_mid_test'];
            $settings['smart2pay_signature'] = $settings['smart2pay_signature_test'];
        }
        if ($settings['smart2pay_send_order_number_as_product_description']) {
            $settings['smart2pay_product_description'] = "Ref. no.: " . $this->session->data['order_id'];
        } else {
            $settings['smart2pay_product_description'] = $settings['smart2pay_custom_product_description'];
        }

        $data['settings'] = $settings;

        $order      = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $orderTotal = round($order['total'] * $order['currency_value'] * 100);

	$skipHpp = $settings['smart2pay_skip_payment_page'];

	if($this->request->get['method'] === '1' || $this->request->get['method'] === '20')
		$skipHpp = 0;

        $data['payment_data'] = array(
            'MerchantID'       => $settings['smart2pay_mid'],
            'MerchantTransactionID' => $this->session->data['order_id'],
            'Amount'           => $orderTotal,
            'Currency'         => $order['currency_code'],
            'ReturnURL'        => $settings['smart2pay_return_url'],
            'IncludeMethodIDs' => $this->request->get['method'],
            'CustomerName'     => $order['payment_firstname'] . ' ' . $order['payment_lastname'],
            'CustomerFirstName'=> $order['payment_firstname'],
            'CustomerLastName' => $order['payment_lastname'],
            'CustomerEmail'    => $order['email'],
            'Country'          => $order['payment_iso_code_2'],
            'MethodID'         => $this->request->get['method'],
            'Description'      => $settings['smart2pay_product_description'],
            'SkipHPP'          => $skipHpp,
            'RedirectInIframe' => $settings['smart2pay_redirect_in_iframe'],
            'SkinID'           => $settings['smart2pay_skin_id'],
			'SiteID'           => $settings['smart2pay_site_id'],
        );

        foreach ($data['payment_data'] as $key => $value) {
            if ( ! $value) {
                unset($data['payment_data'][$key]);
            }
        }

        $stringToHash = $this->model_payment_smart2pay->createStringToHash($data['payment_data']);

        $data['string_to_hash'] = $stringToHash;

        $data['payment_data']['Hash'] = $this->model_payment_smart2pay->computeHash($stringToHash, $settings['smart2pay_signature']);

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        
      	//if order is unconfirmed we confirm it depending on the smart2pay_order_confirm flag
		//status is new for now
		if ($settings['smart2pay_order_confirm'] == 3) {
			$this->model_payment_smart2pay->log("Confirming order on initiate payment", "info");
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $settings['smart2pay_order_status_new']);
		}	
		

        /*
         * Prepare template
         */
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/smart2pay/smart2pay_send_form.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/smart2pay/smart2pay_send_form.tpl';
        } else {
            $this->template = 'default/template/payment/smart2pay_send_form.tpl';
        }

        $this->response->setOutput($this->load->view($this->template, $data));
    }

    /**
     * Feedback action
     *  Default return url after payment
     */
    public function feedback()
    {
        $this->load->model('payment/smart2pay');
        $this->load->model('setting/setting');
        $this->load->model('checkout/order');
		$settings = $this->model_setting_setting->getSetting('smart2pay');
        $status   = null;
        $order    = null;
        $orderID  = null;
        
        $server_base = null;
    	if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$server_base = HTTP_SERVER;
		} else 
		{
				$server_base = HTTPS_SERVER;
		}
        
        $redirect = $server_base . 'index.php';

        if (isset($this->request->get['MerchantTransactionID'])) {
            $orderID  = $this->request->get['MerchantTransactionID'];
            $order = $order = $this->model_checkout_order->getOrder($orderID);
        }
        
        $this->model_payment_smart2pay->log(">>>START FEEDBACK", "info");

        if (isset($this->request->get['data'])) {
            switch ($this->request->get['data']) {
                case 2:
                    $status   = 'success';
                    $redirect = $server_base . 'index.php?route=checkout/success';
                    break;
                case 3:
                    $status   = 'canceled';
                    //$redirect = $server_base . 'index.php?route=checkout/checkout';
                    break;
                case 4:
                    $status   = 'failed';
                    //$redirect = $server_base . 'index.php?route=checkout/checkout';
                    break;
                case 7:
                    $status   = 'processing';
                    $redirect = $server_base . 'index.php?route=checkout/success';
                    break;
            }
        }

        $this->model_payment_smart2pay->log(">>>Customer redirected for order #". $orderID . " with status " . $status, "info");

        if ( ! $status || ! $order) {
            $this->model_payment_smart2pay->log(">>> No status or order" . $status, "info");
            $this->response->redirect($server_base . "index.php");
        } else if ($settings['smart2pay_order_confirm'] == 2) {
            // If current status is
            // settings::smart2pay_order_status_success,
            // which most probably means that notification arrived just
            // before s2p redirected to merchant site (here), do not change status!
            if ($order['order_status_id'] != $settings['smart2pay_order_status_success']) {
                if (in_array($status, array('success', 'processing'))) {
                    if ($order)
                    {
                        $this->model_checkout_order->addOrderHistory(
                            $orderID,
                            $settings['smart2pay_order_status_new']
                        );
                    }
                } else {
                    if ($order) {
                        $this->model_checkout_order->addOrderHistory(
                            $orderID,
                            $settings['smart2pay_order_status_' . $status]
                        );
                    }
                }
            }
		}
		
		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();
			if (isset($this->session->data['shipping_method'])) {
				unset($this->session->data['shipping_method']);
			}
			if (isset($this->session->data['shipping_methods'])) {
				unset($this->session->data['shipping_methods']);
			}
			if (isset($this->session->data['payment_method'])) {
				unset($this->session->data['payment_method']);
			}
			if (isset($this->session->data['payment_methods'])) {
				unset($this->session->data['payment_methods']);
			}
			if (isset($this->session->data['guest'])) {
				unset($this->session->data['guest']);
			}
			if (isset($this->session->data['comment'])) {
				unset($this->session->data['comment']);
			}
			if (isset($this->session->data['order_id'])) {
				unset($this->session->data['order_id']);
			}
			if (isset($this->session->data['coupon'])) {
				unset($this->session->data['coupon']);
			}
			if (isset($this->session->data['reward'])) {
				unset($this->session->data['reward']);
			}
			if (isset($this->session->data['voucher'])) {
				unset($this->session->data['voucher']);
			}
			if (isset($this->session->data['vouchers'])) {
				unset($this->session->data['vouchers']);
			}
		}
	
        $language = new Language(DIR_LANGUAGE);
        $translations = $language->load("payment/smart2pay");

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');

        $data['feedback'] = $translations['info_payment_feedback_' . $status];
        $data['redirect'] = $redirect;

		$this->model_payment_smart2pay->log(">>>END FEEDBACK", "info");
		
		if(strcmp($status, 'success') != 0) {
			/*
			 * Prepare template
			 */
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/smart2pay/smart2pay_feedback.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/smart2pay/smart2pay_feedback.tpl';
			} else {
				$this->template = 'default/template/payment/smart2pay_feedback.tpl';
			}

			$this->response->setOutput($this->load->view($this->template, $data));
		} else {
			$this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
		}
    }

    /**
     * Callback action
     *  Handle payment gateway response
     */
    public function callback() {

        $this->load->model('payment/smart2pay');
        $this->load->model('setting/setting');
        $this->load->model('checkout/order');

        $this->model_payment_smart2pay->log(">>> START CALLBACK", "info");

        $settings = $this->model_setting_setting->getSetting('smart2pay');
        if ($settings['smart2pay_env']) {
            $settings['smart2pay_post_url']  = $settings['smart2pay_post_url_live'];
            $settings['smart2pay_mid']       = $settings['smart2pay_mid_live'];
            $settings['smart2pay_signature'] = $settings['smart2pay_signature_live'];
        } else {
            $settings['smart2pay_post_url']  = $settings['smart2pay_post_url_test'];
            $settings['smart2pay_mid']       = $settings['smart2pay_mid_test'];
            $settings['smart2pay_signature'] = $settings['smart2pay_signature_test'];
        }

        try {
            $data = file_get_contents("php://input");
            parse_str($data, $response);
			
			$this->model_payment_smart2pay->log("Received notification from Smart2Pay: " . $data, "info");

			$this->model_payment_smart2pay->log("StatusID = " . $response['StatusID'], "info");
            $this->model_payment_smart2pay->log("MerchantTransactionID = " . $response['MerchantTransactionID'], "info");

            $vars = array();
			$recomposedHash = '';
			if(!empty($data)){
				$pairs    = explode("&", $data);
				foreach ($pairs as $pair) {
					$nv                = explode("=", $pair);
					$name            = $nv[0];
					$vars[$name]    = $nv[1];
					if(strtolower($name) != 'hash'){
						$recomposedHash .= $name . $vars[$name];
					}
				}
			}
			
            $recomposedHash = $this->model_payment_smart2pay->computeHash($recomposedHash, $settings['smart2pay_signature']);

            // Message is intact
            if ($recomposedHash == $response['Hash']) {

                $this->model_payment_smart2pay->log("Hashes match", "info");

                $orderID = $response['MerchantTransactionID'];

                $order = $order = $this->model_checkout_order->getOrder($orderID);

                $processed_ok = false;
                
                if (
                    $order['order_status_id'] == 0
                    && $response['StatusID'] != "1" // Leave order in pending if notification status is open
                ) {
					// If order is unconfirmed we confirm it depending on the smart2pay_order_confirm flag
					// status is new for now
					if ($settings['smart2pay_order_confirm'] != 2) {
						$this->model_payment_smart2pay->log("Confirming order..", "info");
						$this->model_checkout_order->addOrderHistory($orderID, $settings['smart2pay_order_status_new']);
					}
				}
				
				$order = $order = $this->model_checkout_order->getOrder($orderID);

                $this->model_payment_smart2pay->log("Order status is " . $order['order_status_id'], "info");

				$order['payment_method'] = $this->model_payment_smart2pay->getPaymentMethodNameById($response['MethodID']);
				
				$this->model_payment_smart2pay->log("> DEBUG: Payment method used was " . $order['payment_method'], "info");
               
                /**
                 * Check status ID
                 */
                switch ($response['StatusID']) {
                    // Status = open
                    case "1":
                        $this->model_payment_smart2pay->log("Payment state is open", "info");
                        $processed_ok = true;
                        break;
                    // Status = success
                    case "2":
                        $this->model_payment_smart2pay->log("Payment state is success", "info");

                        // cheking amount  and currency
                        $orderAmount =  round($order['total'] * $order['currency_value'] * 100);
                        $orderCurrency = $order['currency_code'];

                        if( ((int) $orderAmount === (int) $response['Amount']) && ($orderCurrency == $response['Currency'])) {

                            $this->model_payment_smart2pay->log("Amount and currency match", "info");

                            if ($order['order_status_id'] == 0) {
                                $this->model_payment_smart2pay->log("Confirming order..", "info");
                                $this->model_checkout_order->addOrderHistory($orderID, $settings['smart2pay_order_status_new']);
                            }

                            $this->model_payment_smart2pay->log("Updating order - setting received notification to history..", "info");

                            $this->model_checkout_order->addOrderHistory(
                                $orderID,
                                $settings['smart2pay_order_status_success'],
                                "[" . date('Y-m-d H:i:s') . "] Smart2Pay :: order has been paid. [Method: " . $order['payment_method'] . "]"
                            );

                            $processed_ok = true;

                            if ($settings['smart2pay_notify_customer_by_email']) {
                                try {
                                    // Inform customer
                                    $this->model_payment_smart2pay->log("Informing customer via email", "info");
                                    $this->informCustomer($order);
                                } catch (Exception $e) {
                                    $this->model_payment_smart2pay->log("Could not send e-mail: " . $e->getMessage(), "exception");
                                }
                            }
                        }
                        else{
                            $this->model_payment_smart2pay->log(
                                "Amount and currency do NOT match (" . $orderAmount . "/" . $response['Amount'] . " and " . $orderCurrency . "/" . $response['Currency'] . ")",
                                "info"
                            );
                        }
                        break;
                    // Status = canceled
                    case 3:
                        $this->model_payment_smart2pay->log("Payment state is cancelled", "info");
						if ($order['order_status_id']) {
							$this->model_payment_smart2pay->log("Updating order..", "info");
							$this->model_checkout_order->addOrderHistory(
								$orderID,
								$settings['smart2pay_order_status_canceled'],
								"[" . date('Y-m-d H:i:s') . "] Smart2Pay :: order payment has been canceled. [Method: " . $order['payment_method'] . "]"
							);
                            $processed_ok = true;
						}
						
                        break;
                    // Status = failed
                    case 4:
                        $this->model_payment_smart2pay->log("Payment state is failed", "info");
						if ($order['order_status_id']) {
							$this->model_checkout_order->addOrderHistory(
								$orderID,
								$settings['smart2pay_order_status_failed'],
								"[" . date('Y-m-d H:i:s') . "] Smart2Pay :: order payment has failed. [Method: " . $order['payment_method'] . "]"
							);
                            $processed_ok = true;
						}
                        break;
                    // Status = expired
                    case 5:
                        $this->model_payment_smart2pay->log("Payment state is expired", "info");
						if ($order['order_status_id']) {
							$this->model_checkout_order->addOrderHistory(
								$orderID,
								$settings['smart2pay_order_status_expired'],
								"[" . date('Y-m-d H:i:s') . "] Smart2Pay :: order payment has expired. [Method: " . $order['payment_method'] . "]"
							);
                            $processed_ok = true;
						}
                        break;

                    default:
                        $this->model_payment_smart2pay->log("Payment state is unknown. [Method: " . $order['payment_method'] . "]", "info");
                        break;
                }

                if ($processed_ok) { //if notification was processed OK, we respond
                    // NotificationType IS payment
                    if (strtolower($response['NotificationType']) == 'payment') {
                        // prepare string for the hash
                        $responseHashString = "notificationTypePaymentPaymentId" . $response['PaymentID'];
                        // prepare response data
                        $responseData = array(
                            'NotificationType' => 'Payment',
                            'PaymentID' => $response['PaymentID'],
                            'Hash' => $recomposedHash = $this->model_payment_smart2pay->computeHash($responseHashString, $settings['smart2pay_signature'])
                        );
                        // output response
                        echo "NotificationType=payment&PaymentID=" . $responseData['PaymentID'] . "&Hash=" . $responseData['Hash'];
                    }
                } else {
                    echo "OpenCart Plugin was not able to process this notification";
                }
            }
            else{
                $this->model_payment_smart2pay->log("Hashes do not match (received:" . $response['Hash'] . ") (recomposed:" . $recomposedHash . ")", "warning");
                echo "OpenCart Plugin: Hashes did not match (received:" . $response['Hash'] . ") (recomposed:" . $recomposedHash . ")";
            }
        } catch (Exception $e) {
            $this->model_payment_smart2pay->log($e->getMessage(), "exception");
        }

        $this->model_payment_smart2pay->log("END CALLBACK <<<", "info");

        exit;
	}

    private function informCustomer($order)
    {
        if (defined('HTTP_IMAGE')) {
            $logo_path = HTTP_IMAGE . $this->config->get('config_logo');
        } else {
            $logo_path = HTTP_SERVER . 'image/' .$this->config->get('config_logo');
        }

        $data = array();

        $data['logo']       = $logo_path;
        $data['store_name'] = $order['store_name'];
        $data['store_url']  = $order['store_url'];
        $data['order_id']   = $order['order_id'];
        $data['order_date']  = date("d F Y", strtotime($order['date_added']));
        $data['order_total'] = number_format($order['total'], 2);
        $data['order_currency'] = $order['currency_code'];
        $data['customer_name']  = $order['firstname'] . " " . $order['lastname'];

        $data['suport_email'] = $this->config->get('config_email');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/smart2pay/email/smart2pay_payment_confirmation.tpl')) {
            $template = $this->config->get('config_template') . '/template/smart2pay/email/smart2pay_payment_confirmation.tpl';
        } else {
            $template = 'default/template/smart2pay/email/smart2pay_payment_confirmation.tpl';
        }

        $subject = "Payment Confirmation";

        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
        $mail->setTo($order['email']);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($order['store_name']);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setHtml($this->load->view($template, $data));
        //$mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $mail->send();

        $this->model_payment_smart2pay->log("Informed customer via email (mail sent)", "info");
    }

    /**
     * Check pay request
     */
    private function checkPayRequest()
    {
        $this->load->model('payment/smart2pay');

        $error = null;

        if ( ! isset($this->session->data['order_id'])) {
            $error = "Order ID is not set. Possible that session has been cleared.";
        } elseif ( ! isset($this->request->get['method'])) {
            $error = "Payment method ID is missing.";
        }/* elseif ( ! in_array($this->request->get['method'], $this->model_payment_smart2pay->getActiveMethods($payment_address))) {
            $error = "Payment method ID is not one of the active method IDs.";
        }*/

        if ($error) {
            $this->model_payment_smart2pay->log($error, "hijack");
            header('Location: /index.php?smart2pay-pay-attempt-error');
        }
    }
}

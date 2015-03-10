<?php 
class ModelPaymentSmart2pay extends Model {

    /**
     * Get Active Methods
     *
     * @param null $address
     * @param bool $getAllFields
     *
     * @return array
     */
    public function getActiveMethods($address = null, $getAllFields = false)
    {
        $activeMethods = array();

        $allActiveMethods = $this->config->get('smart2pay_active_methods');
		
		// MODIFIED
        // NOT ALL LOGGED IN CUSTOMERS HAVE AN ADDRESS ID BECAUSE ADDRESS IS NOT YET ADDED IN A OPC.
        // FETCH STORED COUNTRY SESSION ID INSTEAD!!!!
        // YOU WOULDN'T NEED THE WHOLE ADDRESS DATA TOO IF YOU'RE MATCHING ONLY COUNTRY
        if ($this->customer->isLogged() && isset($this->session->data['payment_country_id'])) {
            $address = array(); // Clear array
            $address['country_id'] = $this->session->data['payment_country_id'];

            // EXTRAS APPARENTLY THE DATABASE FOR SMART2PAY COULDN'T WORK WITH COUNTRY ID BECAUSE THEY DIDN'T SET COLLATION CORRECTLY
            // SO WE NEED TO FETCH ISO CODE 2
            $address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$address['country_id'] . "'");
            if ($address_query->num_rows) {
                $address['iso_code_2'] = $address_query->row['iso_code_2'];
            }
        }
        // MODIFIED END		
		
        if ( ! $address) {
            $activeMethods = $allActiveMethods;
        } 
        else 
        {
        	if(array_key_exists('iso_code_2', $address)){
		        $query = $this->db->query("
	                SELECT CM.method_id
	                FROM " . DB_PREFIX . "smart2pay_country_method CM
	                LEFT JOIN " . DB_PREFIX . "smart2pay_country C ON C.country_id = CM.country_id
	                WHERE C.code = '" . $this->db->escape($address['iso_code_2']) . "'
	            ");
	        }
	        else{
				  $query = $this->db->query("
	                SELECT CM.method_id
	                FROM " . DB_PREFIX . "smart2pay_country_method CM
	                LEFT JOIN " . DB_PREFIX . "smart2pay_country C ON C.country_id = CM.country_id
	                INNER JOIN " . DB_PREFIX . "country CTR on CTR.iso_code_2 = C.code
	                WHERE CTR.country_id = '" . $this->db->escape($address['country_id']) . "'
	            ");
				
			}
		

            $addressMethods = array();

            if ($query->rows) {
                foreach ($query->rows as $activeMethod) {
                    if (in_array($activeMethod['method_id'], $allActiveMethods)) {
                        $addressMethods[] = $activeMethod['method_id'];
                    }
                }
            }

            $activeMethods = $addressMethods;
        }

        if ($getAllFields && $activeMethods) {
        	
            $query = $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "smart2pay_method M WHERE M.method_id IN (" . implode(", ", $activeMethods) . ")");
            $activeMethods = $query->rows;
        }
		//print_r($activeMethods);
        return $activeMethods;
    }

    /**
     * Get Method
     *
     * @param $address
     * @param $total
     *
     * @return array
     */
    public function getMethod($address, $total) {

        return;

        $method_data = array();

        if ($this->getActiveMethods($address)) {

            $title = $this->config->get("smart2pay_title");

            $this->load->model('setting/setting');
            $settings = $this->model_setting_setting->getSetting('smart2pay');


            $method_data = array(
                'code'       => 'smart2pay',
                'title'      =>  $title,
                'sort_order' => $this->config->get('smart2pay_sort_order')
            );

            
            //  Auto select smart2pay method trick
             
            if ($this->config->get('smart2pay_auto_select')) {
                $this->session->data['payment_method'] = $method_data;
            }
        }

        return $method_data;
  	}
  	
 
  	
  	
  	  /**
     * Get Payment method name by id
     *
     * @param $id
     *
     * @return string
     */
    public function getPaymentMethodNameById($id){
    	$query = $query = $this->db->query("SELECT display_name FROM " . DB_PREFIX . "smart2pay_method M WHERE M.method_id = " .$id);
        $paymentMethod = $query->rows[0];
        return $paymentMethod['display_name'];
    }

	
    /**
     * Compute SHA256 Hash
     *
     * @param $message
     *
     * @return string
     */
    public function computeSHA256Hash($message){
        return hash("sha256", strtolower($message));
    }

    /**
     * Compute Hash
     *
     * @param string $data
     * @param string $signature
     *
     * @return string
     */
    public function computeHash($data, $signature)
    {
        return $this->computeSHA256Hash($data . $signature);
    }

    /**
     * Create string to hash from data
     *
     * @param array $data
     *
     * @return string
     */
    public function createStringToHash(array $data = array())
    {
        $mappedData = array_map(
            function($key, $value) {
                return $key . $value;
            },
            array_keys($data),
            $data
        );

        return join("", $mappedData);
    }

    /**
     * Log
     *
     * @param string $data
     * @param string $type
     */
    public function log($data, $type = "info")
    {
        if ( ! is_string($data)) {
            $data = serialize($data);
        }

        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];

        $query = "INSERT INTO " . DB_PREFIX . "smart2pay_log (log_data, log_type, log_source_file, log_source_file_line) VALUES
            ('" . $this->db->escape($data) . "', '" . $this->db->escape($type) . "', '" . $this->db->escape($file) . "', '" . $this->db->escape($line) . "')";

        $this->db->query($query);
    }

    /**
     *
     */
    public function handleOrder()
    {
//        $this->load->model('checkout/order');
//        $order = $this->model_checkout_order->getOrder(38);
//        echo "<pre>";
//        print_r($order);
//        echo "</pre>";
    }
}
?>
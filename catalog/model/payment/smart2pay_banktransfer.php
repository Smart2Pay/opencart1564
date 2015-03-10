<?php
/**
 * Class ModelPaymentSmart2payBanktransfer
 */
class ModelPaymentSmart2payBanktransfer extends Model {

    static $methodName = "banktransfer";
    static $displayName = "Bank Transfer";
    static $methodID   = 1;

    public function getMethodId()
    {
        return self::$methodID;
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

        $method_data = array();

        $this->load->model("payment/smart2pay");

        $this->load->model('setting/setting');

        $settings = $this->model_setting_setting->getSetting('smart2pay');

        if ($this->isMethodAvailable($address, $total) && $settings['smart2pay_status']) {

            $title = ucfirst(self::$displayName);
            $code  = 'smart2pay_' . self::$methodName;

            $this->load->model('setting/setting');

            $settings = $this->model_setting_setting->getSetting('smart2pay');

            $method_data = array(
                'code'       => $code,
                'title'      =>  $title,
                'terms'      => false,
                'sort_order' => 0 //$this->config->get('smart2pay_sort_order')
            );
        }

        return $method_data;
    }

    /**
     * Check if method is available for a particular address and cart amount total
     *
     * @param $address
     * @param $total
     * @return bool
     */
    public function isMethodAvailable($address, $total)
    {
        if (array_key_exists('iso_code_2', $address)) {
            $query = $this->db->query("
                SELECT CM.method_id
                FROM " . DB_PREFIX . "smart2pay_country_method CM
                LEFT JOIN " . DB_PREFIX . "smart2pay_country C ON C.country_id = CM.country_id
                WHERE C.code = '" . $this->db->escape($address['iso_code_2']) . "' AND CM.method_id = " . static::$methodID . "
            ");
        } else {
            $query = $this->db->query("
                SELECT CM.method_id
                FROM " . DB_PREFIX . "smart2pay_country_method CM
                LEFT JOIN " . DB_PREFIX . "smart2pay_country C ON C.country_id = CM.country_id
                INNER JOIN " . DB_PREFIX . "country CTR on CTR.iso_code_2 = C.code
                WHERE CTR.country_id = '" . $this->db->escape($address['country_id']) . "' AND CM.method_id = " . static::$methodID . "
            ");

        }

        if ($query->rows) {
            return true;
        }

        return false;
    }
}
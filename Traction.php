<?php
/**
 * Class Traction.
 *
 * PHP api wrapper for Traction
 *
 * @author Rufus Post
 * @date 02/12/2009
 */

class Traction {

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        private $gateway = 'au.api.tractionplatform.com/ext/';

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        private $client;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $tracCode;

/**
 * Error returned by traction
 *
 * @var string
 * @access private
 */
        public $tracError;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $transfer;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $response;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $headers;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $endpoint = array();

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $test = false;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $data;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $lastCustomerId;

/**
 * Names of classes with their objects.
 *
 * @var array
 * @access private
 */
        public $secure = false;

/**
 * Initalise http transaction with traction using curl
 *
 * @param string $function  Traction Api to call
 * @access private
 * @static
 */
        private function initalise($function) {
                $http = $this->secure?'https://':'http://';
                $url = $http.$this->gateway.$function;
                $post_data = $this->endpointEncode().'&'.$this->customerEncode();
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
                ob_start();
                curl_exec($ch);
                $this->response = ob_get_clean();
                $this->transfer = curl_getinfo($ch);
		            curl_close($ch);
                if (is_bool($this->response)) {
                        if ($this->response==false){
                                throw new Exception('No connection');
                        } else {
                                $this->response=null;
                        }
                }
                if($this->response) $this->decodeResponse();
        }

/**
 * Decode Traction response
 *
 * @access private
 * @static
 */
        private function decodeResponse() {
                $headers = array();
                $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $this->response));
                foreach( $fields as $field ) {
                        if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                                if( isset($headers[$match[1]]) ) {
                                        $headers[$match[1]] = array($headers[$match[1]], $match[2]);
                                } else {
                                        $headers[$match[1]] = trim($match[2]);
                                }
                        }
                }
                $this -> headers = $headers;
                $this -> tracCode = $headers['Trac-Result'];
                if(isset($headers['Trac-Customerid'])) {
                    $this -> lastCustomerId = $headers['Trac-Customerid'];
                }
                if(isset($headers['Trac-Error'])) {
                        $this->tracError = $headers['Trac-Error'];
                }
        }

/**
 * Create html encoded endpoint data
 *
 * @return string Html encoded data
 * @access private
 * @static
 */
        private function endpointEncode() {
                if(empty($this->endpoint)) return false;
                if(!isset($this->endpoint['matchkey'])) {
                        $this->endpoint['matchkey'] = 'E';
                        $this->endpoint['matchvalue'] = $this->data['email'];
                }

                foreach($this->endpoint as $key => $val) {
                        $endpoint[strtoupper($key)]=$val;
                }
                if($this->test) $endpoint['TEST'] = '1';
                return http_build_query($endpoint);
        }

/**
 * Create html encoded customer data
 *
 * @return string Html encoded data
 * @access private
 * @static
 */
        private function customerEncode() {
                foreach($this->data as $key => $val) {
                        $customer[]=strtoupper($key).'|'.$val;
                }
                return http_build_query(array('CUSTOMER'=>implode(chr(31), $customer)));
        }

/**
 * Call AddCustomer api
 *
 * @access public
 * @static
 */
        public function AddCustomer($email = null) {
                if(!$email) {
                        if(is_array($this->data)) {
                                if(empty($this->data['email'])) return false;
                        }
                } else {
                        $this->data = array('email'=>$email);
                }
                $this->initalise('AddCustomer');
        }

}
?>
<?php

/**
 * API - Theamus API handling class
 * PHP Version 5.5.3
 * Version 1.4.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Matt Temet
 */
class API {
    /**
     * Holds the value of whether or not the api call failed.
     *
     * @var boolean $api_fail
     */
    private $api_fail = false;


    /**
     * Connect this class with Theamus
     *
     * @param object $t
     */
    public function __construct($t) {
        $this->Theamus = $t;
    }


    /**
     * Takes a string of XML and converts it to an array
     *
     * @param string $xml
     * @param boolean $object
     * @return array
     */
    public function xml_decode($xml) {
        $xml_array = array(); // Define the xml results array

        // Get the contents of the XML and turn them into an array
        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $xml, $xml_array);

        // Remove the top layer elements
        $array = array_shift($xml_array);
        unset($array["level"], $array["type"]);

        // Loop through all of the xml nodes
        for($i = 0; $i < sizeof($xml_array); $i++) {
            // Define shortcuts
            $xml_object = $xml_array[$i];
            $level      = $xml_object['level'] - 2;

            // Define the result array index
            $array_index = array();
            for ($ai = 0; $ai < $level; $ai++) {
                $array_index[] = strtolower($xml_array[$ai]['tag']);
            }
            $array_index[] = strtolower($xml_object['tag']);

            // Define the value for the current object
            if ($xml_object["type"] == "complete") {
                // Define the value of the current object
                $value = &$array;
                foreach($array_index as $segment) {
                    $value = &$value[$segment];
                }
                $value = $xml_object;

                // Don't include the xml structure items
                unset($value["level"], $value["type"], $value['tag']);
            }
        }

        // Return the array with the XML results
        return $array;
    }


    /**
     * Check to see if an array is sequential (0, 1, 2, ...) or associative ("key"=>"value")
     *
     * @param array $array
     * @return boolean
     */
    public function array_is_associative($array = array()) {
        // Check for an empty array
        if (empty($array)) return true;

        // Return the value of a check to determine if an array is associative
        return array_keys($array) !== range(0, count($array) - 1);
    }


    /**
     * Checks to see if a string is a date
     *
     * @param string $string
     * @return boolean
     */
    public function string_is_date($string = '') {
        // Check for a string
        if ($string == '') return false;

        // Check the created date to see if the string is actually a date and retrun respectively
        return (($timestamp = strtotime($string)) === false) ? false : true;
    }


    /**
     * Check to see if a string is just encoded JSON
     *
     * @param string $string
     * @return boolean
     */
    public function string_is_json($string = "") {
        // Check for a string
        if ($string == "") return false;

        // Try to decode the string as JSON and return if it failed
        $json = json_decode($string);
        return $json == null ? false : true;
    }


    /**
     * Defines the hash that makes up the API keys or the 420hash
     *
     * @param boolean $user
     * @return string
     */
    public function get_hash($user = false) {
        // Define the hash variables
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $date = $user == true ? date("Y-d-m") : "";
        $server_ip = $_SERVER['SERVER_ADDR'];

        // Return the hash
        return md5($user_ip.$date.$server_ip);
    }


    /**
     * Structures an API error to be consistent with the same error you would get from
     *  a JS API call
     *
     * @param string $message
     * @return string $return
     */
    private function api_error($message = "", $status = 200) {
        $return = array();

        // Define the error
        $return['error']['status'] = 1;
        $return['error']['message'] = $message;

        // Define the response
        $return['response']['status'] = $status;
        $return['response']['headers'] = "";
        $return['response']['data'] = "";

        return $return;
    }


    /**
     * Checks the arguments given via the API call for requirements and validation
     *
     * @param array $args
     * @return array $return
     */
    private function api_check_args($args) {
        $return = array();

        // Define defaults
        $return['ajax'] = "api";
        $return['api-from'] = "php";

        // Define the custom variable
        if (isset($args['custom'])) {
            $return['custom'] = $args['custom'] == true ? true : false;
        } else {
            $return['custom'] = false;
        }

        // Define the type
        if (isset($args['type']) && gettype($args['type']) == "string") {
            if ($args['type'] != "get" && $args['type'] != "post") {
                $this->api_fail = "API request type must be 'post' or 'get'.";
            } else {
                $return['type'] = $args['type'];
            }
        } else {
            $this->api_fail = "Invalid or missing API request type.";
        }

        // Define and check the URL
        if (isset($args['url']) && gettype($args['url']) == "string") {
            $return['url'] = $this->define_api_url(urldecode($args['url']));
        } else {
            $this->api_fail = "Invalid API url.";
        }

        // Define the method
        if (isset($args['method']) && $return['custom'] == false) {
            $return['method_class'] = "";
            if (gettype($args['method']) == "array") {
                count($args['method']) >= 1 ? $return['method_class'] = $args['method'][0] : $this->api_fail = "Undefined API method.";
                count($args['method']) >= 2 ? $return['method'] = $args['method'][1] : $this->api_fail = "Undefined API method after finding class.";
            } elseif (gettype($args['method'] == "string")) {
                $return['method'] = $args['method'];
            } else {
                $this->api_fail = "Invalid API method defined.";
            }
        } else {
            if ($return['custom'] == false) {
                $this->api_fail = "API method not defined.";
            }
        }

        // Define the data
        if (isset($args['data'])) {
            if (gettype($args['data']) == "array") {
                if ($this->array_is_associative($args['data']) == true) {
                    $return['data'] = $args['data'];
                } else {
                    $this->api_fail = "API data parameter must be a key => value array.";
                }
            } else {
                $this->api_fail = "API data parameter must be a key => value array.";
            }
        } else {
            $return['data'] = "";
        }

        // Define the API key
        if (isset($args['key'])) {
            if (gettype($args['key']) == "string") {
                $return['ajax-hash-data'] = urlencode(json_encode(array("key"=>$args['key'])));
            } else {
                $this->api_fail = "Invalid API key type.";
            }
        } else {
            $return['ajax-hash-data'] = urlencode(json_encode(array("key"=>$this->get_hash())));
        }

        // Remove anything that is normally pre-set but because it's a custom call
        if ($return['custom'] == true) {
            unset($return['api-key'], $return['ajax']);

            // Define the response type
            $return['response_type'] = false;
            if (isset($args['response_type'])) {
                // Check for allowed response types: JSON or XML
                if ($args['response_type'] == "json" || $args['response_type'] == "xml") {
                    $return['response_type'] = $args['response_type'];
                } else {
                    $this->api_fail = "The API response type must be either 'json' or 'xml'.";
                }
            }
        }

        return $return;
    }


    /**
     * Performs a check to see if cURL is an option on the server
     *
     * @return boolean
     */
    public function check_curl() {
        if (function_exists("curl_version")) return true;
        return false;
    }


    /**
     * Defines the variables that will be passed during the call
     *
     * @param array $args
     * @param boolean $get
     * @return array
     */
    private function define_api_variables($args, $get = false) {
        $return = array();
        $ignore = array("url", "type", "custom", "response_type");
        foreach ($args as $key => $value) {
            // If the key is meant to be ignored, ignore it
            if (in_array($key, $ignore)) continue;

            // If this is a Theamus -> Theamus ajax call
            if ($args['custom'] == false) {
                // If the value is an array, turn it into a JSON encoded string
                if (is_array($value)) {
                    $value = urlencode(json_encode($value));
                }

                // Add the key/value to the return array
                $return[] = $get == true ? urlencode("$key=$value") : "$key=$value";
            } else {
                // Loop through all of the data variables, defining them as a string
                foreach ($value as $k => $v) $return[] = "$k=$v";
            }
        }

        // If there are results to return, return them or nothing at all
        return count($return) >= 1 ? implode("&", $return) : "";
    }


    /**
     * Since an absolute path is required, check for the existance of a protocol and
     *  define the url based on the results from that
     *
     * @param string $url
     * @return string $new_url
     */
    private function define_api_url($url) {
        // Check if the url is expected to be an absolute path
        if (strpos($url, "http") !== false) {
            $new_url = $url;
        } else {
            // We will be assuming the host address or the predefined base url
            $new_url = $this->Theamus->base_url.$url;
        }

        // Check for url validation and return or throw error
        if (filter_var($new_url, FILTER_VALIDATE_URL)) {
            return $new_url;
        } else {
            // Define an error has happened and return blank
            $this->api_fail = "Invalid URL given.";
            return "";
        }
    }


    /**
     * Send the API out based on the arguments given
     *
     * @param array $args
     * @return string
     */
    private function send_api($args) {
        // Define options based on the call type
        if ($args['type'] == "post") {
            // Define the POST options
            $options = array(
                CURLOPT_POST                => 1,
                CURLOPT_HEADER              => 0,
                CURLOPT_URL                 => $args['url'],
                CURLOPT_FRESH_CONNECT       => 1,
                CURLOPT_RETURNTRANSFER      => 1,
                CURLOPT_FORBID_REUSE        => 1,
                CURLOPT_TIMEOUT             => 4,
                CURLOPT_POSTFIELDS          => $this->define_api_variables($args)
            );
        } elseif ($args['type'] == "get") {
            // Define the variables to send out
            $api_variables = $this->define_api_variables($args, true);

            // Define the separator from the url
            $var_starter = "";
            if ($args['custom'] == false && $api_variables != "") $var_starter = "&";
            if ($args['custom'] == true && $api_variables != "") $var_starter = "?";

            // Define the GET options
            $options = array(
                CURLOPT_URL                 => $args['url'].$var_starter.$api_variables,
                CURLOPT_HEADER              => 0,
                CURLOPT_RETURNTRANSFER      => 1,
                CURLOPT_TIMEOUT             => 4
            );
        }

        // Open the connection, set the options and execute
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        // Find the status and throw an error, if applicable
        $ch_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($ch_status != 200) {
            return $this->api_error("URL responded with an error.", $ch_status);
        } else {
            return $result;
        }
    }


    /**
     * Perfoms everything required to run an API call.
     *
     * @param array $args
     * @return array
     */
    public function api($args = array()) {
        $return = array();
        if (empty($args)) $return = $this->api_error("API arguments are required.");

        // Define and check the arguments
        $args = $this->api_check_args($args);

        // Make the call
        if ($this->check_curl() && $this->api_fail == false) {
            $return = $this->send_api($args);
        } else {
            $return = $this->api_error("You must have cURL available to make API requests.");
        }

        // Throw errors or return
        if ($this->api_fail != false) $return = $this->api_error($this->api_fail);

        if (!is_array($return)) {
            // Check the response type to return the proper data
            if (isset($args['response_type']) && $args['response_type'] == "xml") {
                return $this->xml_decode($return);
            } else {
                return json_decode($return, true);
            }
        } else {
            return $return;
        }
    }
}
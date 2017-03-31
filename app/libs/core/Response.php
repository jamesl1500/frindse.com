<?php

class Response extends Database
{
    static public function make($text, $type = "JSON", $code = "0", $additional_array = "")
    {
        if (!empty($text) && !empty($type)) {
            // Types
            $types = array('JSON', 'STRING', 'json', 'string');

            if (in_array($type, $types)) {
                switch ($type) {
                    case 'JSON':
                        $response = array();

                        $response['code'] = $code;
                        $response['status'] = $text;

                        /// Additional stuff
                        if ($additional_array != "") {
                            $response = array_merge($response, $additional_array);
                        }

                        return json_encode($response);
                        break;
                    case 'STRING':

                        break;
                }
            }
        }
    }
}

?>
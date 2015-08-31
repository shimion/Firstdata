<?php

class FirstData
{
        protected $host = "api.demo.globalgatewaye4.firstdata.com";
        protected $protocol = "https://";
        protected $uri = "/transaction/v19";

        /*Modify this acording to your firstdata api stuff*/
        protected $hmackey = "nVLR9ykiqBovnGc5h0bRqIqDYiTTzkgB";
        protected $keyid = "214427";
        protected $gatewayid = "AH6730-03";
        protected $password = "v010l6676c3140p965ljtrdoos0356p3";


        public function request()
        {
                $location = $this->protocol . $this->host . $this->uri;
                $request = array(
                        'transaction_type' => "00",
                        'amount' => 10.00,
                        'cc_expiry' => "0415",
                        'cc_number' => '4111111111111111',
                        'cardholder_name' => 'Sajal',
                        'reference_no' => '23',
                        'customer_ref' => '11',
                        'reference_3' => '234',
                        'gateway_id' => $this->gatewayid,
                        'password' => $this->password,
                );

                $content = json_encode($request);

                var_dump($content);

                $gge4Date = gmdate("c");
                $contentType = "application/json";
                $contentDigest = sha1($content);
                $contentSize = sizeof($content);
                $method = "POST";

                $hashstr = "$method\n$contentType\n$contentDigest\n$gge4Date\n$this->uri";

                $authstr = 'GGE4_API ' . $this->keyid . ':' . base64_encode(hash_hmac("sha1", $hashstr, $this->hmackey, true));


                $headers = array( 
                        "Content-Type: $contentType",
                        "X-GGe4-Content-SHA1: $contentDigest",
                        "X-GGe4-Date: $gge4Date",
                        "Authorization: $authstr",
                        "Accept: $contentType"
                );

                //Print the headers we area sending
                var_dump($headers);


                //CURL stuff
				
				$ch = curl_init( $location );
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
				
				

                //This guy does the job
                $output = curl_exec($ch);

                //echo curl_error($ch); 
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = $this->parseHeader(substr($output, 0, $header_size));
                $body = substr($output, $header_size);

                curl_close($ch);
                //Print the response header
                var_dump($header);

                /* If we get any of this X-GGe4-Content-SHA1 X-GGe4-Date Authorization
                 * then the API call is valid */
                if (isset($header['authorization']))
                {
                        //Ovbiously before we do anything we should validate the hash
                        var_dump(json_decode($body));
                }
                //Otherwise just debug the error response, which is just plain text
                else
                {
                        echo $body;
                }
        }

        private function parseHeader($rawHeader)
        {
                $header = array();

                //http://blog.motane.lu/2009/02/16/exploding-new-lines-in-php/
                $lines = preg_split('/\r\n|\r|\n/', $rawHeader);

                foreach ($lines as $key => $line)
                {
                        $keyval = explode(': ', $line, 2);

                        if (isset($keyval[0]) && isset($keyval[1]))
                        {
                                $header[strtolower($keyval[0])] = $keyval[1];
                        }
                }

                return $header;
        }
}

$firstdata = new FirstData();

$firstdata->request();
<?php
/**
 * Makes a request to ATS for the top 10 sites in a country
 */
class TopSites {

    protected static $ActionName        = 'TopSites';
    protected static $ResponseGroupName = 'Country';
    protected static $ServiceHost      = 'ats.amazonaws.com';
    protected static $NumReturn         = 100;
    protected static $SigVersion        = '2';
    protected static $HashAlgorithm     = 'HmacSHA256';   

    public function TopSites($accessKeyId, $secretAccessKey, $countryCode, $startNum) {
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->countryCode = $countryCode;
        $this->startNum = $startNum;
    }

    /**
     * Get top sites from ATS
     */ 
    public function getTopSites() {
        $queryParams = $this->buildQueryParams();
        $sig = $this->generateSignature($queryParams);
        $url = 'http://' . self::$ServiceHost . '/?' . $queryParams . 
            '&Signature=' . $sig;
        $ret = self::makeRequest($url);
        self::parseResponse($ret);
    }

    /**
     * Builds an ISO 8601 timestamp for request
     */
    protected static function getTimestamp() {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }

    /**
     * Builds the url for the request to ATS
     * The url will be urlencoded as per RFC 3986 and the uri params
     * will be in alphabetical order
     */
    protected function buildQueryParams() {
        $params = array(
            'Action'            => self::$ActionName,
            'ResponseGroup'     => self::$ResponseGroupName,
            'AWSAccessKeyId'    => $this->accessKeyId,
            'Timestamp'         => self::getTimestamp(),
            'CountryCode'       => $this->countryCode,
            'Count'             => self::$NumReturn,
            'Start'             => $this->startNum,
            'SignatureVersion'  => self::$SigVersion,
            'SignatureMethod'   => self::$HashAlgorithm
        );
        ksort($params);
        $keyvalue = array();
        foreach($params as $k => $v) {
            $keyvalue[] = $k . '=' . rawurlencode($v);
        }
        return implode('&',$keyvalue);
    }

    /**
     * Makes an http request
     *
     * @param $url      URL to make request to
     * @return String   Result of request
     */
    protected static function makeRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Parses the XML response from ATS and echoes the DataUrl element 
     * for each returned site
     *
     * @param String $response    xml response from ATS
     */
    protected static function parseResponse($response) {
        $xml = new SimpleXMLElement($response,null, false, 
                                    'http://ats.amazonaws.com/doc/2005-11-21');
        foreach($xml->Response->TopSitesResult->Alexa->TopSites->Country->Sites->children('http://ats.amazonaws.com/doc/2005-11-21') as $site) {
            echo $site->DataUrl . "\n"; 
        } 
    }

    /**
     * Generates a signature per RFC 2104
     *
     * @param String $queryParams query parameters to use in creating signature
     * @return String             signature
     */
    protected function generateSignature($queryParams) {
        $sign = "GET\n" . strtolower(self::$ServiceHost) . "\n/\n". $queryParams;
        $sig = base64_encode(hash_hmac('sha256', $sign, $this->secretAccessKey, true));
        return rawurlencode($sig);
    }

}

if (count($argv) < 3) {
    echo "Usage: $argv[0] ACCESS_KEY_ID SECRET_ACCESS_KEY [COUNTRY_CODE]\n";
    exit(-1);
}
else {
    $accessKeyId = $argv[1];
    $secretAccessKey = $argv[2];
    $start = count($argv) > 3 ? $argv[3] : 1;
}

$topSites = new TopSites($accessKeyId, $secretAccessKey, 'FR', $start);
$topSites->getTopSites();


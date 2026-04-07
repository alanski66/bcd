<?php

namespace modules\fetchprofile\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
// use modules\fetchprofile\assets\FetchProfile;

// Craft::$app->getView()->registerAssetBundle(SiteAsset::class);



//use guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\RequestException;



/**
 * Fetch Profile controller
 * https://domain.tld/actions/fetch-profile/default/get-data
 */
class DefaultController extends Controller
{
    // Properties
    // =========================================================================

    protected array|bool|int $allowAnonymous = ['get-data', 'verify-id','verify-ukcp','verify-bacp','verify-bacp-register'];


    // Public Methods
    // =========================================================================

    public function actionGetData(): Response
    {
    
        $data = array();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //set header information including cookies, referer, etc. 
        // new GuzzleHttp\Client
        $client = new \GuzzleHttp\Client([
            'cookies' => true,
            'headers' => [
            'Host'=> 'fpt-api.XXXXX.com',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
            'Accept'=> '*/*',
            'Accept-Language'=> 'en-US,en;q=0.5',
            'Accept-Encoding'=> 'gzip, deflate, br',
            'Referer'=> 'https://tools.XXXXX.com/',
            'Cookie'=> '__cfduid=XXXXXX; optimizelyEndUserId=oeu1498769689109r0.32368438346411443; optimizelySegments=%7XX; _vis_opt_test_cookie=1; _vwo_uuid=AF15229BDFDAB8BA12asdasB9561E984147AE; _vis_opt_exp_163_combi=2; optimizelyPendingLogEvents=%5B%22n%3Dhttps%253asdasdA%252F%252Ftools.XXXXX.com%252F%26u%3Doeu1498769689109r0.32368438346411443%26wxhr%3Dtrue%26time%3D1asd501859409.619%26asd345435f%3D8430845asd915%26g%3D%22%5D',
            'Connection'=> 'keep-alive'
            ]
            ]
            );
            $URL = 'https://brightonandhovetherapyhub.co.uk/therapist/271/';
            $response = $client->get($URL);
            $html = (string) $response->getBody()->getContents();

            $crawler = new Crawler($html);
            $hubTitle =  $crawler->filter('.therapists-bio > h1')->innerText();
            $hubTitle =  $crawler->filter('.therapists-bio > h1')->innerText();

            $data["title"]  =   $hubTitle;
            // echo $body;
            // echo "<br /><br /><br />";

        return $this->asJson($data);
    }

/*
* used for bacp and ncps
*/
public function actionVerifyBacp(): Response
{
    $verifyLink = Craft::$app->request->getBodyParam('verifyLink');
    $profileId  = Craft::$app->request->getBodyParam('profileId');
    $org        = Craft::$app->request->getBodyParam('org');
    $data = [];

    // Define expected URL patterns per org
    $patterns = [
        'bacp' => [
            'url'    => '/^https:\/\/www\.bacp\.co\.uk\/therapists\/' . preg_quote($profileId, '/') . '\/?$/',
            'format' => '/^\d+$/',  // BACP IDs are numeric only
        ],
        'ncps' => [
            'url'    => '/^https:\/\/www\.search-ncps\.com\/search\/FindaTherapist\/' . preg_quote($profileId, '/') . '\/?$/',
            'format' => '/^NCS\d{2}-\d{5}$/',  // e.g. NCS23-03863
        ],
    ];

    if (!isset($patterns[$org])) {
        $data['statuscode'] = 'false';
        $data['error']      = 'Unknown organisation';
        return $this->asJson($data);
    }

    // Validate the ID format before making any HTTP request
    if (!preg_match($patterns[$org]['format'], $profileId)) {
        $data['statuscode'] = 'false';
        $data['reason']     = 'invalid_id_format';
        return $this->asJson($data);
    }

    $expectedPattern = $patterns[$org]['url'];

    $client = new \GuzzleHttp\Client([
        'cookies' => false,
        'allow_redirects' => [
            'max'             => 10,
            'track_redirects' => true,
        ],
        'http_errors' => false,
        'timeout'     => 15,
        'verify'      => false,
        'headers'     => [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Connection'      => 'keep-alive',
        ],
    ]);

    try {
        $response   = $client->get($verifyLink);
        $statusCode = $response->getStatusCode();

        $redirectHistory = $response->getHeader('X-Guzzle-Redirect-History');
        $finalUrl = !empty($redirectHistory) ? end($redirectHistory) : $verifyLink;

        $urlIsValid = (bool) preg_match($expectedPattern, $finalUrl);

        Craft::info([
            'org'              => $org,
            'requested_url'    => $verifyLink,
            'final_url'        => $finalUrl,
            'status_code'      => $statusCode,
            'url_is_valid'     => $urlIsValid,
        ], 'membership-verify');

        if ($statusCode === 200 && $urlIsValid) {
            $data['statuscode'] = 'true';
        } else {
            $data['statuscode'] = 'false';
            $data['reason']     = $urlIsValid ? 'bad_status' : 'redirected_away';
        }

        return $this->asJson($data);

    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        Craft::error('Connection error: ' . $e->getMessage(), 'membership-verify');
        $data['statuscode'] = 'false';
        $data['error']      = 'Connection failed';
        return $this->asJson($data);

    } catch (\Exception $e) {
        Craft::error('Unexpected error: ' . $e->getMessage(), 'membership-verify');
        $data['statuscode'] = 'false';
        $data['error']      = 'Unexpected error';
        return $this->asJson($data);
    }
}
    /*
    * used for bacp and ncps
    */
public function Claude1actionVerifyBacp(): Response
{
    $verifyLink = Craft::$app->request->getBodyParam('verifyLink');
    $profileId  = Craft::$app->request->getBodyParam('profileId');
    $data = [];

    $client = new \GuzzleHttp\Client([
        'cookies' => false,
        'allow_redirects' => [
            'max'             => 10,
            'track_redirects' => true,  // Key addition
        ],
        'http_errors' => false,
        'timeout'     => 15,
        'verify'      => false,
        'headers'     => [
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Connection'      => 'keep-alive',
        ],
    ]);

    try {
        $response   = $client->get($verifyLink);
        $statusCode = $response->getStatusCode();

        // Get the final URL after any redirects
        $redirectHistory = $response->getHeader('X-Guzzle-Redirect-History');
        $finalUrl = !empty($redirectHistory)
            ? end($redirectHistory)   // Last URL in the chain
            : $verifyLink;            // No redirect — original URL is final

        // Check the final URL still matches the expected therapist pattern
        // e.g. https://www.bacp.co.uk/therapists/386443
        $expectedPattern = '/^https:\/\/www\.bacp\.co\.uk\/therapists\/' . preg_quote($profileId, '/') . '\/?$/';
        $urlIsValid = (bool) preg_match($expectedPattern, $finalUrl);

        Craft::info([
            'requested_url'    => $verifyLink,
            'final_url'        => $finalUrl,
            'status_code'      => $statusCode,
            'redirect_history' => $redirectHistory,
            'url_is_valid'     => $urlIsValid,
        ], 'bacp-verify');

        if ($statusCode === 200 && $urlIsValid) {
            $data['statuscode'] = 'true';
        } else {
            $data['statuscode'] = 'false';
            $data['reason']     = $urlIsValid ? 'bad_status' : 'redirected_away';
        }

        return $this->asJson($data);

    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        Craft::error('Connection error: ' . $e->getMessage(), 'bacp-verify');
        $data['statuscode'] = 'false';
        $data['error']      = 'Connection failed';
        return $this->asJson($data);

    } catch (\Exception $e) {
        Craft::error('Unexpected error: ' . $e->getMessage(), 'bacp-verify');
        $data['statuscode'] = 'false';
        $data['error']      = 'Unexpected error';
        return $this->asJson($data);
    }
}    
public function OLD2actionVerifyBacp(): Response
{
    $verifyLink = Craft::$app->request->getBodyParam('verifyLink');
    $data = array();
    
    $client = new \GuzzleHttp\Client([
        'cookies' => false,
        'allow_redirects' => true,
        'http_errors' => false,
        'timeout' => 15,
        'verify' => false,
        'debug' => false, // Set to true temporarily to see full request/response
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Connection' => 'keep-alive',
        ]
    ]);
    
    try {
        $response = $client->get($verifyLink);
        
        $statusCode = $response->getStatusCode();
        
        // Log detailed response for debugging
        Craft::info([
            'url' => $verifyLink,
            'status_code' => $statusCode,
            'headers' => $response->getHeaders(),
            'body_preview' => substr($response->getBody()->getContents(), 0, 500)
        ], 'bacp-verify');
        
        if ($statusCode == 200) {
            $data["statuscode"] = 'true';
        } else {
            $data["statuscode"] = $statusCode;
        }
        
        return $this->asJson($data);
        
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        // Connection timeouts, DNS failures, etc.
        Craft::error('Connection error: ' . $e->getMessage(), 'bacp-verify');
        $data["statuscode"] = 'false';
        $data["error"] = 'Connection failed: ' . $e->getMessage();
        return $this->asJson($data);
        
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // HTTP errors
        Craft::error('Request error: ' . $e->getMessage(), 'bacp-verify');
        $data["statuscode"] = 'false';
        $data["error"] = 'Request failed: ' . $e->getMessage();
        
        if ($e->hasResponse()) {
            $data["error_code"] = $e->getResponse()->getStatusCode();
        }
        
        return $this->asJson($data);
        
    } catch (\Exception $e) {
        // Any other errors
        Craft::error('Unexpected error: ' . $e->getMessage(), 'bacp-verify');
        $data["statuscode"] = 'false';
        $data["error"] = 'Unexpected error: ' . $e->getMessage();
        return $this->asJson($data);
    }
}
    public function OLDactionVerifyBacp(): Response
    {
        $verifyLink = Craft::$app->request->getBodyParam('verifyLink');
        $org = Craft::$app->request->getBodyParam('org');
        $profileId = Craft::$app->request->getBodyParam('profileId');
        // $incomingparams = Craft::$app->request->getRawBody();
        // $params = craft\helpers\Json::decode($incomingparams);

        
        //var_dump($verifyLink);die();
        $data = array();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //set header information including cookies, referer, etc. 
        
        // new GuzzleHttp\Client
        $client = new \GuzzleHttp\Client([
            'cookies' => false,
            'allow_redirects' => false,
            'headers' => [
            'Host'=> 'google.com',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
            'Accept'=> '*/*',
            'Accept-Language'=> 'en-US,en;q=0.5',
            'Accept-Encoding'=> 'gzip, deflate, br',
            'Referer'=> 'https://www.google.com/',
            'Connection'=> 'keep-alive',
            'http_errors' => false
            ]
            ]
            );

            try {
                $URL = $verifyLink;
                $response = $client->get($URL);
                // var_dump($response); die();
                if ($response->getStatusCode() == "200"){
                    $data["statuscode"]  = 'true';
                }else{
                    $data["statuscode"]  = $response->getStatusCode();
                }
                return $this->asJson($data);
                // Process response normally...
            } catch (RequestException $e) {
                // An exception was raised but there is an HTTP response body
                // with the exception (in case of 404 and similar errors)
                $data["statuscode"]  = 'false'; //$response->getStatusCode();
                
                return $this->asJson($data);
                // $response = $e->getResponse();
                // $responseBodyAsString = $response->getBody()->getContents();
                // // echo $response->getStatusCode() . PHP_EOL;
                // // echo $responseBodyAsString;
                // return $this->asJson($responseBodyAsString);
            }
           
     
    }

    public function actionVerifyUkcp(): Response
    {
        
        // format "https://www.psychotherapy.org.uk/therapist/Maja-Andersen-JJYWLQA5"; 
        $verifyLink = Craft::$app->request->getBodyParam('verifyLink');
        // Craft::$app->setcookies(['verified' => 'false']);
        setcookie("verified", $value="false", time()+3600);  /* expire in 1 hour */

        // $incomingparams = Craft::$app->request->getRawBody();
        // $params = craft\helpers\Json::decode($incomingparams);
        //     print_r($verifyLink); die();
        // var_dump($verifyLink);die();
        $data = array();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //set header information including cookies, referer, etc. 
        // new GuzzleHttp\Client
        $client = new \GuzzleHttp\Client([
            'cookies' => false,
            'allow_redirects' => false,
            'headers' => [
            'Host'=> 'google.com',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
            'Accept'=> '*/*',
            'Accept-Language'=> 'en-US,en;q=0.5',
            'Accept-Encoding'=> 'gzip, deflate, br',
            'Referer'=> 'https://www.google.com/',
            'Connection'=> 'keep-alive',
            'http_errors' => false
            ]
            ]
            );

            try {
                $URL = $verifyLink;
                // $response = $client->get("https://www.psychotherapy.org.uk/therapist/Roz-Read-iAhuAAAS");
                 $response = $client->get($URL);
                
                //test
                $data["statuscode"] = $response->getStatusCode();
                return $this->asJson($data);
                //end test
                // if ($response->getStatusCode() == "200"){
                //     setcookie("verified", $value="true", time()+3600);  /* expire in 1 hour */
                //     $data["statuscode"]  = 'true';
                    
                // }
                // if ($response->getStatusCode() !== "200"){
                //     $data["statuscode"]  = 'false'; 
                    
                // }
                
                // return $this->asJson($data);
                // Process response normally...
            } catch (RequestException $e) {
                // An exception was raised but there is an HTTP response body
                // with the exception (in case of 404 and similar errors)
                $data["statuscode"]  = 'false'; //$response->getStatusCode();
                
                return $this->asJson($data);
                // $response = $e->getResponse();
                // $responseBodyAsString = $response->getBody()->getContents();
                // // echo $response->getStatusCode() . PHP_EOL;
                // // echo $responseBodyAsString;
                // return $this->asJson($responseBodyAsString);
            }
        }
           
     public function actionVerifyHCPC(): Response
    {
        
        // format //HCPC https://www.hcpc-uk.org/check-the-register/professional-registration-detail/?query=PYL041225&profession=PYL

        $verifyLink = Craft::$app->request->getBodyParam('verifyLink');
        // Craft::$app->setcookies(['verified' => 'false']);
        setcookie("verified", $value="false", time()+3600);  /* expire in 1 hour */

        // $incomingparams = Craft::$app->request->getRawBody();
        // $params = craft\helpers\Json::decode($incomingparams);
        //     print_r($verifyLink); die();
        // var_dump($verifyLink);die();
        $data = array();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //set header information including cookies, referer, etc. 
        // new GuzzleHttp\Client
        $client = new \GuzzleHttp\Client([
            'cookies' => false,
            'allow_redirects' => false,
            'headers' => [
            'Host'=> 'google.com',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
            'Accept'=> '*/*',
            'Accept-Language'=> 'en-US,en;q=0.5',
            'Accept-Encoding'=> 'gzip, deflate, br',
            'Referer'=> 'https://www.google.com/',
            'Connection'=> 'keep-alive',
            'http_errors' => false
            ]
            ]
            );

            try {
                $URL = $verifyLink;
                // $response = $client->get("https://www.psychotherapy.org.uk/therapist/Roz-Read-iAhuAAAS");
                 $response = $client->get($URL);
                
                //test
                $data["statuscode"] = $response->getStatusCode();
                return $this->asJson($data);
                //end test
                // if ($response->getStatusCode() == "200"){
                //     setcookie("verified", $value="true", time()+3600);  /* expire in 1 hour */
                //     $data["statuscode"]  = 'true';
                    
                // }
                // if ($response->getStatusCode() !== "200"){
                //     $data["statuscode"]  = 'false'; 
                    
                // }
                
                // return $this->asJson($data);
                // Process response normally...
            } catch (RequestException $e) {
                // An exception was raised but there is an HTTP response body
                // with the exception (in case of 404 and similar errors)
                $data["statuscode"]  = 'false'; //$response->getStatusCode();
                
                return $this->asJson($data);
                // $response = $e->getResponse();
                // $responseBodyAsString = $response->getBody()->getContents();
                // // echo $response->getStatusCode() . PHP_EOL;
                // // echo $responseBodyAsString;
                // return $this->asJson($responseBodyAsString);
            }
        }

    public function actionVerifyBacpRegister(): Response
    {
        $profileId = Craft::$app->request->getBodyParam('profileId');
        $data = [];

        if (!$profileId || !preg_match('/^\d+$/', $profileId)) {
            $data['statuscode'] = 'false';
            $data['error'] = 'Invalid membership ID format';
            return $this->asJson($data);
        }

        $searchUrl = 'https://www.bacp.co.uk/search/Register?UserLocation=&q=' . urlencode($profileId) . '&SortOrder';

        $client = new \GuzzleHttp\Client([
            'cookies' => false,
            'allow_redirects' => ['max' => 5, 'track_redirects' => true],
            'http_errors' => false,
            'timeout' => 15,
            'verify' => false,
            'headers' => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-GB,en;q=0.5',
            ],
        ]);

        try {
            $response   = $client->get($searchUrl);
            $statusCode = $response->getStatusCode();
            $html       = (string) $response->getBody()->getContents();

            // Log the full HTML so we can inspect the result card structure
            Craft::info([
                'profileId'   => $profileId,
                'searchUrl'   => $searchUrl,
                'statusCode'  => $statusCode,
                'htmlPreview' => substr($html, 0, 5000),
            ], 'bacp-register-verify');

            $needle = 'aria-label="Membership number: ' . $profileId . '"';
            if ($statusCode === 200 && strpos($html, $needle) !== false) {
                $data['statuscode'] = 'true';
            } else {
                $data['statuscode'] = 'false';
                $data['reason']     = $statusCode !== 200 ? 'bad_status' : 'not_found_on_register';
            }

            Craft::info([
                'profileId'  => $profileId,
                'searchUrl'  => $searchUrl,
                'statusCode' => $statusCode,
                'matched'    => $data['statuscode'] === 'true',
            ], 'bacp-register-verify');

            return $this->asJson($data);

        } catch (\Exception $e) {
            Craft::error('BACP Register fetch error: ' . $e->getMessage(), 'bacp-register-verify');
            $data['statuscode'] = 'false';
            $data['error']      = 'Fetch failed';
            return $this->asJson($data);
        }
    }

}
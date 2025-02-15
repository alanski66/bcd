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

    protected array|bool|int $allowAnonymous = ['get-data', 'verify-bacp','verify-ukcp'];


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

    public function actionVerifyBacp(): Response
    {
        $verifyLink = Craft::$app->request->getBodyParam('verifyLink');

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
                if ($response->getStatusCode() == "200"){
                    $data["statuscode"]  = 'true';
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

        // $incomingparams = Craft::$app->request->getRawBody();
        // $params = craft\helpers\Json::decode($incomingparams);
            
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
                // $URL = $verifyLink;
                $response = $client->get("https://www.psychotherapy.org.uk/therapist/Maja-Andersen-JJYWLQA5");
                if ($response->getStatusCode() == "200"){
                    $data["statuscode"]  = 'true';
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
           
     

}

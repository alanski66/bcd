<?php

namespace modules\fetchprofile\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
//use guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use Symfony\Component\DomCrawler\Crawler;



/**
 * Fetch Profile controller
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
        $data = array();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //set header information including cookies, referer, etc. 
        // new GuzzleHttp\Client
        $client = new \GuzzleHttp\Client([
            'cookies' => false,
            'headers' => [
            'Host'=> 'google.com',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
            'Accept'=> '*/*',
            'Accept-Language'=> 'en-US,en;q=0.5',
            'Accept-Encoding'=> 'gzip, deflate, br',
            'Referer'=> 'https://www.google.com/',
            'Connection'=> 'keep-alive'
            ]
            ]
            );
            // $URL = 'https://brightonandhovetherapyhub.co.uk/therapist/271/';
            $URL = "https://www.bacp.co.uk/therapists/251337/alan-bordeville/";
            $response = $client->get($URL);
            $html = (string) $response->getBody()->getContents();
var_dump($response->getStatusCode());
            $crawler = new Crawler($html);
            // $hubTitle =  $crawler->filter('.therapists-bio > h1')->innerText();
            // $hubTelLink =  $crawler->filter('a.main-but')->attr('href');

            $mnoLink = $crawler->filter('.profile-summary__link')->attr('href');
            // $subtopic_id = $_GET['subtopic_id'] ?? 3;

            // $data["title"]  =   $html;
            $data["tellink"]  =   $mnoLink;
           // echo $response;
            // echo "<br /><br /><br />";

        return $this->asJson($data);
    }

    public function actionVerifyUkcp(): Response
    {
        $data = array();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        //set header information including cookies, referer, etc. 
        // new GuzzleHttp\Client
        $client = new \GuzzleHttp\Client([
            'cookies' => false,
            'headers' => [
            'Host'=> 'google.com',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
            'Accept'=> '*/*',
            'Accept-Language'=> 'en-US,en;q=0.5',
            'Accept-Encoding'=> 'gzip, deflate, br',
            'Referer'=> 'https://www.google.com/',
            'Connection'=> 'keep-alive'
            ]
            ]
            );
            // $URL = 'https://brightonandhovetherapyhub.co.uk/therapist/271/';
            $URL = "https://www.bacp.co.uk/therapists/385539/irving-d'mello/";
            $response = $client->get($URL);
            $html = (string) $response->getBody()->getContents();

            $crawler = new Crawler($html);
            // $hubTitle =  $crawler->filter('.therapists-bio > h1')->innerText();
            // $hubTelLink =  $crawler->filter('a.main-but')->attr('href');

            $mnoLink = $crawler->filter('img.profile-summary__link')->attr('href');
            // $subtopic_id = $_GET['subtopic_id'] ?? 3;

            $data["title"]  =   $html;
            $data["tellink"]  =   $mnoLink;
            // echo $body;
            // echo "<br /><br /><br />";

        return $this->asJson($data);
    }
}

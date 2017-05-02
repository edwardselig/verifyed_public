<?php
/*---------- Instagram Demographic Algorithm-----------
        Copyright (c) 2015 Edward Selig
            License: Edward Selig*/

/*---------------runtime settings------------*/
ignore_user_abort(true);
set_time_limit(0);
/*---------------include our functions--------*/
include_once '../library/functions.php';
/*---------------include rolling curl------------*/
include_once '../library/rollingcurlx.class.php';

/*---------------include instagram php scraper SDK------------*/
require_once '../library/instagram-php-scraper-master/vendor/autoload.php';
require_once '../library/instagram-php-scraper-master/src/InstagramScraper.php';
use InstagramScraper\Instagram;

/*-------------include AWS SDK--------------*/
require_once '../library/aws/aws-autoloader.php';
use Aws\Lambda\LambdaClient;

/*---------------get inputs-----------------
    input is the influencer username
    login is the instagram account we're logging in with
    password is the password of the login account*/
$ordernum;$login;$input;$password;$email;$date;$cost;
if(isset($_GET['ordernum'],$_GET['login'],$_GET['username'],$_GET['password'],    $_GET['email'],$_GET['date'],$_GET['cost'])){
    $ordernum = $_GET['ordernum'];
    $login = $_GET['login'];
    $input = $_GET['username'];
    $password = $_GET['password'];
    $email = $_GET['email'];
    $date = $_GET['date'];
    $cost = $_GET['cost'];
}else{
    die("Missing Inputs");
}
/*----------------get followers--------------*/
$output = getFollowers($input,$login,$password);

/*----------------load language--------------*/
$language_json = getLanguageJson();

/*-------------compute average likes----------*/
$likesaverage = getLikesAverage($input);

/*----create array where the results will go---*/
$result = createResult();

/*---------create RollingCurl Objects----------*/
$genderCurl = new RollingCurlX(10);
$languageCurl = new RollingCurlX(10);
$faceCurl = new RollingCurlX(10);
$locationCurl = new RollingCurlX(10);

$scrapedNumber = 2500;
$followedby = $account->followedByCount;
$followedby > $scrapedNumber ? $scrapedNumber = $scrapedNumber : $scrapedNumber = $followedby;
count($output) < $scrapedNumber ? $scrapedNumber = count($output) : $scrapedNumber = $scrapedNumber;

/*if output scraping fails re insert
the username into the queue and die*/
checkLogin($output,$login,$email,$input,$order_id,$cost,$date);

$filtered = 0;$private = 0;
scrapeUsers($filtered, $private,$result,$genderCurl,$languageCurl,$faceCurl,$locationCurl,true);
$genderCurl->execute();
$languageCurl->execute();
$faceCurl->execute();
$locationCurl->execute();
?>

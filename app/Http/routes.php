<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/phpinfo', function () use ($app) {
    phpinfo();
});

$app->post('/sign-in', "AjaxController@signIn");
$app->post('/lot-add', "LotController@add");
$app->post('/lot-create', "LotController@create");
$app->post('/lot-list', "LotController@list");
$app->post('/lot-getDownloadUrl', "LotController@getDownloadUrl");
$app->get('/download', "LotController@download");
$app->get('/${id}/{key}', "QrcodeController@use");
$app->get('/qr-ajax-use', "QrcodeController@ajaxUse");
$app->get('/test', "LotController@create");

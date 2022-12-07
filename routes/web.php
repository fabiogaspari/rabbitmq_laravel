<?php

use Illuminate\Support\Facades\Route;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
}); 

Route::get('/send/{message}', function($message) {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

    $channel = $connection->channel();
    $channel->queue_declare('spring-laravel', false, true, false, false);

    $rabbitMsg = new AMQPMessage($message,
            array('content_type' => 'text/plan',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );
    $channel->basic_publish($rabbitMsg, 'ex-spring-laravel', "foo.bar.baz");

    $channel->close();
    $connection->close();

    return response()->json("Sucesso");
});
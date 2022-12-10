<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitmqListeningCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('ex-spring-laravel', 'direct', false, true, false);

        list($queue_name, ,) = $channel->queue_declare("spring-laravel", false, true, false, false);

        $channel->queue_bind($queue_name, 'ex-spring-laravel', 'foo.bar.laravel');

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] ', $msg->body, "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return Command::SUCCESS;
    }
}

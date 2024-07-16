<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DeclareRabbitMQQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:declare-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Declare RabbitMQ queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'rabbitmq'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'tony'),
            env('RABBITMQ_PASSWORD', 'soprano')
        );

        $channel = $connection->channel();
        $channel->queue_declare(env('RABBITMQ_QUEUE', 'default'), false, true, false, false);
        $channel->close();
        $connection->close();

        $this->info('Queue declared successfully.');
    }
}

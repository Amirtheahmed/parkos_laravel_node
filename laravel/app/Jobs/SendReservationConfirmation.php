<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SendReservationConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Reservation $reservation;

    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.username'),
            config('queue.connections.rabbitmq.password')
        );
        $channel = $connection->channel();
        $channel->queue_declare('email_queue', false, true, false, false);

        $msgBody = json_encode([
            'email' => $this->reservation->customer_email,
            'message' => 'Your reservation is confirmed.'
        ]);
        $msg = new AMQPMessage($msgBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, '', 'email_queue');

        $channel->close();
        $connection->close();
    }
}

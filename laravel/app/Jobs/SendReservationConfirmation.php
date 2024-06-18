<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SendReservationConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(private Reservation $reservation)
    {
        $this->onQueue('email');
    }

    public function handle()
    {
        try {
            $connection = new AMQPStreamConnection(
                config('queue.connections.rabbitmq.host'),
                config('queue.connections.rabbitmq.port'),
                config('queue.connections.rabbitmq.username'),
                config('queue.connections.rabbitmq.password')
            );
        } catch (\Exception $exception) {
            Log::error("Error connecting to RabbitMQ instance: " . $exception->getMessage());
            return;
        }

        $channel = $connection->channel();
        $channel->queue_declare('reservation_email_queue', false, true, false, false);

        $msgBody = json_encode([
            'reservation_id'    => $this->reservation->id,
            'reservation_code'  => $this->reservation->reservation_code,
            'status'            => $this->reservation->payment_status,
            'email'             => $this->reservation->customer_email,
            'message'           => 'Your reservation is confirmed.'
        ]);
        $msg = new AMQPMessage($msgBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, '', 'reservation_email_queue');

        $channel->close();
        $connection->close();
    }
}

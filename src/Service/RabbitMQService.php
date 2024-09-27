<?php
namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Service class for interacting with RabbitMQ.
 */
class RabbitMQService
{
    /**
     * @var AMQPStreamConnection|null The connection to RabbitMQ.
     */
    protected ?AMQPStreamConnection $connection = null;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel|null The channel for communication.
     */
    protected ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    /**
     * Constructor to initialize RabbitMQ connection and channel.
     *
     * @throws \RuntimeException If RabbitMQ configuration is incomplete.
     */
    public function __construct()
    {
        $host = Configure::read('RabbitMQ.host');
        $port = Configure::read('RabbitMQ.port');
        $user = Configure::read('RabbitMQ.user');
        $password = Configure::read('RabbitMQ.password');

        // Check if configuration values are not null or empty
        if (empty($host) || empty($port) || empty($user) || empty($password)) {
            throw new \RuntimeException('RabbitMQ configuration is incomplete');
        }

        try {
            $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
            $this->channel = $this->connection->channel();
        } catch (Exception $e) {
            Log::error("RabbitMQ connection error: " . $e->getMessage());
            throw new \RuntimeException('Failed to connect to RabbitMQ', 0, $e);
        }
    }

    /**
     * Sends a message to a specified RabbitMQ queue.
     *
     * @param string $queue The name of the queue to send the message to.
     * @param string $message The message to be sent.
     * @return void
     */
    public function sendMessage(string $queue, string $message): void
    {
        try {
            // Declare the queue
            $this->channel->queue_declare($queue, false, false, false, false);

            // Create a message
            $msg = new AMQPMessage($message);

            // Publish the message to the specified queue
            $this->channel->basic_publish($msg, '', $queue);
        } catch (Exception $e) {
            Log::error("RabbitMQ send message error: " . $e->getMessage());
            throw new \RuntimeException('Failed to send message to RabbitMQ', 0, $e);
        }
    }

    /**
     * Destructor to close the channel and connection.
     */
    public function __destruct()
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (Exception $e) {
            Log::error("RabbitMQ close connection error: " . $e->getMessage());
        }
    }
}

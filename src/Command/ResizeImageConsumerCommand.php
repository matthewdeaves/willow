<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Exception;
use Imagick;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;

/**
 * ResizeImageConsumerCommand
 *
 * This command class is responsible for consuming messages from a RabbitMQ queue
 * and processing image uploads by resizing them to specified widths.
 *
 * It establishes a connection to a RabbitMQ server using configured settings,
 * listens for messages on the 'image_uploads' queue, and processes each message
 * by resizing the uploaded image to various widths specified in the application
 * configuration.
 *
 * The class uses Imagick for image manipulation and PhpAmqpLib for RabbitMQ
 * communication.
 *
 * @package App\Command
 * @uses \Cake\Command\Command
 * @uses \Cake\Console\Arguments
 * @uses \Cake\Console\ConsoleIo
 * @uses \Cake\Core\Configure
 * @uses \Imagick
 * @uses \PhpAmqpLib\Connection\AMQPStreamConnection
 */
class ResizeImageConsumerCommand extends Command
{
    //store the IO object so we dont have to pass it around
    protected ConsoleIo $io;

    /**
     * Executes the command to consume messages from a RabbitMQ queue and process image uploads.
     *
     * This method establishes a connection to a RabbitMQ server using the configured settings.
     * It declares a queue named 'image_uploads' and waits for incoming messages. Upon receiving
     * a message, it decodes the JSON payload and processes image resizing for each specified
     * width in the configuration.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O object for output.
     * @return int Returns CODE_SUCCESS upon completion.
     * @throws \Exception If there's an error connecting to RabbitMQ or processing messages.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        //save reference for IO
        $this->io = $io;

        $host = Configure::read('SiteSettings.RabbitMQ.host');
        $port = Configure::read('SiteSettings.RabbitMQ.port');
        $user = Configure::read('SiteSettings.RabbitMQ.username');
        $password = Configure::read('SiteSettings.RabbitMQ.password');

        try {
            $connection = new AMQPStreamConnection($host, $port, $user, $password);
            $channel = $connection->channel();

            $channel->queue_declare('image_uploads', false, false, false, false);
            $io->out(' [*] Waiting for messages. To exit press CTRL+C');

            $callback = function ($msg): void {
                echo ' [x] Received ' . $msg->body . "\n";
                $jsonObj = json_decode($msg->body);

                foreach (Configure::read('SiteSettings.ImageSizes') as $width) {
                    $this->createImage($jsonObj->{'path'}, $width);
                }
            };

            $channel->basic_consume('image_uploads', '', false, true, false, false, $callback);

            try {
                $channel->consume();
            } catch (Throwable $exception) {
                $io->out($exception->getMessage());
            }
        } catch (Exception $e) {
            $io->out('RabbitMQ exception: ' . $e->getMessage());
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Function to resize the image to the sizes set in config/app.php
     * Uses Image Magick for PHP
     *
     * @param string $original The path to the original image to resize.
     * @param int $width The width to resize to
     * @return void returns void
     */
    private function createImage(string $original, int $width): void
    {
        try {
            if (file_exists($original)) {
                //create the file if the resized version does not exist or if overwriteExistingImages set to true
                if (!file_exists($original . '_' . $width)) {
                    // Create an Imagick object
                    $imagick = new Imagick($original);

                    // Resize the image to 200 pixels wide while maintaining the aspect ratio
                    $imagick->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);

                    // Save the resized image
                    $imagick->writeImage($original . '_' . $width);

                    // Clear the Imagick object
                    $imagick->clear();

                    $this->io->out('Saved image: ' . $original . '_' . $width);
                } else {
                    $this->io->out('Skipped saving image: ' . $original . '_' . $width);
                }
            }
        } catch (Exception $e) {
            $this->io->out('Error resizing image:' . $original . '_' . $width . ' - ' . $e->getMessage());
        }
    }
}

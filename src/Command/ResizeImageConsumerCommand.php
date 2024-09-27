<?php
namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Exception;
use Imagick;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ResizeImageConsumerCommand extends Command
{
    //store the IO object so we dont have to pass it around
    protected ConsoleIo $io;

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        //save reference for IO
        $this->io = $io;

        $host = Configure::read('RabbitMQ.host');
        $port = Configure::read('RabbitMQ.port');
        $user = Configure::read('RabbitMQ.user');
        $password = Configure::read('RabbitMQ.password');

        try {
            $connection = new AMQPStreamConnection($host, $port, $user, $password);
            $channel = $connection->channel();

            $channel->queue_declare('image_uploads', false, false, false, false);
            $io->out(" [*] Waiting for messages. To exit press CTRL+C");

            $callback = function ($msg) {
                echo ' [x] Received ' . $msg->body . "\n";
                $jsonObj = json_decode($msg->body);

                foreach (Configure::read('ImageSizes') as $width) {
                    $this->createImage($jsonObj->{'path'}, intval($width));
                }
            };

            $channel->basic_consume('image_uploads', '', false, true, false, false, $callback);

            try {
                $channel->consume();
            } catch (\Throwable $exception) {
                $io->out($exception->getMessage());
            }

        } catch (Exception $e) {
            $io->out("RabbitMQ exception: " . $e->getMessage());
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

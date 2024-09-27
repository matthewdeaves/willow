<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Exception;
use Imagick;

/**
 * ResizeImages command.
 */
class ResizeImagesCommand extends Command
{
    /**
     * /store the model names that have images we need to process
     *
     * @var array<string>
     */
    protected array $modelsWithImages = [
        'Users' => 'picture_file',
        'Images' => 'image_file',
    ];

    //Store if we should overwrite existing images or not
    protected bool|string|null $skipExistingImages;

    //store the IO object so we dont have to pass it around
    protected ConsoleIo $io;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addOption('skipExistingImages', [
            'short' => 's',
            'help' => 'Use bin/cake resize_images -s to skip overwriting existing resized images',
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        //set if we are overwriting images from the command option (default true)
        $this->skipExistingImages = $args->getOption('skipExistingImages');

        //save reference for IO
        $this->io = $io;
        //for our models that have images, get their table
        foreach ($this->modelsWithImages as $modelWithImage => $column) {
            $imagesTable = $this->fetchTable($modelWithImage);
            $images = $imagesTable->find('all')
            ->select(['id', $column])
            ->where([$column . ' IS NOT' => null])
            ->toArray();

            foreach ($images as $image) {
                $original = WWW_ROOT . 'files/' . $modelWithImage . DS . $column . DS . $image->{$column};
                if (file_exists($original)) {
                    foreach (Configure::read('SiteSettings.ImageSizes') as $width) {
                        $this->createImage($original, intval($width));
                    }
                }
            }
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
                if (!file_exists($original . '_' . $width) || !$this->skipExistingImages) {
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

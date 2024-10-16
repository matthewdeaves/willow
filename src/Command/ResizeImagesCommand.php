<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\SettingsManager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Exception;
use Imagick;

/**
 * ResizeImages command.
 *
 * This command is responsible for resizing images for specified models.
 */
class ResizeImagesCommand extends Command
{
    /**
     * Stores the model names and their respective image columns to process.
     *
     * @var array<string, string>
     */
    protected array $modelsWithImages = [
        'Users' => 'picture',
        'Images' => 'file',
    ];

    /**
     * Indicates whether to skip overwriting existing resized images.
     *
     * @var string|bool|null
     */
    protected bool|string|null $skipExistingImages;

    /**
     * Stores the ConsoleIo instance for output operations.
     *
     * @var \Cake\Console\ConsoleIo
     */
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
     * Executes the command to resize images.
     *
     * This method iterates through the specified models, retrieves images,
     * and resizes them according to the configured sizes.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code of the command.
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
                    foreach (SettingsManager::read('ImageSizes') as $width) {
                        $this->createImage($original, intval($width));
                    }
                }
            }
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Resizes an image to the specified width.
     *
     * This method uses ImageMagick to resize the image while maintaining the aspect ratio.
     * It also handles file existence checks and skipping based on the skipExistingImages option.
     *
     * @param string $original The path to the original image to resize.
     * @param int $width The width to resize to.
     * @return void
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

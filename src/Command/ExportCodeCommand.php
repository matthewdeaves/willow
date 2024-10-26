<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * ExportCodeCommand
 *
 * This command exports all custom code with relative paths to a text file or separate files.
 * This is useful if you want to have AI work with the source code.
 */
class ExportCodeCommand extends Command
{
    /**
     * Build the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to be modified.
     * @return \Cake\Console\ConsoleOptionParser The modified option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Exports all custom code with relative paths')
            ->addOption('separate', [
                'help' => 'Create a separate file for each directory',
                'boolean' => true,
            ]);

        return $parser;
    }

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $rootDir = ROOT;
        $outputFile = $rootDir . DS . 'willow_cms_code.txt';
        $directories = [
            'Models' => $rootDir . DS . 'src' . DS . 'Model',
            'Views' => $rootDir . DS . 'templates',
            'Controllers' => $rootDir . DS . 'src' . DS . 'Controller',
            'Commands' => $rootDir . DS . 'src' . DS . 'Command',
            'Jobs' => $rootDir . DS . 'src' . DS . 'Job',
            'Services' => $rootDir . DS . 'src' . DS . 'Service',
            'Utilities' => $rootDir . DS . 'src' . DS . 'Utility',
            'Logs' => $rootDir . DS . 'src' . DS . 'Log',
            'Plugins' => $rootDir . DS . 'plugins',
            'Tests' => $rootDir . DS . 'tests',
        ];

        $separateFiles = $args->getOption('separate');

        if (!$separateFiles) {
            $handle = fopen($outputFile, 'w');
            $this->writeMetadata($handle);
        }

        foreach ($directories as $dirName => $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            if ($separateFiles) {
                $handle = fopen($rootDir . DS . "willow_cms_code_{$dirName}.txt", 'w');
                $this->writeMetadata($handle);
            }

            $this->writeSectionHeader($handle, $dirName);

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $this->writeFileContent($handle, $file, $rootDir);
                }
            }

            $this->writeSectionFooter($handle, $dirName);

            if ($separateFiles) {
                fclose($handle);
            }
        }

        if (!$separateFiles) {
            fclose($handle);
            $io->success(sprintf('Code exported successfully to %s', $outputFile));
        } else {
            $io->success('Code exported successfully to separate files.');
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Write metadata to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @return void
     */
    protected function writeMetadata($handle): void
    {
        $metadata = [
            'Export Date' => date('Y-m-d H:i:s'),
            'CakePHP Version' => Configure::version(),
            'PHP Version' => phpversion(),
        ];

        fwrite($handle, "METADATA:\n");
        foreach ($metadata as $key => $value) {
            fwrite($handle, "{$key}: {$value}\n");
        }
        fwrite($handle, "\n" . str_repeat('=', 80) . "\n\n");
    }

    /**
     * Write a section header to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @param string $sectionName The name of the section.
     * @return void
     */
    protected function writeSectionHeader($handle, string $sectionName): void
    {
        fwrite($handle, "\n\n" . str_repeat('=', 80) . "\n");
        fwrite($handle, "BEGIN {$sectionName}\n");
        fwrite($handle, str_repeat('=', 80) . "\n\n");
    }

    /**
     * Write a section footer to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @param string $sectionName The name of the section.
     * @return void
     */
    protected function writeSectionFooter($handle, string $sectionName): void
    {
        fwrite($handle, "\n\n" . str_repeat('=', 80) . "\n");
        fwrite($handle, "END {$sectionName}\n");
        fwrite($handle, str_repeat('=', 80) . "\n\n");
    }

    /**
     * Write the content of a file to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @param \SplFileInfo $file The file information.
     * @param string $rootDir The root directory path.
     * @return void
     */
    protected function writeFileContent($handle, SplFileInfo $file, string $rootDir): void
    {
        $relativePath = str_replace($rootDir . DS, '', $file->getPathname());
        $content = file_get_contents($file->getPathname());

        fwrite($handle, "FILE: {$relativePath}\n");
        fwrite($handle, 'LAST MODIFIED: ' . date('Y-m-d H:i:s', $file->getMTime()) . "\n");
        fwrite($handle, 'SIZE: ' . $file->getSize() . " bytes\n");
        fwrite($handle, "CONTENT:\n");
        fwrite($handle, $content);
        fwrite($handle, "\n\n" . str_repeat('-', 80) . "\n\n");
    }
}

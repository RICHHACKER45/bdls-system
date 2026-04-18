<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class ListDirectoryInfo extends Command
{
    // The command name and optional path argument
    protected $signature = 'dir:info {path?}';

    // The description for php artisan list
    protected $description = 'List all files/folders recursively, respecting gitignore and manual excludes';

    public function handle()
    {
        // 1. Setup path and Finder
        $targetPath = $this->argument('path') ?: base_path();
        $finder = new Finder();

        if (!is_dir($targetPath)) {
            $this->error("The path [{$targetPath}] does not exist.");
            return 1;
        }

        $this->info("Scanning: $targetPath");

        // 2. Apply Filters
        $finder
            ->in($targetPath)
            ->ignoreVCSIgnored(true) // Automatically ignores everything in your .gitignore
            ->exclude([
                'node_modules',
                'vendor',
                'public/build',
                'storage/pail',
                '.fleet',
                '.idea',
                '.vscode',
                '.zed',
            ])
            ->notName([
                '*.log',
                '.DS_Store',
                '.env*',
                '.phpactor.json',
                'auth.json',
                'Thumbs.db',
                'envkey.txt',
            ])
            ->notPath('storage/*.key');

        // 3. Build Table Data
        $headers = ['Relative Path', 'Type', 'Size', 'Last Modified'];
        $data = [];

        foreach ($finder as $item) {
            $data[] = [
                $item->getRelativePathname(),
                $item->isDir() ? '<fg=blue>Directory</>' : 'File',
                $item->isDir() ? '-' : $this->formatBytes($item->getSize()),
                date('Y-m-d H:i', $item->getMTime()),
            ];
        }

        // 4. Output Results
        if (empty($data)) {
            $this->warn('No files found matching the criteria.');
        } else {
            $this->table($headers, $data);
            $this->info("\nTotal items found: " . count($data));
        }
    }

    /**
     * Helper to make file sizes human-readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

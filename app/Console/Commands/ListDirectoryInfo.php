<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ListDirectoryInfo extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dir:info {path?}';

    /**
     * The console command description.
     */
    protected $description = 'List structural project directories and files based on custom inclusion rules';

    public function handle(): int
    {
        $targetPath = $this->argument('path') ?: base_path();
        $finder = new Finder();

        if (! is_dir($targetPath)) {
            $this->error("The path [{$targetPath}] does not exist.");

            return 1;
        }

        $this->info("Scanning directory: {$targetPath}");

        // Scan the target directory
        $finder->in($targetPath)
            ->depth('< 10') // Prevents potential infinite recursion loops
            ->filter(function (SplFileInfo $file) use ($targetPath): bool {
                // Get path relative to base directory (standardized separators)
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());

                return $this->shouldIncludePath($relativePath);
            });

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

        if (empty($data)) {
            $this->warn('No files or directories found matching the specified scope.');

            return 0;
        }

        $this->table($headers, $data);
        $this->info("\nTotal items listed: ".count($data));

        return 0;
    }

    /**
     * Evaluate strict inclusion rules based on requirements.
     */
    private function shouldIncludePath(string $path): bool
    {
        // 1. Root level main folders allowed
        $allowedRootFolders = [
            'app', 
            'bootstrap', 
            'config', 
            'database', 
            'public', 
            'resources', 
            'routes', 
            'storage'
        ];

        $segments = explode('/', $path);
        $rootFolder = $segments[0];

        // Reject any root items not in our explicitly allowed list (e.g., node_modules, vendor, tests)
        if (! in_array($rootFolder, $allowedRootFolders, true)) {
            return false;
        }

        // 2. Specific folder rules:
        
        // bootstrap: exclude 'cache' folder and its contents
        if ($rootFolder === 'bootstrap') {
            if ($path === 'bootstrap/cache' || str_starts_with($path, 'bootstrap/cache/')) {
                return false;
            }
        }

        // storage: include 'storage/app' ONLY. Exclude 'framework', 'logs', or root storage files
        if ($rootFolder === 'storage') {
            if ($path !== 'storage/app' && ! str_starts_with($path, 'storage/app/')) {
                return false;
            }
        }

        // All other allowed roots (app, config, database, public, resources, routes) pass recursively
        return true;
    }

    /**
     * Format bytes into human-readable strings.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = (int) floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1024 ** $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
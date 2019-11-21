<?php

namespace Statamic\Console\Commands;

use Statamic\Facades\YAML;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Illuminate\Filesystem\Filesystem;

class SiteClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:site:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a fresh site, wiping away all content';

    /**
     * Filesystem.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->files = app(Filesystem::class);

        $this
            ->clearCollections()
            ->clearStructures()
            ->clearAssets()
            ->clearViews()
            ->clearUsers()
            ->resetStatamicConfigs();
    }

    /**
     * Clear all collections.
     *
     * @return $this
     */
    protected function clearCollections()
    {
        $this->files->cleanDirectory(base_path('content/collections'));

        $this->info('Collections cleared successfully.');

        return $this;
    }

    /**
     * Clear all structures.
     *
     * @return $this
     */
    protected function clearStructures()
    {
        $this->files->cleanDirectory(base_path('content/structures'));

        $this->info('Structures cleared successfully.');

        return $this;
    }

    /**
     * Clear all assets.
     *
     * @return $this
     */
    protected function clearAssets()
    {
        $path = base_path('content/assets');

        if ($this->files->exists($path)) {
            collect($this->files->files($path))->each(function ($container) {
                $this->removeAssetContainerDisk($container);
            });
        }

        $this->files->cleanDirectory($path);

        $this->info('Assets cleared successfully.');

        return $this;
    }

    /**
     * Remove asset container disk.
     */
    protected function removeAssetContainerDisk($container)
    {
        if ($container->getExtension() !== 'yaml') {
            return;
        }

        if (! $disk = YAML::parse($container->getContents())['disk'] ?? false) {
            return;
        }

        // TODO: Maybe we can eventually bring in and extract this to statamic/migrator's Configurator class...
        $filesystemsPath = config_path('filesystems.php');
        $filesystems = $this->files->get($filesystemsPath);
        $updatedFilesystems = preg_replace("/\s{8}['\"]{$disk}['\"]\X*\s{8}\],?+\n\n?+/mU", '', $filesystems);

        $this->files->put($filesystemsPath, $updatedFilesystems);
    }

    /**
     * Clear all views.
     *
     * @return $this
     */
    protected function clearViews()
    {
        $this->files->cleanDirectory(resource_path('views'));

        $this->info('Views cleared successfully.');

        return $this;
    }

    /**
     * Clear all users.
     *
     * @return $this
     */
    protected function clearUsers()
    {
        $this->files->cleanDirectory(base_path('users'));

        $this->info('Users cleared successfully.');

        return $this;
    }

    /**
     * Reset statamic configs to defaults.
     *
     * @return $this
     */
    protected function resetStatamicConfigs()
    {
        $this->files->cleanDirectory(config_path('statamic'));

        $this->files->copyDirectory(__DIR__.'/../../../config', config_path('statamic'));
        $this->files->copy(__DIR__.'/stubs/config/stache.php.stub', config_path('statamic/stache.php'));
        $this->files->copy(__DIR__.'/stubs/config/users.php.stub', config_path('statamic/users.php'));

        $this->info('Statamic configs cleared successfully.');

        return $this;
    }
}
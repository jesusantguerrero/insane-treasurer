<?php

namespace Insane\Treasurer\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treasurer:install ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install insane components and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->installInertiaStack();
    }


    /**
     * Install the Inertia stack into the application.
     *
     * @return void
     */

    /**
     * Install the Inertia stack into the application.
     *
     * @return void
     */
    protected function installInertiaStack()
    {

          // Install NPM packages...
          $this->updateNodePackages(function ($packages) {
            return [
                'date-fns' => '^2.16.1',
            ] + $packages;
        });

        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Atmosphere'));
        (new Filesystem)->ensureDirectoryExists(public_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Treasurer'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/Billing'));

        // Service Providers
        copy(__DIR__.'/../../stubs/app/Providers/TreasurerServiceProvider.php', app_path('Providers/TreasurerServiceProvider.php')); 

         // Actions...
         copy(__DIR__.'/../../stubs/app/Actions/Atmosphere/ResolveBillable.php', app_path('Actions/Atmosphere/ResolveBillable.php'));

        // Inertia Pages...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Treasurer', resource_path('js/Treasurer'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Pages/Billing', resource_path('js/Pages/Billing'));

        $this->line('');
        $this->info('Inertia scaffolding for treasurer installed successfully.');
        
        // Sync plans
        (new Process(['php', 'artisan', 'treasurer:sync-plans'], base_path()))
        ->setTimeout(null)
        ->run(function ($type, $output) {
            $this->output->write($output);
        });
        $this->line('');
        $this->info('Paypal plans loaded successfully.');

        $this->comment('Please execute "npm install && npm run dev" to build your assets.');
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $command = array_merge(
            ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Delete the "node_modules" directory and remove the associated lock files.
     *
     * @return void
     */
    protected static function flushNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}

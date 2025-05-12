<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CheckTapConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tap:check-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check TAP payment gateway configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking TAP payment gateway configuration...');

        // Check environment variables
        $this->info('Checking environment variables:');
        $tapBaseUrl = env('TAP_BASE_URL');
        $tapSecretKey = env('TAP_SECRET_KEY');

        if (empty($tapBaseUrl)) {
            $this->error('TAP_BASE_URL is not set in .env file');
        } else {
            $this->info('TAP_BASE_URL: ' . $tapBaseUrl);
        }

        if (empty($tapSecretKey)) {
            $this->error('TAP_SECRET_KEY is not set in .env file');
        } else {
            // Only show first few characters of the secret key for security
            $maskedKey = substr($tapSecretKey, 0, 10) . '...';
            $this->info('TAP_SECRET_KEY: ' . $maskedKey);
        }

        // Check config values
        $this->info("\nChecking config values:");
        $configBaseUrl = config('services.tap.base_url');
        $configSecretKey = config('services.tap.secret_key');

        if (empty($configBaseUrl)) {
            $this->error('services.tap.base_url is not configured correctly');
        } else {
            $this->info('services.tap.base_url: ' . $configBaseUrl);
        }

        if (empty($configSecretKey)) {
            $this->error('services.tap.secret_key is not configured correctly');
        } else {
            // Only show first few characters of the secret key for security
            $maskedKey = substr($configSecretKey, 0, 10) . '...';
            $this->info('services.tap.secret_key: ' . $maskedKey);
        }

        $this->info("\nVerifying that tap.callback route is properly defined:");
        try {
            $callbackUrl = route('tap.callback');
            $this->info('Callback URL: ' . $callbackUrl);
        } catch (\Exception $e) {
            $this->error('Failed to generate tap.callback route: ' . $e->getMessage());
        }
    }
}

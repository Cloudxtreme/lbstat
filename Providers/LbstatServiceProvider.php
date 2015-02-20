<?php namespace Dynatron\Lbstat\Providers;

use Module;
use Mrcore\Modules\Foundation\Support\ServiceProvider;

class LbstatServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Mrcore Module Tracking
		Module::trace(get_class(), __function__);

		// Register our Artisan Commands
		$this->commands('Dynatron\Lbstat\Console\Commands\LogParser');

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Mrcore Module Tracking
		Module::trace(get_class(), __function__);

		#$this->app->bind('Mreschke\Dbal\DbalInterface', 'Mreschke\Dbal\Mssql');
		
		$this->app->bind('Dynatron\Lbstat\Repositories\LbstatRepositoryInterface', 'Dynatron\Lbstat\Repositories\MssqlLbstatRepository');

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'Dynatron\Lbstat\Repositories\LbstatRepositoryInterface',
		);
	}

}
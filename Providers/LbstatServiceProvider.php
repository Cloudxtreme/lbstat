<?php namespace Dynatron\Lbstat\Providers;

use Module;
use Illuminate\Support\ServiceProvider;
#use Mrcore\Modules\Foundation\Support\ServiceProvider;

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

		// Bind Alias
		$this->app->alias('Dynatron\Lbstat\Repositories\MssqlLbstatRepository', 'Dynatron\Lbstat\Repositories\LbstatRepositoryInterface');

		// Register our Artisan Commands
		$this->commands('Dynatron\Lbstat\Console\Commands\LogParser');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('Dynatron\Lbstat\Repositories\MssqlLbstatRepository');
	}

}
<?php

namespace App\Middleware;

use Mini\Foundation\Application;
use Mini\Http\Response;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\DB;
use Mini\Support\Str;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Closure;
use PDOException;


class HandleProfiling
{
	/**
	 * The application implementation.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;


	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle the given request and get the response.
	 *
	 * @param  $request
	 * @param  $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request, $next);

		// Get the debug flags from configuration.
		$config = $this->app['config'];

		$debug = $config->get('app.debug', false);

		if ($debug && $this->canPatchContent($response)) {
			$withDatabase = $config->get('profiler', 'withDatabase', false);

			$content = str_replace('<!-- DO NOT DELETE! - Profiler -->',
				$this->getInfo($request, $withDatabase),
				$response->getContent()
			);

			$response->setContent($content);
		}

		return $response;
	}

	protected function canPatchContent(SymfonyResponse $response)
	{
		if ((! $response instanceof Response) && is_subclass_of($response, 'Symfony\Component\Http\Foundation\Response')) {
			return false;
		}

		$contentType = $response->headers->get('Content-Type');

		return Str::is('text/html*', $contentType);
	}

	protected function getInfo(SymfonyRequest $request, $withDatabase)
	{
		$requestTime = $request->server('REQUEST_TIME_FLOAT');

		$elapsedTime = sprintf("%01.4f", (microtime(true) - $requestTime));

		//
		$memoryUsage = static::formatSize(memory_get_usage());

		//
		$umax = sprintf("%0d", intval(25 / $elapsedTime));

		if (! $withDatabase) {
			return __('Elapsed Time: <b>{0}</b> sec | Memory Usage: <b>{1}</b> | UMAX: <b>{2}</b>', $elapsedTime, $memoryUsage, $umax);
		}

		$queryLog = $this->getQueryLog();

		$queries = count($queryLog);

		return __('Elapsed Time: <b>{0}</b> sec | Memory Usage: <b>{1}</b> | SQL: <b>{2}</b> {3, plural, one{query} other{queries}} | UMAX: <b>{4}</b>', $elapsedTime, $memoryUsage, $queries, $queries, $umax);
	}

	protected function getQueryLog()
	{
		try {
			$connection = $this->app['db']->connection();

			return $connection->getQueryLog();
		}
		catch (PDOException $e) {
			return array();
		}
	}

	protected static function formatSize($bytes, $decimals = 2)
	{
		$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');

		$factor = floor((strlen($bytes) - 1) / 3);

		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) .@$size[$factor];
	}
}

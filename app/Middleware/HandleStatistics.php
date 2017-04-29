<?php

namespace App\Middleware;

use Mini\Http\Response;
use Mini\Support\Facades\Config;
use Mini\Support\Str;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Closure;


class HandleStatistics
{

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

        // Get the debug flag from configuration.
        $debug = Config::get('app.debug', false);

        if ($debug && $this->canPatchContent($response)) {
            $content = str_replace('<!-- DO NOT DELETE! - Profiler -->', $this->getReport($request), $response->getContent());

            $response->setContent($content);
        }

        return $response;
    }

    protected function getReport($request)
    {
        $elapsedTime = microtime(true) - $request->server('REQUEST_TIME_FLOAT');

        $elapsedStr = sprintf("%01.4f", $elapsedTime);

        //
        $memoryUsage = static::humanSize(memory_get_usage());

        //
        $umax = sprintf("%0d", intval(25 / $elapsedTime));

        return sprintf('Elapsed Time: <b>%s</b> sec | Memory Usage: <b>%s</b> | UMAX: <b>%s</b>', $elapsedStr, $memoryUsage, $umax);
    }

    protected function canPatchContent(SymfonyResponse $response)
    {
        if ((! $response instanceof Response) && is_subclass_of($response, 'Symfony\Component\Http\Foundation\Response')) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        return Str::is('text/html*', $contentType);
    }

    protected static function humanSize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');

        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

<?php

namespace App\Middleware;

use Mini\Helpers\Profiler;
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
            $statistics = Profiler::getReport($request);

            $content = str_replace('<!-- DO NOT DELETE! - Profiler -->', $statistics, $response->getContent());

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
}

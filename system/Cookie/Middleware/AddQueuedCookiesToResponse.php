<?php

namespace Mini\Cookie\Middleware;

use Mini\Foundation\Application;

use Closure;


class AddQueuedCookiesToResponse
{
    /**
     * The cookie jar instance.
     *
     * @var \Mini\Cookie\CookieJar
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     *
     * @param  \Mini\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->cookies = $app['cookie'];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Mini\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}

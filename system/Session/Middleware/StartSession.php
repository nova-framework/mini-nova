<?php

namespace Mini\Session\Middleware;

use Mini\Http\Request;
use Mini\Session\Store as SessionStore;

use Closure;


class StartSession
{
    /**
     * The session store.
     *
     * @var \Mini\Session\Store
     */
    protected $sessionStore;


    /**
     * Create a new session middleware.
     *
     * @param  \Mini\Session\Store  $session
     * @return void
     */
    public function __construct(SessionStore $store)
    {
        $this->sessionStore = $store;
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
        $session = $this->startSession($request);

        $request->setSession($session);

        $response = $next($request);

        //
        $this->storeCurrentUrl($request, $session);

        return $response;
    }

    /**
     * Start the session for the given request.
     *
     * @param  \Mini\Http\Request  $request
     * @return \Mini\Session\Store
     */
    protected function startSession(Request $request)
    {
        $this->sessionStore->start();

        return $this->sessionStore;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Mini\Http\Request  $request
     * @param  \Mini\Session\SessionInterface  $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        if (($request->method() === 'GET') && $request->route() && ! $request->ajax()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }
}

<?php

namespace Mini\Http;

use Mini\Http\ResponseTrait;
use Nova\Session\Store as SessionStore;

use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;


class RedirectResponse extends SymfonyRedirectResponse
{
    use ResponseTrait;

    /**
     * The request instance.
     *
     * @var \Http\Request
     */
    protected $request;

    /**
     * The session store implementation.
     *
     * @var \Nova\Session\Store
     */
    protected $session;


    /**
     * Add multiple cookies to the response.
     *
     * @param  array  $cookie
     * @return $this
     */
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }

        return $this;
    }

    /**
     * Flash an array of input to the session.
     *
     * @param  array  $input
     * @return $this
     */
    public function withInput(array $input = null)
    {
        $input = $input ?: $this->request->input();

        $this->session->flashInput(array_filter($input, function ($value)
        {
            return ! $value instanceof SymfonyUploadedFile;
        }));

        return $this;
    }

    /**
     * Flash an array of input to the session.
     *
     * @param  mixed  string
     * @return $this
     */
    public function onlyInput()
    {
        return $this->withInput($this->request->only(func_get_args()));
    }

    /**
     * Flash an array of input to the session.
     *
     * @param  mixed  string
     * @return \Nova\Http\RedirectResponse
     */
    public function exceptInput()
    {
        return $this->withInput($this->request->except(func_get_args()));
    }

    /**
     * Get the request instance.
     *
     * @return  \Nova\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the request instance.
     *
     * @param  \Nova\Http\Request  $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the session store implementation.
     *
     * @return \Session\Store
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the session store implementation.
     *
     * @param  \Session\Store  $session
     * @return void
     */
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }

}

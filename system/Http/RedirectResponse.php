<?php

namespace Mini\Http;

use Mini\Http\ResponseTrait;

use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;


class RedirectResponse extends SymfonyRedirectResponse
{
    use ResponseTrait;


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

}

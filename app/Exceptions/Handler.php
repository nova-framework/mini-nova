<?php

namespace App\Exceptions;

use Mini\Foundation\Exceptions\Handler as ExceptionHandler;

use Exception;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = array(
        'Symfony\Component\HttpKernel\Exception\HttpException',
    );

    
    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Mini\Http\Request  $request
     * @param  \Exception  $e
     * @return \Mini\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}

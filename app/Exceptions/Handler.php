<?php

namespace App\Exceptions;

use Mini\Foundation\Exceptions\Handler as ExceptionHandler;
use Mini\Support\Facades\Response;
use Mini\View\View;

use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $status = $e->getStatusCode();

        if (($e instanceof HttpException) && View::exists("Errors/{$status}")) {
            $content = View::make('Layouts/Default')
                ->shares('title', "Error {$status}")
                ->nest('content', "Errors/{$status}", array('exception' => $e))
                ->render();

            return Response::make($content, $status, $e->getHeaders());
        }

        return parent::render($request, $e);
    }

}

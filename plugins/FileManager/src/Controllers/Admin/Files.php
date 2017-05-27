<?php

namespace FileManager\Controllers\Admin;

use Mini\Http\Request;
use Mini\Routing\Route;
use Mini\Support\Facades\App;
use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Response;
use Mini\Support\Facades\View;

use Backend\Controllers\BaseController;


class Files extends BaseController
{
    /**
     * The File Dispatcher instance.
     *
     * @var \Mini\Asset\DispatcherInterface
     */
    private $fileDispatcher;


    public function __construct()
    {
        parent::__construct();

        // Setup the Middleware.
        $this->middleware('role:administrator');
    }

    public function index()
    {
        return $this->getView()
            ->shares('title', __d('files', 'Files'));
    }

    public function connector()
    {
        // Disable the auto-rendering on a (Template) Layout.
        $this->layout = false;

        return $this->getView();
    }

    public function preview(Request $request, $path)
    {
        // Calculate the Preview file path.
        $path = str_replace('/', DS, BASEPATH .ltrim($path, '/'));

        return $this->serveFile($path, $request);
    }

    public function thumbnails(Request $request, $thumbnail)
    {
        // Calculate the thumbnail file path.
        $path = str_replace('/', DS, BASEPATH .'storage/files/thumbnails/' .$thumbnail);

        return $this->serveFile($path, $request);
    }

    /**
     * Return a Symfony Response instance for serving a File
     *
     * @param string $path
     * @param \Mini\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function serveFile($path, $request)
    {
        $dispatcher = $this->getFileDispatcher();

        return $dispatcher->serve($path, $request);
    }

    /**
     * Return a Files Dispatcher instance
     *
     * @return \Mini\Routing\Assets\Dispatcher
     */
    protected function getFileDispatcher()
    {
        if (isset($this->fileDispatcher)) {
			return $this->fileDispatcher;
		}

        return $this->fileDispatcher = App::make('asset.dispatcher');
    }
}

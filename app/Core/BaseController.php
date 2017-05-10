<?php

namespace App\Core;

use Mini\Database\Query\Builder as QueryBuilder;
use Mini\Http\Response;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
use Mini\Support\Facades\View;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;


class BaseController extends Controller
{
    /**
     * The currently used Layout.
     *
     * @var string
     */
    protected $layout = 'Default';


    /**
     * Execute an action on the controller.
     *
     * @param string  $method
     * @param array   $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, array $parameters = array())
    {
        $response = parent::callAction($method, $parameters);

        return $this->processResponse($response);
    }

    /**
     * Method executed after any action.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    protected function processResponse($response)
    {
        if ($response instanceof RenderableInterface) {
            if (! empty($this->layout)) {
                $view = 'Layouts/' .$this->layout;

                $content = View::fetch($view, array('content' => $response->render()));
            } else {
                $content = $response->render();
            }

            return new Response($content);
        } else if (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        }

        return $response;
    }

    /**
     * Return a default View instance.
     *
     * @return \Nova\View\View
     * @throws \BadMethodCallException
     */
    protected function makeView(array $data = array())
    {
        // Get the currently called Action.
        list(, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $method = $caller['function'];

         // Transform the complete class name on a path like variable.
        $classPath = str_replace('\\', '/', static::class);

        // Check for a valid controller on Application.
        if (preg_match('#^(?:.+)/Controllers/(.*)$#s', $classPath, $matches)) {
            $view = $matches[1] .'/' .ucfirst($method);

            return View::make($view, $data);
        }

        throw new BadMethodCallException('Invalid Controller namespace: ' .static::class);
    }

    /**
     * Server Side Processor for DataTables.
     *
     * @param Mini\Database\Query\Builder $query
     * @param array $input
     * @param array $options
     *
     * @return array
     */
    protected function dataTable(QueryBuilder $query, array $input, array $options)
    {
        $columns = array_get($input, 'columns', array());

        // Compute the total count.
        $totalCount = $query->count();

        // Compute the draw.
        $draw = intval(array_get($input, 'draw', 0));

        // Handle the global searching.
        $search = trim(array_get($input, 'search.value'));

        if (! empty($search)) {
            $query->whereNested(function($query) use($columns, $options, $search)
            {
                foreach($columns as $column) {
                    $data = $column['data'];

                    $option = array_first($options, function ($key, $value) use ($data)
                    {
                        return ($value['data'] == $data);
                    });

                    if ($column['searchable'] == 'true') {
                        $query->orWhere($option['field'], 'LIKE', '%' .$search .'%');
                    }
                }
            });
        }

        // Handle the column searching.
        foreach($columns as $column) {
            $data = $column['data'];

            $option = array_first($options, function ($key, $value) use ($data)
            {
                return ($value['data'] == $data);
            });

            $search = trim(array_get($column, 'search.value'));

            if (($column['searchable'] == 'true') && (strlen($search) > 0)) {
                $query->where($option['field'], 'LIKE', '%' .$search .'%');
            }
        }

        // Compute the filtered count.
        $filteredCount = $query->count();

        // Handle the column ordering.
        $orders = array_get($input, 'order', array());

        foreach ($orders as $order) {
            $index = intval($order['column']);

            $column = array_get($input, 'columns.' .$index, array());

            //
            $data = $column['data'];

            $option = array_first($options, function ($key, $value) use ($data)
            {
                return ($value['data'] == $data);
            });

            if ($column['orderable'] == 'true') {
                $dir = ($order['dir'] === 'asc') ? 'ASC' : 'DESC';

                $query->orderBy($option['field'], $dir);
            }
        }

        // Handle the pagination.
        $start  = array_get($input, 'start',  0);
        $length = array_get($input, 'length', 25);

        $query->skip($start)->take($length);

        // Retrieve the data from database.
        $results = $query->get();

        //
        // Format the data on respect of DataTables specs.

        $columns = array();

        foreach ($options as $option) {
            $key = $option['data'];

            //
            $field = array_get($option, 'field');

            $columns[$key] = array_get($option, 'uses', $field);
        }

        //
        $data = array();

        foreach ($results as $result) {
            $record = array();

            foreach ($columns as $key => $value) {
                // Process for standard columns.
                if (is_string($value)) {
                    $record[$key] = $result->{$value};

                    continue;
                }

                // Process for dynamic columns.
                $record[$key] = call_user_func($value, $result, $key);
            }

            $data[] = $record;
        }

        return array(
            "draw"            => $draw,
            "recordsTotal"    => $totalCount,
            "recordsFiltered" => $filteredCount,
            "data"            => $data
        );
    }

    /**
     * Return the current Layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
}

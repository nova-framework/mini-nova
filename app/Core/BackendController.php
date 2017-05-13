<?php

namespace App\Core;

use Mini\Support\Facades\Auth;
use Mini\Support\Facades\View;

use App\Core\BaseController;
use App\Models\Message;


class BackendController extends BaseController
{
	/**
	 * The currently used Layout.
	 *
	 * @var string
	 */
	protected $layout = 'Backend';


	/**
	 * Method executed before any action.
	 */
	protected function before()
	{
		if (! Auth::check()) {
			// The User is not authenticated; nothing to do.
			return;
		}

		$user = Auth::user();

		View::share('currentUser', $user);

		//
		$messages = Message::where('receiver_id', $user->id)->unread()->count();

		View::share('privateMessageCount', $messages);
	}

	/**
	 * Server Side Processor for DataTables.
	 *
	 * @param Mini\Database\Query\Builder|Mini\Database\ORM\Builder $query
	 * @param array $input
	 * @param array $options
	 *
	 * @return array
	 */
	protected function dataTable($query, array $input, array $options)
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
			"draw"			=> $draw,
			"recordsTotal"	=> $totalCount,
			"recordsFiltered" => $filteredCount,
			"data"			=> $data
		);
	}
}

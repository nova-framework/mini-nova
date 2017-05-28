<?php

namespace Taxonomy\Support\Traits;

use Mini\Support\Str;


trait UniqueSlugTrait
{
	public static function uniqueSlug($name, $id = 0)
	{
		$slug = Str::slug($name);

		//
		$segments = explode('-', $slug);

		if ((count($segments) > 1) && is_integer(end($segments))) {
			$count = (int) array_pop($segments);

			$slug = implode('-', $segments);
		} else {
			$count = 0;
		}

		while (true) {
			$search = ($count > 0) ? $slug .'-' .$count : $slug;

			$query = static::where('slug', $search);

			if ($id > 0) {
				$query->where('id', '<>', $id);
			}

			if (! $query->exists()) {
				return $search;
			}

			$count++;
		}
	}
}

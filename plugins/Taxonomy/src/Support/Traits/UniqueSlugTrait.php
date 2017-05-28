<?php

namespace Taxonomy\Support\Traits;

use Mini\Support\Str;


trait UniqueSlugTrait
{
	public static function uniqueSlug($name, $id = 0)
	{
		$segments = explode('-', Str::slug($name));

		if ((count($segments) > 1) && is_integer(end($segments))) {
			$count = (int) array_pop($segments);
		} else {
			$count = 0;
		}

		$name = implode('-', $segments);

		while (true) {
			$slug = ($count > 0) ? $name .'-' .$count : $name;

			//
			$query = static::where('slug', $slug);

			if ($id > 0) {
				$query->where('id', '<>', $id);
			}

			if ($query->exists()) {
				$count++;

				continue;
			}

			return $slug;
		}
	}
}

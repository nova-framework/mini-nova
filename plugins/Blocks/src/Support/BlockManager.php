<?php

namespace Blocks\Support;

use Mini\Container\Container;
use Mini\Http\Request;
use Mini\Support\Facades\Auth;
use Mini\Support\Facades\View;
use Mini\Support\Str;

use Blocks\Models\Block;


class BlockManager
{
	/**
	 * @var Mini\Container\Container
	 */
	protected $container;

	/**
	 * @var Mini\Http\Request
	 */
	protected $request;


	public function __construct(Container $container, Request $request)
	{
		$this->container = $container;

		$this->request = $request;
	}

	public function render($area)
	{
		$mode = Auth::check() ? 'auth' : 'guest';

		$blocks = Block::where('area', $area)->where(function ($query) use ($mode)
		{
			return $query->whereNull('auth_mode')->orWhere('auth_mode', $mode);

		})->get();

		// Render the Blocks found for this area.
		$results = array();

		foreach ($blocks as $block) {
			if ($this->canRenderBlock($block, $mode)) {
				$results[] = $this->renderBlock($block);
			}
		}

		return implode(PHP_EOL, $results);
	}

	protected function renderBlock(Block $block)
	{
		$theme = $this->container['config']->get('app.theme');

		$hideTitle = ($block->hide_title !== 0);

		return View::fetch("$theme::Blocks/Default", array(
			'title'		=> $block->title,
			'content'	=> $block->content,
			'hideTitle' => $hideTitle,
		));
	}

	protected function canRenderBlock($block, $mode)
	{
		if (! $this->visibleForCurrentUser($block, $mode)) {
			return false;
		}

		$path = $this->request->path();

		$paths = isset($block->paths) ? trim($block->paths) : '';

		if (empty($paths)) {
			return true;
		}

		$paths = array_map('trim', explode(PHP_EOL, $paths));

		$patterns = array_filter($paths, function ($value)
		{
			return ! empty($value);
		});

		$inverse = ($block->paths_mode === 1);

		foreach ($patterns as $pattern) {
			if (Str::is($pattern, $path)) {
				return $inverse ? false : true;
			}
		}

		return $inverse ? true : false;
	}

	protected function visibleForCurrentUser($block, $mode)
	{
		if (is_null($block->auth_mode)) {
			return true;
		}

		// If we are on the mode 'guest' the checking is simple.
		else if ($mode === 'guest') {
			return ($block->auth_mode === 'guest');
		}

		// We are on the mode 'auth'
		else if ($block->auth_mode !== 'auth') {
			return false;
		} else if (empty($block->user_roles)) {
			return true;
		}

		$user = Auth::user();

		$roles = array_filter(explode(',', $block->user_roles), function ($value)
		{
			return ! empty($value);
		});

		return $user->hasRole($roles);
	}
}

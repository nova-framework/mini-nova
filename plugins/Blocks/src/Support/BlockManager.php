<?php

namespace Blocks\Support;

use Mini\Container\Container;
use Mini\Http\Request;
use Mini\Support\Facades\Auth;
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

		return $this->processBlocks($blocks, $mode);
	}

	protected function processBlocks($blocks, $mode)
	{
		$results = array();

		foreach ($blocks as $block) {
			if ($this->canRenderBlock($block, $mode)) {
				$results[] = $this->renderBlock($block);
			}
		}

		return implode(PHP_EOL, $results);
	}

	protected function canRenderBlock($block, $mode)
	{
		if (! $this->visibleForCurrentPath($block)) {
			return false;
		}

		return $this->visibleForCurrentUser($block, $mode);
	}

	protected function visibleForCurrentPath($block)
	{
		$paths = isset($block->paths) ? trim($block->paths) : '';

		if (empty($paths)) {
			return true;
		}

		$paths = array_map('trim', explode(PHP_EOL, $paths));

		$patterns = array_filter($paths, function($value)
		{
			return ! empty($value);
		});

		$path = $this->request->path();

		$result = false;

		foreach ($patterns as $pattern) {
			$result = Str::is($pattern, $path);

			if ($result) {
				break;
			}
		}

		return ($block->paths_mode === 1) ? ! $result : $result;
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
		if ($block->auth_mode === 'auth') {
			if (empty($block->user_roles)) {
				return true;
			}

			$user = Auth::user();

			$roles = array_filter(explode(',', $block->user_roles), function($value)
			{
				return ! empty($value);
			});

			return $user->hasRole($roles);
		}

		return false;
	}

	protected function renderBlock(Block $block)
	{
		$content = '';

		//
		$content .= '<h4><strong>' .$block->title  .'</strong></h4><hr style="margin-top: 0;">';

		$content .= '<p>' .$block->content  .'</p><br>';

		return $content;
	}
}

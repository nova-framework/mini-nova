<?php

namespace Blocks\Support;

use Mini\Container\Container;
use Mini\Http\Request;
use Mini\Support\Facades\Auth;

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
			if ($this->blockIsVisible($block, $mode)) {
				$results[] = $this->getBlockContent($block);
			}
		}

		return implode(PHP_EOL, $results);
	}

	protected function blockIsVisible($block, $mode)
	{
		return $this->userHasAccess($block, $mode);
	}

	protected function userHasAccess($block, $mode)
	{
		if (is_null($block->auth_mode)) {
			return true;
		}

		// If we are on the mode 'guest'
		else if ($mode === 'guest') {
			return ($block->auth_mode === 'guest');
		}

		// We are on the mode 'auth'
		else if ($mode === 'auth') {
			$user = Auth::user();

			if ($block->auth_mode === 'auth') {
				if (isset($block->user_roles)) {
					$roles = array_filter(explode(',', $block->user_roles), function($value)
					{
						return ! empty($value);
					});

					if (! empty($roles)) {
						return $user->hasRole($roles);
					}
				}

				return true;
			}
		}

		return false;
	}

	protected function getBlockContent(Block $block)
	{
		$content = '';

		//
		$content .= '<h4><strong>' .$block->title  .'</strong></h4><hr style="margin-top: 0;">';

		$content .= '<p>' .$block->content  .'</p><br>';

		return $content;
	}
}

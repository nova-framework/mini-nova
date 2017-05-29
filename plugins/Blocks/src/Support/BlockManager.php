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
		$authMode = Auth::check() ? 'auth' : 'guest';

		$blocks = Block::where('area', $area)->where(function ($query) use ($authMode)
		{
			return $query->whereNull('auth_mode')->orWhere('auth_mode', $authMode);

		})->get();

		//
		$result = '';

		foreach ($blocks as $block) {
			$result .= $this->gatherBlockContent($block);
		}

		return $result;
	}

	protected function gatherBlockContent(Block $block)
	{
		$content = '';

		//
		$content .= '<h4><strong>' .$block->title  .'</strong></h4><hr style="margin-top: 0;">';

		$content .= '<p>' .$block->content  .'</p><br>';

		return $content;
	}
}
<?php

use Mini\Http\Request;

/**
 * Role-based Authorization Middleware.
 */
Route::middleware('role', function(Request $request, Closure $next, $role)
{
	$roles = array_slice(func_get_args(), 2);

	//
	$guard = Config::get('auth.default', 'web');

	$user = Auth::guard($guard)->user();

	if (! is_null($user) && ! $user->hasRole($roles)) {
		$uri = Config::get("auth.guards.{$guard}.paths.dashboard", 'admin/dashboard');

		$status = __('You are not authorized to access this resource.');

		return Redirect::to($uri)->with('warning', $status);
	}

	return $next($request);
});

/**
 * Request's Referer Middleware.
 */
Route::middleware('referer', function(Request $request, Closure $next)
{
	$referrer = $request->header('referer');

	if (! Str::startsWith($referrer, Config::get('app.url'))) {
		return Redirect::back();
	}

	return $next($request);
});

/**
 * Listener Closure to the Event 'router.executing.controller'.
 */

use Mini\Routing\Controller;

use App\Controllers\BackendController;
use App\Models\Notification;
use App\Models\Message;


Event::listen('router.executing.controller', function(Controller $controller, Request $request, $method, array $parameters)
{
	// Share the Views the current URI.
	View::share('currentUri', $request->path());

	if (($controller instanceof BackendController) && Auth::check()) {
		$user = Auth::user();

		View::share('currentUser', $user);

		//
		$notifications = Notification::where('user_id', $user->id)->unread()->count();

		View::share('notificationCount', $notifications);

		//
		$messages = Message::where('receiver_id', $user->id)->unread()->count();

		View::share('privateMessageCount', $messages);

		// Share the Views the Backend's base URI.
		$segments = $request->segments();

		$path = '';

		if(! empty($segments)) {
			// Make the path equal with the first part if it exists, i.e. 'admin'
			$path = array_shift($segments);

			$segment = ! empty($segments) ? array_shift($segments) : '';

			if (($path == 'admin') && empty($segment)) {
				$path = 'admin/dashboard';
			} else if (! empty($segment)) {
				$path .= '/' .$segment;
			}
		}

		View::share('baseUri', isset($path);
	}
});

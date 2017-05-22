<?php

use Mini\Http\Request;
use Mini\Routing\Controller;

use Backend\Controllers\BaseController as BackendController;
use Backend\Models\Activity;
use Backend\Models\Notification;
use Backend\Models\Message;


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
 * Listener Closure to the Event 'router.executing.controller'.
 */
Event::listen('router.executing.controller', function(Controller $controller, Request $request)
{
	Activity::updateForCurrentUser($request);

	if (! $controller instanceof BackendController) {
		return;
	}

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

	View::share('baseUri', $path);

	// Get the current User instance.
	$user = Auth::user();

	if (is_null($user)) {
		// No further processing for non-authenticated users.
		return;
	}

	View::share('currentUser', $user);

	//
	$notifications = Notification::where('user_id', $user->id)->unread()->count();

	View::share('notificationCount', $notifications);

	//
	$messages = Message::where('receiver_id', $user->id)->unread()->count();

	View::share('privateMessageCount', $messages);
});

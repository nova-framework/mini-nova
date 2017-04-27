<?php

namespace Mini\Helpers;

use Mini\Http\Request;


class Profiler
{

    public static function report(Request $request)
    {
        $timestamp = microtime(true);

        //
        $requestTime = $request->server('REQUEST_TIME_FLOAT');

        $elapsedTime = $timestamp - $requestTime;

        $elapsedStr = sprintf("%01.4f", $elapsedTime);

        //
        $memoryUsage = static::humanSize(memory_get_usage());

        //
        $umax = sprintf("%0d", intval(25 / $elapsedTime));

        return sprintf('Elapsed Time: <b>%s</b> sec | Memory Usage: <b>%s</b> | UMAX: <b>%s</b>', $elapsedStr, $memoryUsage, $umax);
    }

    protected static function humanSize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');

        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

}

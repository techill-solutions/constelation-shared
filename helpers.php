<?php
/**
 * Global Helpers for Lumen
 *
 * These are common Laravel helpers that are not available in Lumen by default.
 */

if (! function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return \Carbon\Carbon
     */
    function now($tz = null)
    {
        return \Carbon\Carbon::now($tz);
    }
}

if (! function_exists('today')) {
    /**
     * Create a new Carbon instance for today.
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return \Carbon\Carbon
     */
    function today($tz = null)
    {
        return \Carbon\Carbon::today($tz);
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  string|array|null  $content
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        $factory = app(\Laravel\Lumen\Http\ResponseFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }
}

<?php declare(strict_types=1);
// =================================================================
// =================================================================
// **   _  ___   _____ _   _ _      _                             **
// **  | || \ \ / / _ ) | | | |    /_\                            **
// **  | __ |\ V /| _ \ |_| | |__ / _ \                           **
// **  |_||_| |_| |___/\___/|____/_/ \_\                          **
// **                                                             **
// **  Copyright (C) HYBULA B.V. - All Rights Reserved            **
// **  This is proprietary and confidential software,             **
// **  unauthorized copying of this code is strictly prohibited.  **
// **                                                             **
// =================================================================
// =================================================================

namespace Hybula\Routex;

class Routex
{

    private static array $routes = [];
    private static array $patterns = [];
    private static \Closure $error;
    private static int $code = 404;

    private static function pcre($pattern): string
    {
        $pattern = ltrim($pattern, '/');
        $pattern = str_replace('/', '\/', $pattern);
        foreach (self::$patterns as $placeholder => $customPattern) {
            $pattern = str_replace($placeholder, $customPattern, $pattern);
        }
        return '/^'.$pattern.'$/';
    }

    public static function patterns(array $patterns): void
    {
        self::$patterns[':domain'] = '((?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63})';
        self::$patterns[':number'] = '(\d+)';
        self::$patterns[':word'] = '(\w+)';
        self::$patterns = array_merge(self::$patterns, $patterns);
    }

    public static function get($pattern, $closure): void
    {
        self::$routes['GET'][self::pcre($pattern)] = $closure;
        self::$routes['GET'][self::pcre($pattern.'/')] = $closure;
    }

    public static function post($pattern, $closure): void
    {
        self::$routes['POST'][self::pcre($pattern)] = $closure;
        self::$routes['POST'][self::pcre($pattern.'/')] = $closure;
    }

    public static function patch($pattern, $closure): void
    {
        self::$routes['PATCH'][self::pcre($pattern)] = $closure;
        self::$routes['PATCH'][self::pcre($pattern.'/')] = $closure;
    }

    public static function put($pattern, $closure): void
    {
        self::$routes['PUT'][self::pcre($pattern)] = $closure;
        self::$routes['PUT'][self::pcre($pattern.'/')] = $closure;
    }

    public static function error($code, $closure): void
    {
        self::$code = $code;
        self::$error = $closure;
    }

    public static function run(): ?bool
    {
        foreach (self::$routes as $method => $data) {
            if ($method == $_SERVER['REQUEST_METHOD']) {
                foreach ($data as $pattern => $closure) {
                    $requestUri = ltrim(rawurldecode($_SERVER['REQUEST_URI']), '/');
                    if (preg_match($pattern, $requestUri, $params)) {
                        array_shift($params);
                        return call_user_func_array($closure, array_values($params));
                    }
                }
            }
        }

        http_response_code(self::$code);
        if (isset(self::$error)) {
            return call_user_func(self::$error);
        }
        return false;
    }

}

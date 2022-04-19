<?php

declare(strict_types=1);
/**
 * =========================================
 * =========================================
 * **   _  ___   _____ _   _ _      _     **
 * **  | || \ \ / / _ ) | | | |    /_\    **
 * **  | __ |\ V /| _ \ |_| | |__ / _ \   **
 * **  |_||_| |_| |___/\___/|____/_/ \_\  **
 * **                                     **
 * =========================================
 * =========================================
 *
 * Routex PHP Router (PHP)
 *
 * @package Hybula\Routex
 * @author Hybula Development Team <development@hybula.com>
 * @version 1.0.1
 * @copyright Hybula B.V.
 * @license MPL-2.0 License
 * @see https://github.com/hybula/php-routex/
 */

namespace Hybula\Routex;

class Routex
{

    private static $routes = [];
    private static $patterns = [];
    private static $error;
    private static $code = 404;
    private static $debug = false;
    private static $base = false;

    private static function pcre($pattern, $redundant = false): string
    {
        if ($redundant) {
            $pattern = ltrim($pattern, '/');
        } else {
            $pattern = trim($pattern, '/');
        }
        if (self::$base) {
            $pattern = self::$base.'/'.$pattern;
        }
        $pattern = str_replace('/', '\/', $pattern);
        foreach (self::$patterns as $placeholder => $customPattern) {
            $pattern = str_replace($placeholder, $customPattern, $pattern);
        }
        return '/^'.$pattern.'$/';
    }

    public static function base($base = false): void
    {
        self::$base = trim($base, '/');
    }

    public static function debug($debug = true): void
    {
        self::$debug = $debug;
    }

    public static function patterns(array $patterns = []): void
    {
        self::$patterns[':domain'] = '((?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63})';
        self::$patterns[':number'] = '(\d+)';
        self::$patterns[':word'] = '(\w+)';
        self::$patterns = array_merge(self::$patterns, $patterns);
    }

    public static function get($pattern, $closure): void
    {
        self::$routes['GET'][self::pcre($pattern)] = $closure;
        self::$routes['GET'][self::pcre($pattern . '/', true)] = $closure;
    }

    public static function post($pattern, $closure): void
    {
        self::$routes['POST'][self::pcre($pattern)] = $closure;
        self::$routes['POST'][self::pcre($pattern . '/', true)] = $closure;
    }

    public static function patch($pattern, $closure): void
    {
        self::$routes['PATCH'][self::pcre($pattern)] = $closure;
        self::$routes['PATCH'][self::pcre($pattern . '/', true)] = $closure;
    }

    public static function put($pattern, $closure): void
    {
        self::$routes['PUT'][self::pcre($pattern)] = $closure;
        self::$routes['PUT'][self::pcre($pattern . '/', true)] = $closure;
    }

    public static function delete($pattern, $closure): void
    {
        self::$routes['DELETE'][self::pcre($pattern)] = $closure;
        self::$routes['DELETE'][self::pcre($pattern . '/', true)] = $closure;
    }

    public static function error($code, $closure): void
    {
        self::$code = $code;
        self::$error = $closure;
    }

    public static function load($directory): void
    {
        foreach (glob(rtrim($directory, '/') . '/*.php') as $route) {
            include $route;
        }
    }

    public static function run(): ?bool
    {
        $requestUri = ltrim(rawurldecode($_SERVER['REQUEST_URI']), '/');
        $requestUri = explode('?', $requestUri)[0];
        foreach (self::$routes as $method => $data) {
            if ($method == $_SERVER['REQUEST_METHOD']) {
                foreach ($data as $pattern => $closure) {
                    if (preg_match($pattern, $requestUri, $params)) {
                        if (self::$debug) {
                            echo 'Success ' . $requestUri . ' >>> ' . $pattern . PHP_EOL;
                        }
                        array_shift($params);
                        return call_user_func_array($closure, array_values($params));
                    }
                    if (self::$debug) {
                        echo 'Failed ' . $requestUri . ' >>> ' . $pattern . PHP_EOL;
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

<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Helpers;

use Tightenco\Collect\Support\Arr;

/**
 * Class Str.
 *
 * Helper methods for string.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Str
{
    /**
     * Get the substring after the last occurrence of search.
     *
     * @param string $search
     * @param string $subject
     *
     * @return string
     */
    public static function beforeLast(string $search, string $subject): string
    {
        $lastPosition = strrpos($subject, $search);
        if ($lastPosition === false) {
            return $subject;
        }

        return substr($subject, 0, $lastPosition);
    }

    /**
     * Get the substring after the first occurrence of search.
     *
     * @param string $search
     * @param string $subject
     *
     * @return string
     */
    public static function afterFirst(string $search, string $subject): string
    {
        $lastPosition = strpos($subject, $search);
        if ($lastPosition === false) {
            return $subject;
        }

        return substr($subject, $lastPosition + 1);
    }

    /**
     * Get the substring after the last occurrence of search.
     *
     * @param string $search
     * @param string $subject
     *
     * @return string
     */
    public static function afterLast(string $search, string $subject): string
    {
        $lastPosition = strrpos($subject, $search);
        if ($lastPosition === false) {
            return $subject;
        }

        return substr($subject, $lastPosition + 1);
    }

    /**
     * Replace the first occurrence in the given string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {
        if (! self::contains($search, $subject)) {
            return $subject;
        }

        return preg_replace('/'.preg_quote($search, '/').'/', $replace, $subject, 1);
    }

    /**
     * Replace the last occurrence in the given string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        $position = strrpos($subject, $search);
        if ($position === false) {
            return $subject;
        }

        return substr_replace($subject, $replace, $position, strlen($search));
    }

    /**
     * Check if the given string contains with (one of) the given search.
     *
     * @param string|string[] $searches
     * @param string          $subject
     *
     * @return bool
     */
    public static function contains($searches, string $subject): bool
    {
        $searches = Arr::wrap($searches);

        foreach ($searches as $search) {
            if (strpos($subject, $search) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the given string contains with (one of) the given regex.
     *
     * @param string|string[] $expressions
     * @param string          $subject
     *
     * @return bool
     */
    public static function containsRegex($expressions, string $subject): bool
    {
        $expressions = Arr::wrap($expressions);

        foreach ($expressions as $expression) {
            if (preg_match('/'.$expression.'/i', $subject)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the given string starts with (one of) the given search.
     *
     * @param string|string[] $searches
     * @param string          $subject
     *
     * @return bool
     */
    public static function startsWith($searches, string $subject): bool
    {
        $searches = Arr::wrap($searches);

        foreach ($searches as $search) {
            if (strpos($subject, $search) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the given string ends with (one of) the given search.
     *
     * @param string|string[] $searches
     * @param string          $subject
     *
     * @return bool
     */
    public static function endsWith($searches, string $subject): bool
    {
        $searches = Arr::wrap($searches);

        foreach ($searches as $search) {
            if (substr($subject, -strlen($search)) === $search) {
                return true;
            }
        }

        return false;
    }
}

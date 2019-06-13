<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Helpers;

/**
 * Class Str.
 *
 * @internal
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
     * Check if the given string contains with the given search.
     *
     * @param string $search
     * @param string $subject
     *
     * @return bool
     */
    public static function contains(string $search, string $subject): bool
    {
        return strpos($subject, $search) !== false;
    }

    /**
     * Check if the given string starts with the given search.
     *
     * @param string $search
     * @param string $subject
     *
     * @return bool
     */
    public static function startsWith(string $search, string $subject): bool
    {
        return strpos($subject, $search) === 0;
    }
}

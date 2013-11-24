<?php
namespace Bigtallbill\PasswordLocker;

/**
 * Interface IPasswordFactory
 * @package Bigtallbill\PasswordLocker
 */
interface IPasswordFactory
{
    /**
     * Make a new random password
     *
     * @param string $template A string to use as a template or seed
     *
     * @return string The new random password
     */
    public static function make($template = '');
}

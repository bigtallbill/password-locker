<?php
namespace Bigtallbill\PasswordLocker;

/**
 * Class PasswordFactory
 * @package Bigtallbill\PasswordLocker
 */
class PasswordFactory implements IPasswordFactory
{
    const CHARSET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const NUMBERSET = '0123456789';
    const SPECIALSET = '=*.-_%&@!';

    public static $TEMPLATE_NUMBER = 'N';
    public static $TEMPLATE_STRING = 'C';
    public static $TEMPLATE_SPECIAL = 'S';
    public static $TEMPLATE_NUMBER_STRING = 'NC';

    /**
     * Make a new random password
     *
     * @param string $template A string to use as a template
     *
     * @return string The new random password
     */
    public static function make($template = '')
    {
        $matches = array();
        preg_match_all("/(\\{[A-Z]+:\\d+\\})/", $template, $matches);
        $password = '';

        foreach ($matches[0] as $passwordSegment) {
            $passwordSegment = preg_replace("/[{}]/", "", $passwordSegment);
            $passwordSegment = explode(':', $passwordSegment);


            switch($passwordSegment[0]) {
                case static::$TEMPLATE_STRING:
                    $password .= static::getRandomCharacters($passwordSegment[1]);
                    break;
                case static::$TEMPLATE_NUMBER:
                    $password .= static::getRandomCharacters($passwordSegment[1], self::NUMBERSET);
                    break;
                case static::$TEMPLATE_SPECIAL:
                    $password .= static::getRandomCharacters($passwordSegment[1], self::SPECIALSET);
                    break;
                case static::$TEMPLATE_NUMBER_STRING:
                    $password .= static::getRandomCharacters($passwordSegment[1], self::CHARSET . self::NUMBERSET);
                    break;
            }

        }

        return $password;
    }

    /**
     * Gets a string of the specified length filled with random
     * characters from $charset
     *
     * @param int    $length
     * @param string $charset The character-set to use
     *
     * @return string
     */
    public static function getRandomCharacters($length, $charset = self::CHARSET)
    {
        $final = '';

        for ($i = 0; $i < $length; $i++) {
            $final .= static::getRandomCharacterInString($charset);
        }

        return $final;
    }

    /**
     * Gets a single random character in $str
     *
     * @param $str
     *
     * @return string A single random character from $str
     */
    public static function getRandomCharacterInString($str)
    {
        return $str[mt_rand(0, strlen($str) - 1)];
    }
}

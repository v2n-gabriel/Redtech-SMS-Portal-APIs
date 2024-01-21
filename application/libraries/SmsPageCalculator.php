<?php

class SmsPageCalculator
{
    const PAGE_LENGTH = 160, SYMBOL_EUROS = "€", SYMBOL_SINGLE_BACKSLASH = "\\";
    private static $sevenBitEncodingTwoCharacters = array("|", "^", "{", "}", "[", "]", "~");
    private static $escapableCharacters = array("\r", "\t", "\n", "\f", "\v");

    private static $signedCharRepresentationOfEscapableCharacters = array();

    function __construct()
    {
//        self::convertSevenBitEncodingTwoCharactersToSignedChars();
    }

    private static function convertSevenBitEncodingTwoCharactersToSignedChars()
    {
        self::$sevenBitEncodingTwoCharacters = array_merge(array(self::SYMBOL_SINGLE_BACKSLASH, self::SYMBOL_EUROS), self::$sevenBitEncodingTwoCharacters, self::$escapableCharacters);# the first element of the resulting array should be a single backslash because it is a special case
        foreach (self::$sevenBitEncodingTwoCharacters as $sevenBitEncodingTwoCharacter) {
            self::$signedCharRepresentationOfEscapableCharacters[]= self::unpackCharacter($sevenBitEncodingTwoCharacter);
        }
    }


    private static function unpackCharacter($character)
    {
        return implode("", unpack("c*", $character));
    }

    static function calculatePages($content){
        return self::calculatePagesSimple($content);
    }
    static function calculatePagesSimple($content){
        $message_length = strlen($content);
        $euro_occurrences = substr_count($content, self::SYMBOL_EUROS);
        $euro_symbol_length = strlen(self::SYMBOL_EUROS);
        if (0 < $euro_occurrences) {
            if (2 < $euro_symbol_length) {#2 < strlen(self::SYMBOL_EUROS) is for fail-safe across several versions of different language
                $message_length -= ($euro_symbol_length - 2) * $euro_occurrences;
            } elseif (1 === $euro_symbol_length) {
                $message_length += $euro_occurrences;
            }
            $content = str_replace(self::SYMBOL_EUROS, "", $content);
        }
        foreach (self::$sevenBitEncodingTwoCharacters as $character){
            $message_length += substr_count($content, $character);
        }
        foreach (self::$escapableCharacters as $escapableCharacter) {
            $message_length += substr_count($content, $escapableCharacter) * 2;
        }
        $message_length += substr_count($content, self::SYMBOL_SINGLE_BACKSLASH) * 3;
        return ceil($message_length / self::PAGE_LENGTH);
    }

    static function calculatePagesHeavy($content)
    {
        $message_length = strlen($content);
        $euro_occurrences = substr_count($content, self::SYMBOL_EUROS);
        $euro_symbol_length = strlen(self::SYMBOL_EUROS);
        if (0 < $euro_occurrences) {
            if (2 < $euro_symbol_length) {#2 < strlen(self::SYMBOL_EUROS) is for fail-safe across several versions of different language
                $message_length -= ($euro_symbol_length - 2) * $euro_occurrences;
            } elseif (1 === $euro_symbol_length) {
                $message_length += $euro_occurrences;
            }
            $content = str_replace(self::SYMBOL_EUROS, "", $content);
        }
        static::printDebugOutput("length after euros is $message_length");
        foreach (str_split($content) as $char) {
            $unpacked_character = self::unpackCharacter($char);
            if(in_array($unpacked_character,self::$signedCharRepresentationOfEscapableCharacters)){
                static::printDebugOutput("char $char matches");
                if($unpacked_character !== self::$signedCharRepresentationOfEscapableCharacters[0]){
                    $message_length += 1;
                    static::printDebugOutput("Not a single backslash");
                }
                $message_length += 1;
            }
        }
        static::printDebugOutput("sub count is ".substr_count($content,"\\"));
        $message_length += substr_count($content,"\\") * 2;# this is for a special case of double backslash
        return ceil($message_length / self::PAGE_LENGTH);
    }

    private static function printDebugOutput($message){
//        echo $message . "\n";
    }

}
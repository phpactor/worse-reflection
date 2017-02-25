<?php

namespace DTL\WorseReflection\Util;

class DocCommentParser
{
    public static function parseVarDoc($doc)
    {
        if (!preg_match('{@var ([^\s]+)}', $doc, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public static function parseProse($docComment)
    {
        $docComment = self::stripDocComment($docComment);

        return $docComment;
    }

    /**
     * Stolen from phpDocumentor2
     */
    private static function stripDocComment($comment)
    {
        $comment = trim(preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#u', '$1', $comment));

        // reg ex above is not able to remove */ from a single line docblock
        if (substr($comment, -2) == '*/') {
            $comment = trim(substr($comment, 0, -2));
        }

        return str_replace(array("\r\n", "\r"), "\n", $comment);
    }
}

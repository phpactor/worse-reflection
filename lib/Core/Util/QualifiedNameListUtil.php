<?php

namespace Phpactor\WorseReflection\Core\Util;

use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;
use RuntimeException;

class QualifiedNameListUtil
{
    public static function firstQualifiedName($qualifiedNameOrList): ?QualifiedName {
        if ($qualifiedNameOrList instanceof QualifiedNameList) {
            return self::firstReturnTypeOrNull($qualifiedNameOrList);
        }

        if ($qualifiedNameOrList instanceof QualifiedName) {
            return $qualifiedNameOrList;
        }

        throw new RuntimeException(sprintf(
            'Do not know how to resolve qualified name from class "%s"',
            get_class($qualifiedNameOrList)
        ));
    }

    /**
     * @return Token|QualifiedName|null
     */
    public static function firstQualifiedNameOrToken($qualifiedNameOrList) {
        if ($qualifiedNameOrList instanceof QualifiedNameList) {
            return self::firstReturnTypeOrNullOrToken($qualifiedNameOrList);
        }

        if ($qualifiedNameOrList instanceof QualifiedName) {
            return $qualifiedNameOrList;
        }

        if ($qualifiedNameOrList instanceof Token) {
            return $qualifiedNameOrList;
        }

        if (null === $qualifiedNameOrList) {
            return null;
        }

        throw new RuntimeException(sprintf(
            'Do not know how to resolve qualified name from class "%s"',
            get_class($qualifiedNameOrList)
        ));
    }

    public static function firstReturnTypeOrNull(?QualifiedNameList $types): ?QualifiedName {
        if (!$types) {
            return null;
        }

        foreach ($types->children as $child) {
            if (!$child instanceof QualifiedName) {
                continue;
            }
            return $child;
        }

        return null;
    }

    /**
     * @return Token|QualifiedName|null
     */
    public static function firstReturnTypeOrNullOrToken(?QualifiedNameList $types) {
        if (!$types) {
            return null;
        }

        foreach ($types->children as $child) {
            if (!$child instanceof QualifiedName && !$child instanceof Token) {
                continue;
            }
            return $child;
        }

        return null;
    }
}

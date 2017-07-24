<?php

namespace Phpactor\WorseReflection\Docblock;

final class DocblockParser
{
    const TAGS = [
        'var',
        'return',
    ];

    public function parse(string $text): Docblock
    {
        $tags = [];
        foreach (self::TAGS as $tag) {
            preg_match(sprintf('{@%s (?<target>\$\w+)? ?(?<value>[\w\\\]+)}', $tag), $text, $matches);

            if (!$matches) {
                continue;
            }

            if ($matches['target']) {
                $tags[] = DocblockTag::fromNameTargetAndValue($tag, $matches['target'], $matches['value']);
                continue;
            }

            $tags[] = DocblockTag::fromNameAndValue($tag, $matches['value']);
        }

        return Docblock::fromTags($tags);
    }
}

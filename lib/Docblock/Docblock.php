<?php

namespace Phpactor\WorseReflection\Docblock;

class Docblock
{
    private $tags = [];

    private function __construct(array $tags)
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public static function fromTags(array $tags)
    {
        return new self($tags);
    }

    public function tagsNamed(string $tagName): array
    {
        $tags = [];
        foreach ($this->tags as $tag) {
            if ($tag->name() === $tagName) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    private function addTag(DocblockTag $tag)
    {
        $this->tags[] = $tag;
    }
}

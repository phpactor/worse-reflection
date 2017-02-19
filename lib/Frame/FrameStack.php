<?php

namespace DTL\WorseReflection\Frame;

class FrameStack
{
    private $frames = [];

    public function spawn()
    {
        $frame = new Frame();
        $this->frames[] = $frame;

        return $frame;
    }

    public function spawnWith(array $names)
    {
        $newFrame = new Frame();
        $currentFrame = $this->top();

        if ($diff = array_diff($names, $currentFrame->keys())) {
            throw new \InvalidArgumentException(sprintf(
                'Trying to spawn with unknown variables: "%s", frame variables: "%s"',
                implode('", "', $names), implode('", "', $currentFrame->keys())
            ));
        }

        foreach ($names as $name) {
            $newFrame->set($name, $currentFrame->getType($name));
        }

        return $this->frames[] = $newFrame;
    }

    public function pop()
    {
        if ([] === $this->frames) {
            throw new \RuntimeException(
                'Cannot pop on empty frame stack'
            );
        }
        return array_pop($this->frames);
    }

    public function top()
    {
        if ([] === $this->frames) {
            throw new \RuntimeException(
                'Stack is empty, cannot get top'
            );
        }

        return end($this->frames);
    }
}

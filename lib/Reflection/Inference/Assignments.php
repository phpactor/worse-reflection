<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Phpactor\WorseReflection\Type;

abstract class Assignments
{
    /**
     * @var array
     */
    private $assignments = [];

    protected function __construct(array $assignments)
    {
        $this->assignments = $assignments;
    }

    public function has(string $name)
    {
        return isset($this->assignments[$name]);
    }

    public function set(string $name, Type $type)
    {
        $this->assignments[$name] = $type;
    }

    public function get(string $name)
    {
        if (!isset($this->assignments[$name])) {
            $type = strtolower(
                str_replace(
                    'Assignments',
                    '',
                    basename(str_replace('\\', '/', get_class($this)))
                )
            );

            throw new \InvalidArgumentException(sprintf(
                'Unknown "%s" value "%s", known values: "%s"',
                $type, $name, implode('", "', array_keys($this->assignments))
            ));
        }
        return $this->assignments[$name];
    }
}


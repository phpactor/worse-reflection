<?php

namespace Phpactor\WorseReflection\Util;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

class FrameRenderer
{
    public function render(Frame $frame, $indentation = 0): string
    {
        $output = [];

        foreach ($frame->locals() as $local) {
            $output[] = $this->formatLocal($local);
        }

        /** @var Variable $local */
        foreach ($frame->properties() as $local) {
            $output[] = $this->formatProperty($local);
        }

        /** @var SymbolInformation $problem */
        foreach ($frame->problems() as $problem) {
            $output[] = $this->formatProblem($problem);
        }

        $indent = str_repeat(' ', $indentation);
        $output = array_map(function ($line) use ($indent) {
            return $indent . $line;
        }, $output);

        $indentation += 2;
        foreach ($frame->children() as $child) {
            $output[] = $indent . 'f: ' . $child->name();
            $output[] = $this->render($child, $indentation);
        }

        return implode(PHP_EOL, $output);
    }

    private function formatLocal(Variable $local): string
    {
        return sprintf(
            'v: %s:%s %s %s',
            $local->name(),
            $local->offset()->toInt(),
            (string) $local->symbolInformation()->type(),
            var_export($local->symbolInformation()->value(), true)
        );
    }

    private function formatProperty(Variable $local): string
    {
        return sprintf(
            'p: %s:%s %s %s',
            $local->name(),
            $local->offset()->toInt(),
            (string) $local->symbolInformation()->type(),
            var_export($local->symbolInformation()->value(), true)
        );
    }

    private function formatProblem(SymbolInformation $problem): string
    {
        return sprintf(
            'x: %s:%s',
            $problem->symbol()->position()->start(),
            implode(', ', $problem->issues())
        );
    }
}

<?php

namespace Phpactor\WorseReflection\Util;

use Phpactor\WorseReflection\Core\Inference\Frame;

class FrameRenderer
{
    public function render(Frame $frame, $indentation = 0)
    {
        $output = [];
        $output[] = '+';

        /** @var Variable $local */
        foreach ($frame->locals() as $local) {
            $output[] = sprintf('   - %s:%s %s %s', $local->name(), $local->offset()->toInt(), (string) $local->symbolInformation()->type(), var_export($local->symbolInformation()->value(), true));
        }

        /** @var Variable $local */
        foreach ($frame->properties() as $local) {
            $output[] = sprintf('   + %s:%s %s %s', $local->name(), $local->offset()->toInt(), (string) $local->symbolInformation()->type(), var_export($local->symbolInformation()->value(), true));
        }

        /** @var SymbolInformation $problem */
        foreach ($frame->problems() as $problem) {
            $output[] = sprintf('   x %s', implode(', ', $problem->issues()));
        }

        $indentation += 2;
        foreach ($frame->children() as $child) {
            $output[] = $this->render($child, $indentation);
        }

        return implode(PHP_EOL, $output);
    }
}

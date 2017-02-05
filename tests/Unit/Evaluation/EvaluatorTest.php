<?php

namespace DTL\WorseReflection\Tests\Unit\Evaluation;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator\CaseDelegatingEvaluator;

class EvaluationTest extends IntegrationTestCase
{
    private $evaluator;

    public function setUp()
    {
        $this->evaluator = new CaseDelegatingEvaluator();
    }

    public function testEvaluate()
    {
        $parser = $this->getParser();
        $stmts = $parser->parse(<<<'EOT'
<?php

$scalar = 'bar';
$object = \stdClass::class;
$variable = $scalar;
$new = new \stdClass();
$closure = function ($foobar) {
    $barfoo = $variable;
};

function foobar(string $paramOne, $paramTwo)
{
}
EOT
        );

        $frame = new Frame();
        foreach ($stmts as $stmt) {
            $this->evaluator->__invoke($stmt, $frame);
        }

        var_dump($frame);die();;
    }
}

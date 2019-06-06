<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Tests\Unit\Parsers;

use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Sources\StringSource;
use Roave\BetterReflection\BetterReflection;

/**
 * Class CodeParserTest.
 *
 * @covers CodeParser
 */
class CodeParserTest extends TestCase
{
    /**
     * @var Mock
     */
    protected $betterReflection;

    /**
     * @var CodeParser $codeParser
     */
    protected $codeParser;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->codeParser = new CodeParser(new BetterReflection());
    }

    public function testWhenNoClassInCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('code contains less or more than one class/interface/trait');

        $this->codeParser->parse(new StringSource('<?php'));
    }

    public function testWhenTooMuchClassesInCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('code contains less or more than one class/interface/trait');

        $this->codeParser->parse(new StringSource('<?php class Foo {} class Bar {}'));
    }

    public function testWhenOnlyOneClassInCode(): void
    {
        $class = $this->codeParser->parse(new StringSource('<?php class Foo {}'));

        $this->assertSame('Foo', $class->getName());
    }
}

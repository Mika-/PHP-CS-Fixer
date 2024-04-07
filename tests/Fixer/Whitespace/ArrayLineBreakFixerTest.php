<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\Whitespace;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Whitespace\ArrayLineBreakFixer
 */
final class ArrayLineBreakFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public static function provideFixCases(): iterable
    {
        yield from self::withLongArraySyntaxCases([
            'Should fix opening line' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        'A',
                        'B',
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = ['A',
                        'B',
                    ];
                    INPUT,
            ],
            'Should fix closing line' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        'A',
                        'B'
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = [
                        'A',
                        'B'];
                    INPUT,
            ],
            'Should fix opening and closing lines' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        'A',
                        'B'
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = ['A',
                        'B'];
                    INPUT,
            ],
            'Should fix nested complex array' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        'A',
                        'B' => [
                            'BA',
                            'BB'
                        ],
                        'C' => ['CA'],
                        'D'
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = ['A',
                        'B' => ['BA',
                            'BB'],
                        'C' => ['CA'],
                        'D'];
                    INPUT,
            ],
            'Should work with ternary' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        $bar ?
                          'bar' :
                          'foo'
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = [$bar ?
                          'bar' :
                          'foo'];
                    INPUT,
            ],
            'Should work with comments' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        'foo', // comment
                    'bar',
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = ['foo', // comment
                    'bar',
                    ];
                    INPUT,
            ],
            'Should fix nested array' => [
                <<<'EXPECTED'
                    <?php
                    $foo = [
                        [
                          new Foo(
                              'foo'
                          ),
                        ]
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $foo = [[
                          new Foo(
                              'foo'
                          ),]];
                    INPUT,
            ],
            'Should fix sparse commented array' => [
                <<<'EXPECTED'
                    <?php
                    $arr = [
                        'a' => 'b',

                    //  'c' => 'd',
                    ];
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    $arr = ['a' => 'b',

                    //  'c' => 'd',
                    ];
                    INPUT,
            ],
            'Should fix inside HTML' => [
                <<<'EXPECTED'
                    <div>
                        <a
                            class="link"
                            href="<?= Url::to([
                                  '/site/page',
                              'id' => 123,
                            ]); ?>"
                        >
                            Link text
                        </a>
                    </div>
                    EXPECTED,
                <<<'INPUT'
                    <div>
                        <a
                            class="link"
                            href="<?= Url::to([
                                  '/site/page',
                              'id' => 123,]); ?>"
                        >
                            Link text
                        </a>
                    </div>
                    INPUT,
            ],
            'Should fix in inline code' => [
                <<<'EXPECTED'
                    <?php if ($foo): ?>
                        <?php foo([
                            'bar',
                          'baz',
                        ]) ?>
                    <?php endif ?>
                    EXPECTED,
                <<<'INPUT'
                    <?php if ($foo): ?>
                        <?php foo(['bar',
                          'baz',
                        ]) ?>
                    <?php endif ?>
                    INPUT,
            ],
            'Should fix with expression inside' => [
                <<<'EXPECTED'
                    <?php

                    class Foo
                    {
                        public function bar()
                        {
                            return new Bar([
                    (new Baz())
                        ->qux([
                            function ($a) {
                            foreach ($a as $b) {
                                if ($b) {
                                    throw new Exception(sprintf(
                                        'Oops: %s',
                                        $b
                                    ));
                                }
                            }
                        }
                        ]),
                            ]);
                        }
                    }
                    EXPECTED,
                <<<'INPUT'
                    <?php

                    class Foo
                    {
                        public function bar()
                        {
                            return new Bar([
                    (new Baz())
                        ->qux([function ($a) {
                            foreach ($a as $b) {
                                if ($b) {
                                    throw new Exception(sprintf(
                                        'Oops: %s',
                                        $b
                                    ));
                                }
                            }
                        }]),
                            ]);
                        }
                    }
                    INPUT,
            ],
        ]);
    }

    /**
     * @dataProvider provideFix80Cases
     *
     * @requires PHP 8.0
     */
    public function testFix80(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public static function provideFix80Cases(): iterable
    {
        yield from [
            'Should fix attribute arguments' => [
                <<<'EXPECTED'
                    <?php
                    class Foo {
                     #[SimpleAttribute]
                     #[ComplexAttribute(
                     foo: true,
                        bar: [
                            1,
                                        2,
                                  3
                        ]
                     )]
                      public function bar()
                         {
                         }
                    }
                    EXPECTED,
                <<<'INPUT'
                    <?php
                    class Foo {
                     #[SimpleAttribute]
                     #[ComplexAttribute(
                     foo: true,
                        bar: [1,
                                        2,
                                  3]
                     )]
                      public function bar()
                         {
                         }
                    }
                    INPUT,
            ],
        ];
    }

    /**
     * @param list<array{0: string, 1?: string}> $cases
     *
     * @return list<array{0: string, 1?: string}>
     */
    private static function withLongArraySyntaxCases(array $cases): array
    {
        $longSyntaxCases = [];

        foreach ($cases as $case) {
            $case[0] = self::toLongArraySyntax($case[0]);
            if (isset($case[1])) {
                $case[1] = self::toLongArraySyntax($case[1]);
            }

            $longSyntaxCases[] = $case;
        }

        return [...$cases, ...$longSyntaxCases];
    }

    private static function toLongArraySyntax(string $php): string
    {
        return strtr($php, [
            '[' => 'array(',
            ']' => ')',
        ]);
    }
}

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

namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\Indentation;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

final class ArrayLineBreakFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    use Indentation;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'No array value should exist on opening or closing line of multi line array.',
            [
                new CodeSample(
                    "<?php\n\$foo = ['A',\n    'B',\n    'C'];\n",
                ),
                new CodeSample(
                    "<?php\n\$foo = [[\n    'foo' => 'bar'\n]];\n",
                ),
            ],
        );
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $indent = $this->whitespacesConfig->getIndent();
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        $lastIndent = '';
        $scopes = [];

        for ($index = 0; $index < $tokens->count() - 1; ++$index) {
            $token = $tokens[$index];

            if ($token->isComment()) {
                continue;
            }

            if (
                $token->isGivenKind([CT::T_ARRAY_SQUARE_BRACE_OPEN, CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN])
                || ($token->equals('(') && $tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind([T_ARRAY, T_LIST]))
            ) {
                $blockType = Tokens::detectBlockType($token);
                $endIndex = $tokens->findBlockEnd($blockType['type'], $index);

                $isMultiLine = false;

                for ($i = $index + 1; $i < $endIndex; ++$i) {
                    if ($this->isNewLineToken($tokens, $i)) {
                        $isMultiLine = true;

                        break;
                    }
                }

                $scopes[] = [
                    'multi_line' => $isMultiLine,
                    'start_index' => $index,
                    'end_index' => $endIndex,
                    'initial_indent' => $lastIndent,
                ];
            }

            if ($this->isNewLineToken($tokens, $index)) {
                $lastIndent = $this->extractIndent($this->computeNewLineContent($tokens, $index));
            }

            $currentScope = [] !== $scopes ? \count($scopes) - 1 : null;

            if (null === $currentScope) {
                continue;
            }

            if (
                $scopes[$currentScope]['multi_line']
                && $index === $scopes[$currentScope]['start_index']
                && !$this->isNewLineToken($tokens, $index + 1)) {

                $shouldIndent = $this->shouldIndent($tokens, $index, $scopes, $currentScope);
                $added = $tokens->ensureWhitespaceAtIndex($index, 1, $lineEnding . $scopes[$currentScope]['initial_indent'].($shouldIndent ? $indent : ''));

                if ($added) {
                    foreach ($scopes as $i => $scope) {
                        if ($scope['start_index'] > $index) {
                            $scope[$i]['start_index'] += 1;
                        }

                        if ($scope['end_index'] > $index) {
                            $scopes[$i]['end_index'] += 1;
                        }
                    }
                }

                continue;
            }

            if ($index === $scopes[$currentScope]['end_index']) {
                if (
                    $scopes[$currentScope]['multi_line']
                    && !$this->isNewLineToken($tokens, $index - 1)) {

                    $shouldIndent = $this->shouldIndent($tokens, $index, $scopes, $currentScope);
                    $added = $tokens->ensureWhitespaceAtIndex($index - 1, 1, $lineEnding . $scopes[$currentScope]['initial_indent'].($shouldIndent ? $indent : ''));

                    if ($added) {
                        foreach ($scopes as $i => $scope) {
                            if ($scope['start_index'] > $index) {
                                $scopes[$i]['start_index'] += 1;
                            }

                            if ($scope['end_index'] > $index) {
                                $scopes[$i]['end_index'] += 1;
                            }
                        }
                    }
                }

                while ([] !== $scopes && $index === $scopes[$currentScope]['end_index']) {
                    array_pop($scopes);
                    --$currentScope;
                }
            }
        }
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_ARRAY, T_LIST, CT::T_ARRAY_SQUARE_BRACE_OPEN, CT::T_DESTRUCTURING_SQUARE_BRACE_OPEN]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before ArrayIndentationFixer.
     */
    public function getPriority(): int
    {
        return 30;
    }

    /**
     * @param int<0, max> $index
     * @param list<array{
     *     multi_line: bool,
     *     start_index: int<0, max>,
     *     end_index: int<0, max>,
     *     initial_indent: string,
     * }> $scopes
     * @param int<0, max> $currentScope
     */
    private function shouldIndent(Tokens $tokens, int $index, array $scopes, int $currentScope) : bool
    {
        for ($searchEndIndex = $index + 1; $searchEndIndex < $scopes[$currentScope]['end_index']; ++$searchEndIndex) {
            $searchEndToken = $tokens[$searchEndIndex];

            if (
                (!$searchEndToken->isWhitespace() && !$searchEndToken->isComment())
                || ($searchEndToken->isWhitespace() && Preg::match('/\R/', $searchEndToken->getContent()))
            ) {
                return true;
            }
        }

        return false;
    }
}

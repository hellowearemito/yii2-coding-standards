<?php

/**
 * ArrayDeclarationSniff
 *
 * Verifies that arrays conform to the array coding standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Nikola Kovacs <nikola.kovacs@gmail.com>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

namespace Mito\Application\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class ArrayDeclarationSniff implements Sniff
{
    /**
     * The number of spaces multi-line array elements should be indented.
     *
     * @var int
     */
    private $indent = 4;

    /**
     * The number of spaces required after opening bracket and before closing bracket in single-line arrays.
     *
     * @var int
     */
    private $singleLineBracketSpacing = 0;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        ];

    }//end register()

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The current file being checked.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'no');

            // Array keyword should be lower case.
            if ($tokens[$stackPtr]['content'] !== strtolower($tokens[$stackPtr]['content'])) {
                if ($tokens[$stackPtr]['content'] === strtoupper($tokens[$stackPtr]['content'])) {
                    $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'upper');
                } else {
                    $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'mixed');
                }

                $error = 'Array keyword should be lower case; expected "array" but found "%s"';
                $data  = [$tokens[$stackPtr]['content']];
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NotLowerCase', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken($stackPtr, 'array');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'lower');
            }

            $arrayStart = $tokens[$stackPtr]['parenthesis_opener'];
            if (isset($tokens[$arrayStart]['parenthesis_closer']) === false) {
                return;
            }

            $arrayEnd = $tokens[$arrayStart]['parenthesis_closer'];

            if ($arrayStart !== ($stackPtr + 1)) {
                $error = 'There must be no space between the "array" keyword and the opening parenthesis';

                $next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), $arrayStart, true);
                if (isset(Tokens::$commentTokens[$tokens[$next]['code']]) === true) {
                    // We don't have anywhere to put the comment, so don't attempt to fix it.
                    $phpcsFile->addError($error, $stackPtr, 'SpaceAfterKeyword');
                } else {
                    $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword');
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($stackPtr + 1); $i < $arrayStart; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->endChangeset();
                    }
                }
            }
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'yes');
            $arrayStart = $stackPtr;
            $arrayEnd   = $tokens[$stackPtr]['bracket_closer'];
        }//end if

        // Check for empty arrays.
        $content = $phpcsFile->findNext(T_WHITESPACE, ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceInEmptyArray');

                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }

            // We can return here because there is nothing else to check. All code
            // below can assume that the array is not empty.
            return;
        }

        $targetLine = $tokens[$arrayStart]['line'];
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $openingLine = $tokens[$i]['line'];
                $i           = $tokens[$i]['parenthesis_closer'];
            } else if ($tokens[$i]['code'] === T_ARRAY) {
                $openingLine = $tokens[$i]['line'];
                $i           = $tokens[$tokens[$i]['parenthesis_opener']]['parenthesis_closer'];
            } else if (isset(Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
                // Skip to the end of multi-line strings.
                $openingLine = $tokens[$i]['line'];
                $i           = $phpcsFile->findNext($tokens[$i]['code'], ($i + 1), null, true);
                $i--;
            } else if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                $openingLine = $tokens[$i]['line'];
                $i           = $tokens[$i]['bracket_closer'];
            } else if ($tokens[$i]['code'] === T_CLOSURE) {
                $openingLine = $tokens[$i]['line'];
                $i           = $tokens[$i]['scope_closer'];
            } else {
                continue;
            }

            if ($openingLine === $targetLine) {
                $targetLine = $tokens[$i]['line'];
            }
        }

        if ($targetLine === $tokens[$arrayEnd]['line']) {
            $this->processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        } else {
            $this->processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        }

    }//end process()

    /**
     * Processes a single-line array definition.
     *
     * @param File $phpcsFile  The current file being checked.
     * @param int                         $stackPtr   The position of the current token
     *                                                in the stack passed in $tokens.
     * @param int                         $arrayStart The token that starts the array definition.
     * @param int                         $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if there are multiple values. If so, then it has to be multiple lines
        // unless it is contained inside a function call or condition.
        $valueCount = 0;
        $commas     = [];
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === T_ARRAY) {
                $i = $tokens[$tokens[$i]['parenthesis_opener']]['parenthesis_closer'];
                continue;
            }

            // Skip to the end of multi-line strings.
            if (isset(Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
                $i = $phpcsFile->findNext($tokens[$i]['code'], ($i + 1), null, true);
                $i--;
                continue;
            }

            if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                $i = $tokens[$i]['bracket_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === T_CLOSURE) {
                $i = $tokens[$i]['scope_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === T_COMMA) {
                // Before counting this comma, make sure we are not
                // at the end of the array.
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), $arrayEnd, true);
                if ($next !== false) {
                    $valueCount++;
                    $commas[] = $i;
                } else {
                    // There is a comma at the end of a single line array.
                    $error = 'Comma not allowed after last value in single-line array declaration';
                    $fix   = $phpcsFile->addFixableError($error, $i, 'CommaAfterLast');
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }
            }
        }//end for

        // Now check each of the double arrows (if any).
        $nextArrow = $arrayStart;
        while (($nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($nextArrow + 1), $arrayEnd)) !== false) {
            if ($tokens[($nextArrow - 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow - 1)]['content'];
                $error   = 'Expected 1 space between "%s" and double arrow; 0 found';
                $data    = [$content];
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContentBefore($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow - 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow - 2)]['content'];
                    $error   = 'Expected 1 space between "%s" and double arrow; %s found';
                    $data    = [
                        $content,
                        $spaceLength,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow - 1), ' ');
                    }
                }
            }//end if

            if ($tokens[($nextArrow + 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow + 1)]['content'];
                $error   = 'Expected 1 space between double arrow and "%s"; 0 found';
                $data    = [$content];
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow + 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow + 2)]['content'];
                    $error   = 'Expected 1 space between double arrow and "%s"; %s found';
                    $data    = [
                        $content,
                        $spaceLength,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow + 1), ' ');
                    }
                }
            }//end if
        }//end while

        // Check bracket spacing.
        if ($tokens[($arrayStart + 1)]['code'] !== T_WHITESPACE) {
            $spaceLength = 0;
            $content     = $tokens[($arrayStart + 1)]['content'];
        } else {
            $spaceLength = $tokens[($arrayStart + 1)]['length'];
            $content     = $tokens[($arrayStart + 2)]['content'];
        }//end if
        $expectedSpacing = $this->singleLineBracketSpacing;
        if ($spaceLength !== $expectedSpacing) {
            $error = 'Expected %s space(s) between opening bracket and "%s"; %s found';
            $data  = [
                $expectedSpacing,
                $content,
                $spaceLength,
            ];
            $fix   = $phpcsFile->addFixableError($error, ($arrayStart + 1), 'SpaceAfterOpeningBracket', $data);
            if ($fix === true) {
                if ($spaceLength > 0) {
                    $phpcsFile->fixer->replaceToken(($arrayStart + 1), str_repeat(' ', $expectedSpacing));
                } else {
                    $phpcsFile->fixer->addContent($arrayStart, str_repeat(' ', $expectedSpacing));
                }//end if
            }//end if
        }//end if

        if ($tokens[($arrayEnd - 1)]['code'] !== T_WHITESPACE) {
            $spaceLength = 0;
            $content     = $tokens[($arrayEnd - 1)]['content'];
        } else {
            $spaceLength = $tokens[($arrayEnd - 1)]['length'];
            $content     = $tokens[($arrayEnd - 2)]['content'];
        }//end if
        if ($spaceLength !== $expectedSpacing) {
            $error = 'Expected %s space(s) between "%s" and closing bracket; %s found';
            $data  = [
                $expectedSpacing,
                $content,
                $spaceLength,
            ];
            $fix   = $phpcsFile->addFixableError($error, ($arrayEnd - 1), 'SpaceBeforeClosingBracket', $data);
            if ($fix === true) {
                if ($spaceLength > 0) {
                    $phpcsFile->fixer->replaceToken(($arrayEnd - 1), str_repeat(' ', $expectedSpacing));
                } else {
                    $phpcsFile->fixer->addContent(($arrayEnd - 1), str_repeat(' ', $expectedSpacing));
                }//end if
            }
        }

        if ($valueCount > 0) {
            // We have a multiple value array that is inside a condition or
            // function. Check its spacing is correct.
            foreach ($commas as $comma) {
                if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($comma + 1)]['content'];
                    $error   = 'Expected 1 space between comma and "%s"; 0 found';
                    $data    = [$content];
                    $fix     = $phpcsFile->addFixableError($error, $comma, 'NoSpaceAfterComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->addContent($comma, ' ');
                    }
                } else {
                    $spaceLength = $tokens[($comma + 1)]['length'];
                    if ($spaceLength !== 1) {
                        $content = $tokens[($comma + 2)]['content'];
                        $error   = 'Expected 1 space between comma and "%s"; %s found';
                        $data    = [
                            $content,
                            $spaceLength,
                        ];

                        $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceAfterComma', $data);
                        if ($fix === true) {
                            $phpcsFile->fixer->replaceToken(($comma + 1), ' ');
                        }
                    }
                }//end if

                if ($tokens[($comma - 1)]['code'] === T_WHITESPACE) {
                    $content     = $tokens[($comma - 2)]['content'];
                    $spaceLength = $tokens[($comma - 1)]['length'];
                    $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                    $data        = [
                        $content,
                        $spaceLength,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceBeforeComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($comma - 1), '');
                    }
                }
            }//end foreach
        }//end if

    }//end processSingleLineArray()

    /**
     * Processes a multi-line array definition.
     *
     * @param File $phpcsFile  The current file being checked.
     * @param int                         $stackPtr   The position of the current token
     *                                                in the stack passed in $tokens.
     * @param int                         $arrayStart The token that starts the array definition.
     * @param int                         $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens       = $phpcsFile->getTokens();

        // We need to work out how far indented the array
        // itself is, so we can work out how far to
        // indent the elements.
        $start = $phpcsFile->findStartOfStatement($stackPtr);
        foreach (array('stackPtr', 'start') as $checkToken) {
            $x = $$checkToken;
            for ($i = ($x - 1); $i >= 0; $i--) {
                if ($tokens[$i]['line'] !== $tokens[$x]['line']) {
                    $i++;
                    break;
                }
            }

            if ($i <= 0) {
                $arrayIndent = 0;
            } else if ($tokens[$i]['code'] === T_WHITESPACE) {
                $arrayIndent = strlen($tokens[$i]['content']);
            } else if ($tokens[$i]['code'] === T_CONSTANT_ENCAPSED_STRING) {
                $arrayIndent = 0;
            } else {
                $trimmed = ltrim($tokens[$i]['content']);
                if ($trimmed === '') {
                    if ($tokens[$i]['code'] === T_INLINE_HTML) {
                        $arrayIndent = strlen($tokens[$i]['content']);
                    } else {
                        $arrayIndent = ($tokens[$i]['column'] - 1);
                    }
                } else {
                    $arrayIndent = (strlen($tokens[$i]['content']) - strlen($trimmed));
                }
            }

            $varName  = $checkToken.'Indent';
            $$varName = $arrayIndent;
        }//end foreach
        $arrayIndent = max($startIndent, $stackPtrIndent);

        // Check the closing bracket is on a new line.
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $arrayStart, true);
        if ($tokens[$lastContent]['line'] === $tokens[$arrayEnd]['line']) {
            $error = 'Closing parenthesis of array declaration must be on a new line';
            $fix   = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNewLine');
            if ($fix === true) {
                $phpcsFile->fixer->addNewlineBefore($arrayEnd);
            }
        } else if ($tokens[$arrayEnd]['column'] !== ($arrayIndent + 1)) {
            // Check the closing bracket is lined up under the "a" in array.
            $expected = $arrayIndent;
            $found    = ($tokens[$arrayEnd]['column'] - 1);
            $error    = 'Closing parenthesis not aligned correctly; expected %s space(s) but found %s';
            $data     = [
                $expected,
                $found,
            ];

            $fix = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNotAligned', $data);
            if ($fix === true) {
                if ($found === 0) {
                    $phpcsFile->fixer->addContent(($arrayEnd - 1), str_repeat(' ', $expected));
                } else {
                    $phpcsFile->fixer->replaceToken(($arrayEnd - 1), str_repeat(' ', $expected));
                }
            }
        }//end if

        $indices          = array();
        $maxLength        = 0;
        $maxArrowDistance = 0;

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $lastToken = $tokens[$stackPtr]['parenthesis_opener'];
        } else {
            $lastToken = $stackPtr;
        }

        // Find all the double arrows that reside in this scope.
        for ($nextToken = ($stackPtr + 1); $nextToken < $arrayEnd; $nextToken++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS
                && (isset($tokens[$nextToken]['parenthesis_owner']) === false
                    || $tokens[$nextToken]['parenthesis_owner'] !== $stackPtr)
            ) {
                $nextToken = $tokens[$nextToken]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$nextToken]['code'] === T_ARRAY
                || $tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY
                || $tokens[$nextToken]['code'] === T_CLOSURE
                || $tokens[$nextToken]['code'] === T_FN
                || $tokens[$nextToken]['code'] === T_MATCH
            ) {
                // Let subsequent calls of this test handle nested arrays.
                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $indices[] = ['value' => $nextToken];
                    $lastToken = $nextToken;
                }

                if ($tokens[$nextToken]['code'] === T_ARRAY) {
                    $nextToken = $tokens[$tokens[$nextToken]['parenthesis_opener']]['parenthesis_closer'];
                } else if ($tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY) {
                    $nextToken = $tokens[$nextToken]['bracket_closer'];
                } else {
                    // T_CLOSURE.
                    $nextToken = $tokens[$nextToken]['scope_closer'];
                }

                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] !== T_COMMA) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }//end if

            if ($tokens[$nextToken]['code'] !== T_DOUBLE_ARROW && $tokens[$nextToken]['code'] !== T_COMMA) {
                continue;
            }

            $currentEntry = array();

            if ($tokens[$nextToken]['code'] === T_COMMA) {
                $stackPtrCount = 0;
                if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
                    $stackPtrCount = count($tokens[$stackPtr]['nested_parenthesis']);
                }

                $commaCount = 0;
                if (isset($tokens[$nextToken]['nested_parenthesis']) === true) {
                    $commaCount = count($tokens[$nextToken]['nested_parenthesis']);
                    if ($tokens[$stackPtr]['code'] === T_ARRAY) {
                        // Remove parenthesis that are used to define the array.
                        $commaCount--;
                    }
                }

                if ($commaCount > $stackPtrCount) {
                    // This comma is inside more parenthesis than the ARRAY keyword,
                    // then there it is actually a comma used to separate arguments
                    // in a function call.
                    continue;
                }

                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $valueContent = $phpcsFile->findNext(
                        Tokens::$emptyTokens,
                        ($lastToken + 1),
                        $nextToken,
                        true
                    );

                    $currentEntry['value'] = $valueContent;
                    $currentEntry['comma'] = $nextToken;

                    $indices[] = $currentEntry;
                }//end if

                $lastToken        = $nextToken;
                continue;
            }//end if

            if ($tokens[$nextToken]['code'] === T_DOUBLE_ARROW) {
                if ($tokens[$lastToken]['code'] === T_DOUBLE_ARROW) {
                    $error = 'Duouble arrow after double arrow, possible missing comma';
                    $phpcsFile->addError($error, $nextToken, 'MissingComma');
                    return;
                }

                $currentEntry['arrow'] = $nextToken;

                // Find the start of index that uses this double arrow.
                $indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findStartOfStatement($indexEnd);

                if ($indexStart === $indexEnd) {
                    $indexStart = $indexEnd;
                    $currentEntry['index_content'] = $tokens[$indexEnd]['content'];
                } else {
                    $currentEntry['index_content'] = $phpcsFile->getTokensAsString($indexStart, ($indexEnd - $indexStart + 1));
                }

                $currentEntry['index']     = $indexStart;
                $currentEntry['index_end'] = $indexEnd;

                $indexLength = strlen($currentEntry['index_content']);
                if ($tokens[$indexEnd]['line'] === $tokens[$nextToken]['line']) {
                    if ($maxLength < $indexLength) {
                        $maxLength = $indexLength;
                    }

                    $arrowDistance = ($tokens[$nextToken]['column'] - (strlen($currentEntry['index_content']) + $tokens[$indexStart]['column']));
                    if ($maxArrowDistance < $arrowDistance) {
                        $maxArrowDistance = $arrowDistance;
                    }
                }

                // Find the value of this index.
                $nextContent = $phpcsFile->findNext(
                    Tokens::$emptyTokens,
                    ($nextToken + 1),
                    $arrayEnd,
                    true
                );

                $currentEntry['value'] = $nextContent;
                $indices[] = $currentEntry;
                $lastToken = $nextToken;
            }//end if
        }//end for

        // If the array is already not aligned, don't force it to be.
        if ($maxArrowDistance > 1) {
            $arrayShouldBeAligned = true;
        } else {
            $arrayShouldBeAligned = false;
        }

        if (empty($indices) === false) {
            $count = count($indices);

            // Only add trailing content if last element was added because we found a comma.
            // If it was added because we found a double arrow, then the trailing content is already in indices.
            if (isset($indices[($count - 1)]['comma']) !== false) {
                $lastComma = $indices[($count - 1)]['comma'];

                $trailingContent = $phpcsFile->findNext(
                    Tokens::$emptyTokens,
                    ($lastComma + 1),
                    $arrayEnd,
                    true
                );

                if ($trailingContent !== false && $tokens[$trailingContent]['code'] !== T_COMMA) {
                    $indices[] = array('value' => $trailingContent);
                }
            }
        } else {
            $trailingContent = $phpcsFile->findNext(
                Tokens::$emptyTokens,
                ($arrayStart + 1),
                $arrayEnd,
                true
            );

            if ($trailingContent !== false) {
                $indices[] = array('value' => $trailingContent);
            }
        }//end if

        $indicesStart = ($arrayIndent + $this->indent + 1);

        if (empty($indices) === false) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(
                Tokens::$emptyTokens,
                ($arrayEnd - 1),
                $lastIndex,
                true
            );
            if ($tokens[$trailingContent]['code'] !== T_COMMA) {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'no');
                $error = 'Comma required after last value in array declaration';
                $fix   = $phpcsFile->addFixableError($error, $trailingContent, 'NoCommaAfterLast');
                if ($fix === true) {
                    if (in_array($tokens[$trailingContent]['code'], [T_END_NOWDOC, T_END_HEREDOC]) === true) {
                        $phpcsFile->fixer->addContent($trailingContent, "\n".str_repeat(' ', ($indicesStart - 1)).',');
                    } else {
                        $phpcsFile->fixer->addContent($trailingContent, ',');
                    }
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'yes');
            }
        }//end if

        $elementLine     = $tokens[$stackPtr]['line'];
        $elementEndLine  = $elementLine;
        foreach ($indices as $idx => $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                if (empty($index['value']) === true) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }
            }

            // Check each line ends in a comma.
            $valueLine      = $tokens[$index['value']]['line'];
            $nextComma      = false;
            for ($i = $index['value']; $i < $arrayEnd; $i++) {
                // Skip bracketed statements, like function calls.
                if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                    $i         = $tokens[$i]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_ARRAY) {
                    $i         = $tokens[$tokens[$i]['parenthesis_opener']]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                // Skip to the end of multi-line strings.
                if (isset(Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
                    $i = $phpcsFile->findNext($tokens[$i]['code'], ($i + 1), null, true);
                    $i--;
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                    $i         = $tokens[$i]['bracket_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_CLOSURE) {
                    $i         = $tokens[$i]['scope_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_COMMA) {
                    $nextComma = $i;
                    break;
                }
            }//end for

            if ($idx !== (count($indices) - 1) && ($nextComma === false)) {
                $error = 'Each line in an array declaration must end in a comma';
                $fix   = $phpcsFile->addFixableError($error, $index['value'], 'NoComma');

                if ($fix === true) {
                    // Find the end of the line and put a comma there.
                    for ($i = ($index['value'] + 1); $i < $phpcsFile->numTokens; $i++) {
                        if ($tokens[$i]['line'] > $valueLine) {
                            break;
                        }
                    }

                    $phpcsFile->fixer->addContentBefore(($i - 1), ',');
                }
            }

            // Check that there is no space before the comma.
            if ($nextComma !== false && $tokens[($nextComma - 1)]['code'] === T_WHITESPACE) {
                $prevLastContent = $phpcsFile->findPrevious(
                    Tokens::$emptyTokens,
                    ($nextComma - 1),
                    null,
                    true
                );

                if (in_array($tokens[$prevLastContent]['code'], [T_END_NOWDOC, T_END_HEREDOC]) === true) {
                    // Comma must be on a new line, but should be indented the same as indices.
                    if ($tokens[$nextComma]['column'] !== $indicesStart) {
                        $expected = ($indicesStart - 1);
                        $found    = ($tokens[$nextComma]['column'] - 1);
                        $error    = 'Comma not indented correctly; expected %s spaces but found %s';
                        $data     = array(
                            $expected,
                            $found,
                        );

                        $fix = $phpcsFile->addFixableError($error, $nextComma, 'CommaNotAligned', $data);
                        if ($fix === true) {
                            if ($found === 0) {
                                $phpcsFile->fixer->addContent(($nextComma - 1), str_repeat(' ', $expected));
                            } else {
                                $phpcsFile->fixer->replaceToken(($nextComma - 1), str_repeat(' ', $expected));
                            }
                        }
                    }
                } else {
                    $content = $tokens[($nextComma - 2)]['content'];
                    if ($tokens[($nextComma - 1)]['content'] === $phpcsFile->eolChar) {
                        $spaceLength = 'newline';
                    } else {
                        $spaceLength = $tokens[($nextComma - 1)]['length'];
                    }

                    $error = 'Expected 0 spaces between "%s" and comma; %s found';
                    $data  = array(
                        $content,
                        $spaceLength,
                    );

                    $fix = $phpcsFile->addFixableError($error, $nextComma, 'SpaceBeforeComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextComma - 1), '');
                    }
                }//end if
            }//end if

            if (isset($index['index']) === false) {
                $elementToken = $index['value'];
            } else {
                $elementToken = $index['index'];
            }

            $lastElementLine = $elementEndLine;
            $elementLine     = $tokens[$elementToken]['line'];
            $elementEndLine  = $elementLine;

            if ($nextComma !== false) {
                $elementEndLine = $tokens[$nextComma]['line'];
            }

            if ($elementLine === $tokens[$stackPtr]['line']) {
                $error = 'The first element in a multi-value array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $elementToken, 'FirstElementNoNewline');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($elementToken);
                }

                continue;
            }

            if ($elementLine === $lastElementLine) {
                $error = 'Each element in a multi-line array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $elementToken, 'ElementNoNewline');
                if ($fix === true) {
                    if ($tokens[($elementToken - 1)]['code'] === T_WHITESPACE) {
                        $phpcsFile->fixer->replaceToken(($elementToken - 1), '');
                    }

                    $phpcsFile->fixer->addNewlineBefore($elementToken);
                }

                continue;
            }

            if (isset($index['index']) === true) {
                if ($tokens[$index['index']]['column'] !== $indicesStart) {
                    $expected = ($indicesStart - 1);
                    $found    = ($tokens[$index['index']]['column'] - 1);
                    $error    = 'Array key not indented correctly; expected %s spaces but found %s';
                    $data     = array(
                        $expected,
                        $found,
                    );

                    $fix = $phpcsFile->addFixableError($error, $index['index'], 'KeyNotAligned', $data);
                    if ($fix === true) {
                        if ($found === 0) {
                            $phpcsFile->fixer->addContent(($index['index'] - 1), str_repeat(' ', $expected));
                        } else {
                            $phpcsFile->fixer->replaceToken(($index['index'] - 1), str_repeat(' ', $expected));
                        }
                    }

                    continue;
                }

                if ($tokens[$index['index']]['line'] !== $tokens[$index['index_end']]['line']) {
                    $error = 'Index spans multiple lines';
                    $phpcsFile->addError($error, $index['index'], 'IndexOneLine');
                    continue;
                }

                if ($tokens[$index['arrow']]['line'] !== $elementLine) {
                    $error = 'Key and double arrow must be on the same line';
                    $fix   = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowSameLine');
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($index['arrow'] - 1); $i > $index['index']; $i--) {
                            if ($tokens[$i]['code'] !== T_WHITESPACE) {
                                break;
                            }

                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->endChangeset();
                    }

                    continue;
                }

                if ($arrayShouldBeAligned === true) {
                    $arrowStart = ($indicesStart + $maxLength + 1);
                } else {
                    $arrowStart = ((strlen($index['index_content']) + $tokens[$index['index']]['column']) + 1);
                }

                if ($tokens[$index['arrow']]['column'] !== $arrowStart) {
                    $expected = ($arrowStart - (strlen($index['index_content']) + $tokens[$index['index']]['column']));
                    $found    = ($tokens[$index['arrow']]['column'] - (strlen($index['index_content']) + $tokens[$index['index']]['column']));
                    $error    = 'Array double arrow not aligned correctly; expected %s space(s) but found %s';
                    $data     = array(
                        $expected,
                        $found,
                    );

                    $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowNotAligned', $data);
                    if ($fix === true) {
                        if ($found === 0) {
                            $phpcsFile->fixer->addContent(($index['arrow'] - 1), str_repeat(' ', $expected));
                        } else {
                            $phpcsFile->fixer->replaceToken(($index['arrow'] - 1), str_repeat(' ', $expected));
                        }
                    }

                    continue;
                }

                $valueStart = ($arrowStart + 3);
            } else {
                $valueStart = $indicesStart;
            }//end if

            if ($tokens[$index['value']]['column'] !== $valueStart) {
                if (isset($index['arrow']) === true) {
                    $verb       = 'aligned';
                    $expected   = ($valueStart - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                    $found      = ($tokens[$index['value']]['column'] - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                    $errorToken = $index['arrow'];
                } else {
                    $verb       = 'indented';
                    $expected   = ($indicesStart - 1);
                    $found      = ($tokens[$index['value']]['column'] - 1);
                    $errorToken = $index['value'];
                }

                if ($found < 0) {
                    $found = 'newline';
                }

                $error = "Array value not $verb correctly; expected %s space(s) but found %s";
                $data  = [
                    $expected,
                    $found,
                ];

                $fix = $phpcsFile->addFixableError($error, $errorToken, 'ValueNotAligned', $data);
                if ($fix === true) {
                    if ($found === 'newline') {
                        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($index['value'] - 1), null, true);
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($prev + 1); $i < $index['value']; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                        $phpcsFile->fixer->endChangeset();
                    } else if ($found === 0) {
                        $phpcsFile->fixer->addContent(($index['value'] - 1), str_repeat(' ', $expected));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                    }
                }
            }//end if
        }//end foreach

    }//end processMultiLineArray()
}//end class

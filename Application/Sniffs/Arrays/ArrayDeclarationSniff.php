<?php
/**
 * A test to ensure that arrays conform to the array coding standard.
 */

/**
 * A test to ensure that arrays conform to the array coding standard.
 */
class Application_Sniffs_Arrays_ArrayDeclarationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The number of spaces multi-line array elements should be indented.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * Whether to align multi-line arrays.
     * if null, alignment will not be checked.
     *
     * @var bool|null
     */
    public $alignMultiLine = false;

    /**
     * Whether to allow multi-line arrays with a single value.
     *
     * @var bool
     */
    public $allowSingleMultiLine = true;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_ARRAY,
                T_OPEN_SHORT_ARRAY,
               );

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being checked.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
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
                $data  = array($tokens[$stackPtr]['content']);
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
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword');

                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($stackPtr + 1); $i < $arrayStart; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
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

                // We can return here because there is nothing else to check. All code
                // below can assume that the array is not empty.
                return;
            }
        }

        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            $this->processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        } else {
            $this->processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        }

    }//end process()


    /**
     * Processes a single-line array definition.
     *
     * @param PHP_CodeSniffer_File $phpcsFile  The current file being checked.
     * @param int                  $stackPtr   The position of the current token
     *                                         in the stack passed in $tokens.
     * @param int                  $arrayStart The token that starts the array definition.
     * @param int                  $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processSingleLineArray(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if there are multiple values. If so, then it has to be multiple lines
        // unless it is contained inside a function call or condition.
        $valueCount = 0;
        $commas     = array();
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'];
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
                $data    = array($content);
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContentBefore($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow - 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow - 2)]['content'];
                    $error   = 'Expected 1 space between "%s" and double arrow; %s found';
                    $data    = array(
                                $content,
                                $spaceLength,
                               );

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow - 1), ' ');
                    }
                }
            }//end if

            if ($tokens[($nextArrow + 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow + 1)]['content'];
                $error   = 'Expected 1 space between double arrow and "%s"; 0 found';
                $data    = array($content);
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow + 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow + 2)]['content'];
                    $error   = 'Expected 1 space between double arrow and "%s"; %s found';
                    $data    = array(
                                $content,
                                $spaceLength,
                               );

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow + 1), ' ');
                    }
                }
            }//end if
        }//end while

        if ($valueCount > 0) {
            $conditionCheck = $phpcsFile->findPrevious(array(T_OPEN_PARENTHESIS, T_SEMICOLON), ($stackPtr - 1), null, false);

            /*if ($conditionCheck === false
                || $tokens[$conditionCheck]['line'] !== $tokens[$stackPtr]['line']
            ) {
                $error = 'Array with multiple values cannot be declared on a single line';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SingleLineNotAllowed');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->addNewline($arrayStart);
                    $phpcsFile->fixer->addNewlineBefore($arrayEnd);
                    $phpcsFile->fixer->endChangeset();
                }

                return;
            }*/

            // We have a multiple value array that is inside a condition or
            // function. Check its spacing is correct.
            foreach ($commas as $comma) {
                if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($comma + 1)]['content'];
                    $error   = 'Expected 1 space between comma and "%s"; 0 found';
                    $data    = array($content);
                    $fix     = $phpcsFile->addFixableError($error, $comma, 'NoSpaceAfterComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->addContent($comma, ' ');
                    }
                } else {
                    $spaceLength = $tokens[($comma + 1)]['length'];
                    if ($spaceLength !== 1) {
                        $content = $tokens[($comma + 2)]['content'];
                        $error   = 'Expected 1 space between comma and "%s"; %s found';
                        $data    = array(
                                    $content,
                                    $spaceLength,
                                   );

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
                    $data        = array(
                                    $content,
                                    $spaceLength,
                                   );

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
     * @param PHP_CodeSniffer_File $phpcsFile  The current file being checked.
     * @param int                  $stackPtr   The position of the current token
     *                                         in the stack passed in $tokens.
     * @param int                  $arrayStart The token that starts the array definition.
     * @param int                  $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processMultiLineArray(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens       = $phpcsFile->getTokens();
        $keywordStart = $tokens[$stackPtr]['column'];

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
        } else if ($tokens[$arrayEnd]['column'] !== $arrayIndent + 1) {
            // Check the closing bracket is not indented.
            $expected = $arrayIndent;
            $found    = ($tokens[$arrayEnd]['column'] - 1);
            $error    = 'Closing parenthesis not indented correctly; expected %s space(s) but found %s';
            $data     = array(
                         $expected,
                         $found,
                        );

            $fix = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNotAligned', $data);
            if ($fix === true) {
                if ($found === 0) {
                    $phpcsFile->fixer->addContent(($arrayEnd - 1), str_repeat(' ', $expected));
                } else {
                    $phpcsFile->fixer->replaceToken(($arrayEnd - 1), str_repeat(' ', $expected));
                }
            }
        }//end if

        $nextToken  = $stackPtr;
        //$keyUsed    = false;
        //$singleUsed = false;
        $indices    = array();
        $maxLength  = 0;
        //$key        = false;
        $currentEntry = array();

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $lastToken = $tokens[$stackPtr]['parenthesis_opener'];
        } else {
            $lastToken = $stackPtr;
        }
        $lastElementToken = $lastToken;

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

            if ($tokens[$nextToken]['code'] === T_ARRAY) {
                // Let subsequent calls of this test handle nested arrays.
                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $indices[] = array('value' => $nextToken);
                    $lastToken = $nextToken;
                }

                $nextToken = $tokens[$tokens[$nextToken]['parenthesis_opener']]['parenthesis_closer'];
                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] !== T_COMMA) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }

            if ($tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY) {
                // Let subsequent calls of this test handle nested arrays.
                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $indices[] = array('value' => $nextToken);
                    $lastToken = $nextToken;
                }

                $nextToken = $tokens[$nextToken]['bracket_closer'];
                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] !== T_COMMA) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }

            if ($tokens[$nextToken]['code'] === T_CLOSURE) {
                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $indices[] = array('value' => $nextToken);
                    $lastToken = $nextToken;
                }

                $nextToken = $tokens[$nextToken]['scope_closer'];
                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] !== T_COMMA) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }

            if ($tokens[$nextToken]['code'] !== T_DOUBLE_ARROW
                && $tokens[$nextToken]['code'] !== T_COMMA
            ) {
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

                /*if ($keyUsed === true && $tokens[$lastToken]['code'] === T_COMMA) {
                    $error = 'No key specified for array entry; first entry specifies key';
                    $phpcsFile->addError($error, $nextToken, 'NoKeySpecified');
                    return;
                }*/

                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    /*if ($tokens[($nextToken - 1)]['code'] === T_WHITESPACE) {
                        $content = $tokens[($nextToken - 2)]['content'];
                        if ($tokens[($nextToken - 1)]['content'] === $phpcsFile->eolChar) {
                            $spaceLength = 'newline';
                        } else {
                            $spaceLength = $tokens[($nextToken - 1)]['length'];
                        }

                        $error = 'Expected 0 spaces between "%s" and comma; %s found';
                        $data  = array(
                                  $content,
                                  $spaceLength,
                                 );

                        $fix = $phpcsFile->addFixableError($error, $nextToken, 'SpaceBeforeComma', $data);
                        if ($fix === true) {
                            $phpcsFile->fixer->replaceToken(($nextToken - 1), '');
                        }
                    }*/

                    $valueContent = $phpcsFile->findNext(
                        PHP_CodeSniffer_Tokens::$emptyTokens,
                        ($lastToken + 1),
                        $nextToken,
                        true
                    );

                    // $emptyLine = $phpcsFile->findPrevious(T_WHITESPACE, ($valueContent - 1), $lastElementToken, true);

                    // if ($tokens[$emptyLine]['line'] !== $tokens[$valueContent]['line'] - 1) {
                    //     $error = 'Empty lines are not allowed in multi-line arrays';
                    //     $phpcsFile->addError($error, $emptyLine, 'EmptyLine');
                    //     /*$fix = $phpcsFile->addFixableError($error, $emptyLine, 'EmptyLine');
                    //     if ($fix === true) {
                    //         for ($i = )
                    //     }*/
                    // }

                    $currentEntry['value'] = $valueContent;

                    $indices[]  = $currentEntry;
                    //$singleUsed = true;
                }//end if

                $lastToken        = $nextToken;
                $lastElementToken = $nextToken;
                continue;
            }//end if

            if ($tokens[$nextToken]['code'] === T_DOUBLE_ARROW) {
                /*if ($singleUsed === true) {
                    $error = 'Key specified for array entry; first entry has no key';
                    $phpcsFile->addError($error, $nextToken, 'KeySpecified');
                    return;
                }*/

                if ($tokens[$lastToken]['code'] === T_DOUBLE_ARROW) {
                    $error = 'Duouble arrow after double arrow, possible missing comma';
                    $phpcsFile->addError($error, $nextToken, 'MissingComma');
                    return;
                }

                $currentEntry['arrow'] = $nextToken;
                //$keyUsed = true;

                // Find the start of index that uses this double arrow.
                $indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findStartOfStatement($indexEnd);

                if ($indexStart === $indexEnd) {
                    $indexStart                    = $indexEnd;
                    $currentEntry['index_content'] = $tokens[$indexEnd]['content'];
                } else {
                    $currentEntry['index_content'] = $phpcsFile->getTokensAsString($indexStart, ($indexEnd - $indexStart + 1));
                }
                $currentEntry['index']         = $indexStart;
                $currentEntry['index_end']     = $indexEnd;

                $indexLength = strlen($currentEntry['index_content']);
                if ($maxLength < $indexLength) {
                    $maxLength = $indexLength;
                }

                // Find the value of this index.
                $nextContent = $phpcsFile->findNext(
                    PHP_CodeSniffer_Tokens::$emptyTokens,
                    ($nextToken + 1),
                    $arrayEnd,
                    true
                );

                // $emptyLine = $phpcsFile->findPrevious(T_WHITESPACE, ($indexStart - 1), $lastElementToken, true);

                // if ($tokens[$emptyLine]['line'] !== $tokens[$indexStart]['line'] - 1) {
                //     $error = 'Empty lines are not allowed in multi-line arrays';
                //     $phpcsFile->addError($error, $emptyLine, 'EmptyLine');
                //     /*$fix = $phpcsFile->addFixableError($error, $emptyLine, 'EmptyLine');
                //     if ($fix === true) {
                //         for ($i = )
                //     }*/
                // }

                $currentEntry['value'] = $nextContent;

                $indices[] = $currentEntry;
                $lastToken = $nextToken;
            }//end if
        }//end for

        // Check for multi-line arrays that should be single-line.
        $singleValue = false;

        if (empty($indices) === true) {
            $singleValue = true;
        } else if (count($indices) === 1 && $tokens[$lastToken]['code'] === T_COMMA) {
            // There may be another array value without a comma.
            $exclude     = PHP_CodeSniffer_Tokens::$emptyTokens;
            $exclude[]   = T_COMMA;
            $nextContent = $phpcsFile->findNext($exclude, ($indices[0]['value'] + 1), $arrayEnd, true);
            if ($nextContent === false) {
                $singleValue = true;
            }
        }

        if ($singleValue === true && !$this->allowSingleMultiLine) {
            // Array cannot be empty, so this is a multi-line array with
            // a single value. It should be defined on single line.
            $error = 'Multi-line array contains a single value; use single-line array instead';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'MultiLineNotAllowed');

            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
                    if ($tokens[$i]['code'] !== T_WHITESPACE) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                for ($i = ($arrayEnd - 1); $i > $arrayStart; $i--) {
                    if ($tokens[$i]['code'] !== T_WHITESPACE) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->endChangeset();
            }

            return;
        }//end if

        /*
            This section checks for arrays that don't specify keys.

            Arrays such as:
               array(
                'aaa',
                'bbb',
                'd',
               );
        */

        /*if ($keyUsed === false && empty($indices) === false) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($arrayEnd - 1),
                $lastIndex,
                true
            );

            if ($tokens[$trailingContent]['code'] !== T_COMMA) {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'no');
                $error = 'Comma required after last value in array declaration';
                $fix   = $phpcsFile->addFixableError($error, $trailingContent, 'NoCommaAfterLast');
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($trailingContent, ',');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'yes');
            }

            $lastValueLine = false;
            foreach ($indices as $value) {
                if (empty($value['value']) === true) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }

                if ($lastValueLine !== false && $tokens[$value['value']]['line'] === $lastValueLine) {
                    $error = 'Each value in a multi-line array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $value['value'], 'ValueNoNewline');
                    if ($fix === true) {
                        if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                            $phpcsFile->fixer->replaceToken(($value['value'] - 1), '');
                        }

                        $phpcsFile->fixer->addNewlineBefore($value['value']);
                    }
                } else if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                    $expected = $arrayIndent + $this->indent;

                    $first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $value['value'], true);
                    $found = ($tokens[$first]['column'] - 1);
                    if ($found !== $expected) {
                        $error = 'Array value not indented correctly; expected %s spaces but found %s';
                        $data  = array(
                                  $expected,
                                  $found,
                                 );

                        $fix = $phpcsFile->addFixableError($error, $value['value'], 'ValueNotAligned', $data);
                        if ($fix === true) {
                            if ($found === 0) {
                                $phpcsFile->fixer->addContent(($value['value'] - 1), str_repeat(' ', $expected));
                            } else {
                                $phpcsFile->fixer->replaceToken(($value['value'] - 1), str_repeat(' ', $expected));
                            }
                        }
                    }
                }//end if

                //var_dump($tokens[$value['value']]['line']);

                $lastValueLine = $tokens[$value['value']]['line'];
            }//end foreach
        }*/ //end if

        if (empty($indices) === false) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];
        } else {
            $lastIndex = $arrayStart;

            $trailingContent = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($arrayStart + 1),
                $arrayEnd,
                true
            );

            if ($trailingContent !== false) {
                $indices[] = array('value' => $trailingContent);
            }
        }

        if (empty($indices) === false) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($arrayEnd - 1),
                $lastIndex,
                true
            );
            if ($tokens[$trailingContent]['code'] !== T_COMMA) {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'no');
                $error = 'Comma required after last value in array declaration';
                $fix   = $phpcsFile->addFixableError($error, $trailingContent, 'NoCommaAfterLast');
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($trailingContent, ',');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'yes');
            }
        }

        $numValues = count($indices);

        $indicesStart    = $arrayIndent + $this->indent + 1;
        $elementLine     = $tokens[$stackPtr]['line'];
        $lastElementLine = null;
        foreach ($indices as $idx => $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                /*if ($tokens[$index['value']]['line'] === $tokens[$stackPtr]['line'] && $numValues > 1) {
                    $error = 'The first value in a multi-value array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'FirstValueNoNewline');
                    if ($fix === true) {
                        $phpcsFile->fixer->addNewlineBefore($index['value']);
                    }
                }*/

                if (empty($index['value']) === true) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }
            }

            $lastElementLine = $elementLine;
            $elementToken    = isset($index['index']) === false ? $index['value'] : $index['index'];
            $elementLine     = $tokens[$elementToken]['line'];

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
                    $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowSameLine');
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

                if ($this->alignMultiLine === true) {
                    $arrowStart = ($indicesStart + $maxLength + 1);
                } elseif ($this->alignMultiLine === false) {
                    $arrowStart = ((strlen($index['index_content']) + $tokens[$index['index']]['column']) + 1);
                } else {
                    $arrowStart = null;
                }

                if ($arrowStart !== null && $tokens[$index['arrow']]['column'] !== $arrowStart) {
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

                if ($arrowStart === null) {
                    $valueStart = null;
                } else {
                    $valueStart = ($arrowStart + 3);
                }

            } else {
                $valueStart = $indicesStart;
            }


            if ($valueStart !== null && $tokens[$index['value']]['column'] !== $valueStart) {
                if (isset($index['arrow']) === true) {
                    $verb = 'aligned';
                    $expected = ($valueStart - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                    $found    = ($tokens[$index['value']]['column'] - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                    $errorToken = $index['arrow'];
                } else {
                    $verb = 'indented';
                    $expected = ($indicesStart - 1);
                    $found = ($tokens[$index['value']]['column'] - 1);
                    $errorToken = $index['value'];
                }
                if ($found < 0) {
                    $found = 'newline';
                }

                $error = "Array value not $verb correctly; expected %s space(s) but found %s";
                $data  = array(
                          $expected,
                          $found,
                         );

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

            // Check each line ends in a comma.
            $valueLine      = $tokens[$index['value']]['line'];
            $valueStartLine = $valueLine;
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
                if (isset(PHP_CodeSniffer_Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
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

            if ($idx !== count($indices) - 1 && ($nextComma === false/* || ($tokens[$nextComma]['line'] !== $valueLine)*/)) {
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
                $content     = $tokens[($nextComma - 2)]['content'];
                if ($tokens[($nextComma - 1)]['content'] === $phpcsFile->eolChar) {
                    $spaceLength = 'newline';
                } else {
                    $spaceLength = $tokens[($nextComma - 1)]['length'];
                }
                $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                $data        = array(
                                $content,
                                $spaceLength,
                               );

                $fix = $phpcsFile->addFixableError($error, $nextComma, 'SpaceBeforeComma', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($nextComma - 1), '');
                }
            }
        }//end foreach

    }//end processMultiLineArray()


}//end class

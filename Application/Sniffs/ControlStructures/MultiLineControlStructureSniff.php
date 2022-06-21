<?php

namespace Mito\Application\Sniffs\ControlStructures;

/**
 * MultiLineControlStructureSniff.
 *
 * PHP version 5
 *
 * @category  ControlStructures
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
/**
 * MultiLineControlStructureSniff.
 *
 * Verifies that multiline control structures are correctly formatted.
 *
 * @category  ControlStructures
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class MultiLineControlStructureSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                  );

    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_IF,
                T_WHILE,
                T_FOREACH,
                T_FOR,
                T_SWITCH,
                T_DO,
                T_ELSE,
                T_ELSEIF,
                T_TRY,
                T_CATCH,
               );

    }//end register()


    /**
     * Emits error for and fixes closing bracket on same line as condition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile    The file being scanned.
     * @param array                $tokens       Token stack of the file.
     * @param int                  $closeBracket The position of the closing bracket in the stack passed in $tokens.
     *                                           in the stack passed in $tokens.
     *
     * @return void
     */
    private function _closingBracketError(\PHP_CodeSniffer\Files\File $phpcsFile, $tokens, $closeBracket)
    {
        $error = 'Closing parenthesis of a multi-line control structure must be on a new line';
        $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'CloseBracketNewLine');
        if ($fix === true) {
            // Account for a comment at the end of the line.
            $next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
            if ($tokens[$next]['code'] !== T_COMMENT) {
                $phpcsFile->fixer->addNewlineBefore($closeBracket);
            } else {
                $next = $phpcsFile->findNext(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, ($next + 1), null, true);
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($closeBracket, '');
                $phpcsFile->fixer->addContentBefore($next, ')');
                $phpcsFile->fixer->endChangeset();
            }
        }

    }//end _closingBracketError()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (isset($tokens[$stackPtr]['parenthesis_opener']) === false) {
            return;
        }

        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $targetLine   = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            // Skip bracketed statements, like function calls and arrays.
            // These don't count as multiple lines even if they are multiline.
            if (in_array($tokens[$i]['code'], \PHP_CodeSniffer\Util\Tokens::$functionNameTokens) === true || $tokens[$i]['code'] === T_ARRAY) {
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
                if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
                    // This is a function call.
                    $openingLine = $tokens[$i]['line'];
                    $i           = $tokens[$next]['parenthesis_closer'];
                    if ($openingLine === $targetLine) {
                        $targetLine = $tokens[$i]['line'];
                    }

                    continue;
                }
            } else if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                // This is a short array.
                $openingLine = $tokens[$i]['line'];
                $i           = $tokens[$i]['bracket_closer'];
                if ($openingLine === $targetLine) {
                    $targetLine = $tokens[$i]['line'];
                }

                continue;
            }//end if
        }//end for

        if ($targetLine === $tokens[$closeBracket]['line']) {
            return;
        }

        // We need to work out how far indented the statement
        // itself is, so we can work out how far to indent conditions.
        $statementIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($i >= 0 && $tokens[$i]['code'] === T_WHITESPACE) {
            $statementIndent = strlen($tokens[$i]['content']);
        }

        $next = $phpcsFile->findNext(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, ($openBracket + 1), null, true);
        if ($tokens[$next]['line'] === $tokens[$openBracket]['line']) {
            $error = 'Opening parenthesis of a multi-line control structure must be the last content on the line';
            $fix   = $phpcsFile->addFixableError($error, $next, 'ContentAfterOpenBracket');
            if ($fix === true) {
                $phpcsFile->fixer->addContent(
                    $openBracket,
                    $phpcsFile->eolChar.str_repeat(' ', ($statementIndent + $this->indent))
                );
            }
        }

        // Each line between the parenthesis should be indented 4 spaces
        // and start with an operator, unless the line is inside a
        // function call, in which case it is ignored.
        $lastLine = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i <= $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($tokens[$i]['line'] === $tokens[$closeBracket]['line']) {
                    $next = $phpcsFile->findNext(T_WHITESPACE, $i, null, true);
                    if ($next !== $closeBracket) {
                        $this->_closingBracketError($phpcsFile, $tokens, $closeBracket);
                        $expectedIndent = ($statementIndent + $this->indent);
                    } else {
                        // Closing brace needs to be indented to the same level
                        // as the statement.
                        $expectedIndent = $statementIndent;
                    }//end if
                } else {
                    $expectedIndent = ($statementIndent + $this->indent);
                }//end if
                if ($tokens[$i]['code'] === T_COMMENT) {
                    $lastLine = $tokens[$i]['line'];
                    continue;
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line control structure not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );
                    $fix   = $phpcsFile->addFixableError($error, $i, 'Alignment', $data);
                    if ($fix === true) {
                        $spaces = str_repeat(' ', $expectedIndent);
                        if ($foundIndent === 0) {
                            $phpcsFile->fixer->addContentBefore($i, $spaces);
                        } else {
                            $phpcsFile->fixer->replaceToken($i, $spaces);
                        }
                    }
                }

                $lastLine = $tokens[$i]['line'];
            }//end if
            $jumped = false;
            if (in_array($tokens[$i]['code'], \PHP_CodeSniffer\Util\Tokens::$functionNameTokens) === true || $tokens[$i]['code'] === T_ARRAY) {
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
                if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
                    // This is a function call or array, so skip to the end as they
                    // have their own indentation rules.
                    $i = $tokens[$next]['parenthesis_closer'];
                    if ($lastLine !== $tokens[$i]['line']) {
                        $jumped = true;
                    }

                    $lastLine = $tokens[$i]['line'];
                }
            } else if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                // This is a short array, so skip to the end as they
                // have their own indentation rules.
                $i = $tokens[$i]['bracket_closer'];
                if ($lastLine !== $tokens[$i]['line']) {
                    $jumped = true;
                }

                $lastLine = $tokens[$i]['line'];
            }//end if

            // If we jumped over a multiline function or array, we need to check if the closing bracket is on the same line.
            if ($jumped === true && $lastLine === $tokens[$closeBracket]['line']) {
                $this->_closingBracketError($phpcsFile, $tokens, $closeBracket);
            }
        }//end for

    }//end process()


}//end class

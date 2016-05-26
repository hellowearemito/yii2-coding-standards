<?php

class Application_Sniffs_ControlStructures_MultiLineControlStructureSniff implements PHP_CodeSniffer_Sniff
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
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (isset($tokens[$stackPtr]['parenthesis_opener']) === false) {
            return;
        }
        $openBracket    = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket   = $tokens[$stackPtr]['parenthesis_closer'];
        if ($tokens[$openBracket]['line'] === $tokens[$closeBracket]['line']) {
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
        $next = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($openBracket + 1), null, true);
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
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($tokens[$i]['line'] === $tokens[$closeBracket]['line']) {
                    $next = $phpcsFile->findNext(T_WHITESPACE, $i, null, true);
                    if ($next !== $closeBracket) {
                        // Closing bracket is on the same line as a condition.
                        $error = 'Closing parenthesis of a multi-line control structure must be on a new line';
                        $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'CloseBracketNewLine');
                        if ($fix === true) {
                            // Account for a comment at the end of the line.
                            $next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
                            if ($tokens[$next]['code'] !== T_COMMENT) {
                                $phpcsFile->fixer->addNewlineBefore($closeBracket);
                            } else {
                                $next = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($next + 1), null, true);
                                $phpcsFile->fixer->beginChangeset();
                                $phpcsFile->fixer->replaceToken($closeBracket, '');
                                $phpcsFile->fixer->addContentBefore($next, ')');
                                $phpcsFile->fixer->endChangeset();
                            }
                        }
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
                    $fix = $phpcsFile->addFixableError($error, $i, 'Alignment', $data);
                    if ($fix === true) {
                        $spaces = str_repeat(' ', $expectedIndent);
                        if ($foundIndent === 0) {
                            $phpcsFile->fixer->addContentBefore($i, $spaces);
                        } else {
                            $phpcsFile->fixer->replaceToken($i, $spaces);
                        }
                    }
                }
                /*if ($tokens[$i]['line'] !== $tokens[$closeBracket]['line']) {
                    $next = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $i, null, true);
                    if (isset(PHP_CodeSniffer_Tokens::$booleanOperators[$tokens[$next]['code']]) === false) {
                        $error = 'Each line in a multi-line IF statement must begin with a boolean operator';
                        $fix   = $phpcsFile->addFixableError($error, $i, 'StartWithBoolean');
                        if ($fix === true) {
                            $prev = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($i - 1), $openBracket, true);
                            if (isset(PHP_CodeSniffer_Tokens::$booleanOperators[$tokens[$prev]['code']]) === true) {
                                $phpcsFile->fixer->beginChangeset();
                                $phpcsFile->fixer->replaceToken($prev, '');
                                $phpcsFile->fixer->addContentBefore($next, $tokens[$prev]['content'].' ');
                                $phpcsFile->fixer->endChangeset();
                            } else {
                                for ($x = ($prev + 1); $x < $next; $x++) {
                                    $phpcsFile->fixer->replaceToken($x, '');
                                }
                            }
                        }
                    }
                }*///end if
                $lastLine = $tokens[$i]['line'];
            }//end if
            if ($tokens[$i]['code'] === T_STRING) {
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
                if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
                    // This is a function call, so skip to the end as they
                    // have their own indentation rules.
                    $i        = $tokens[$next]['parenthesis_closer'];
                    $lastLine = $tokens[$i]['line'];
                    continue;
                }
            }
        }//end for
    }//end process()
}//end class

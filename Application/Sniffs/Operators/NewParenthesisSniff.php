<?php

namespace Mito\Application\Sniffs\Operators;

/**
 * NewParenthesisSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Nikola Kovacs <nikola.kovacs@gmail.com>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * NewParenthesisSniff
 *
 * Verifies that parenthesis is used with the new operator.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Nikola Kovacs <nikola.kovacs@gmail.com>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class NewParenthesisSniff implements \PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('PHP');


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_NEW);

    }//end register()


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

        // Check for php 7 anonymous classes.
        $class = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$class]['code'] === T_ANON_CLASS) {
            $open        = $phpcsFile->findNext(T_WHITESPACE, ($class + 1), null, true);
            $insertAfter = $class;
        } else {
            $open        = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($stackPtr + 1), null, false, null, true);
            $insertAfter = $phpcsFile->findEndOfStatement($stackPtr);

            // If the last token is an end token, find the last non-empty token.
            $endTokens = array(
                          T_COLON,
                          T_COMMA,
                          T_DOUBLE_ARROW,
                          T_SEMICOLON,
                          T_CLOSE_PARENTHESIS,
                          T_CLOSE_SQUARE_BRACKET,
                          T_CLOSE_CURLY_BRACKET,
                          T_CLOSE_SHORT_ARRAY,
                          T_OPEN_TAG,
                          T_CLOSE_TAG,
                         );
            if (in_array($tokens[$insertAfter]['code'], $endTokens) === true) {
                $insertAfter = $phpcsFile->findPrevious(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, ($insertAfter - 1), null, true);
            }
        }//end if

        if ($open !== false && $tokens[$open]['code'] === T_OPEN_PARENTHESIS) {
            return;
        }

        $error = 'Use parentheses when instantiating classes with new.';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NoParenthesis');
        if ($fix === true) {
            $phpcsFile->fixer->addContent($insertAfter, '()');
        }

    }//end process()


}//end class

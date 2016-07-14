<?php
/**
 * Application_Sniffs_Operators_NewParenthesisSniff.
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
 * Application_Sniffs_Operators_NewParenthesisSniff
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
class Application_Sniffs_Operators_NewParenthesisSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                  );
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_NEW,
               );
    }
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
        // check for php 7 anonymous classes
        $class = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$class]['code'] === T_ANON_CLASS) {
            $open = $phpcsFile->findNext(T_WHITESPACE, ($class + 1), null, true);
            $insertAfter = $class;
        } else {
            $open = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($stackPtr + 1), null, false, null, true);
            $insertAfter = ($phpcsFile->findEndOfStatement($stackPtr) - 1);
        }
        if ($open !== false && $tokens[$open]['code'] === T_OPEN_PARENTHESIS) {
            return;
        }

        $error = 'Use parentheses when instantiating classes with new.';
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'NoParenthesis');
        if ($fix === true) {
            $phpcsFile->fixer->addContent($insertAfter, '()');
        }
    }
}

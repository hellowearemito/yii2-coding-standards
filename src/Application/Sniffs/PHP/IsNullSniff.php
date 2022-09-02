<?php

namespace Mito\Application\Sniffs\PHP;

/**
 * IsNullSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
/**
 * IsNullSniff.
 *
 * Discourages the use of is_null.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class IsNullSniff extends \PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $ignore = array(
                   T_DOUBLE_COLON    => true,
                   T_OBJECT_OPERATOR => true,
                   T_FUNCTION        => true,
                   T_CONST           => true,
                   T_PUBLIC          => true,
                   T_PRIVATE         => true,
                   T_PROTECTED       => true,
                   T_AS              => true,
                   T_NEW             => true,
                   T_INSTEADOF       => true,
                   T_NS_SEPARATOR    => true,
                   T_IMPLEMENTS      => true,
                  );

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        $nsToken = null;

        if ($tokens[$prevToken]['code'] === T_NS_SEPARATOR) {
            $nsToken   = $prevToken;
            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($prevToken - 1), null, true);
            if ($tokens[$prevToken]['code'] === T_STRING) {
                // Not in the global namespace.
                return;
            }
        }

        if (isset($ignore[$tokens[$prevToken]['code']]) === true) {
            // Not a call to a PHP function.
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if (isset($ignore[$tokens[$nextToken]['code']]) === true) {
            // Not a call to a PHP function.
            return;
        }

        if ($tokens[$stackPtr]['code'] === T_STRING && $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a call to a PHP function.
            return;
        }

        $function = mb_strtolower($tokens[$stackPtr]['content']);

        if ($function !== 'is_null') {
            return;
        }

        $replacement = ' === null';
        $negate      = false;
        if ($tokens[$prevToken]['code'] === T_BOOLEAN_NOT) {
            $replacement = ' !== null';
            $negate      = true;
        }

        if (isset($tokens[$nextToken]['parenthesis_closer']) !== true) {
            $phpcsFile->addError('The use of is_null() is forbidden', $stackPtr, 'Found');
            return;
        }

        $closer = $tokens[$nextToken]['parenthesis_closer'];

        $fix = $phpcsFile->addFixableError('The use of is_null() is forbidden', $stackPtr, 'Found');
        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPtr, '');
            $phpcsFile->fixer->replaceToken($nextToken, '');
            if ($negate === true) {
                $phpcsFile->fixer->replaceToken($prevToken, '');
            }

            if ($nsToken !== null) {
                $phpcsFile->fixer->replaceToken($nsToken, '');
            }

            $phpcsFile->fixer->replaceToken($closer, $replacement);
            $phpcsFile->fixer->endChangeset();
        }

    }//end process()


}//end class

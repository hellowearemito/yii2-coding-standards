<?php
/**
 * Application_Sniffs_ControlStructures_DisallowAlternativeControlStructureSyntaxSniff.
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
 * Application_Sniffs_ControlStructures_DisallowAlternativeControlStructureSyntaxSniff.
 *
 * Verifies that alternative control structure syntax is not used.
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
class Application_Sniffs_ControlStructures_DisallowAlternativeControlStructureSyntaxSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_IF,
                /* T_ELSE,
                T_ELSEIF, */
                T_FOREACH,
                T_WHILE,
                T_SWITCH,
                T_FOR,
               );
    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (isset($tokens[$stackPtr]['scope_opener']) !== true) {
            return;
        }

        $opener = $tokens[$stackPtr]['scope_opener'];

        if ($tokens[$opener]['type'] !== 'T_COLON') {
            return;
        }

        if (isset($tokens[$stackPtr]['scope_closer']) !== true) {
            return;
        }

        $closer = $tokens[$stackPtr]['scope_closer'];

        $elses = [];

        while (true) {
            if (in_array($tokens[$closer]['code'], [T_ELSE, T_ELSEIF], true) === true) {
                if (isset($tokens[$closer]['scope_opener']) !== true || isset($tokens[$closer]['scope_closer']) !== true) {
                    return;
                }
                $elses[] = $closer;
                $closer = $tokens[$closer]['scope_closer'];
                continue;
            }
            break;
        }

        $closers = [T_ENDIF, T_ENDFOREACH, T_ENDFOR, T_ENDWHILE, T_ENDSWITCH];
        if (in_array($tokens[$closer]['code'], $closers, true) !== true) {
            return;
        }

        $comma = $phpcsFile->findNext(T_WHITESPACE, ($closer + 1), null, true);
        if ($tokens[$comma]['code'] !== T_SEMICOLON) {
            $comma = null;
        }

        $fix = $phpcsFile->addFixableError('Alternative control structure syntax is not allowed; found ":", expected "{"', $stackPtr, 'Opener');
        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($opener, '{');

            foreach ($elses as $else) {
                $phpcsFile->fixer->addContentBefore($else, '}');
                $phpcsFile->fixer->replaceToken($tokens[$else]['scope_opener'], '{');
            }
            if ($comma !== null) {
                $phpcsFile->fixer->replaceToken($comma, '');
            }

            $phpcsFile->fixer->replaceToken($closer, '}');
            $phpcsFile->fixer->endChangeset();
        }
    }
}

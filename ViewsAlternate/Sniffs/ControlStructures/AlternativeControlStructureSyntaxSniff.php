<?php
/**
 * ViewsAlternate_Sniffs_ControlStructures_AlternativeControlStructureSyntaxSniff.
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
 * ViewsAlternate_Sniffs_ControlStructures_AlternativeControlStructureSyntaxSniff.
 *
 * Verifies that alternative control structure syntax is used.
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
class ViewsAlternate_Sniffs_ControlStructures_AlternativeControlStructureSyntaxSniff implements PHP_CodeSniffer_Sniff
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

        if ($tokens[$opener]['code'] !== T_OPEN_CURLY_BRACKET) {
            return;
        }

        if (isset($tokens[$stackPtr]['scope_closer']) !== true) {
            return;
        }

        $closer = $tokens[$stackPtr]['scope_closer'];
        $elses  = [];

        $mapping = [
            T_IF => 'endif',
            T_FOREACH => 'endforeach',
            T_FOR => 'endfor',
            T_WHILE => 'endwhile',
            T_SWITCH => 'endswitch',
        ];

        $closeText = $mapping[$tokens[$stackPtr]['code']];

        while (true) {
            $else = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($closer + 1), null, true);
            if ($else === false) {
                break;
            }
            if (in_array($tokens[$else]['code'], [T_ELSE, T_ELSEIF], true) === false) {
                break;
            }

            if (isset($tokens[$else]['scope_opener']) !== true || isset($tokens[$else]['scope_closer']) !== true) {
                // This sniff cannot deal with this, but the InlineControlStructure sniff can fix it for us.
                return;
            }

            $elses[] = [
                'opener' => $tokens[$else]['scope_opener'],
                'closer' => $closer,
            ];
            $closer = $tokens[$else]['scope_closer'];
        }

        if ($tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET) {
            // Something is wrong.
            return;
        }

        $comma = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($closer + 1), null, true);
        if ($tokens[$comma]['code'] !== T_SEMICOLON) {
            $comma = null;
        }

        $fix = $phpcsFile->addFixableError('Alternative control structure syntax is required; found "{", expected ":"', $stackPtr, 'Required');
        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($opener, ':');

            foreach ($elses as $else) {
                $phpcsFile->fixer->replaceToken($else['opener'], ':');
                $phpcsFile->fixer->replaceToken($else['closer'], '');
                // delete whitespace after opener
                $nonSpace = $phpcsFile->findNext(T_WHITESPACE, ($else['closer'] + 1), null, true);
                for ($i = ($else['closer'] + 1); $i < $nonSpace; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }



            if ($comma === null) {
                $closeText .= ';';
            }

            $phpcsFile->fixer->replaceToken($closer, $closeText);
            $phpcsFile->fixer->endChangeset();
        }
    }
}

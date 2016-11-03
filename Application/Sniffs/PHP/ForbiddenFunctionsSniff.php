<?php
/**
 * Application_Sniffs_PHP_ForbiddenFunctionsSniff.
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
 * Application_Sniffs_PHP_ForbiddenFunctionsSniff.
 *
 * Discourages the use of alias functions that are kept in PHP for compatibility
 * with older versions. Can be used to forbid the use of any function.
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
class Application_Sniffs_PHP_ForbiddenFunctionsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff
{
    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists. IE, the
     * function should just not be used.
     *
     * @var array(string => string|null)
     */
    public $forbiddenFunctions = array(
                                  'sizeof'          => 'count',
                                  'delete'          => 'unset',
                                  'print'           => 'echo',
                                  // 'is_null'         => null,
                                  'create_function' => null,
                                 );


    /**
     * Generates the error or warning for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the forbidden function
     *                                        in the token array.
     * @param string               $function  The name of the forbidden function.
     * @param string               $pattern   The pattern used for the match.
     *
     * @return void
     */
    protected function addError($phpcsFile, $stackPtr, $function, $pattern=null)
    {
        $data  = array($function);
        $error = 'The use of function %s() is ';
        if ($this->error === true) {
            $type   = 'Found';
            $error .= 'forbidden';
        } else {
            $type   = 'Discouraged';
            $error .= 'discouraged';
        }

        if ($pattern === null) {
            $pattern = strtolower($function);
        }

        $replacement = null;
        if ($this->forbiddenFunctions[$pattern] !== null
            && $this->forbiddenFunctions[$pattern] !== 'null'
        ) {
            $type       .= 'WithAlternative';
            $replacement = $this->forbiddenFunctions[$pattern];
            $data[]      = $replacement;
            $error      .= '; use %s() instead';
        }

        if ($replacement === null) {
            if ($this->error === true) {
                $phpcsFile->addError($error, $stackPtr, $type, $data);
            } else {
                $phpcsFile->addWarning($error, $stackPtr, $type, $data);
            }
        } else {
            if ($this->error === true) {
                $fix = $phpcsFile->addFixableError($error, $stackPtr, $type, $data);
            } else {
                $fix = $phpcsFile->addFixableWarning($error, $stackPtr, $type, $data);
            }

            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($stackPtr, $replacement);
            }
        }

    }//end addError()


}//end class

<?php

namespace Mito\Application\Sniffs\Classes;

use PHP_CodeSniffer\Exceptions\RuntimeException;

/**
 * PropertyOrderSniff.
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
 * PropertyOrderSniff
 *
 * Verifies that properties are declared before methods and in visibility order.
 * Private properties may be declared after methods.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Nikola Kovacs <nikola.kovacs@gmail.com>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PropertyOrderSniff implements \PHP_CodeSniffer\Sniffs\Sniff
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
        return array(
                T_CLASS,
                T_INTERFACE,
                T_TRAIT,
               );

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
        $scopes = array(
                   'public'    => 0,
                   'protected' => 1,
                   'private'   => 2,
                  );

        $scopesFlipped  = array_flip($scopes);
        $end            = $tokens[$stackPtr]['scope_closer'];
        $methodsStarted = false;
        $currentScope   = 0;
        for ($i = ($stackPtr + 1); $i < $end; $i++) {
            if ($tokens[$i]['code'] === T_FUNCTION) {
                $methodsStarted = true;
                // Continue scanning after end of method body.
                if (isset($tokens[$i]['scope_closer']) !== false) {
                    $i = $tokens[$i]['scope_closer'];
                    continue;
                }

                // If the function has no body, continue after parameters.
                if (isset($tokens[$i]['parenthesis_closer']) !== false) {
                    $i = $tokens[$i]['parenthesis_closer'];
                    continue;
                }
            } else if ($tokens[$i]['code'] === T_VARIABLE) {
                try {
                    $propProps = $phpcsFile->getMemberProperties($i);
                } catch (RuntimeException $e) {
                    // Not a class member var.
                    continue;
                }

                if ($methodsStarted === true && $propProps['scope'] !== 'private') {
                    $error = 'Public and protected properties should be declared before methods.';
                    $phpcsFile->addError($error, $i, 'PropertyAfterMethod');
                    continue;
                }

                $scope = $scopes[$propProps['scope']];
                if ($scope < $currentScope) {
                    $error = 'Property order incorrect: %s after %s.';
                    $data  = array(
                              $scopesFlipped[$scope],
                              $scopesFlipped[$currentScope],
                             );
                    $phpcsFile->addError($error, $i, 'IncorrectPropertyOrder', $data);
                } else {
                    $currentScope = $scope;
                }
            }//end if
        }//end for

    }//end process()


}//end class

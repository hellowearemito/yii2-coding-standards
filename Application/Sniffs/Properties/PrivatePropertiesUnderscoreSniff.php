<?php
/**
 * Application_Sniffs_Properties_PrivatePropertiesUnderscoreSniff
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
 * Application_Sniffs_Properties_PrivatePropertiesUnderscoreSniff
 *
 * Verifies that private property names are prefixed with an underscore.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Nikola Kovacs <nikola.kovacs@gmail.com>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Application_Sniffs_Properties_PrivatePropertiesUnderscoreSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_PRIVATE);

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $file    The current file being checked.
     * @param int                  $pointer The position of the current token in
     *                                      the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $file, $pointer)
    {
        $tokens = $file->getTokens();
        if ($tokens[$pointer]['content'] === 'private'
            && $tokens[($pointer + 1)]['type'] === 'T_WHITESPACE'
            && $tokens[($pointer + 2)]['type'] === 'T_VARIABLE'
            && strpos($tokens[($pointer + 2)]['content'], '$_') !== 0
        ) {
            $file->addError('Private property name must be prefixed with underscore.', $pointer);
        }

    }//end process()


}//end class

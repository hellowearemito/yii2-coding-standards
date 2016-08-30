<?php
/**
 * Unit test class for the MultiLineCondition sniff.
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
 * Unit test class for the MultiLineCondition sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
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
class Application_Tests_ControlStructures_MultiLineControlStructureUnitTest extends AbstractSniffUnitTest
{


    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @param string $testFile The name of the file being tested.
     *
     * @return array<int, int>
     */
    public function getErrorList($testFile='MultiLineControlStructureUnitTest.inc')
    {
        $errors = array(
                   23  => 1,
                   44  => 1,
                   45  => 1,
                   46  => 1,
                   47  => 1,
                   54  => 1,
                   67  => 1,
                   73  => 1,
                   77  => 1,
                   83  => 1,
                   89  => 1,
                   94  => 1,
                   96  => 2,
                   111 => 2,
                   151 => 1,
                   160 => 1,
                  );

        if ($testFile === 'MultiLineControlStructureUnitTest.inc') {
            $errors[219] = 1;
            $errors[222] = 2;
            $errors[225] = 1;
            $errors[226] = 1;
            $errors[229] = 1;
            $errors[233] = 1;
            $errors[236] = 1;
            $errors[241] = 1;
            $errors[242] = 1;
        }

        return $errors;

    }//end getErrorList()


    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getWarningList()
    {
        return array();

    }//end getWarningList()


}//end class

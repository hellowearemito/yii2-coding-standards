<?php
/**
 * Unit test class for the ControlSignature sniff.
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
 * Unit test class for the ControlSignature sniff.
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
class Application_Tests_ControlStructures_ControlSignatureUnitTest extends AbstractSniffUnitTest
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
    public function getErrorList($testFile='ControlSignatureUnitTest.inc')
    {
        $errors = array(
                   8   => 1,
                   13  => 1,
                   16  => 1,
                   19  => 1,
                   21  => 1,
                   23  => 2,
                   29  => 2,
                   33  => 1,
                   39  => 2,
                   43  => 1,
                   49  => 2,
                   53  => 1,
                   63  => 2,
                   67  => 2,
                   77  => 4,
                   81  => 2,
                   95  => 1,
                   100 => 1,
                   109 => 1,
                   113 => 1,
                  );

        if ($testFile === 'ControlSignatureUnitTest.inc') {
            $errors[123] = 1;
            $errors[131] = 2;
            $errors[135] = 1;
            $errors[151] = 1;
            $errors[154] = 1;
            $errors[159] = 1;
            $errors[166] = 1;
            $errors[171] = 2;
            $errors[186] = 1;
            $errors[190] = 1;
            $errors[194] = 1;
            $errors[199] = 2;
            $errors[200] = 2;
            $errors[204] = 1;
            $errors[226] = 1;
            $errors[233] = 1;
            $errors[238] = 2;
            $errors[242] = 2;
            $errors[243] = 2;
            $errors[247] = 1;
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

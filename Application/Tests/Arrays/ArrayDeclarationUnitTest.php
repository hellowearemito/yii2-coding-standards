<?php

namespace Mito\Application\Tests\Arrays;

/**
 * Unit test class for the ArrayDeclaration sniff.
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
 * Unit test class for the ArrayDeclaration sniff.
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
class ArrayDeclarationUnitTest extends \PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest
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
    public function getErrorList($testFile='')
    {
        switch ($testFile) {
        case 'ArrayDeclarationUnitTest.1.inc':
            return array(
                    7   => 2,
                    9   => 1,
                    10  => 1,
                    11  => 1,
                    22  => 1,
                    // 23  => 1,
                    24  => 1,
                    25  => 1,
                    31  => 1,
                    35  => 1,
                    36  => 1,
                    // 41  => 1,
                    46  => 1,
                    47  => 1,
                    50  => 1,
                    51  => 1,
                    53  => 1,
                    56  => 1,
                    58  => 1,
                    61  => 1,
                    62  => 2,
                    63  => 1,
                    64  => 1,
                    65  => 1,
                    66  => 2,
                    70  => 1,
                    76  => 2,
                    77  => 1,
                    78  => 6,
                    79  => 2,
                    81  => 2,
                    82  => 4,
                    88  => 1,
                    100 => 1,
                    101 => 1,
                    102 => 1,
                    105 => 1,
                    106 => 2,
                    107 => 2,
                    125 => 1,
                    126 => 1,
                    141 => 1,
                    144 => 2,
                    146 => 1,
                    148 => 1,
                    157 => 1,
                    173 => 1,
                    174 => 4,
                    179 => 1,
                    182 => 1,
                    188 => 1,
                    207 => 1,
                    212 => 1,
                    214 => 1,
                    218 => 5,
                    219 => 5,
                    255 => 1,
                    294 => 1,
                    295 => 1,
                    296 => 1,
                    313 => 1,
                    320 => 1,
                    338 => 1,
                    342 => 1,
                    347 => 2,
                    348 => 1,
                    349 => 2,
                    350 => 1,
                    355 => 2,
                    356 => 1,
                    357 => 2,
                    358 => 1,
                    376 => 1,
                    381 => 1,
                    383 => 1,
                    384 => 1,
                    388 => 1,
                    394 => 2,
                    397 => 2,
                    399 => 2,
                    400 => 2,
                    401 => 2,
                    405 => 2,
                    406 => 2,
                    409 => 2,
                    410 => 2,
                    429 => 2,
                    437 => 1,
                    441 => 1,
                    449 => 1,
                    453 => 1,
                   );
        case 'ArrayDeclarationUnitTest.2.inc':
            return array(
                    10  => 1,
                    11  => 1,
                    // 23  => 1,
                    24  => 1,
                    25  => 1,
                    31  => 1,
                    36  => 1,
                    // 41  => 1,
                    46  => 1,
                    47  => 1,
                    51  => 1,
                    53  => 1,
                    56  => 1,
                    61  => 1,
                    62  => 1,
                    63  => 1,
                    64  => 1,
                    65  => 1,
                    66  => 1,
                    70  => 1,
                    76  => 1,
                    77  => 1,
                    78  => 6,
                    79  => 2,
                    81  => 2,
                    82  => 4,
                    88  => 1,
                    100 => 1,
                    101 => 1,
                    102 => 1,
                    105 => 1,
                    106 => 2,
                    107 => 2,
                    125 => 1,
                    126 => 1,
                    141 => 1,
                    144 => 2,
                    146 => 1,
                    148 => 1,
                    157 => 1,
                    173 => 1,
                    174 => 4,
                    179 => 1,
                    190 => 1,
                    191 => 1,
                    192 => 1,
                    207 => 1,
                    210 => 4,
                    211 => 4,
                    247 => 1,
                    286 => 1,
                    287 => 1,
                    288 => 1,
                    305 => 1,
                    312 => 1,
                    330 => 1,
                    334 => 1,
                    339 => 2,
                    340 => 1,
                    341 => 2,
                    342 => 1,
                    347 => 2,
                    348 => 1,
                    349 => 2,
                    350 => 1,
                    368 => 1,
                    373 => 1,
                    375 => 1,
                    376 => 1,
                    380 => 1,
                    386 => 2,
                    389 => 2,
                    391 => 2,
                    392 => 2,
                    393 => 2,
                    397 => 2,
                    398 => 2,
                    401 => 2,
                    402 => 2,
                    421 => 2,
                    429 => 1,
                    433 => 1,
                    441 => 1,
                    445 => 1,
                   );
        default:
            return array();
        }//end switch

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

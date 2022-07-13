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
                    6   => 2,
                    8   => 1,
                    9  => 1,
                    10  => 1,
                    21  => 1,
                    // 22  => 1,
                    23  => 1,
                    24  => 1,
                    30  => 1,
                    34  => 1,
                    35  => 1,
                    // 40  => 1,
                    45  => 1,
                    46  => 1,
                    49  => 1,
                    50  => 1,
                    52  => 1,
                    55  => 1,
                    57  => 1,
                    60  => 1,
                    61  => 2,
                    62  => 1,
                    63  => 1,
                    64  => 1,
                    65  => 2,
                    69  => 1,
                    75  => 2,
                    76  => 1,
                    77  => 6,
                    78  => 2,
                    80  => 2,
                    81  => 4,
                    87  => 1,
                    99  => 1,
                    100 => 1,
                    101 => 1,
                    104 => 1,
                    105 => 2,
                    106 => 2,
                    124 => 1,
                    125 => 1,
                    140 => 1,
                    143 => 2,
                    145 => 1,
                    147 => 1,
                    156 => 1,
                    172 => 1,
                    173 => 4,
                    178 => 1,
                    181 => 1,
                    187 => 1,
                    206 => 1,
                    211 => 1,
                    213 => 1,
                    217 => 5,
                    218 => 5,
                    254 => 1,
                    293 => 1,
                    294 => 1,
                    295 => 1,
                    312 => 1,
                    319 => 1,
                    337 => 1,
                    341 => 1,
                    346 => 2,
                    347 => 1,
                    348 => 2,
                    349 => 1,
                    354 => 2,
                    355 => 1,
                    356 => 2,
                    357 => 1,
                    375 => 1,
                    380 => 1,
                    382 => 1,
                    383 => 1,
                    387 => 1,
                    393 => 2,
                    395 => 2,
                    397 => 2,
                    398 => 2,
                    399 => 2,
                    418 => 2,
                    426 => 1,
                    430 => 1,
                    438 => 1,
                    442 => 1,
                   );
        case 'ArrayDeclarationUnitTest.2.inc':
            return array(
                    9  => 1,
                    10  => 1,
                    // 22  => 1,
                    23  => 1,
                    24  => 1,
                    30  => 1,
                    35  => 1,
                    // 40  => 1,
                    45  => 1,
                    46  => 1,
                    50  => 1,
                    52  => 1,
                    55  => 1,
                    60  => 1,
                    61  => 1,
                    62  => 1,
                    63  => 1,
                    64  => 1,
                    65  => 1,
                    69  => 1,
                    75  => 1,
                    76  => 1,
                    77  => 6,
                    78  => 2,
                    80  => 2,
                    81  => 4,
                    87  => 1,
                    99 => 1,
                    100 => 1,
                    101 => 1,
                    104 => 1,
                    105 => 2,
                    106 => 2,
                    124 => 1,
                    125 => 1,
                    140 => 1,
                    143 => 2,
                    145 => 1,
                    147 => 1,
                    156 => 1,
                    172 => 1,
                    173 => 4,
                    178 => 1,
                    189 => 1,
                    190 => 1,
                    191 => 1,
                    206 => 1,
                    209 => 4,
                    210 => 4,
                    246 => 1,
                    287 => 1,
                    286 => 1,
                    285 => 1,
                    304 => 1,
                    311 => 1,
                    329 => 1,
                    333 => 1,
                    338 => 2,
                    339 => 1,
                    340 => 2,
                    341 => 1,
                    346 => 2,
                    347 => 1,
                    348 => 2,
                    349 => 1,
                    367 => 1,
                    372 => 1,
                    374 => 1,
                    375 => 1,
                    379 => 1,
                    385 => 2,
                    387 => 2,
                    389 => 2,
                    390 => 2,
                    391 => 2,
                    410 => 2,
                    418 => 1,
                    422 => 1,
                    430 => 1,
                    434 => 1,
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

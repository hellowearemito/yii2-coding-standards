<?php

if (defined('PHP_CODESNIFFER_IN_TESTS') === false) {
    define('PHP_CODESNIFFER_IN_TESTS', true);
}

require_once 'TestSuite.php';
include_once 'AllSniffs.php';

$composerAutoload = [
    __DIR__ . '/../vendor/autoload.php', // in repo
    __DIR__ . '/../../../autoload.php', // installed
];
$vendorPath = null;
foreach ($composerAutoload as $autoload) {
    if (file_exists($autoload)) {
        require($autoload);
        $vendorPath = dirname($autoload);
        break;
    }
}

class AllTests
{
    /**
     * Prepare the test runner.
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }//end main()
    /**
     * Add all PHP_CodeSniffer test suites into a single test suite.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'] = array();
        // Use a special PHP_CodeSniffer test suite so that we can
        // unset our autoload function after the run.
        $suite = new PHP_CodeSniffer_TestSuite('Mito Coding Standards');
        $suite->addTest(AllSniffs::suite());
        // Unregister this here because the PEAR tester loads
        // all package suites before running then, so our autoloader
        // will cause problems for the packages included after us.
        spl_autoload_unregister(array('PHP_CodeSniffer', 'autoload'));
        return $suite;
    }//end suite()
}//end class

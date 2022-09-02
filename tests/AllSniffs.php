<?php

namespace PHP_CodeSniffer\Tests;

use PHP_CodeSniffer\Autoload;

include_once __DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php';

class AllSniffs
{

    /**
     * A list of test file paths without a corresponding sniff file.
     *
     * @var array
     */
    public static $orphanedTests = array();


    /**
     * Prepare the test runner.
     *
     * @return void
     */
    public static function main()
    {
        \PHPUnit\TextUI\TestRunner::run(self::suite());

    }//end main()


    /**
     * Add all sniff unit tests into a test suite.
     *
     * Sniff unit tests are found by recursing through the 'Tests' directory
     * of each installed coding standard.
     *
     * @return \PHPUnit\Framework\TestSuite
     */
    public static function suite()
    {
        $suite = new \PHPUnit\Framework\TestSuite('Mito Coding Standards Sniffs');

        $standards = [
            'Application',
            'Others',
            'Views',
            'ViewsAlternate',
        ];
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src';

        Autoload::addSearchPath($path);

        $GLOBALS['PHP_CODESNIFFER_SNIFF_CODES'] = [];
        $GLOBALS['PHP_CODESNIFFER_FIXABLE_CODES'] = [];

        foreach ($standards as $standard) {

            $testsDir = $path.DIRECTORY_SEPARATOR.$standard.DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR;

            if (is_dir($testsDir) === false) {
                // No tests for this standard.
                continue;
            }

            $di = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($testsDir));

            foreach ($di as $file) {
                // Skip hidden files.
                if (substr($file->getFilename(), 0, 1) === '.') {
                    continue;
                }

                // Tests must have the extension 'php'.
                $parts = explode('.', $file);
                $ext   = array_pop($parts);
                if ($ext !== 'php') {
                    continue;
                }

                $filePath  = $file->getPathname();
                $className = str_replace($path.DIRECTORY_SEPARATOR, '', $filePath);
                $className = substr($className, 0, -4);
                $className = '/Mito/'. $className;

                // Include the sniff here so tests can use it in their setup() methods.
                $parts = explode('/', $className);
                if (isset($parts[2],$parts[4],$parts[5]) === true) {
                    $sniffPath = $path.DIRECTORY_SEPARATOR.$parts[2].DIRECTORY_SEPARATOR.'Sniffs'.DIRECTORY_SEPARATOR.$parts[4].DIRECTORY_SEPARATOR.$parts[5];
                    $sniffPath = substr($sniffPath, 0, -8).'Sniff.php';

                    if (file_exists($sniffPath) === true) {
                        $className = Autoload::loadFile($filePath);
                        $GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'][$className] = $path.DIRECTORY_SEPARATOR.$parts[2];
                        $GLOBALS['PHP_CODESNIFFER_TEST_DIRS'][$className] = $testsDir;
                        $suite->addTestSuite($className);
                    } else {
                        self::$orphanedTests[] = $filePath;
                    }
                } else {
                    self::$orphanedTests[] = $filePath;
                }
            }//end foreach
        }//end foreach

        return $suite;

    }//end suite()


}//end class

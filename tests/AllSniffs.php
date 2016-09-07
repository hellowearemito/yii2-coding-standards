<?php

require_once dirname(__FILE__).'/AbstractSniffUnitTest.php';

class AllSniffs
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
     * Add all sniff unit tests into a test suite.
     *
     * Sniff unit tests are found by recursing through the 'Tests' directory
     * of each installed coding standard.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Mito Coding Standards Sniffs');

        $standards = [
            'Application',
            'Others',
            'Views',
            'ViewsAlternate',
        ];
        $path = dirname(__DIR__);

        foreach ($standards as $standard) {
            $testsDir = $path.DIRECTORY_SEPARATOR.$standard.DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR;
            if (is_dir($testsDir) === false) {
                // No tests for this standard.
                continue;
            }
            $di = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testsDir));
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
                $className = str_replace(DIRECTORY_SEPARATOR, '_', $className);
                // Include the sniff here so tests can use it in their setup() methods.
                $parts     = explode('_', $className);
                $sniffPath = $path.DIRECTORY_SEPARATOR.$parts[0].DIRECTORY_SEPARATOR.'Sniffs'.DIRECTORY_SEPARATOR.$parts[2].DIRECTORY_SEPARATOR.$parts[3];
                $sniffPath = substr($sniffPath, 0, -8).'Sniff.php';
                include_once $sniffPath;
                include_once $filePath;
                $GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'][$className] = $path;
                $suite->addTestSuite($className);
            }//end foreach
        }//end foreach
        return $suite;
    }//end suite()
}//end class

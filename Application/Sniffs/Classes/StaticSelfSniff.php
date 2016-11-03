<?php
/**
 * Application_Sniffs_Classes_StaticSelfSniff.
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

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
    $error = 'Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Tests self member references.
 *
 * Verifies that :
 * <ul>
 *  <li>static:: is used instead of Static::</li>
 *  <li>self:: is used instead of Self::</li>
 *  <li>self:: is used instead of self ::</li>
 *  <li>static:: is used instead of static ::</li>
 *  <li>self:: is used for constants</li>
 *  <li>self:: is used for private static properties and methods</li>
 *  <li>self:: is allowed for recursion</li>
 *  <li>in all other cases, static:: is used instead of self::</li>
 * </ul>
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
class Application_Sniffs_Classes_StaticSelfSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{


    /**
     * Constructs a Squiz_Sniffs_Classes_SelfMemberReferenceSniff.
     */
    public function __construct()
    {
        parent::__construct(array(T_CLASS, T_TRAIT), array(T_DOUBLE_COLON));

    }//end __construct()


    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     * @param int                  $currScope The current scope opener token.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope)
    {
        $tokens = $phpcsFile->getTokens();

        $methods    = [];
        $properties = [];

        // Find all methods and properties in this class.
        // TODO: this should be done once for each file.
        for ($i = ($currScope + 1); $i < $phpcsFile->numTokens; $i++) {
            if ($tokens[$i]['code'] === T_FUNCTION) {
                $methodPtr = $i;

                // Continue scanning after end of method body
                // or parameter list if method has no body.
                if (isset($tokens[$i]['scope_closer']) !== false) {
                    $i = $tokens[$i]['scope_closer'];
                } else if (isset($tokens[$methodPtr]['parenthesis_closer']) !== false) {
                    $i = $tokens[$i]['parenthesis_closer'];
                }

                $methodProps = $phpcsFile->getMethodProperties($methodPtr);
                if ($methodProps['is_static'] !== true) {
                    continue;
                }

                if (isset($tokens[$methodPtr]['parenthesis_opener']) === false) {
                    // Something is wrong with this method.
                    continue;
                }

                $name = $phpcsFile->findNext(T_WHITESPACE, ($methodPtr + 1), $tokens[$methodPtr]['parenthesis_opener'], true, null, true);
                if ($name === false) {
                    // Something is wrong with this method.
                    continue;
                }

                $methods[$tokens[$name]['content']] = [
                                                       'declaration' => $methodPtr,
                                                       'properties'  => $methodProps,
                                                      ];
            } else if ($tokens[$i]['code'] === T_VARIABLE) {
                try {
                    $propProps = $phpcsFile->getMemberProperties($i);
                } catch (\PHP_CodeSniffer_Exception $e) {
                    // Not a class member var.
                    continue;
                }

                if ($propProps['is_static'] !== true) {
                    continue;
                }

                $properties[$tokens[$i]['content']] = [
                                                       'declaration' => $i,
                                                       'properties'  => $propProps,
                                                      ];
            }//end if
        }//end for

        $calledClassName = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        $own      = false;
        $fixStart = null;
        $declarationName = $tokens[$calledClassName]['content'];
        if ($tokens[$calledClassName]['code'] === T_SELF) {
            $own = true;
        } else if ($tokens[$calledClassName]['code'] === T_STATIC) {
            $own = true;
        } else if ($tokens[$calledClassName]['code'] === T_STRING) {
            // If the class is called with a namespace prefix, build fully qualified
            // namespace calls for both current scope class and requested class.
            if ($tokens[($calledClassName - 1)]['code'] === T_NS_SEPARATOR) {
                $fullQualifiedClassName = $this->getNamespaceOfScope($phpcsFile, $currScope);
                if ($fullQualifiedClassName !== '') {
                    $fullQualifiedClassName .= '\\';
                }

                $fullQualifiedClassName .= $phpcsFile->getDeclarationName($currScope);
                $fullQualifiedClassName  = '\\'.$fullQualifiedClassName;
                $declarationName         = $this->getDeclarationNameWithNamespace($tokens, $calledClassName);
            } else {
                $fullQualifiedClassName = $phpcsFile->getDeclarationName($currScope);
            }

            if ($declarationName === $fullQualifiedClassName) {
                // Class name is the same as the current class, which is not allowed
                // except if being used inside a closure.
                if ($phpcsFile->hasCondition($stackPtr, T_CLOSURE) === false) {
                    $own      = true;
                    $fixStart = $phpcsFile->findPrevious(array(T_NS_SEPARATOR, T_STRING), $calledClassName, null, true);
                }
            }//end if
        }//end if

        if ($tokens[($stackPtr - 1)]['code'] === T_WHITESPACE) {
            $found = strlen($tokens[($stackPtr - 1)]['content']);
            $error = 'Expected 0 spaces before double colon; %s found';
            $data  = array($found);
            $fix   = $phpcsFile->addFixableError($error, $calledClassName, 'SpaceBefore', $data);

            if ($fix === true) {
                $phpcsFile->fixer->replaceToken(($stackPtr - 1), '');
            }
        }

        if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
            $found = strlen($tokens[($stackPtr + 1)]['content']);
            $error = 'Expected 0 spaces after double colon; %s found';
            $data  = array($found);
            $fix   = $phpcsFile->addFixableError($error, $calledClassName, 'SpaceAfter', $data);

            if ($fix === true) {
                $phpcsFile->fixer->replaceToken(($stackPtr + 1), '');
            }
        }

        if ($own === false) {
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true, null, true);
        $name      = $tokens[$nextToken]['content'];
        $reason    = '';
        $expected  = null;
        if ($tokens[$nextToken]['code'] === T_VARIABLE) {
            if (array_key_exists($name, $properties) === true
                && $properties[$name]['properties']['scope'] === 'private'
            ) {
                $reason   = 'for private property access';
                $expected = T_SELF;
            } else {
                $reason   = 'for property access';
                $expected = T_STATIC;
            }
        } else if ($tokens[$nextToken]['code'] === T_STRING
            // Special case for PHP 5.5 class name resolution.
            && strtolower($tokens[$nextToken]['content']) !== 'class'
        ) {
            // Method call or constant.
            $openBracket = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true, null, true);
            if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
                $openBracket = false;
            }

            if ($openBracket === false) {
                // Constants must be accessed using self.
                $reason   = 'for constant access';
                $expected = T_SELF;
            } else if (array_key_exists($name, $methods) === false) {
                $reason   = 'for method access';
                $expected = T_STATIC;
            } else if ($methods[$name]['properties']['scope'] === 'private') {
                // Private methods must be accessed using self.
                $reason   = 'for private method access';
                $expected = T_SELF;
            } else {
                $condition = $phpcsFile->getCondition($nextToken, T_FUNCTION);
                if ($condition !== false) {
                    $functionName = $phpcsFile->findNext(T_WHITESPACE, ($condition + 1), $tokens[$condition]['parenthesis_opener'], true, null, true);
                    if ($functionName !== false && $tokens[$functionName]['content'] === $name) {
                        // Recursion may use self or static, but default to self to avoid changing meaning.
                        $expected = [
                                     T_SELF,
                                     T_STATIC,
                                    ];
                    } else {
                        $reason   = 'for method access';
                        $expected = T_STATIC;
                    }//end if
                }//end if
            }//end if
        }//end if

        if ($expected === null) {
            return;
        }

        if (is_array($expected) === true) {
            if (in_array($tokens[$calledClassName]['code'], $expected) === true) {
                return;
            }

            $expected = $expected[0];
        }

        if ($expected === T_SELF) {
            $expectedContent = 'self';
        } else {
            $expectedContent = 'static';
        }

        if ($tokens[$calledClassName]['code'] !== $expected
            || strtolower($declarationName) !== $declarationName
        ) {
            $error = 'Expected "%s::%s" %s; found "%s::%s"';
            $data  = array(
                      $expectedContent,
                      $name,
                      $reason,
                      $declarationName,
                      $name,
                     );
            $fix   = $phpcsFile->addFixableError($error, $calledClassName, 'Incorrect', $data);

            if ($fix === true) {
                if ($fixStart === null) {
                    $phpcsFile->fixer->replaceToken($calledClassName, $expectedContent);
                } else {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($fixStart + 1); $i < $calledClassName; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->replaceToken($calledClassName, $expectedContent);
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }//end if

    }//end processTokenWithinScope()


    /**
     * Returns the declaration names for classes/interfaces/functions with a namespace.
     *
     * @param array $tokens   Token stack for this file
     * @param int   $stackPtr The position where the namespace building will start.
     *
     * @return string
     */
    protected function getDeclarationNameWithNamespace(array $tokens, $stackPtr)
    {
        $nameParts      = array();
        $currentPointer = $stackPtr;
        while ($tokens[$currentPointer]['code'] === T_NS_SEPARATOR
            || $tokens[$currentPointer]['code'] === T_STRING
        ) {
            $nameParts[] = $tokens[$currentPointer]['content'];
            $currentPointer--;
        }

        $nameParts = array_reverse($nameParts);
        return implode('', $nameParts);

    }//end getDeclarationNameWithNamespace()


    /**
     * Returns the namespace declaration of a file.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the search for the
     *                                        namespace declaration will start.
     *
     * @return string
     */
    protected function getNamespaceOfScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $namespace            = '';
        $namespaceDeclaration = $phpcsFile->findPrevious(T_NAMESPACE, $stackPtr);

        if ($namespaceDeclaration !== false) {
            $endOfNamespaceDeclaration = $phpcsFile->findNext(T_SEMICOLON, $namespaceDeclaration);
            $namespace = $this->getDeclarationNameWithNamespace(
                $phpcsFile->getTokens(),
                ($endOfNamespaceDeclaration - 1)
            );
        }

        return $namespace;

    }//end getNamespaceOfScope()


}//end class

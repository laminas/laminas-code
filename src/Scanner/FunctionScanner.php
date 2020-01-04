<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Scanner;

use Laminas\Code\Annotation;
use Laminas\Code\Exception;
use Laminas\Code\NameInformation;

class FunctionScanner implements ScannerInterface
{
    /**
     * @var bool
     */
    protected $isScanned = false;

    /**
     * @var string
     */
    protected $docComment;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $shortName;

    /**
     * @var int
     */
    protected $lineStart;

    /**
     * @var int
     */
    protected $lineEnd;

    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @var NameInformation
     */
    protected $nameInformation;

    /**
     * @var array
     */
    protected $infos = [];

    /**
     * @param  array $functionTokens
     * @param  NameInformation|null $nameInformation
     */
    public function __construct(array $functionTokens, NameInformation $nameInformation = null)
    {
        $this->tokens          = $functionTokens;
        $this->nameInformation = $nameInformation;
    }

    /**
     * Get annotations
     *
     * @param  Annotation\AnnotationManager $annotationManager
     * @return bool|Annotation\AnnotationCollection
     */
    public function getAnnotations(Annotation\AnnotationManager $annotationManager)
    {
        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        return new AnnotationScanner($annotationManager, $docComment, $this->nameInformation);
    }

    /**
     * @return string
     */
    public function getNamespaceName()
    {
        return $this->nameInformation->getNamespace();
    }

    /**
     * @return bool
     */
    public function inNamespace()
    {
        return false === empty($this->getNamespaceName());
    }

    /**
     * Return documentation comment
     *
     * @return null|string
     */
    public function getDocComment()
    {
        $this->scan();

        return $this->docComment;
    }

    /**
     * Return documentation block
     *
     * @return false|DocBlockScanner
     */
    public function getDocBlock()
    {
        if (! $docComment = $this->getDocComment()) {
            return false;
        }

        return new DocBlockScanner($docComment);
    }

    /**
     * Return a name of class
     *
     * @return null|string
     */
    public function getName()
    {
        $this->scan();
        return $this->name;
    }

    /**
     * Return short name of class
     *
     * @return null|string
     */
    public function getShortName()
    {
        $this->scan();
        return $this->shortName;
    }

    /**
     * Return number of first line
     *
     * @return int|null
     */
    public function getLineStart()
    {
        $this->scan();
        return $this->lineStart;
    }

    /**
     * Return number of last line
     *
     * @return int|null
     */
    public function getLineEnd()
    {
        $this->scan();
        return $this->lineEnd;
    }

    /**
     * Return a list of ParameterNames
     *
     * @return array
     */
    public function getParameterNames()
    {
        $this->scan();

        $return = [];
        foreach ($this->infos as $info) {
            if ($info['type'] == 'parameter') {
                $return[] = $info['name'];
            }
        }

        return $return;
    }

    /**
     * Return a list of Parameters
     *
     * @return ParameterScanner[]
     */
    public function getParameters()
    {
        $this->scan();

        $return = [];
        foreach ($this->infos as $info) {
            if ($info['type'] == 'parameter') {
                $return[$info['name']] = $this->getParameter($info['name']);
            }
        }

        return $return;
    }

    /**
     * @param  int|string $parameterNameOrInfoIndex
     * @return ParameterScanner
     * @throws Exception\InvalidArgumentException
     */
    public function getParameter($parameterNameOrInfoIndex)
    {
        $this->scan();

        if (is_int($parameterNameOrInfoIndex)) {
            if (! isset($this->infos[$parameterNameOrInfoIndex])) {
                throw new Exception\InvalidArgumentException('Index of info offset is not about a parameter');
            }
            $info = $this->infos[$parameterNameOrInfoIndex];
            if ($info['type'] != 'parameter') {
                throw new Exception\InvalidArgumentException('Index of info offset is not about a parameter');
            }
        } elseif (is_string($parameterNameOrInfoIndex)) {
            foreach ($this->infos as $info) {
                if ($info['type'] === 'parameter' && $info['name'] === $parameterNameOrInfoIndex) {
                    break;
                }
                unset($info);
            }
            if (! isset($info)) {
                throw new Exception\InvalidArgumentException('Index of info offset is not about a parameter');
            }
        }

        $p = new ParameterScanner(
            array_slice($this->tokens, $info['tokenStart'], $info['tokenEnd'] - $info['tokenStart']),
            $this->nameInformation
        );
        $p->setDeclaringFunction($this->name);
        $p->setDeclaringScannerFunction($this);
        $p->setPosition($info['position']);

        return $p;
    }

    /**
     * Verify if class has Parameter
     *
     * @param  string $name
     * @return bool
     */
    public function hasParameter($name)
    {
        $this->scan();

        foreach ($this->infos as $info) {
            if ($info['type'] === 'parameter' && $info['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        $this->scan();

        return $this->body;
    }

    public static function export()
    {
        return "TODO: function export()";
    }

    public function __toString()
    {
        return "TODO: function toString()";
    }

    /**
     * Scan tokens
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function scan()
    {
        if ($this->isScanned) {
            return;
        }

        if (! $this->tokens) {
            throw new Exception\RuntimeException('No tokens were provided');
        }
        /**
         * Variables & Setup
         */
        $tokens          = &$this->tokens; // localize
        $infos           = &$this->infos; // localize
        $tokenIndex      = null;
        $token           = null;
        $tokenType       = null;
        $tokenContent    = null;
        $tokenLine       = null;
        $namespace   = $this->nameInformation instanceof NameInformation ? $this->nameInformation->getNamespace() : '';
        $infoIndex       = 0;
        $parentCount     = 0;

        /*
         * MACRO creation
         */
        $MACRO_TOKEN_ADVANCE = function () use (
            &$tokens,
            &$tokenIndex,
            &$token,
            &$tokenType,
            &$tokenContent,
            &$tokenLine
        ) {
            static $lastTokenArray = null;
            $tokenIndex = $tokenIndex === null ? 0 : $tokenIndex + 1;
            if (! isset($tokens[$tokenIndex])) {
                $token        = false;
                $tokenContent = false;
                $tokenType    = false;
                $tokenLine    = false;

                return false;
            }
            if (is_string($tokens[$tokenIndex]) && $tokens[$tokenIndex] === '"') {
                do {
                    $tokenIndex++;
                } while (! (is_string($tokens[$tokenIndex]) && $tokens[$tokenIndex] === '"'));
            }
            $token = $tokens[$tokenIndex];
            if (is_string($token)) {
                $tokenType    = null;
                $tokenContent = $token;
                $tokenLine   += substr_count(
                    $lastTokenArray[1] ?? '',
                    "\n"
                ); // adjust token line by last known newline count
            } else {
                $lastTokenArray = $token;
                [$tokenType, $tokenContent, $tokenLine] = $token;
            }

            return $tokenIndex;
        };
        $MACRO_INFO_START    = function () use (&$infoIndex, &$infos, &$tokenIndex, &$tokenLine) {
            $infos[$infoIndex] = [
                'type'        => 'parameter',
                'tokenStart'  => $tokenIndex,
                'tokenEnd'    => null,
                'lineStart'   => $tokenLine,
                'lineEnd'     => $tokenLine,
                'name'        => null,
                'position'    => $infoIndex + 1, // position is +1 of infoIndex
            ];
        };
        $MACRO_INFO_ADVANCE  = function () use (&$infoIndex, &$infos, &$tokenIndex, &$tokenLine) {
            $infos[$infoIndex]['tokenEnd'] = $tokenIndex;
            $infos[$infoIndex]['lineEnd']  = $tokenLine;
            $infoIndex++;

            return $infoIndex;
        };

        /**
         * START FINITE STATE MACHINE FOR SCANNING TOKENS
         */
        // Initialize token
        $MACRO_TOKEN_ADVANCE();

        SCANNER_TOP:

        if ($token === false) {
            goto SCANNER_END;
        }

        $this->lineStart = $this->lineStart ? : $tokenLine;

        switch ($tokenType) {
            case T_DOC_COMMENT:
                if ($this->docComment === null && $this->name === null) {
                    $this->docComment = $tokenContent;
                }
                goto SCANNER_CONTINUE_SIGNATURE;
                //goto no break needed

            case T_FUNCTION:
                $this->shortName = $tokens[$tokenIndex + 2][1];
                if ($namespace) {
                    $this->name = $namespace . '\\' . $this->shortName;
                } else {
                    $this->name = $this->shortName;
                }

                goto SCANNER_CONTINUE_SIGNATURE;
                //goto no break needed

            case T_VARIABLE:
            case T_STRING:
                if ($parentCount === 1) {
                    if (! isset($infos[$infoIndex])) {
                        $MACRO_INFO_START();
                    }
                    if ($tokenType === T_VARIABLE) {
                        $infos[$infoIndex]['name'] = ltrim($tokenContent, '$');
                    }
                }

                goto SCANNER_CONTINUE_SIGNATURE;
                // goto (no break needed);

            case null:
                switch ($tokenContent) {
                    case '&':
                        if (! isset($infos[$infoIndex])) {
                            $MACRO_INFO_START();
                        }
                        goto SCANNER_CONTINUE_SIGNATURE;
                        // goto (no break needed);
                    case '(':
                        $parentCount++;
                        goto SCANNER_CONTINUE_SIGNATURE;
                        // goto (no break needed);
                    case ')':
                        $parentCount--;
                        if ($parentCount > 0) {
                            goto SCANNER_CONTINUE_SIGNATURE;
                        }
                        if ($parentCount === 0) {
                            if ($infos) {
                                $MACRO_INFO_ADVANCE();
                            }
                        }
                        goto SCANNER_CONTINUE_BODY;
                        // goto (no break needed);
                    case ',':
                        if ($parentCount === 1) {
                            $MACRO_INFO_ADVANCE();
                        }
                        goto SCANNER_CONTINUE_SIGNATURE;
                }
        }

        SCANNER_CONTINUE_SIGNATURE:

        if ($MACRO_TOKEN_ADVANCE() === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_CONTINUE_BODY:

        $braceCount = 0;
        while ($MACRO_TOKEN_ADVANCE() !== false) {
            if ($tokenContent == '}') {
                $braceCount--;
            }
            if ($braceCount > 0) {
                $this->body .= $tokenContent;
            }
            if ($tokenContent == '{') {
                $braceCount++;
            }
            $this->lineEnd = $tokenLine;
        }

        SCANNER_END:

        $this->isScanned = true;
    }
}

<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Scanner;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Exception;

class FileScanner extends TokenArrayScanner implements ScannerInterface
{
    /**
     * @var string
     */
    protected $file = null;

    /**
     * @param  string $file
     * @param  null|AnnotationManager $annotationManager
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($file, AnnotationManager $annotationManager = null)
    {
        $this->file = $file;
        if (!file_exists($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'File "%s" not found',
                $file
            ));
        }
        parent::__construct(token_get_all(file_get_contents($file)), $annotationManager);
    }

    /**
     * @return null|string
     */
    public function getFile()
    {
        return $this->file;
    }
}

<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\TestAsset;


/**
 * TestSampleClass6 DocBlock Short Desc
 *
 * Testing for formatted dockblock tags. See Laminas-6726.
 * (This long description should be longer than 3 lines.
 * It indeed is longer than 3 lines
 * now.)
 *
 * @author Carlton Gibson <carlton.gibson@noumenal.co.uk>
 */
class TestSampleClass6
{

    /**
     * Method ShortDescription
     *
     * Notice the multiple spaces aligning the columns in the docblock
     * tags. (This long description should be longer than 3 lines.
     * It indeed is longer than 3 lines
     * now.)
     *
     * @emptyTag
     * @descriptionTag           A tag with just a description
     * @param   int     $var     Description of $var
     * @return  string           Description of return value
     */
    public function doSomething($var)
    {
        //we need a multi-line method body.
        $assigned = 1;
        $alsoAssigined = 2;
        return 'mixedValue';
    }
}

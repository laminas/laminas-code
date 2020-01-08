<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Scanner\TestAsset\MapperExample;

class DbAdapter
{
    protected $username = null;
    protected $password = null;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function __toString()
    {
        return 'I am ' . get_class($this) . ' object (hash ' . spl_object_hash($this) . '), with these parameters (username = ' . $this->username . ', password = ' . $this->password . ')';
    }

}

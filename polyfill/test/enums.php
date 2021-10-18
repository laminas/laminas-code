<?php

if (\PHP_VERSION_ID < 80100) {
    return;
}

require_once('polyfill/test/Environment.php');
require_once('polyfill/test/Orientation.php');
require_once('polyfill/test/Flags.php');

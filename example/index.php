<?php

namespace codesaur\Http\Message\Example;

/* DEV: v1.2021.03.02
 * 
 * This is an example script!
 */

require_once '../vendor/autoload.php';

use codesaur\Http\Message\ServerRequest;

$request = new ServerRequest();
$request->initFromGlobal();
var_dump($request);

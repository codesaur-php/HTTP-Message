<?php

namespace codesaur\Http\Message\Example;

/* DEV: v1.2021.03.02
 * 
 * This is an example script!
 */

\ini_set('display_errors', 'On');
\error_reporting(\E_ALL);

require_once '../vendor/autoload.php';

use codesaur\Http\Message\ServerRequest;

$request = new ServerRequest();
$request->initFromGlobal();
\var_dump($request);

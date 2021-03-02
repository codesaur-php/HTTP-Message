<?php

namespace codesaur\Http\Message;

interface ReasonPrhaseInterface
{
    // Informational
    const STATUS_100 = 'Continue';
    const STATUS_101 = 'Switching Protocols';
    const STATUS_102 = 'Processing';
    const STATUS_103 = 'Early Hints';
    
    // Successful
    const STATUS_200 = 'OK';
    const STATUS_201 = 'Created';
    const STATUS_202 = 'Accepted';
    const STATUS_203 = 'Non-Authoritative Information';
    const STATUS_204 = 'No Content';
    const STATUS_205 = 'Reset Content';
    const STATUS_206 = 'Partial Content';
    const STATUS_207 = 'Multi-Status';
    const STATUS_208 = 'Already Reported';
    const STATUS_226 = 'IM Used';
    
    // Redirection
    const STATUS_300 = 'Multiple Choices';
    const STATUS_301 = 'Moved Permanently';
    const STATUS_302 = 'Found';
    const STATUS_303 = 'See Other';
    const STATUS_304 = 'Not Modified';
    const STATUS_305 = 'Use Proxy';
    const STATUS_306 = '(Unused)';
    const STATUS_307 = 'Temporary Redirect';
    const STATUS_308 = 'Permanent Redirect';
    
    // Client Errors
    const STATUS_400 = 'Bad Request';
    const STATUS_401 = 'Unauthorized';
    const STATUS_402 = 'Payment Required';
    const STATUS_403 = 'Forbidden';
    const STATUS_404 = 'Not Found';
    const STATUS_405 = 'Method Not Allowed';
    const STATUS_406 = 'Not Acceptable';
    const STATUS_407 = 'Proxy Authentication Required';
    const STATUS_408 = 'Request Timeout';
    const STATUS_409 = 'Conflict';
    const STATUS_410 = 'Gone';
    const STATUS_411 = 'Length Required';
    const STATUS_412 = 'Precondition Failed';
    const STATUS_413 = 'Payload Too Large';
    const STATUS_414 = 'URI Too Long';
    const STATUS_415 = 'Unsupported Media Type';
    const STATUS_416 = 'Range Not Satisfiable';
    const STATUS_417 = 'Expectation Failed';
    const STATUS_418 = 'I\'m a Teapot';    
    const STATUS_421 = 'Misdirected Request';
    const STATUS_422 = 'Unprocessable Entity';
    const STATUS_423 = 'Locked';
    const STATUS_424 = 'Failed Dependency';
    const STATUS_425 = 'Too Early';
    const STATUS_426 = 'Upgrade Required';    
    const STATUS_428 = 'Precondition Required';
    const STATUS_429 = 'Too Many Requests';
    const STATUS_431 = 'Request Header Fields Too Large';
    const STATUS_451 = 'Unavailable For Legal Reasons';
    
    // Server Errors
    const STATUS_500 = 'Internal Server Error';
    const STATUS_501 = 'Not Implemented';
    const STATUS_502 = 'Bad Gateway';
    const STATUS_503 = 'Service Unavailable';
    const STATUS_504 = 'Gateway Timeout';
    const STATUS_505 = 'HTTP Version Not Supported';
    const STATUS_506 = 'Variant Also Negotiates';
    const STATUS_507 = 'Insufficient Storage';
    const STATUS_508 = 'Loop Detected';
    const STATUS_510 = 'Not Extended';
    const STATUS_511 = 'Network Authentication Required';
}

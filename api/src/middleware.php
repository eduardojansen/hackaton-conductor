<?php
$app->add(new \Slim\Middleware\JwtAuthentication([
    "path"     => "/rest",
    'secure'   => false,
    "secret"   => "32A734CCEF845942A44BED7E8175F",
    "cookie"   => "stoken",
    "callback" => function ($request, $response, $arguments) use ($app) {
        $app->jwtHash = $arguments["decoded"];
    },
    "rules"    => [
        new \Slim\Middleware\JwtAuthentication\RequestMethodRule([
            "passthrough" => ["OPTIONS"]
        ])
    ]
]));

$app->add(function ($request, $response, $next) {

    $allowHeaders = $request->getHeader("Access-Control-Request-Headers");
    $allowOriginDomain = '';

    $newResponse = $response
        ->withHeader('Access-Control-Allow-Origin', $allowOriginDomain)
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, HEAD, OPTIONS, DELETE')
        ->withHeader('Access-Control-Allow-Headers', $allowHeaders);

    $newResponse = $next($request, $newResponse);

    return $newResponse;
});

<?php

use Slim\App;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);

    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "path" => "/api", /* or ["/api", "/admin"] */
        "secure" => false,
        "attribute" => "decoded_token_data",
        "secret" => "supersecretkeyyoushouldnotcommittogithub",
        "algorithm" => ["HS256"],
        "error" => function ($res, $args) {
        $data["status"] = "error";
        $data["message"] = $args["message"];
        return $res
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
        ]));
};

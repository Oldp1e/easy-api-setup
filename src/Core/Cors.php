<?php

namespace Src\Core;

class Cors
{
    public static function corsOptions()
    {
        $origin = $_ENV['CROSS_ORIGIN_ACCEPTED_URL'];
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Credentials: false");
        header("Content-Length: 0");
        header("Content-Type: text/plain");
    }

    public static function initCors($corsObject)
    {
        $allowedOrigins = array_map('trim', explode(',', $corsObject['origin']));
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header("Access-Control-Allow-Methods: {$corsObject['methods']}");
        header("Access-Control-Allow-Headers: {$corsObject['headers.allow']}");
        header("Access-Control-Expose-Headers: {$corsObject['headers.expose']}");
        header("Access-Control-Allow-Credentials: " . ($corsObject['credentials'] ? 'true' : 'false'));
    }

}
<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            // 'http://localhost:4200',
            'https://smarttradeind.com',
            'https://admin.smarttradeind.com'
        ];

        $origin = $request->headers->get('Origin');

        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS,PATCH',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Authorization, apikey',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        // Always set origin (important)
        if (in_array($origin, $allowedOrigins)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
        } else {
            $headers['Access-Control-Allow-Origin'] = '*'; // fallback (for debugging)
        }

        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)->withHeaders($headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ForcePutForMidtrans
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info("MASUK KE MIDDLEWARE");
        $request->merge([
            '_method' => 'PUT',
        ]);
        Log::info('Modified Payload with _method PUT:', $request->all());
        return $next($request);
    }
}

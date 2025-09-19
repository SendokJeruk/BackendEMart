<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Collection;
use Illuminate\Pagination\AbstractPaginator;

class CheckEmptyData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // JSON
        if (str_contains($response->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($response->getContent(), true);

            if (isset($data['data'])) {
                $isEmpty = false;

                // empty array
                if (is_array($data['data']) && empty($data['data'])) {
                    $isEmpty = true;
                }

                // paginate
                if (isset($data['data']['data']) && empty($data['data']['data'])) {
                    $isEmpty = true;
                }

                if ($isEmpty) {
                    $data['message'] = 'No Data';
                    unset($data['data']);
                    // $response->setStatusCode(204);
                    $response->setContent(json_encode($data));
                }
            }
        }

        return $response;
    }

}

<?php

namespace App\Http\Middleware;

use App\Models\Company\ConfigMeta;
use Closure;

class FocusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * Set core configuraion key value to company instance 
         */
        try {
            $company = auth()->user()->business;
            $meta = ConfigMeta::where('feature_id', 2)->first();
            config(['core' => $company, 'app.timezone' => $company->zone, 'currency' => $meta->currency]);
        } catch (\Throwable $th) {
            abort(500, 'Something went wrong! Check System Configurations');
        }

        return $next($request);
    }
}

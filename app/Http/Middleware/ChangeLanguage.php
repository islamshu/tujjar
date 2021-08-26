<?php

namespace App\Http\Middleware;

use Closure;

class ChangeLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app()->setLocale('ar');
        $langs = $request->header('lang');
      

        if(isset($langs)  && $langs == 'en' )
    
        
            app()->setLocale('en');
            // dd(app()->getLocale());
          
        return $next($request);
    }
}
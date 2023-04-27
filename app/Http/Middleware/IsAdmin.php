<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     */
    public function handle(Request $request, Closure $next)
    {
//        if ($request->user()->jenis_user == 'staff') {
//            return $next($request);
//        }

        $kategori = $request->user()->ktgkaryawan->toArray();

        $id_admin = [1,2];
        $isAdmin = array_filter($kategori, function ($obj) use ($id_admin) {
            foreach ($id_admin as $value) {
                if ($obj['id'] == $value) {
                    return true;
                }
            }
            return false;
        });

        $isAdmin ? $isAdmin = true : $isAdmin = false;

        if ($isAdmin) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Access denied',
            'admin' => $isAdmin
        ], 403);

    }
}

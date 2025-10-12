<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ຢ່າລືມ import Auth
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. ກວດສອບວ່າຜູ້ໃຊ້ Login ຢູ່ບໍ່
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. ດຶງຂໍ້ມູນຜູ້ໃຊ້ທີ່ກຳລັງ Login ຢູ່
        $user = Auth::user();

        // 3. ວົນ loop ກວດສອບ role ທີ່ໄດ້ຮັບອະນຸຍາດ
        foreach ($roles as $role) {
            // 4. ກວດສອບວ່າ role ຂອງຜູ້ໃຊ້ກົງກັບ role ທີ່ອະນຸຍາດບໍ່
            // ເຮົາໃຊ້ $user->role->name ເພາະເຮົາຈະສ້າງ Relationship ໃນ Model
            if ($user->role->name == $role) {
                // ຖ້າກົງ, ໃຫ້ຜ່ານໄປໜ້າຕໍ່ໄປ
                return $next($request);
            }
        }

        // 5. ຖ້າບໍ່ມີ role ໃດກົງເລີຍ, ໃຫ້ສົ່ງໄປໜ້າ 403 Forbidden
        //abort(403, 'UNAUTHORIZED ACTION.');
        abort(403, __('auth.unauthorized'));
    }
}
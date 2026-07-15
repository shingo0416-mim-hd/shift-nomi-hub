<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LineAdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('line.admin-dashboard', [
            'tenantPath' => $request->attributes->get('tenantPath'),
            'tenant' => $request->attributes->get('tenant'),
            'member' => $request->attributes->get('lineMember'),
            'adminUser' => $request->attributes->get('lineAdminUser'),
            'canManageMembers' => $request->attributes->get('lineMember')?->canManageMembers() === true,
        ]);
    }
}

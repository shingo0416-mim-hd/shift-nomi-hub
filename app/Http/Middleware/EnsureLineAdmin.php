<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureLineAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');
        $lineId = Session::get('line_id');
        $memberId = Session::get('line_member_id');

        if (! $tenant || ! $lineId || ! $memberId) {
            abort(403, 'LINEログイン情報が確認できません。');
        }

        $member = Member::query()
            ->with('user')
            ->where('tenant_id', $tenant->id)
            ->where('line_id', $lineId)
            ->find($memberId);

        $adminUser = $member?->user;
        if (! $member || ! $member->canManageShiftSchedules() || ! $adminUser) {
            abort(403, 'このLINEアカウントには管理画面の権限がありません。');
        }

        $request->attributes->set('lineMember', $member);
        $request->attributes->set('lineAdminUser', $adminUser);
        $request->setUserResolver(fn () => $adminUser);

        return $next($request);
    }
}

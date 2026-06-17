<?php

namespace App\Http\Controllers\Liff;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function show(string $registrationToken): View
    {
        $member = Member::query()
            ->with(Schema::hasTable('line_liff_settings') ? ['tenant.lineLiffSetting', 'store'] : ['tenant', 'store'])
            ->where('registration_token', $registrationToken)
            ->firstOrFail();

        return view('liff.register', [
            'member' => $member,
            'registrationToken' => $registrationToken,
            'liffId' => $member->tenant?->relationLoaded('lineLiffSetting') ? $member->tenant->lineLiffSetting?->liff_id : null,
        ]);
    }
}

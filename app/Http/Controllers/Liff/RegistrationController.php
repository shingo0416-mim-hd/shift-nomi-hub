<?php

namespace App\Http\Controllers\Liff;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function show(string $registrationToken): View
    {
        $member = Member::query()
            ->with('tenant', 'store')
            ->where('registration_token', $registrationToken)
            ->firstOrFail();

        return view('liff.register', [
            'member' => $member,
            'registrationToken' => $registrationToken,
            'liffId' => $member->tenant?->line_liff_id,
        ]);
    }
}

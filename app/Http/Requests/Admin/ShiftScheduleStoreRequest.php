<?php

namespace App\Http\Requests\Admin;

use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ShiftScheduleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lineMember = $this->attributes->get('lineMember');

        if ($lineMember) {
            return $lineMember->canManageShiftSchedules();
        }

        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'store_id' => ['required', Rule::exists('stores', 'id')->where('tenant_id', $tenantId)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'days' => ['nullable', 'array'],
            'days.*.scheduled_on' => ['required_with:days', 'date'],
            'days.*.store_id' => ['nullable', Rule::exists('stores', 'id')->where('tenant_id', $tenantId)],
            'days.*.is_day_off' => ['nullable', 'boolean'],
            'days.*.starts_at' => ['nullable', 'date_format:H:i', 'regex:/^\d{2}:(00|30)$/'],
            'days.*.ends_at' => ['nullable', 'date_format:H:i', 'regex:/^\d{2}:(00|30)$/'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $startsOn = CarbonImmutable::parse($this->input('starts_on'))->startOfDay();
                $endsOn = CarbonImmutable::parse($this->input('ends_on'))->startOfDay();

                if (! $startsOn->isSameMonth($endsOn, true)) {
                    $validator->errors()->add('ends_on', '開始日と終了日は同じ月内で指定してください。');
                }

                $seenDates = [];
                foreach ($this->input('days', []) as $index => $day) {
                    $scheduledOn = CarbonImmutable::parse($day['scheduled_on'])->startOfDay();
                    $date = $scheduledOn->toDateString();

                    if ($scheduledOn->lt($startsOn) || $scheduledOn->gt($endsOn)) {
                        $validator->errors()->add("days.{$index}.scheduled_on", '日別店舗の日付はシフト表の期間内で指定してください。');
                    }

                    if (isset($seenDates[$date])) {
                        $validator->errors()->add("days.{$index}.scheduled_on", '日別店舗の日付が重複しています。');
                    }

                    $isDayOff = filter_var($day['is_day_off'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    if ($isDayOff) {
                        $seenDates[$date] = true;

                        continue;
                    }

                    if (empty($day['store_id'])) {
                        $validator->errors()->add("days.{$index}.store_id", '勤務日は店舗を選択してください。');
                    }

                    if (! empty($day['starts_at']) && empty($day['ends_at'])) {
                        $validator->errors()->add("days.{$index}.ends_at", '開始時刻を指定した場合は終了時刻も指定してください。');
                    }

                    if (empty($day['starts_at']) && ! empty($day['ends_at'])) {
                        $validator->errors()->add("days.{$index}.starts_at", '終了時刻を指定した場合は開始時刻も指定してください。');
                    }

                    if (! empty($day['starts_at']) && ! empty($day['ends_at']) && $day['starts_at'] >= $day['ends_at']) {
                        $validator->errors()->add("days.{$index}.ends_at", '終了時刻は開始時刻より後にしてください。');
                    }

                    $seenDates[$date] = true;
                }

                if ($this->filled('days')) {
                    foreach (CarbonPeriod::create($startsOn, $endsOn) as $date) {
                        if (! isset($seenDates[$date->toDateString()])) {
                            $validator->errors()->add('days', '日別店舗はシフト表の期間内すべての日付を指定してください。');

                            break;
                        }
                    }
                }
            },
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Member;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkforceExchange
{
    public const HEADERS = [
        'hr' => ['member_id', 'name', 'store_id', 'status'],
        'attendance' => ['external_id', 'member_id', 'starts_at', 'ends_at', 'break_minutes'],
        'pos' => ['store_id', 'interval_start', 'customers', 'sales'],
    ];

    public function import(int $tenantId, string $kind, string $path): int
    {
        $stream = fopen($path, 'r');
        try {
            $header = fgetcsv($stream, 0, ',', '"', '');
            if ($header) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }
            if ($header !== (self::HEADERS[$kind] ?? null)) {
                throw ValidationException::withMessages(['file' => 'CSV見出しを指定のテンプレートと一致させてください。']);
            }
            $rows = [];
            $seen = [];
            while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
                if ($values === [null]) {
                    continue;
                }
                if (count($rows) >= 5000 || count($values) !== count($header)) {
                    throw ValidationException::withMessages(['file' => 'CSVは5000行以内で、各行の列数を見出しと一致させてください。']);
                }
                $row = array_combine($header, $values);
                $memberRule = Rule::exists('members', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at');
                $storeRule = Rule::exists('stores', 'id')->where('tenant_id', $tenantId);
                $rules = match ($kind) {
                    'hr' => ['member_id' => ['required', 'integer', $memberRule], 'name' => ['required', 'string', 'max:255'], 'store_id' => ['required', 'integer', $storeRule], 'status' => ['required', Rule::in(['active', 'inactive'])]],
                    'attendance' => ['external_id' => ['required', 'string', 'max:100'], 'member_id' => ['required', 'integer', $memberRule], 'starts_at' => ['required', 'date_format:Y-m-d H:i'], 'ends_at' => ['required', 'date_format:Y-m-d H:i', 'after:starts_at'], 'break_minutes' => ['required', 'integer', 'min:0', 'max:1440']],
                    'pos' => ['store_id' => ['required', 'integer', $storeRule], 'interval_start' => ['required', 'date_format:Y-m-d H:i', 'regex:/:(00|30)$/'], 'customers' => ['required', 'integer', 'min:0', 'max:1000000'], 'sales' => ['required', 'numeric', 'min:0', 'max:999999999999']],
                };
                $validator = Validator::make($row, $rules);
                if ($validator->fails()) {
                    throw ValidationException::withMessages(['file' => (count($rows) + 2).'行目: '.$validator->errors()->first()]);
                }
                if ($kind === 'attendance' && $row['break_minutes'] >= CarbonImmutable::parse($row['starts_at'])->diffInMinutes(CarbonImmutable::parse($row['ends_at']))) {
                    throw ValidationException::withMessages(['file' => '休憩時間は勤務時間より短くしてください。']);
                }
                $key = match ($kind) {
                    'hr' => $row['member_id'], 'attendance' => $row['external_id'], 'pos' => $row['store_id'].'/'.$row['interval_start']
                };
                if (isset($seen[$key])) {
                    throw ValidationException::withMessages(['file' => 'CSV内で識別キーが重複しています。']);
                }
                $seen[$key] = true;
                $rows[] = $row;
            }
            if (! $rows) {
                throw ValidationException::withMessages(['file' => 'データ行がありません。']);
            }
        } finally {
            fclose($stream);
        }
        DB::transaction(function () use ($rows, $tenantId, $kind) {
            foreach ($rows as $row) {
                if ($kind === 'hr') {
                    Member::where('tenant_id', $tenantId)->findOrFail($row['member_id'])->update(['name' => $row['name'], 'display_name' => $row['name'], 'store_id' => $row['store_id'], 'status' => $row['status']]);
                } else {
                    $table = $kind === 'pos' ? 'pos_records' : 'attendance_records';
                    $key = $kind === 'pos' ? ['store_id' => $row['store_id'], 'interval_start' => $row['interval_start']] : ['tenant_id' => $tenantId, 'external_id' => $row['external_id']];
                    DB::table($table)->updateOrInsert($key, $row + ['created_at' => now(), 'updated_at' => now()]);
                }
            }
        });

        return count($rows);
    }

    public function xlsx(array $rows): string
    {
        $escape = fn ($value) => htmlspecialchars(preg_replace('/[^\P{C}\t\r\n]/u', '', (string) $value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $sheet = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($rows as $row) {
            $sheet .= '<row>';
            foreach ($row as $value) {
                $sheet .= '<c t="inlineStr"><is><t xml:space="preserve">'.$escape($value).'</t></is></c>';
            } $sheet .= '</row>';
        }
        $sheet .= '</sheetData></worksheet>';
        $path = tempnam(sys_get_temp_dir(), 'shift-export-');
        try {
            $zip = new \ZipArchive;
            $zip->open($path, \ZipArchive::OVERWRITE);
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="シフト" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
            $zip->close();

            return file_get_contents($path);
        } finally {
            @unlink($path);
        }
    }
}

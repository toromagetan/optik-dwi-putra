<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $values = [];
        foreach (StoreSetting::REGISTRY as $key => $meta) {
            $values[$key] = StoreSetting::get($key);
        }

        return view('admin.settings.index', [
            'values' => $values,
            'groups' => StoreSetting::groups(),
            'registry' => StoreSetting::REGISTRY,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (StoreSetting::REGISTRY as $key => [$label, $type]) {
            $rules[$key] = match ($type) {
                'email' => ['nullable', 'email', 'max:255'],
                'url' => ['nullable', 'string', 'max:2048', 'starts_with:http://,https://'],
                'number' => ['nullable', 'integer', 'min:1', 'max:100'],
                default => ['nullable', 'string', 'max:2000'],
            };
        }
        $rules['logo_file'] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'];

        $data = $request->validate($rules);
        $changed = [];

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $name = 'logo-'.Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs('settings', $name, 'public');

            $old = StoreSetting::get('logo_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }

            StoreSetting::set('logo_path', $path);
            $changed[] = 'logo_path';
        }

        foreach (StoreSetting::REGISTRY as $key => $meta) {
            if ($key === 'logo_path' || ! array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key] !== null ? trim((string) $data[$key]) : null;
            if ($key === 'whatsapp_number') {
                $value = $value ? preg_replace('/\D+/', '', $value) : null;
            }
            if (StoreSetting::get($key) !== $value) {
                StoreSetting::set($key, $value);
                $changed[] = $key;
            }
        }

        if ($changed !== []) {
            $anchor = StoreSetting::query()->firstOrCreate(['setting_key' => 'store_name']);
            AuditLog::record('settings.updated', $anchor, ['keys' => $changed]);
        }

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}

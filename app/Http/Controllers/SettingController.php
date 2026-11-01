<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::where('tenant_id', auth()->user()->tenant_id)->pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tax_active' => 'sometimes|boolean',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            ...$this->pointRules(),
        ]);
        $data['tax_active'] = $request->boolean('tax_active') ? '1' : '0';
        if (isset($data['point_mode'])) {
            Gate::authorize('feature-loyalty');
            $data = $this->normalizePoints($request, $data);
        }
        $this->save($request, $data);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function updatePoints(Request $request)
    {
        Gate::authorize('feature-loyalty');
        $data = $request->validate(['point_mode' => 'required|in:disabled,per_investment,flat,percentage', ...array_diff_key($this->pointRules(), ['point_mode' => true])]);
        $this->save($request, $this->normalizePoints($request, $data));

        return back()->with('success', 'Pengaturan poin berhasil diperbarui.');
    }

    private function pointRules(): array
    {
        return [
            'point_mode' => 'sometimes|in:disabled,per_investment,flat,percentage',
            'point_rule_value' => 'nullable|numeric|min:0|max:1000000000',
            'point_redemption_points' => 'sometimes|integer|min:1|max:100000000',
            'point_redemption_value' => 'sometimes|integer|min:1|max:999999999',
        ];
    }

    private function normalizePoints(Request $request, array $data): array
    {
        $data['point_rule_value'] = $data['point_mode'] === 'disabled' ? 0 : ($data['point_rule_value'] ?? 0);
        $data['point_member_only'] = '1';

        return $data;
    }

    private function save(Request $request, array $data): void
    {
        DB::transaction(function () use ($request, $data) {
            foreach ($data as $key => $value) {
                Setting::updateOrCreate(['tenant_id' => $request->user()->tenant_id, 'key' => $key], ['value' => $value]);
            }
        });
    }
}

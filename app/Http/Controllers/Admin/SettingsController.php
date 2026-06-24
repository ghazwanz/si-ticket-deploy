<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $platformFeePercent = SystemSetting::get('platform_fee_percent', 5.00);

        return view('admin.settings.index', compact('user', 'platformFeePercent'));
    }

    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $oldValue = SystemSetting::get('platform_fee_percent', '5.00');
        $newValue = number_format((float) $validated['platform_fee_percent'], 2, '.', '');

        SystemSetting::set('platform_fee_percent', $newValue);

        return back()->with('success', 'Konfigurasi biaya platform berhasil diperbarui.');
    }
}

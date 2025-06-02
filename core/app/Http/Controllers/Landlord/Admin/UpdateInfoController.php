<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateInfo;

class UpdateInfoController extends Controller
{
    public function mark_update_info()
    {
        $info = UpdateInfo::whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'status' => (bool) $info,
        ]);
    }
}

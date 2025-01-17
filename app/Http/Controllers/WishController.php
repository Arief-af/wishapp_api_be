<?php
namespace App\Http\Controllers;

use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishController extends Controller
{
    public function store(Request $request)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'nominal_target' => 'required|numeric|min:0',
        'start_date' => 'required|date|before_or_equal:target_date',
        'target_date' => 'required|date|after_or_equal:start_date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    // Hitung jumlah hari antara start_date dan target_date
    $startDate = \Carbon\Carbon::parse($request->start_date);
    $targetDate = \Carbon\Carbon::parse($request->target_date);
    $days = $startDate->diffInDays($targetDate) + 1; // Tambahkan 1 agar inklusif

    // Hitung spend_per_day
    $spendPerDay = $days > 0 ? $request->nominal_target / $days : 0;

    // Simpan wish
    $wish = Wish::create([
        'user_id' => Auth::id(),
        'title' => $request->title,
        'nominal_target' => $request->nominal_target,
        'spend_per_day' => round($spendPerDay, 2), // Dibulatkan ke 2 desimal
        'start_date' => $request->start_date,
        'target_date' => $request->target_date,
    ]);

    return response()->json([
        'message' => 'Wish created successfully',
        'wish' => $wish
    ], 201);
}

}

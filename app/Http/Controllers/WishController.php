<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishController extends Controller
{
    public function index()
    {
        $wishes = Wish::where('user_id', Auth::id())->get();
        return response()->json([
            'wishes' => $wishes
        ], 200);
    }

    public function update($id, Request $request)
    {
        // Find the wish by ID
        $wish = Wish::find($id);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'nominal_target' => 'sometimes|required|numeric|min:0',
            'start_date' => 'sometimes|required|date|before_or_equal:target_date',
            'target_date' => 'sometimes|required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Update the wish with the provided data
        $wish->update($request->all());

        // Recalculate spend_per_day if start_date or target_date has been updated
        if ($request->has('start_date') || $request->has('target_date') || $request->has('nominal_target')) {
            // Calculate days between start_date and target_date
            $startDate = \Carbon\Carbon::parse($wish->start_date);
            $targetDate = \Carbon\Carbon::parse($wish->target_date);
            $days = $startDate->diffInDays($targetDate) + 1; // Add 1 to be inclusive

            // Calculate spend_per_day
            $spendPerDay = $days > 0 ? $wish->nominal_target / $days : 0;
            $wish->spend_per_day = round($spendPerDay, 2); // Round to 2 decimal places

            // Save the updated wish
            $wish->save();
        }

        return response()->json([
            'message' => 'Wish updated successfully',
            'wish' => $wish
        ], 200);
    }

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

    public function destroy($id)
    {
        $wish = Wish::find($id);
        $wish->delete();
        return response()->json([
            'message' => 'Wish deleted successfully'
        ], 200);
    }

    public function show($id)
    {
        $wish = Wish::find($id);

        if (!$wish) {
            return response()->json([
                'message' => 'Wish not found'
            ], 404);
        }

        // Hitung persentase
        $percentage = $wish->nominal_target > 0
            ? min(100, ($wish->balance / $wish->nominal_target) * 100)
            : 0;

        // Tambahkan persentase ke dalam array wish
        $wishArray = $wish->toArray();
        $wishArray['percentage'] = round($percentage, 2); // Dibulatkan ke 2 desimal

        return response()->json([
            'wish' => $wishArray
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    public function index($id)
    {
        $withdraw = Withdraw::where('wish_id', $id)->get();
        return response()->json([
            'withdraw' => $withdraw
        ], 200);
    }

    public function store(Request $request, $id)
    {
        $data = $request->all();
    
        // Ambil data pengguna yang terkait
        $user = auth()->user(); // Pastikan menggunakan auth untuk mendapatkan user yang sedang login
    
        // Ambil relasi balance dari user
        $balance = $user->balance;
    
        // Ambil data wish berdasarkan ID
        $wish = Wish::find($id);
        if (!$wish) {
            return response()->json([
                'message' => 'Wish not found.'
            ], 404);
        }
    
        // Cek apakah target saving sudah tercapai
        if ($request->nominal > $wish->balance) {
            return response()->json([
                'message' => 'Saldo tidak cukup.',
                'wish' => $wish
            ], 400);
        } else {
            // Update balance dan wish
            $balance->nominal += $request->nominal;
            $wish->balance -= $request->nominal;
    
            // Simpan perubahan pada wish dan balance
            $balance->save();
            $wish->save();
    
            // Create a withdraw record with the correct wish_id
            $withdraw = Withdraw::create([
                'wish_id' => $wish->id, // Corrected to use wish's id
                'nominal' => $request->nominal,
                'description' => $request->description,
                'date' => now()->toDateString() // More robust date handling
            ]);
    
            // Check if the target balance is reached
            if ($wish->balance >= $wish->nominal_target) {
                $wish->status = 'success';
                $wish->save();
    
                return response()->json([
                    'message' => 'Target saving telah tercapai! Tidak dapat menyimpan data baru.',
                    'wish' => $wish
                ], 400);
            } else {
                $wish->status = 'on_going';
                $wish->save();
            }
        }
    
        return response()->json([
            'balance' => $balance,
            'withdraw' => $withdraw,
            'wish' => $wish
        ], 200);
    }
    
}

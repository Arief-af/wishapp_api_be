<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Wish;
use Illuminate\Http\Request;

class SavingBalance extends Controller
{
    public function index($id)
    {
        $wishes = Saving::where('wish_id', $id)->get();
        return response()->json([
            'savings' => $wishes
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

        // Cek apakah target saving sudah tercapai
        if ($wish->balance >= $wish->nominal_target) {
            $wish->status= 'success';
            $wish->save();
            return response()->json([
                'message' => 'Target saving telah tercapai! Tidak dapat menyimpan data baru.',
                'wish' => $wish
            ], 400);
        }

        // Hitung sisa target yang belum tercapai
        $remainingTarget = $wish->nominal_target - $wish->balance;

        // Jika nominal melebihi sisa target, hanya gunakan sisa target
        $nominalToSave = min($data['nominal'], $remainingTarget);

        // Cek apakah saldo cukup untuk nominal yang akan disimpan
        if ($balance->nominal < $nominalToSave) {
            return response()->json([
                'message' => 'Saldo utama Anda tidak mencukupi untuk melakukan saving.'
            ], 400);
        }

        // Kurangi saldo pengguna dengan nominal yang akan disimpan
        $balance->nominal -= $nominalToSave;
        $balance->save(); // Simpan perubahan pada tabel balance

        // Tambahkan nominal ke balance wish
        $wish->balance += $nominalToSave;
        // Simpan data saving hanya jika target belum tercapai
        $saving = Saving::create([
            'wish_id' => $id,
            'nominal' => $nominalToSave, // Gunakan nominal yang sudah disesuaikan
            'date' => $data['date']
        ]);

        // Simpan perubahan pada wish
        $wish->save();

        // Cek apakah balance pada wish telah mencapai atau melebihi nominal_target
        if ($wish->balance >= $wish->nominal_target) {
            $wish->status = 'success'; // Ubah status menjadi success
            $wish->save(); // Simpan perubahan
            return response()->json([
                'message' => 'Target saving telah tercapai!',
                'wish' => $wish
            ], 200);
        }

        return response()->json([
            'balance' => $balance,
            'saving' => $saving,
            'wish' => $wish
        ], 200);
    }
}

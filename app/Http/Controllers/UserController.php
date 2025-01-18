<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Str;
use App\Models\Saving;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Fetching Saving records where the associated wish belongs to the authenticated user
        $wish = Saving::with('wish')
            ->whereHas('wish', function ($query) {
                $query->where('user_id', auth()->id()); // Filter wishes by authenticated user
            })
            ->get();

        // Fetching Withdraw records where the associated wish belongs to the authenticated user
        $withdraw = Withdraw::with('wish')
            ->whereHas('wish', function ($query) {
                $query->where('user_id', auth()->id()); // Filter wishes by authenticated user
            })
            ->get();

        // Merging the collections of Saving and Withdraw records
        $wish_withdraw = $wish->merge($withdraw);

        // Sorting the merged collection by 'date' in descending order
        $sorted = $wish_withdraw->sortByDesc(function ($item) {
            return $item->date; // Assuming 'date' is the correct field name
        })->values();

        // Returning the sorted data in a JSON response
        return response()->json([
            'data' => $sorted
        ], 200);
    }

    public function update(Request $request)
    {
        $id = auth()->id();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,  // Ensures the email is unique, excluding the current user's email
            'password' => 'nullable|confirmed|min:8',  // Only validates password if it is provided
            'ibu_kandung' => 'required',
            'nik' => 'required|numeric|unique:users,nik,' . $id,  // Ensures the NIK is unique, excluding the current user's NIK
        ]);

        $user = auth()->user();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->ibu_kandung = $validated['ibu_kandung'];
        $user->nik = $validated['nik'];

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return response()->json([
            'user' => $user
        ], 200);
    }


    

    public function recovery(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Retrieve the user based on the provided email
        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Generate a temporary password
            $temporaryPassword = Str::random(8); // You can set the desired length of the temporary password

            // Update the user's password with the temporary password
            $user->password = bcrypt($temporaryPassword);
            $user->save();

            // Prepare the email content
            $subject = "Hai, {$user->name}, data akun kamu telah diperbarui!";
            $message = "Halo {$user->name},\n\nKami telah menerima permintaan untuk memulihkan kata sandi akun kamu.\n\n" .
                "Kata sandi sementara kamu adalah: {$temporaryPassword}\n\n" .
                "Silakan gunakan kata sandi tersebut untuk login dan segera ubah kata sandi kamu setelah berhasil masuk.\n\n" .
                "Jika kamu tidak meminta pemulihan kata sandi, silakan abaikan email ini.\n\n" .
                "Terima kasih,\nTim Kami";

            // Send the email using Mail::raw for a simple plain-text email
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                    ->subject($subject);
            });

            return response()->json([
                'message' => 'Password recovery email sent successfully!',
            ], 200);
        }

        return response()->json([
            'error' => 'User not found.',
        ], 404);
    }
}

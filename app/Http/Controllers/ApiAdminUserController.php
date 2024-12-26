<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ApiAdminUserController extends Controller
{
    //
    public function apiRegisterUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Menghasilkan token setelah registrasi
        $token = $user->createToken('user')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token
        ], 201);
    }


    public function apiUserLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cek kredensial admin
        $user = User::where('email', $request->email)->first();

        // Validasi apakah user ada dan password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Menghasilkan token API menggunakan Laravel Sanctum
        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ], 200);
    }

    public function apiUserRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Menghasilkan token setelah registrasi
        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token
        ], 201);
    }

    // user api
    public function addToCart(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Mengambil keranjang dari cache (menggunakan Cache karena session kurang ideal di API)
        $cart = Cache::get('cart_' . $request->user()->id, []);

        // Mengecek apakah produk sudah ada di dalam keranjang
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $product->id) {
                $item['quantity'] += 1;  // Menambah jumlah produk
                $found = true;
                break;
            }
        }

        // Jika produk belum ada di keranjang, tambahkan produk baru
        if (!$found) {
            $cart[] = [
                'id' => $product->id,
                'name' => $product->name_product,
                'price' => $product->product_price,
                'quantity' => 1,
                'image' => json_decode($product->product_photo)[0]
            ];
        }

        // Menyimpan keranjang yang sudah diperbarui ke cache
        Cache::put('cart_' . $request->user()->id, $cart, now()->addMinutes(30)); // Set expired time 30 minutes

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang!',
            'cart' => $cart
        ], 200);
    }

    // Fungsi untuk menampilkan keranjang
    public function showCart(Request $request)
    {
        // Mengambil keranjang dari cache
        $cart = Cache::get('cart_' . $request->user()->id, []);

        return response()->json([
            'cart' => $cart
        ], 200);
    }

    // Fungsi untuk mengubah jumlah produk di keranjang
    public function updateQuantity(Request $request, $id)
    {
        // Mendapatkan keranjang dari cache
        $cart = Cache::get('cart_' . $request->user()->id, []);

        // Loop untuk mencari produk yang dimaksud
        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                $item['quantity'] = $request->quantity;  // Update jumlah produk
                break;
            }
        }

        // Menyimpan keranjang yang sudah diperbarui ke cache
        Cache::put('cart_' . $request->user()->id, $cart, now()->addMinutes(30));

        return response()->json([
            'message' => 'Jumlah produk berhasil diperbarui!',
            'cart' => $cart
        ], 200);
    }

    // Fungsi untuk menghapus produk dari keranjang
    public function removeFromCart(Request $request, $id)
    {
        // Mengambil keranjang dari cache
        $cart = Cache::get('cart_' . $request->user()->id, []);

        // Filter produk yang ingin dihapus
        $cart = array_filter($cart, function ($item) use ($id) {
            return $item['id'] != $id;
        });

        // Menyimpan keranjang yang sudah diperbarui ke cache
        Cache::put('cart_' . $request->user()->id, array_values($cart), now()->addMinutes(30));

        return response()->json([
            'message' => 'Produk berhasil dihapus dari keranjang!',
            'cart' => $cart
        ], 200);
    }

    // Fungsi untuk proses checkout (mengembalikan isi keranjang)
    public function checkout(Request $request)
    {
        // Mengambil keranjang dari cache
        $cart = Cache::get('cart_' . $request->user()->id, []);

        return response()->json([
            'cart' => $cart,
            'message' => 'Proses checkout berhasil!',
        ], 200);
    }

    // Fungsi untuk menghapus semua produk di keranjang
    public function clearCart(Request $request)
    {
        Cache::forget('cart_' . $request->user()->id);

        return response()->json([
            'message' => 'Keranjang telah dikosongkan!'
        ], 200);
    }
}

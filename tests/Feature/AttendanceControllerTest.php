<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\User; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AttendanceControllerTest extends TestCase
{
    // FITUR 1: RefreshDatabase
    // Ini sangat penting. Setiap kali satu fungsi test (misal: test_store_employee_success) selesai,
    // Laravel akan MENGHAPUS SEMUA DATA di database testing.
    // Jadi, database selalu bersih seperti baru setiap kali tes jalan.
    use RefreshDatabase;
    
    // FITUR 2: WithFaker
    // Alat bantu untuk membuat data palsu (nama acak, alamat, dll) jika diperlukan.
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    // --- TEST CASE 1: SKENARIO GAGAL (NEGATIVE TEST) ---
    // Tujuannya: Memastikan aplikasi MENOLAK data yang tidak lengkap.
    public function test_store_employee_validation_fails()
    {
        // 1. Buat user "pura-pura"
        $user = User::factory()->create();

        // 2. Siapkan data yang SENGAJA SALAH
        // Nama kosong (padahal wajib), Umur 15 (padahal minimal 17)
        $invalidData = [
            'name' => '',
            'age' => 15,
        ];

        // 3. Kirim data ke endpoint API
        // actingAs($user): Login sebagai user tadi
        // postJson: Kirim data via POST dengan format JSON
        $response = $this->actingAs($user)->postJson('/api/employees', $invalidData);

        // 4. Verifikasi Hasil (Assert)
        // Harapannya: Server membalas dengan kode 422 (Unprocessable Entity / Error Validasi)
        $response->assertStatus(422);
        
        // Harapannya: JSON balasan berisi pesan error untuk field 'name' dan 'age'
        $response->assertJsonValidationErrors(['name', 'age']);
    }

    // --- TEST CASE 2: SKENARIO SUKSES (POSITIVE TEST) ---
    // Tujuannya: Memastikan aplikasi MENERIMA & MENYIMPAN data yang benar.
    public function test_store_employee_success()
    {
        // 1. Buat user login
        $user = User::factory()->create();

        // 2. Siapkan nama unik
        // Kita pakai angka acak (rand) agar namanya unik (misal: "Budi Santoso 4821")
        // Ini mencegah error "Duplicate Entry" jika tes dijalankan berkali-kali.
        $uniqueName = 'Budi Santoso ' . rand(1000, 9999);

        // 3. Siapkan data LENGKAP & VALID
        $validData = [
            'name' => $uniqueName,
            'age' => 30,
            'gender' => 'Laki-laki',
            'position' => 'Staff IT',
            'join_date' => '2025-10-01',
        ];

        // 4. Kirim data
        $response = $this->actingAs($user)->postJson('/api/employees', $validData);

        // 5. Verifikasi Hasil
        // Harapannya: Status 201 (Created / Berhasil Dibuat)
        $response->assertStatus(201);
        
        // Harapannya: Ada pesan sukses di balasan JSON
        $response->assertJson([
            'message' => 'Karyawan berhasil ditambahkan!',
        ]);

        // 6. Cek Database (Verifikasi Akhir)
        // Pastikan data benar-benar masuk ke tabel 'employees'
        $this->assertDatabaseHas('employees', [
            'name' => $uniqueName,
            'position' => 'Staff IT'
        ]);
    }
}
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=Admin}';

    protected $description = 'Buat satu admin melalui prompt password tersembunyi';

    public function handle(): int
    {
        if (User::exists()) {
            $this->error('Admin sudah tersedia. Tidak dapat membuat akun kedua.');

            return self::FAILURE;
        }
        $data = ['name' => $this->option('name'), 'email' => trim($this->argument('email')),
            'password' => $this->secret('Password admin (minimal 12 karakter)'),
            'password_confirmation' => $this->secret('Ulangi password')];
        $validator = Validator::make($data, ['name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'password' => 'required|string|min:12|confirmed']);
        if ($validator->fails()) {
            $this->error('Nama/email tidak valid atau password kurang dari 12 karakter/tidak cocok.');

            return self::FAILURE;
        }
        try {
            User::create(collect($data)->only('name', 'email', 'password')->all());
        } catch (QueryException $e) {
            if ($e->getCode() !== '23505') {
                throw $e;
            }
            $this->error('Admin sudah dibuat oleh proses lain.');

            return self::FAILURE;
        }
        $this->info('Admin berhasil dibuat.');

        return self::SUCCESS;
    }
}

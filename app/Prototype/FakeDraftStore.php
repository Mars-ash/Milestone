<?php

namespace App\Prototype;

use Illuminate\Support\Str;

class FakeDraftStore
{
    /**
     * Ambil seluruh draft tersimpan dari session adapter FakeStore.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return FakeStore::getDrafts();
    }

    /**
     * Cari draft berdasarkan UUID unik.
     *
     * @param string $uuid
     * @return array<string, mixed>|null
     */
    public static function find(string $uuid): ?array
    {
        return FakeStore::findDraft($uuid);
    }

    /**
     * Simpan atau perbarui draft ke dalam store.
     * PRD D11: Draft tidak mempengaruhi data tersimpan dan tidak dihitung Open/Closed.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function save(array $payload): array
    {
        return FakeStore::saveDraft($payload);
    }

    /**
     * Hapus draft berdasarkan UUID.
     *
     * @param string $uuid
     * @return bool
     */
    public static function delete(string $uuid): bool
    {
        return FakeStore::deleteDraft($uuid);
    }

    /**
     * Buang draft (discard) oleh aksi pengguna.
     *
     * @param string $uuid
     * @return bool
     */
    public static function discard(string $uuid): bool
    {
        return static::delete($uuid);
    }

    /**
     * Cek apakah draft payload benar-benar kosong.
     * AT14: Form baru dibuka tidak boleh membuat draft kosong.
     *
     * @param array<string, mixed> $payload
     * @return bool
     */
    public static function isEmptyDraft(array $payload): bool
    {
        $hasProject = ! empty(trim($payload['project_id'] ?? ''));
        $hasNotes = ! empty(trim($payload['notes'] ?? ''));
        $hasPic = ! empty(trim($payload['pic_progress'] ?? ''));
        $hasPicCust = ! empty(trim($payload['pic_customer'] ?? ''));
        $hasMachine = ! empty(trim($payload['machine'] ?? ''));
        $hasDh = ! empty(trim($payload['die_high_raw'] ?? ''));
        $hasProblems = ! empty($payload['problems']);
        $hasActions = ! empty($payload['next_actions']);
        $hasFeedbacks = ! empty($payload['feedbacks']);

        return ! ($hasProject || $hasNotes || $hasPic || $hasPicCust || $hasMachine || $hasDh || $hasProblems || $hasActions || $hasFeedbacks);
    }

    /**
     * Hitung total draft aktif saat ini.
     *
     * @return int
     */
    public static function count(): int
    {
        return count(static::all());
    }
}

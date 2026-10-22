<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Console\Command;

class CleanupStaging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup-staging
                            {--dry-run : Menampilkan daftar berkas tanpa menghapusnya}
                            {--hours=24 : Batas usia berkas staging kedaluwarsa dalam jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan berkas staging kedaluwarsa atau sisa draft yang dibuang';

    /**
     * Execute the console command.
     */
    public function handle(AttachmentService $attachmentService): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        $this->info("Memeriksa berkas staging yang lebih tua dari {$hours} jam atau yatim (orphan)...");

        // Cari lampiran staging kedaluwarsa atau yang draftnya sudah applied / tidak ada
        $query = Attachment::where('state', 'staging')
            ->where(function ($q) use ($threshold) {
                $q->where('created_at', '<', $threshold)
                    ->orWhereDoesntHave('draft')
                    ->orWhereHas('draft', fn ($dq) => $dq->where('state', 'applied'));
            });

        $staged = $query->get();
        $count = $staged->count();

        if ($count === 0) {
            $this->info('Tidak ada berkas staging kedaluwarsa yang ditemukan.');

            return Command::SUCCESS;
        }

        $this->line("Ditemukan {$count} berkas staging.");

        $disk = $attachmentService->getDisk();
        $deletedCount = 0;

        foreach ($staged as $att) {
            if ($isDryRun) {
                $this->line("[DRY-RUN] Akan menghapus staging: {$att->id} ({$att->storage_key})");
                $deletedCount++;
            } else {
                try {
                    if ($disk->exists($att->storage_key)) {
                        $disk->delete($att->storage_key);
                    }
                    $att->delete();
                    $deletedCount++;
                    $this->line("Dihapus: {$att->id} ({$att->storage_key})");
                } catch (\Throwable $e) {
                    $this->error("Gagal menghapus {$att->id}: ".$e->getMessage());
                }
            }
        }

        if ($isDryRun) {
            $this->info("[DRY-RUN Selesai] Total {$deletedCount} berkas disimulasikan untuk dihapus.");
        } else {
            $this->info("[Selesai] Total {$deletedCount} berkas staging berhasil dibersihkan.");
        }

        return Command::SUCCESS;
    }
}

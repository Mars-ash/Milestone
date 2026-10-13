<div class="space-y-6">
    @if(!$project)
        <div class="bg-white p-8 rounded-lg border border-[#D8DEE6] text-center">
            <h3 class="text-base font-bold text-[#18212B]">Project Tidak Ditemukan</h3>
            <p class="text-xs text-[#536170] mt-1">Data master project dengan ID tersebut tidak ada di sistem.</p>
            <div class="mt-4">
                <a href="?tab=projects" class="px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white">
                    Kembali ke Daftar Project
                </a>
            </div>
        </div>
    @else
        <!-- Header Detail Master Project -->
        <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#2459A6] bg-[#F0F4FA] px-2.5 py-0.5 rounded">
                            Master Project (D01: 1 JO = 1 Part No)
                        </span>
                        <span class="text-xs text-[#536170]">ID: {{ $project['id'] }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-[#18212B] mt-1.5 flex items-baseline gap-2">
                        <span>{{ $project['part_name'] }}</span>
                        <span class="text-sm font-mono text-[#536170]">({{ $project['part_no'] }})</span>
                    </h1>
                    <div class="text-xs sm:text-sm text-[#536170] mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><strong>JO No:</strong> <span class="text-[#2459A6] font-bold">{{ $project['jo_no'] }}</span></span>
                        <span><strong>Customer:</strong> {{ $project['customer'] ?: '-' }}</span>
                        <span><strong>Model:</strong> {{ $project['model'] ?: '-' }}</span>
                        <span><strong>No Doc:</strong> {{ $project['no_doc'] ?: '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a
                        href="?tab=projects"
                        class="px-3.5 py-2 text-xs font-semibold rounded-md border border-[#D8DEE6] bg-white text-[#536170] hover:bg-[#F5F6F8] transition-colors min-h-[44px] flex items-center"
                    >
                        &larr; Kembali
                    </a>
                    <a
                        href="?tab=projects&edit={{ $project['id'] }}"
                        class="px-4 py-2 text-xs font-semibold rounded-md bg-[#2459A6] text-white hover:bg-[#1D4785] transition-colors shadow-2xs min-h-[44px] flex items-center gap-1.5"
                        data-testid="edit-project-btn"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit Master
                    </a>
                </div>
            </div>
        </div>

        <!-- Dua Kolom: Spesifikasi Parameter & Jadwal Master -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kartu 1: Parameter & Spesifikasi Tooling (D15 Raw Text) -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6] pb-1 border-b border-[#E8ECF0]">
                    Parameter Material & Tooling (D15 Raw Text)
                </h3>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-xs">
                    <div>
                        <dt class="text-[#8C9BAE]">Material</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['material'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Spec</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['spec'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Material Thickness</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['material_thickness_raw'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Material Width</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['material_width_raw'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Process Master</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['process'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Die High (DH)</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['dh'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Size</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['size'] ?? null) ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Qty</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">{{ ($project['qty'] ?? null) ?: '-' }}</dd>
                    </div>
                </dl>
                @if(!empty($project['remark']))
                    <div class="pt-2 border-t border-[#F0F2F5] text-xs">
                        <span class="text-[#8C9BAE] block">Remark:</span>
                        <p class="text-[#18212B] mt-0.5 italic">{{ $project['remark'] }}</p>
                    </div>
                @endif
            </div>

            <!-- Kartu 2: Jadwal & Milestone Master Manual (FR03, AT04, D14) -->
            <div class="bg-white p-5 rounded-lg border border-[#D8DEE6] shadow-2xs space-y-3">
                <div class="flex items-center justify-between pb-1 border-b border-[#E8ECF0]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2459A6]">
                        Jadwal & Target Master (Manual)
                    </h3>
                    <span class="text-[11px] text-[#536170]">Sent Sample tidak trigger otomatis</span>
                </div>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-xs">
                    <div class="bg-[#F8FAFC] p-2.5 rounded border border-[#E8ECF0]">
                        <dt class="text-[#8C9BAE] font-medium">T0 Target Date</dt>
                        <dd class="font-bold text-[#18212B] mt-0.5" data-testid="detail-t0-target">
                            {{ !empty($project['t0_target']) ? \Illuminate\Support\Carbon::parse($project['t0_target'])->format('d M Y') : (!empty($project['t0_target_date']) ? \Illuminate\Support\Carbon::parse($project['t0_target_date'])->format('d M Y') : 'Belum diisi') }}
                        </dd>
                    </div>
                    <div class="bg-[#F8FAFC] p-2.5 rounded border border-[#E8ECF0]">
                        <dt class="text-[#8C9BAE] font-medium">T0 Actual Date</dt>
                        <dd class="font-bold text-[#18212B] mt-0.5" data-testid="detail-t0-actual">
                            {{ !empty($project['t0_actual']) ? \Illuminate\Support\Carbon::parse($project['t0_actual'])->format('d M Y') : (!empty($project['t0_actual_date']) ? \Illuminate\Support\Carbon::parse($project['t0_actual_date'])->format('d M Y') : 'Belum diisi') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Event 1 (PP1)</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">
                            {{ !empty($project['event_1']) ? \Illuminate\Support\Carbon::parse($project['event_1'])->format('d M Y') : (!empty($project['event_1_date']) ? \Illuminate\Support\Carbon::parse($project['event_1_date'])->format('d M Y') : '-') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Event 2 (PP2)</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">
                            {{ !empty($project['event_2']) ? \Illuminate\Support\Carbon::parse($project['event_2'])->format('d M Y') : (!empty($project['event_2_date']) ? \Illuminate\Support\Carbon::parse($project['event_2_date'])->format('d M Y') : '-') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Event 3 (MP)</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">
                            {{ !empty($project['event_3']) ? \Illuminate\Support\Carbon::parse($project['event_3'])->format('d M Y') : (!empty($project['event_3_date']) ? \Illuminate\Support\Carbon::parse($project['event_3_date'])->format('d M Y') : '-') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[#8C9BAE]">Date Receive</dt>
                        <dd class="font-medium text-[#18212B] mt-0.5">
                            {{ !empty($project['date_receive']) ? \Illuminate\Support\Carbon::parse($project['date_receive'])->format('d M Y') : '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Riwayat Progress Terkait Project Ini (FR02, AT03: Mengikuti Master Terkini) -->
        <div class="bg-white rounded-lg border border-[#D8DEE6] overflow-hidden shadow-2xs">
            <div class="px-5 py-4 border-b border-[#D8DEE6] flex items-center justify-between bg-white">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-[#18212B]">
                        Riwayat Kegiatan / Progress Terkait
                    </h3>
                    <p class="text-xs text-[#536170] mt-0.5">
                        Identitas progress selalu merujuk dinamis ke master terkini (FR02, AT03).
                    </p>
                </div>
                <span class="text-xs font-semibold bg-[#EBF2FA] text-[#2459A6] px-2.5 py-1 rounded">
                    {{ count($progresses) }} Kegiatan
                </span>
            </div>

            @if(count($progresses) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm text-[#18212B]">
                        <thead class="bg-[#F5F6F8] border-b border-[#D8DEE6] text-xs font-semibold text-[#536170]">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Activity</th>
                                <th class="px-4 py-3">PIC Progress</th>
                                <th class="px-4 py-3">Problem & Tindakan</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D8DEE6] bg-white">
                            @foreach($progresses as $p)
                                <tr class="hover:bg-[#F9FAFC] transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ !empty($p['activity_date']) ? \Illuminate\Support\Carbon::parse($p['activity_date'])->format('d M Y') : 'Tanggal belum tersedia' }}
                                        <div class="text-[11px] text-[#536170]">{{ $p['id'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <x-ui.badge :variant="match($p['activity']) {
                                            'Trial' => 'primary',
                                            'Sent Sample' => 'info',
                                            'Send Document' => 'warning',
                                            'Audit' => 'success',
                                            default => 'neutral',
                                        }">
                                            {{ $p['activity'] }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ ($p['pic_progress'] ?? null) ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!empty($p['problems']))
                                            <span class="text-xs font-semibold text-[#C5221F]">
                                                {{ count($p['problems']) }} Problem
                                            </span>
                                        @else
                                            <span class="text-xs text-[#8C9BAE]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if(strtolower($p['status']) === 'open')
                                            <span class="text-xs font-bold text-[#0E6B38] bg-[#EAF7EE] px-2 py-0.5 rounded">Open</span>
                                        @else
                                            <span class="text-xs font-medium text-[#536170] bg-[#F0F2F5] px-2 py-0.5 rounded">Closed</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a
                                            href="?tab=progresses&id={{ $p['id'] }}"
                                            class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded border border-[#D8DEE6] text-[#2459A6] hover:bg-[#F0F4FA]"
                                        >
                                            Buka Progress
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-xs text-[#536170]">
                    Belum ada progress kegiatan yang dicatat untuk master project ini.
                </div>
            @endif
        </div>
    @endif
</div>

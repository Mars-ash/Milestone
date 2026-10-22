@props([
    'project' => null,
])

<div class="bg-[#F8FAFC] border border-[#D8DEE6] rounded-md p-3.5 sm:p-4 text-xs" data-testid="progress-identity-panel">
    <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-[#E8ECF0]">
        <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-[#2459A6]">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Identitas Terkini (Hanya Baca dari Master)
        </div>
        @if($project)
            <span class="text-[11px] font-semibold text-[#0E6B38] bg-[#EAF7EE] px-2 py-0.5 rounded">
                JO Terverifikasi
            </span>
        @else
            <span class="text-[11px] text-[#8C9BAE] italic">
                Pilih JO No terlebih dahulu
            </span>
        @endif
    </div>

    @if($project)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- 1. Part No -->
            <div class="bg-white p-2.5 rounded border border-[#E8ECF0]">
                <span class="text-[11px] text-[#8C9BAE] block font-medium">1. Part No</span>
                <span class="font-mono font-bold text-[#18212B] text-xs sm:text-sm mt-0.5 block" data-testid="identity-part-no">
                    {{ $project['part_no'] ?? '-' }}
                </span>
            </div>

            <!-- 2. Part Name -->
            <div class="bg-white p-2.5 rounded border border-[#E8ECF0] sm:col-span-1 md:col-span-2">
                <span class="text-[11px] text-[#8C9BAE] block font-medium">2. Part Name</span>
                <span class="font-bold text-[#18212B] text-xs sm:text-sm mt-0.5 block truncate" data-testid="identity-part-name" title="{{ $project['part_name'] ?? '' }}">
                    {{ $project['part_name'] ?? '-' }}
                </span>
            </div>

            <!-- 3. Customer -->
            <div class="bg-white p-2.5 rounded border border-[#E8ECF0]">
                <span class="text-[11px] text-[#8C9BAE] block font-medium">3. Customer</span>
                <span class="font-medium text-[#18212B] text-xs mt-0.5 block truncate" data-testid="identity-customer">
                    {{ $project['customer'] ?? '-' }}
                </span>
            </div>

            <!-- 4. Model -->
            <div class="bg-white p-2.5 rounded border border-[#E8ECF0]">
                <span class="text-[11px] text-[#8C9BAE] block font-medium">4. Model</span>
                <span class="font-medium text-[#18212B] text-xs mt-0.5 block truncate" data-testid="identity-model">
                    {{ $project['model'] ?? '-' }}
                </span>
            </div>
        </div>

        <!-- 5. No Doc & Keterangan -->
        <div class="mt-2.5 pt-2 border-t border-[#E8ECF0] flex flex-wrap items-center justify-between text-[11px] text-[#536170]">
            <div>
                <strong>5. No Doc:</strong>
                <span class="font-mono text-[#18212B] ml-1" data-testid="identity-no-doc">
                    {{ $project['no_doc'] ?? '-' }}
                </span>
            </div>
            <div class="text-[#8C9BAE]">
                *Identitas ini bersumber langsung dari Project Master (FR02, AT03).
            </div>
        </div>
    @else
        <div class="py-3 text-center text-xs text-[#8C9BAE]">
            Silakan pilih JO No di atas. Lima field identitas (Part No, Part Name, Customer, Model, No Doc) akan otomatis muncul di sini secara hanya-baca.
        </div>
    @endif
</div>

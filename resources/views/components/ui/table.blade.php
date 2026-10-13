@props([
    'headers' => [],
])

<div class="w-full overflow-x-auto rounded-md border border-[#D8DEE6] bg-white">
    <table {{ $attributes->merge(['class' => 'w-full text-left border-collapse text-sm text-[#18212B]']) }}>
        @if(!empty($headers))
            <thead class="bg-[#F5F6F8] border-b border-[#D8DEE6] text-xs font-semibold text-[#536170] tracking-wider uppercase select-none">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-4 py-3 min-h-[44px]">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-[#D8DEE6] bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>

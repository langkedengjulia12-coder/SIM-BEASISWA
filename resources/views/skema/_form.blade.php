@php
    $b = $s?->bobot ?? ['akademik' => 40, 'nonakademik' => 30, 'kebutuhan' => 30];
    @endphp
<x-field :keep="$s === null" label="Nama Skema" name="nama_skema" :value="$s?->nama_skema" required class="lg:col-span-2" />
<x-select-field label="Jenis Beasiswa" name="jenis" required>
    @foreach ($jenis as $k => $lbl)<option value="{{ $k }}" @selected(($s === null ? old('jenis') : $s->jenis) == $k)>{{ $lbl }}</option>@endforeach
</x-select-field>
<x-field :keep="$s === null" label="Kuota Penerima" name="kuota" type="number" min="1" :value="$s?->kuota" required />
<x-field :keep="$s === null" label="Nominal per Penerima per Termin (Rp)" name="nominal_per_penerima" type="number" min="0" step="1" :value="$s ? (int) $s->nominal_per_penerima : null" required />
<x-field :keep="$s === null" label="IPK Minimum" name="ipk_minimum" type="number" min="0" max="4" step="0.01" :value="$s?->ipk_minimum ?? '3.00'" required />
<x-field :keep="$s === null" label="Tanggal Buka Pendaftaran" name="tgl_buka" type="date" :value="$s?->tgl_buka?->toDateString()" required />
<x-field :keep="$s === null" label="Tanggal Tutup Pendaftaran" name="tgl_tutup" type="date" :value="$s?->tgl_tutup?->toDateString()" required />
<x-select-field label="Aturan Penyaluran" name="aturan_penyaluran" required>
    @foreach ($aturan as $k => $lbl)<option value="{{ $k }}" @selected(($s === null ? old('aturan_penyaluran', 'per_semester') : $s->aturan_penyaluran) == $k)>{{ $lbl }} ({{ config('beasiswa.termin')[$k] }} termin)</option>@endforeach
</x-select-field>
<div class="sm:col-span-2 lg:col-span-3 rounded-lg bg-slate-50 p-3">
    <p class="mb-2 text-xs font-semibold text-slate-600">Bobot Kriteria Seleksi (total harus 100%)</p>
    <div class="grid gap-3 sm:grid-cols-3">
        <x-field :keep="$s === null" label="Akademik (%)" name="bobot_akademik" type="number" min="0" max="100" :value="$b['akademik']" required />
        <x-field :keep="$s === null" label="Prestasi / Non-akademik (%)" name="bobot_nonakademik" type="number" min="0" max="100" :value="$b['nonakademik']" required />
        <x-field :keep="$s === null" label="Kebutuhan Ekonomi / Proposal (%)" name="bobot_kebutuhan" type="number" min="0" max="100" :value="$b['kebutuhan']" required />
    </div>
</div>

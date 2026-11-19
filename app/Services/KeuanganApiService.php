<?php

namespace App\Services;

use App\Models\Penyaluran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Integrasi REST API sistem keuangan universitas (KAK 7.1 modul Penyaluran Dana).
 *
 * Kontrak API yang diharapkan:
 *   POST {KEUANGAN_API_URL}   (JSON)
 *     { id_penyaluran, npm, nama, bank, no_rekening, nominal, termin_ke }
 *   Respons sukses (2xx):  { "no_referensi": "...", "status": "tersalurkan" | "diproses" }
 *
 * Bila KEUANGAN_API_URL kosong -> mode SIMULASI (selalu berhasil).
 */
class KeuanganApiService
{
    /** @return array{ok:bool, ref:?string, status:string, pesan:?string} */
    public function kirimInstruksi(Penyaluran $penyaluran): array
    {
        $url = config('beasiswa.keuangan.url');

        if (empty($url)) {
            return [
                'ok' => true,
                'ref' => 'SIM-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'status' => 'tersalurkan',
                'pesan' => 'Mode simulasi (KEUANGAN_API_URL belum diatur).',
            ];
        }

        $penerima = $penyaluran->penerima()->with('mahasiswa')->first();
        $mhs = $penerima->mahasiswa;

        try {
            $http = Http::acceptJson()->timeout((int) config('beasiswa.keuangan.timeout', 10));
            if ($token = config('beasiswa.keuangan.token')) {
                $http = $http->withToken($token);
            }

            $res = $http->post($url, [
                'id_penyaluran' => $penyaluran->id_penyaluran,
                'npm' => $mhs->npm,
                'nama' => $mhs->nama_lengkap,
                'bank' => $mhs->nama_bank,
                'no_rekening' => $mhs->no_rekening,   // didekripsi hanya saat dikirim
                'nominal' => $penyaluran->nominal,
                'termin_ke' => $penyaluran->termin_ke,
            ]);

            if ($res->successful() && $res->json('no_referensi')) {
                $status = $res->json('status') === 'diproses' ? 'diproses' : 'tersalurkan';

                return ['ok' => true, 'ref' => (string) $res->json('no_referensi'), 'status' => $status, 'pesan' => null];
            }

            return ['ok' => false, 'ref' => null, 'status' => 'gagal', 'pesan' => 'HTTP '.$res->status()];
        } catch (Throwable $e) {
            return ['ok' => false, 'ref' => null, 'status' => 'gagal', 'pesan' => $e->getMessage()];
        }
    }
}

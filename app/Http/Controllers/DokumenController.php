<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Services\DokumenService;
use Illuminate\Http\Request;

/** Unduh berkas pendaftaran (didekripsi saat diakses; hanya staf atau pemilik). */
class DokumenController extends Controller
{
    public function unduh(Request $request, DokumenPendaftaran $dokumen, DokumenService $svc)
    {
        $u = $request->user();
        $milik = $u->isMahasiswa() && optional($u->mahasiswa)->npm === $dokumen->pendaftaran->npm;
        abort_unless($u->isStaf() || $milik, 403);

        return $svc->respons($dokumen->path_file, $dokumen->nama_file);
    }
}

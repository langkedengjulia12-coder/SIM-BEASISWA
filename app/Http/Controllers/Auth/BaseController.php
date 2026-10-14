<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function created($data = null, string $message = 'Data berhasil dibuat'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(string $message = 'Terjadi kesalahan', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $code);
    }

    protected function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'Tidak diizinkan'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function validationError($errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    protected function paginate($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }
}
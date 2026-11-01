<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(): View
    {
        return view('beasiswa.partners', [
            'partners' => Partner::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Partner::create($this->validated($request));

        return to_route('admin.partners.index')->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $partner->update($this->validated($request, $partner));

        return to_route('admin.partners.index')->with('success', 'Data mitra berhasil diperbarui.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $partner->delete();

        return to_route('admin.partners.index')->with('success', 'Mitra berhasil dihapus.');
    }

    private function validated(Request $request, ?Partner $partner = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'mou_number' => ['required', 'string', 'max:100', Rule::unique('partners', 'mou_number')->ignore($partner)],
            'mou_start' => ['required', 'date'],
            'mou_end' => ['required', 'date', 'after_or_equal:mou_start'],
            'funding_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,expired,inactive'],
        ]);
    }
}
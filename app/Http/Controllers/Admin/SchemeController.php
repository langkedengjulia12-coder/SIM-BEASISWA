<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\ScholarshipScheme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchemeController extends Controller
{
    public function index(): View
    {
        return view('beasiswa.schemes', [
            'schemes' => ScholarshipScheme::query()->with('partner')->orderBy('name')->get(),
            'partners' => Partner::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ScholarshipScheme::create($this->validated($request));

        return to_route('admin.schemes.index')->with('success', 'Skema beasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, ScholarshipScheme $scheme): RedirectResponse
    {
        $scheme->update($this->validated($request, $scheme));

        return to_route('admin.schemes.index')->with('success', 'Skema beasiswa berhasil diperbarui.');
    }

    public function destroy(ScholarshipScheme $scheme): RedirectResponse
    {
        $scheme->delete();

        return to_route('admin.schemes.index')->with('success', 'Skema beasiswa berhasil dihapus.');
    }

    private function validated(Request $request, ?ScholarshipScheme $scheme = null): array
    {
        return $request->validate([
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('scholarship_schemes', 'code')->ignore($scheme)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'funding_per_student' => ['required', 'numeric', 'min:0'],
            'quota' => ['required', 'integer', 'min:1'],
            'minimum_gpa' => ['nullable', 'numeric', 'between:0,4'],
            'academic_year' => ['required', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
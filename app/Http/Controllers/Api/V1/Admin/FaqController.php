<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqRequest;
use App\Http\Requests\StoreFaqRequest;
use App\Services\FaqService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(protected FaqService $faqService)
    {
        // Apply admin authentication middleware
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index', 'show']);
    }

    // Display a listing of the resource.
    public function index()
    {
        $faqs = $this->faqService->getAll(function ($query) {
            // Show only active FAQs to guests
            if (!auth('sanctum')->check()) {
                $query->where('is_active', true);
            }
        });

        return response_success('FAQs retrieved successfully.', $faqs);
    }

    // Store a newly created resource in storage.
    public function store(FaqRequest $request)
    {
        $data = $request->validated();
        $faq = $this->faqService->create($data);
        return response_success('FAQ created successfully.', $faq, 201);
    }

    // Display the specified resource.
    public function show(string $id)
    {
        $faq = $this->faqService->getById($id);
        // Prevent non-admins from viewing an inactive FAQ
        if (!$faq->is_active) {
            if (!auth('sanctum')->check() || !auth('sanctum')->user()->hasRole('admin')) {
                return response_error('FAQ not found.', [], 404);
            }
        }
        return response_success('FAQ retrieved successfully.', $faq);
    }

    // Update the specified resource in storage.
    public function update(FaqRequest $request, string $id)
    {
        $data = $request->validated();
        $faq = $this->faqService->update($id, $data);
        return response_success('FAQ updated successfully.', $faq);
    }


    // Remove the specified resource from storage.
    public function destroy(string $id)
    {
        $this->faqService->delete($id);
        return response_success('FAQ deleted successfully.');
    }
}

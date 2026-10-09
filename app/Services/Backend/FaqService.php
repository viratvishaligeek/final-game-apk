<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function getAll(): Collection
    {
        return Faq::latest()->get();
    }

    public function create(array $data): Faq
    {
        return Faq::create($data);
    }

    public function update(Faq $faq, array $data): bool
    {
        return $faq->update($data);
    }

    public function delete(Faq $faq): ?bool
    {
        return $faq->delete();
    }
}

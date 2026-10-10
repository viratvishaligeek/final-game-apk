<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class PlayOnlineKhaiwalFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I use the monthly Satta King result chart?',
                'answer' => 'Choose a year, then select an available month. Open a market chart to see date-wise records for that month only.',
            ],
            [
                'question' => 'Where do the displayed result numbers come from?',
                'answer' => 'Numbers are read from saved result records in the website database. The site does not generate or predict missing results.',
            ],
            [
                'question' => 'What does a dash in the chart mean?',
                'answer' => 'A dash means no non-empty result is available for that market and date. Future dates are marked not due yet.',
            ],
            [
                'question' => 'Can I view older market records?',
                'answer' => 'Yes. Use the year and month selector on the homepage or a market chart. Only months with stored records are listed as available.',
            ],
            [
                'question' => 'Are historical results predictions or guarantees?',
                'answer' => 'No. Historical records are for reference only. They do not predict future results or guarantee any financial outcome.',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                ['answer' => $faq['answer']]
            );
        }
    }
}

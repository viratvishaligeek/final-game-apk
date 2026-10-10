<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'name' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => <<<'HTML'
<h2>Terms &amp; Conditions</h2>

<p>Play Online Khaiwal provides access to stored market result records and monthly charts for informational reference. The website does not verify third-party source records independently and does not predict future results.</p>

<h3>Use of information</h3>
<p>Visitors should check dates and market names carefully. Missing entries are shown as pending or unavailable and must not be interpreted as a result.</p>

<h3>Responsible use</h3>
<p>Use this website only in ways permitted by applicable local laws. Historical data is not financial advice and no outcome is guaranteed.</p>

<h3>Changes</h3>
<p>Page content and site features may be updated to correct errors or improve clarity.</p>
HTML,
                'status' => 'active',
            ],

            [
                'name' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => <<<'HTML'
<h2>Privacy Policy</h2>

<p>Play Online Khaiwal is a result-reference website. Public pages display market names, dates, and stored result values. The site does not publish private account names or contact details in its public winner section unless an administrator has recorded an approved public display name.</p>

<h3>Technical information</h3>
<p>The hosting and application environment may process operational information such as request logs and security events. Exact retention and cookie behavior depend on the deployed configuration.</p>

<h3>Contact</h3>
<p>For privacy questions, use the contact details configured by the site operator, when provided.</p>
HTML,
                'status' => 'active',
            ],

            [
                'name' => 'About Us',
                'slug' => 'about-us',
                'content' => <<<'HTML'
<h2>About Play Online Khaiwal</h2>

<p>Play Online Khaiwal organizes stored Satta King and Satta Matka result records into date-wise market boards and monthly charts. Visitors can select a market, year, and month to review the records available in the database.</p>

<h3>How records are displayed</h3>
<p>Published values are displayed as stored, including leading zeros where present. Missing values are marked pending or unavailable rather than estimated.</p>

<h3>Historical charts</h3>
<p>Each market's monthly chart is limited to the selected calendar month. Available months are linked by year to make older records easier to browse.</p>

<p>Historical results are records, not predictions or guarantees of future outcomes.</p>
HTML,
                'status' => 'active',
            ],
            [
                'name' => 'WhatsApp & Contact',
                'slug' => 'whatsapp',
                'content' => <<<'HTML'
<h2>Contact Play Online Khaiwal</h2>

<p>For website questions or corrections to a displayed record, use the public contact details configured by the site administrator.</p>

<p>Contact details and app download information will appear here when the operator has configured them. No unverified phone number or email address is published as a placeholder.</p>
HTML,
                'status' => 'active',
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                [
                    'name' => $page['name'],
                    'content' => $page['content'],
                    'status' => $page['status'],
                    'is_editable' => 'yes',
                    'menu_visible' => true,
                    'menu_order' => array_search($page['slug'], array_column($pages, 'slug'), true) + 1,
                    'meta_title' => $page['name'] . ' | Play Online Khaiwal',
                    'meta_description' => match ($page['slug']) {
                        'about-us' => 'Learn how Play Online Khaiwal organizes stored Satta King result records and game-wise monthly charts.',
                        'privacy-policy' => 'Read the privacy information for Play Online Khaiwal and its public result-reference pages.',
                        'terms-conditions' => 'Read the terms for using Play Online Khaiwal result records and monthly charts.',
                        'whatsapp' => 'Contact Play Online Khaiwal about website questions or corrections to displayed records.',
                        default => 'Information about Play Online Khaiwal and its monthly result charts.',
                    },
                    'meta_keywords' => 'Play Online Khaiwal, Satta King result chart, monthly result records',
                    'noindex' => false,
                ]
            );
        }

        $this->command?->info(
            count($pages) . ' CMS pages seeded successfully.'
        );
    }
}

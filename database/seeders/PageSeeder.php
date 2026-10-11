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
            [
                'name' => 'How to Play',
                'slug' => 'how-to-play',
                'content' => <<<'HTML'
<h2>How to Use Play Online Khaiwal</h2>

<p>Play Online Khaiwal provides access to available historical Satta King and Satta Matka market result records and monthly charts.</p>

<h3>Browse Market Results</h3>
<p>Visit the relevant market section to view available result records. Check the market name and date carefully before reviewing an entry.</p>

<h3>View Monthly Charts</h3>
<p>Select the available year and month to browse historical records for a particular market.</p>

<h3>Understand Pending Results</h3>
<p>A pending or unavailable entry means that a result is not currently available in the website database. It should not be treated as a confirmed result.</p>

<h3>Important Information</h3>
<p>Historical charts are provided for informational reference only. They do not predict or guarantee future outcomes. Always comply with applicable local laws.</p>
HTML,
                'status' => 'active',
            ],

            [
                'name' => 'Game Rates',
                'slug' => 'game-rates',
                'content' => <<<'HTML'
<h2>Game Rates</h2>

<p>This page provides general information about game rates associated with the markets listed on Play Online Khaiwal.</p>

<h3>Market Information</h3>
<p>Available market information and any published rates should be checked against the relevant official source. Rates and rules may differ between markets and operators.</p>

<h3>Accuracy and Updates</h3>
<p>Information may change or become outdated. Please verify current details independently before relying on them.</p>

<h3>Disclaimer</h3>
<p>Play Online Khaiwal does not guarantee the accuracy of third-party rate information or any outcome. Visitors are responsible for complying with applicable laws.</p>
HTML,
                'status' => 'active',
            ],

            [
                'name' => 'Offers',
                'slug' => 'offers',
                'content' => <<<'HTML'
<h2>Offers and Announcements</h2>

<p>This page is reserved for official website announcements and offers published by the Play Online Khaiwal administrator.</p>

<h3>Current Offers</h3>
<p>No offer is confirmed unless it is explicitly published and verified by the website administrator.</p>

<h3>Stay Informed</h3>
<p>Check this page for updates when official announcements become available.</p>

<h3>Important Notice</h3>
<p>Be cautious of unofficial messages claiming to represent this website. Do not share passwords, payment details, or sensitive personal information with unverified sources.</p>
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
                        'how-to-play' => 'Learn how to browse market results and historical monthly charts on Play Online Khaiwal.',
                        'game-rates' => 'Read general information about market rates and published game information on Play Online Khaiwal.',
                        'offers' => 'View official announcements and verified offers published by Play Online Khaiwal.',

                        default => 'Information about Play Online Khaiwal and its monthly result charts.',
                    },
                    'meta_keywords' => 'Play Online Khaiwal, Satta King result chart, monthly result records',
                    'noindex' => false,
                ]
            );
        }

        // Replace only known placeholder/default copy from the previous seeder;
        // customized page content is left untouched.
        $legacyDefaults = [
            'whatsapp' => ['%XXXXXXXXXX%', '%@example.%', '%Add your WhatsApp number here%'],
            'about-us' => ['%Welcome to our platform.%'],
            'terms-conditions' => ['%Wallet & Transactions%', '%Account Responsibility%'],
            'privacy-policy' => ['%This privacy policy explains how%'],
        ];

        foreach ($legacyDefaults as $slug => $markers) {
            $definition = collect($pages)->firstWhere('slug', $slug);
            if (!$definition) {
                continue;
            }

            Page::query()
                ->where('slug', $slug)
                ->where(function ($query) use ($markers) {
                    foreach ($markers as $marker) {
                        $query->orWhere('content', 'like', $marker);
                    }
                })
                ->update([
                    'name' => $definition['name'],
                    'content' => $definition['content'],
                    'is_editable' => 'yes',
                ]);
        }

        $obsoleteDefaults = [
            'how-to-play' => '%Follow the steps below to start playing%',
            'game-rates' => '%Game rates may vary depending on the game type%',
            'offers' => '%Check out our latest offers and promotions%',
        ];

        foreach ($obsoleteDefaults as $slug => $marker) {
            Page::query()
                ->where('slug', $slug)
                ->where('content', 'like', $marker)
                ->update(['menu_visible' => false, 'noindex' => true]);
        }

        $this->command?->info(
            count($pages) . ' CMS pages seeded successfully.'
        );
    }
}

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
<h2>Terms & Conditions</h2>

<p>
Welcome to our platform. By accessing or using this application,
you agree to comply with and be bound by the following terms and conditions.
</p>

<h3>1. Account Responsibility</h3>

<p>
You are responsible for maintaining the confidentiality of your account
credentials and for all activities performed through your account.
</p>

<h3>2. Wallet & Transactions</h3>

<p>
All wallet deposits, withdrawals, and other financial transactions
are subject to verification and the applicable rules of the platform.
</p>

<h3>3. Game Participation</h3>

<p>
Users must participate only in games that are available and open for play.
Once a valid bet has been successfully placed, it may not be cancelled
unless specifically permitted by the platform.
</p>

<h3>4. Prohibited Activities</h3>

<p>
Any attempt to manipulate games, exploit technical issues, use fraudulent
payment methods, or abuse the platform may result in suspension or
termination of the account.
</p>

<h3>5. Changes to Terms</h3>

<p>
We reserve the right to update these terms and conditions whenever required.
Updated terms will become effective once published on this platform.
</p>

<h3>6. Contact</h3>

<p>
If you have any questions regarding these terms, please contact our support team.
</p>
HTML,
                'status' => 1,
            ],

            [
                'name' => 'How To Play',
                'slug' => 'how-to-play',
                'content' => <<<'HTML'
<h2>How To Play</h2>

<p>
Follow the steps below to start playing on our platform.
</p>

<h3>1. Create Your Account</h3>

<p>
Register your account and complete the required verification process.
</p>

<h3>2. Add Money</h3>

<p>
Open the Wallet section and use the available payment options to add
money to your wallet.
</p>

<h3>3. Select a Game</h3>

<p>
Choose the game you want to play from the available games list.
Make sure the game is currently open for betting.
</p>

<h3>4. Select Your Bet</h3>

<p>
Select the required game type and choose your number, digit or combination.
Enter the amount you want to play.
</p>

<h3>5. Review Your Bet</h3>

<p>
Check your selected numbers and total amount carefully before submitting
your bet.
</p>

<h3>6. Place Bet</h3>

<p>
Click on the <strong>Place Bet</strong> button and confirm your bet.
The applicable amount will be deducted from your wallet.
</p>

<h3>7. Check Results</h3>

<p>
Results can be viewed from the Monthly Chart and your Play History.
</p>

<h3>8. Winnings</h3>

<p>
Eligible winnings are credited according to the applicable game rules
and platform configuration.
</p>
HTML,
                'status' => 1,
            ],

            [
                'name' => 'Game Rates',
                'slug' => 'game-rates',
                'content' => <<<'HTML'
<h2>Game Rates</h2>

<p>
Game rates may vary depending on the game type and current platform
configuration.
</p>

<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
<thead>
<tr>
<th>Game Type</th>
<th>Minimum Bet</th>
<th>Rate</th>
</tr>
</thead>

<tbody>
<tr>
<td>Single / Jodi</td>
<td>₹10</td>
<td>As per current game rate</td>
</tr>

<tr>
<td>Harup</td>
<td>₹10</td>
<td>As per current game rate</td>
</tr>

<tr>
<td>Crossing</td>
<td>₹10</td>
<td>As per current game rate</td>
</tr>
</tbody>
</table>
</div>

<h3>Important</h3>

<p>
The actual applicable rate is determined by the game configuration
available at the time of play.
</p>

<p>
Please check the game information before placing your bet.
</p>
HTML,
                'status' => 1,
            ],

            [
                'name' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => <<<'HTML'
<h2>Privacy Policy</h2>

<p>
Your privacy is important to us. This privacy policy explains how
information may be collected, used, and protected when you use our platform.
</p>

<h3>1. Information We Collect</h3>

<p>
We may collect information required for account creation, authentication,
wallet transactions, customer support, and platform security.
</p>

<h3>2. How We Use Information</h3>

<ul>
<li>To provide and maintain our services.</li>
<li>To manage your account.</li>
<li>To process wallet transactions.</li>
<li>To improve platform security.</li>
<li>To provide customer support.</li>
</ul>

<h3>3. Payment Information</h3>

<p>
Payment-related information may be processed through authorized payment
providers. We do not intentionally store sensitive payment credentials
unless required and permitted for providing the service.
</p>

<h3>4. Data Security</h3>

<p>
We use reasonable technical and organizational measures to protect
information against unauthorized access, alteration, disclosure, or destruction.
</p>

<h3>5. Data Sharing</h3>

<p>
Information may be shared with authorized service providers where required
to operate the platform, process transactions, provide support, or comply
with applicable legal requirements.
</p>

<h3>6. Policy Updates</h3>

<p>
This privacy policy may be updated from time to time. Any changes will be
published on this page.
</p>
HTML,
                'status' => 1,
            ],

            [
                'name' => 'About Us',
                'slug' => 'about-us',
                'content' => <<<'HTML'
<h2>About Us</h2>

<p>
Welcome to our platform. We provide a simple and convenient digital
experience for managing your account, wallet, games, results, and transactions
from one place.
</p>

<h3>Our Platform</h3>

<p>
Our goal is to provide a user-friendly platform with a clean interface,
secure account management, convenient wallet functionality, and easy access
to game and result information.
</p>

<h3>What We Offer</h3>

<ul>
<li>Easy account management</li>
<li>Wallet and transaction management</li>
<li>Game participation</li>
<li>Play history</li>
<li>Monthly result charts</li>
<li>Withdrawal functionality</li>
<li>Customer support</li>
</ul>

<h3>Our Commitment</h3>

<p>
We continuously work to improve the platform experience, performance,
reliability, and security for our users.
</p>

<p>
For questions or assistance, please contact our support team.
</p>
HTML,
                'status' => 1,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                [
                    'name' => $page['name'],
                    'content' => $page['content'],
                    'status' => $page['status'],
                    'is_editable' => 'no',
                ]
            );
        }

        $this->command?->info(
            count($pages) . ' CMS pages seeded successfully.'
        );
    }
}

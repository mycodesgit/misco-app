<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\User;
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DailyTicketRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Retrieve existing dependencies
        $requesters = User::where('role', 'Requester')->get();
        if ($requesters->isEmpty()) {
            $requesters = User::all(); // Fallback if no specific Requester role exists
        }

        $itStaff = User::whereIn('role', ['IT Support', 'Administrator'])->get();
        $categories = Category::all();
        $subcategories = Subcategory::all();

        // Safety check to ensure required relations exist before seeding
        if ($requesters->isEmpty() || $categories->isEmpty() || $subcategories->isEmpty()) {
            $this->command->warn('Seeding skipped: Please ensure users, categories, and subcategories are seeded first.');
            return;
        }

        // Sample IT support issue topics
        $issues = [
            'Cannot connect to the office Wi-Fi network.',
            'Network printer on the 2nd floor is offline and unresponsive.',
            'Computer monitor display flickering during boot.',
            'Forgot password for the MIS portal account.',
            'Local network drive (Shared Drive) missing after restart.',
            'System running extremely slow during morning login hours.',
            'MS Word and Excel asking for product key activation.',
            'Antivirus software showing outdated virus definitions warning.',
        ];

        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        $statuses   = ['Pending', 'In Progress', 'Resolved', 'Cancelled'];

        // 2. Generate sample ticket requests
        for ($i = 1; $i <= 15; $i++) {
            $createdDate = Carbon::now()->subDays(rand(1, 14))->subHours(rand(1, 8));
            $ticketNumber = 'TKT-' . $createdDate->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $status = $statuses[array_rand($statuses)];

            // Handle timestamps based on status
            $startedAt  = in_array($status, ['In Progress', 'Resolved']) ? (clone $createdDate)->addMinutes(rand(15, 60)) : null;
            $resolvedAt = ($status === 'Resolved') ? (clone $startedAt)->addHours(rand(1, 5)) : null;
            $assignedTo = in_array($status, ['In Progress', 'Resolved']) && $itStaff->isNotEmpty() ? $itStaff->random()->id : null;

            $randomRequester = $requesters->random();
            $randomCat = $categories->random();
            
            // Pick a matching subcategory if available, otherwise any subcategory
            $matchingSubcats = $subcategories->where('cat_id', $randomCat->id);
            $randomSubcat = $matchingSubcats->isNotEmpty() ? $matchingSubcats->random() : $subcategories->random();

            DailyTicketRequest::create([
                'user_id'           => $randomRequester->id,
                'cat_id'            => $randomCat->id,
                'subcat_id'         => $randomSubcat->id,
                'ticket_number'     => $ticketNumber,
                'issue_description' => $issues[array_rand($issues)],
                'priority'          => $priorities[array_rand($priorities)],
                'contactno'         => '0917' . rand(1000000, 9999999),
                'attachment'        => null,
                'remarks'           => $status === 'Resolved' ? 'Issue verified and resolved.' : null,
                'assigned_to'       => $assignedTo,
                'status'            => $status,
                'started_at'        => $startedAt,
                'resolved_at'       => $resolvedAt,
                'created_at'        => $createdDate,
                'updated_at'        => $resolvedAt ?? $startedAt ?? $createdDate,
            ]);
        }

        $this->command->info('DailyTicketRequest table seeded successfully with 15 tickets!');
    }
}

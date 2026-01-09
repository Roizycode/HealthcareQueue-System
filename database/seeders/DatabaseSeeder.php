<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Priority;
use App\Models\Counter;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\QueueSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks and truncate tables for a fresh seed
        Schema::disableForeignKeyConstraints();
        
        // Truncate tables in reverse dependency order
        DB::table('notification_logs')->truncate();
        DB::table('queues')->truncate();
        DB::table('counters')->truncate();
        DB::table('patients')->truncate();
        DB::table('priorities')->truncate();
        DB::table('services')->truncate();
        DB::table('queue_settings')->truncate();
        // Don't truncate users to preserve any custom accounts, use updateOrCreate
        
        Schema::enableForeignKeyConstraints();
        
        // ==========================================
        // USERS
        // ==========================================
        
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@healthqueue.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1234567890',
                'employee_id' => 'EMP-001',
                'is_active' => true,
            ]
        );

        // Staff Users
        $staff1 = User::updateOrCreate(
            ['email' => 'staff@healthqueue.com'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '+1234567891',
                'employee_id' => 'EMP-002',
                'is_active' => true,
            ]
        );

        $staff2 = User::updateOrCreate(
            ['email' => 'staff2@healthqueue.com'],
            [
                'name' => 'Michael Chen',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '+1234567892',
                'employee_id' => 'EMP-003',
                'is_active' => true,
            ]
        );

        $staff3 = User::updateOrCreate(
            ['email' => 'staff3@healthqueue.com'],
            [
                'name' => 'Emily Davis',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '+1234567893',
                'employee_id' => 'EMP-004',
                'is_active' => true,
            ]
        );

        // ==========================================
        // SERVICES
        // ==========================================
        
        $consultation = Service::create([
            'name' => 'Consultation',
            'code' => 'CON',
            'description' => 'General medical consultation with experienced doctors',
            'icon' => 'fa-stethoscope',
            'color' => '#667eea',
            'average_service_time' => 15,
            'max_queue_size' => 100,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $laboratory = Service::create([
            'name' => 'Laboratory',
            'code' => 'LAB',
            'description' => 'Comprehensive lab tests and diagnostic services',
            'icon' => 'fa-flask',
            'color' => '#11998e',
            'average_service_time' => 10,
            'max_queue_size' => 80,
            'display_order' => 2,
            'is_active' => true,
        ]);

        $pharmacy = Service::create([
            'name' => 'Pharmacy',
            'code' => 'PHR',
            'description' => 'Quick prescription fulfillment and medications',
            'icon' => 'fa-pills',
            'color' => '#f093fb',
            'average_service_time' => 8,
            'max_queue_size' => 120,
            'display_order' => 3,
            'is_active' => true,
        ]);

        $radiology = Service::create([
            'name' => 'Radiology',
            'code' => 'RAD',
            'description' => 'X-rays, CT scans, MRI and imaging services',
            'icon' => 'fa-x-ray',
            'color' => '#ff6b6b',
            'average_service_time' => 20,
            'max_queue_size' => 50,
            'display_order' => 4,
            'is_active' => true,
        ]);

        // ==========================================
        // PRIORITIES
        // ==========================================
        
        $emergency = Priority::create([
            'name' => 'Emergency',
            'code' => 'EMG',
            'description' => 'Life-threatening situations requiring immediate attention',
            'level' => 100,
            'color' => '#dc3545',
            'icon' => 'fa-ambulance',
            'max_wait_time' => 5,
            'is_active' => true,
        ]);

        $senior = Priority::create([
            'name' => 'Senior Citizen',
            'code' => 'SNR',
            'description' => 'Patients aged 60 and above',
            'level' => 75,
            'color' => '#f093fb',
            'icon' => 'fa-user-clock',
            'max_wait_time' => 20,
            'is_active' => true,
        ]);

        $pwd = Priority::create([
            'name' => 'PWD',
            'code' => 'PWD',
            'description' => 'Persons with disabilities',
            'level' => 70,
            'color' => '#17a2b8',
            'icon' => 'fa-wheelchair',
            'max_wait_time' => 20,
            'is_active' => true,
        ]);

        $regular = Priority::create([
            'name' => 'Regular',
            'code' => 'REG',
            'description' => 'Standard priority for all patients',
            'level' => 10,
            'color' => '#6c757d',
            'icon' => 'fa-user',
            'max_wait_time' => 60,
            'is_active' => true,
        ]);

        // ==========================================
        // COUNTERS
        // ==========================================
        
        // Consultation Counters
        Counter::create([
            'service_id' => $consultation->id,
            'name' => 'Counter 1',
            'code' => 'C1',
            'assigned_staff_id' => $staff1->id,
            'status' => 'open',
            'location' => 'Ground Floor, Room 101',
            'is_active' => true,
        ]);

        Counter::create([
            'service_id' => $consultation->id,
            'name' => 'Counter 2',
            'code' => 'C2',
            'assigned_staff_id' => $staff2->id,
            'status' => 'open',
            'location' => 'Ground Floor, Room 102',
            'is_active' => true,
        ]);

        // Laboratory Counters
        Counter::create([
            'service_id' => $laboratory->id,
            'name' => 'Lab Window A',
            'code' => 'LA',
            'status' => 'open',
            'location' => '1st Floor, Lab Section',
            'is_active' => true,
        ]);

        Counter::create([
            'service_id' => $laboratory->id,
            'name' => 'Lab Window B',
            'code' => 'LB',
            'status' => 'closed',
            'location' => '1st Floor, Lab Section',
            'is_active' => true,
        ]);

        // Pharmacy Counters
        Counter::create([
            'service_id' => $pharmacy->id,
            'name' => 'Pharmacy 1',
            'code' => 'P1',
            'assigned_staff_id' => $staff3->id,
            'status' => 'open',
            'location' => 'Ground Floor, Pharmacy',
            'is_active' => true,
        ]);

        Counter::create([
            'service_id' => $pharmacy->id,
            'name' => 'Pharmacy 2',
            'code' => 'P2',
            'status' => 'open',
            'location' => 'Ground Floor, Pharmacy',
            'is_active' => true,
        ]);

        // Radiology Counter
        Counter::create([
            'service_id' => $radiology->id,
            'name' => 'X-Ray 1',
            'code' => 'X1',
            'status' => 'open',
            'location' => '2nd Floor, Radiology',
            'is_active' => true,
        ]);

        // ==========================================
        // SAMPLE DATA (15 Samples)
        // ==========================================
        $this->command->info('Creating 15 sample patients and queues...');
        
        $statuses = ['completed', 'completed', 'completed', 'completed', 'completed', 'serving', 'serving', 'called', 'called', 'waiting', 'waiting', 'waiting', 'waiting', 'waiting', 'waiting'];
        
        foreach(range(1, 15) as $i) {
            $patient = Patient::create([
                'user_id' => null,
                'first_name' => fake()->firstName,
                'last_name' => fake()->lastName,
                'phone' => '+639' . fake()->numerify('#########'),
                'email' => fake()->safeEmail, // Use safeEmail
                'date_of_birth' => fake()->date,
                'gender' => fake()->randomElement(['male', 'female']),
                'address' => fake()->address,
                'is_senior' => fake()->boolean(20),
                'is_pwd' => fake()->boolean(10),
            ]);

            $service = fake()->randomElement([$consultation, $laboratory, $pharmacy, $radiology]);
            
            // Determine priority based on patient + random emergency
            if ($patient->is_senior) $priority = $senior;
            elseif ($patient->is_pwd) $priority = $pwd;
            else $priority = $regular;
            
            if (fake()->boolean(5)) $priority = $emergency;
            
            $status = $statuses[$i-1];
            $counter = null;
            $called_by = null;
            
            if (in_array($status, ['serving', 'called', 'completed'])) {
                $counter = Counter::where('service_id', $service->id)->inRandomOrder()->first();
                $called_by = $staff1->id; // Assign to Sarah default
            }

            Queue::create([
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'priority_id' => $priority->id,
                'counter_id' => $counter?->id,
                'called_by' => $called_by,
                'queue_number' => $service->code . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'queue_position' => 0,
                'status' => $status,
                'queue_type' => 'walk_in',
                'created_at' => now()->subMinutes(rand(20, 300)),
                'checked_in_at' => now()->subMinutes(rand(20, 300)),
                'called_at' => ($status != 'waiting') ? now()->subMinutes(rand(10, 20)) : null,
                'serving_started_at' => ($status == 'serving' || $status == 'completed') ? now()->subMinutes(rand(5, 10)) : null,
                'completed_at' => ($status == 'completed') ? now()->subMinutes(rand(1, 5)) : null,
                'actual_wait_time' => ($status == 'completed') ? rand(5, 30) : null,
            ]);
        }

        // ==========================================
        // QUEUE SETTINGS
        // ==========================================
        
        QueueSetting::seedDefaults();

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo Credentials:');
        $this->command->info('  Admin: admin@healthqueue.com / password');
        $this->command->info('  Staff: staff@healthqueue.com / password');
    }
}

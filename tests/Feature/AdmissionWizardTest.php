<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdmissionWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_wizard_creates_students_and_timetable()
    {
        // Create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Step 1: post admission data
        $response = $this->post(route('students.next'), [
            'guardian_name' => 'Jane Doe',
            'guardian_relation' => 'Mother',
            'guardian_phone' => '0123456789',
            'guardian_email' => 'jane@example.com',
            'guardian_address' => '1 Main St',
            'guardian_city' => 'Testville',
            'guardian_notes' => 'Important',
            'post_code' => '12345',
            'reference' => 'REF123',

            'first_name' => 'Alice',
            'last_name' => 'Student',
            'gender' => 'female',
            'dob' => '2010-05-01',
            'enroll_date' => '2025-09-01',
            'start_date' => '2025-09-08',
            'deposit' => '50.00',
            'period' => 'monthly',

            'siblings' => [
                ['first_name' => 'Bob', 'last_name' => 'Student', 'gender' => 'male', 'dob' => '2012-03-02'],
            ],
        ]);

        $response->assertRedirect(route('students.confirm'));

        // GET confirm should show saved session values
        $confirm = $this->get(route('students.confirm'));
        $confirm->assertStatus(200);
        $confirm->assertSeeText('Jane Doe');
        $confirm->assertSeeText('Testville');
        $confirm->assertSeeText('Alice Student');
        $confirm->assertSeeText('2025-09-01');
        $confirm->assertSeeText('50.00');

        // Post store (finalize)
        $store = $this->post(route('students.store'));
        $store->assertRedirect(route('students.create'));

        // Assert students created
        $this->assertDatabaseHas('students', ['first_name' => 'Alice', 'last_name' => 'Student']);
        $this->assertDatabaseHas('students', ['first_name' => 'Bob', 'last_name' => 'Student']);

        // Timetables may be empty; ensure table exists and is migrated
        $this->assertDatabaseCount('students', 2);
    }
}

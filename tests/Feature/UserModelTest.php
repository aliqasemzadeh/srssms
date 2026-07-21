<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_mass_assignment_of_migration_columns(): void
    {
        $user = User::create([
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'mobile' => '09123456789',
            'email' => 'ali@example.com',
            'username' => 'ali.rezaei',
            'password' => 'password',
        ]);

        $this->assertSame('Ali', $user->first_name);
        $this->assertSame('Rezaei', $user->last_name);
        $this->assertSame('09123456789', $user->mobile);
        $this->assertSame('ali@example.com', $user->email);
        $this->assertSame('ali.rezaei', $user->username);
        $this->assertNotSame('password', $user->password);
    }

    public function test_creates_a_user_from_the_factory(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->first_name);
        $this->assertNotEmpty($user->last_name);
        $this->assertNotEmpty($user->mobile);
        $this->assertTrue($user->exists);
    }
}

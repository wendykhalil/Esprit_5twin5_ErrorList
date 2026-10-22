<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_missing_fields(): void
    {
        $this->from('/login')
            ->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login', 'password']);

        $this->assertGuest();
    }

    public function test_login_rejects_invalid_email_and_non_string_values_as_json(): void
    {
        $this->postJson('/login', [
            'login' => ['not-a-string'],
            'password' => ['secret'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login', 'password']);

        $this->assertGuest();
    }

    public function test_login_rejects_an_email_that_is_too_long(): void
    {
        $this->postJson('/login', [
            'login' => str_repeat('a', 256),
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }

    public function test_login_rejects_a_password_longer_than_the_hash_limit(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', [
            'login' => $user->email,
            'password' => str_repeat('a', 73),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $this->assertGuest();
    }

    public function test_login_normalizes_email_without_changing_the_password(): void
    {
        $user = User::factory()->create([
            'email' => 'person@example.com',
        ]);

        $this->post('/login', [
            'login' => '  Person@Example.com  ',
            'password' => 'password',
        ])->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_returns_the_same_error_for_unknown_and_wrong_credentials(): void
    {
        $user = User::factory()->create();

        $unknown = $this->postJson('/login', [
            'login' => 'missing@example.com',
            'password' => 'password',
        ]);

        $wrong = $this->postJson('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $unknown->assertUnprocessable();
        $wrong->assertUnprocessable();
        $this->assertSame(
            $unknown->json('errors.login.0'),
            $wrong->json('errors.login.0')
        );
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('login');
        }

        $response = $this->from('/login')
            ->post('/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect('/login')->assertSessionHasErrors('login');
        $this->assertStringContainsString(
            'Trop de tentatives',
            session('errors')->get('login')[0]
        );

        $this->assertGuest();
    }

    public function test_login_rejects_unexpected_fields_before_authentication(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', [
            'login' => $user->email,
            'password' => 'password',
            'role' => 'admin',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);

        $this->assertGuest();
    }

    public function test_registration_rejects_empty_short_and_mismatched_passwords(): void
    {
        $this->postJson('/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'username', 'phone', 'email', 'password', 'password_confirmation']);

        $this->postJson('/register', [
            'name' => 'Ada Lovelace',
            'username' => 'ada_lovelace',
            'phone' => '+21620000000',
            'email' => 'ada@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $this->postJson('/register', [
            'name' => 'Ada Lovelace',
            'username' => 'ada_lovelace',
            'phone' => '+21620000000',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password_confirmation']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_a_password_that_would_be_truncated_by_bcrypt(): void
    {
        $this->postJson('/register', [
            'name' => 'Ada Lovelace',
            'username' => 'ada_lovelace',
            'phone' => '+21620000000',
            'email' => 'ada@example.com',
            'password' => str_repeat('a', 73),
            'password_confirmation' => str_repeat('a', 73),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_invalid_types_and_duplicate_emails(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->postJson('/register', [
            'name' => ['Ada'],
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);

        $this->postJson('/register', [
            'name' => 'Ada Lovelace',
            'email' => '  ADA@example.com  ',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertSame(1, User::query()->count());
    }

    public function test_registration_rejects_privileged_role_assignment(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Ada Lovelace',
                'username' => 'ada_lovelace',
                'email' => 'ada@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'admin',
                'phone' => '0600000000',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['role']);

        $this->assertDatabaseCount('users', 0);
    }
}

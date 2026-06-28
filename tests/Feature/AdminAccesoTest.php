<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_admin_sembrado_puede_iniciar_sesion(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'gabriel-chernitsky@overcloud.us')->first();
        $this->assertNotNull($admin, 'El usuario admin debe estar sembrado.');

        $response = $this->post('/login', [
            'email' => 'gabriel-chernitsky@overcloud.us',
            'password' => '2OFqZ17eNcNP',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_el_dashboard_carga_para_el_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'gabriel-chernitsky@overcloud.us')->first();

        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    public function test_el_seeder_es_idempotente(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('email', 'gabriel-chernitsky@overcloud.us')->count());
    }
}

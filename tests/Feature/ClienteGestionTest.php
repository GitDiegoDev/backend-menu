<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClienteGestionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('clientes')) {
            Schema::create('clientes', function ($table) {
                $table->id();
                $table->string('nombre', 100);
                $table->string('telefono', 20)->unique();
                $table->integer('sellos_actuales')->default(0);
                $table->integer('premios_disponibles')->default(0);
                $table->integer('premios_canjeados')->default(0);
                $table->timestamp('fecha_alta')->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
            });
        }

        $this->user = User::factory()->create();
    }

    public function test_can_list_clientes_ordered_by_nombre()
    {
        Cliente::create(['nombre' => 'Zulema', 'telefono' => '111111', 'sellos_actuales' => 1]);
        Cliente::create(['nombre' => 'Alberto', 'telefono' => '222222', 'sellos_actuales' => 2]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/clientes');

        $response->assertStatus(200);
        $response->assertJsonPath('0.nombre', 'Alberto');
        $response->assertJsonPath('1.nombre', 'Zulema');
    }

    public function test_can_get_cliente_by_id()
    {
        $cliente = Cliente::create(['nombre' => 'Juan Pérez', 'telefono' => '3764123456', 'sellos_actuales' => 4]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/admin/clientes/{$cliente->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $cliente->id,
                'nombre' => 'Juan Pérez',
                'telefono' => '3764123456',
                'sellos_actuales' => 4,
            ]);
    }

    public function test_can_update_sellos_manually()
    {
        $cliente = Cliente::create(['nombre' => 'Juan Pérez', 'telefono' => '3764123456', 'sellos_actuales' => 4]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/admin/clientes/{$cliente->id}/sellos", [
                'sellos_actuales' => 7
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cliente' => [
                    'id' => $cliente->id,
                    'nombre' => 'Juan Pérez',
                    'sellos_actuales' => 7
                ]
            ]);

        $this->assertEquals(7, $cliente->fresh()->sellos_actuales);
    }

    public function test_cannot_update_sellos_with_invalid_values()
    {
        $cliente = Cliente::create(['nombre' => 'Juan Pérez', 'telefono' => '3764123456', 'sellos_actuales' => 4]);

        // Negative
        $response = $this->actingAs($this->user)
            ->putJson("/api/admin/clientes/{$cliente->id}/sellos", [
                'sellos_actuales' => -1
            ]);
        $response->assertStatus(400)
            ->assertJson(['error' => 'La cantidad de sellos debe estar entre 0 y 10']);

        // Greater than 10
        $response = $this->actingAs($this->user)
            ->putJson("/api/admin/clientes/{$cliente->id}/sellos", [
                'sellos_actuales' => 11
            ]);
        $response->assertStatus(400)
            ->assertJson(['error' => 'La cantidad de sellos debe estar entre 0 y 10']);
    }

    public function test_routes_require_authentication()
    {
        $this->getJson('/api/admin/clientes')->assertStatus(401);
        $this->getJson('/api/admin/clientes/1')->assertStatus(401);
        $this->putJson('/api/admin/clientes/1/sellos', ['sellos_actuales' => 5])->assertStatus(401);
    }

    public function test_can_still_get_cliente_by_telefono_publicly()
    {
        $cliente = Cliente::create(['nombre' => 'Juan Pérez', 'telefono' => '3764123456', 'sellos_actuales' => 4]);

        $response = $this->getJson("/api/clientes/3764123456");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nombre', 'Juan Pérez');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FidelizacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Asegurarse de que la tabla clientes existe en la base de datos de prueba (sqlite en memoria)
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

        // Limpiar la tabla antes de cada prueba
        Cliente::truncate();
    }

    public function test_can_identify_client_by_phone()
    {
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 5,
        ]);

        $response = $this->getJson('/api/clientes/3764123456');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nombre' => 'Diego',
                    'telefono' => '3764123456',
                    'sellos_actuales' => 5,
                    'faltan' => 5,
                ]
            ]);
    }

    public function test_returns_404_if_client_not_found()
    {
        $response = $this->getJson('/api/clientes/nonexistent');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
    }

    public function test_can_register_pedido_for_new_client()
    {
        $response = $this->postJson('/api/clientes/pedido', [
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nombre' => 'Diego',
                    'telefono' => '3764123456',
                    'sellos_actuales' => 1,
                    'premios_disponibles' => 0,
                    'faltan' => 9,
                ]
            ]);

        $this->assertDatabaseHas('clientes', [
            'telefono' => '3764123456',
            'sellos_actuales' => 1
        ]);
    }

    public function test_can_register_pedido_for_existing_client()
    {
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 1,
        ]);

        $response = $this->postJson('/api/clientes/pedido', [
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sellos_actuales', 2);
    }

    public function test_generates_reward_after_10_stamps()
    {
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 9,
            'premios_disponibles' => 0,
        ]);

        $response = $this->postJson('/api/clientes/pedido', [
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sellos_actuales' => 0,
                    'premios_disponibles' => 1,
                ]
            ]);
    }

    public function test_can_claim_reward()
    {
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 0,
            'premios_disponibles' => 1,
            'premios_canjeados' => 0,
        ]);

        $response = $this->postJson('/api/clientes/reclamar', [
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'premios_disponibles' => 0,
                    'premios_canjeados' => 1,
                ]
            ]);
    }

    public function test_cannot_claim_reward_if_none_available()
    {
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 5,
            'premios_disponibles' => 0,
            'premios_canjeados' => 0,
        ]);

        $response = $this->postJson('/api/clientes/reclamar', [
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'No hay recompensas disponibles para reclamar'
            ]);
    }
}

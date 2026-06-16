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
                $table->date('fecha_ultimo_sello')->nullable();
                $table->timestamp('fecha_alta')->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
            });
        }

        if (!Schema::hasTable('pedidos_fidelizacion')) {
            Schema::create('pedidos_fidelizacion', function ($table) {
                $table->id();
                $table->string('pedido_uuid')->unique();
                $table->string('telefono');
                $table->timestamp('fecha_creacion')->nullable();
            });
        }

        // Limpiar las tablas antes de cada prueba
        Cliente::truncate();
        \App\Models\PedidoFidelizacion::truncate();
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
            'pedido_uuid' => 'order-1',
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
            'pedido_uuid' => 'order-2',
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
            'pedido_uuid' => 'order-3',
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

    public function test_prevents_duplicate_pedido_uuid()
    {
        $response1 = $this->postJson('/api/clientes/pedido', [
            'pedido_uuid' => 'duplicate-order',
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response1->assertStatus(200)
            ->assertJsonPath('data.sellos_actuales', 1);

        $response2 = $this->postJson('/api/clientes/pedido', [
            'pedido_uuid' => 'duplicate-order',
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response2->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'El pedido ya ha sido procesado'
            ]);

        // Verificar que solo tiene 1 sello
        $this->assertEquals(1, Cliente::where('telefono', '3764123456')->first()->sellos_actuales);
    }

    public function test_does_not_grant_stamp_if_already_received_today()
    {
        $fechaHoy = now()->toDateString();
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 1,
            'fecha_ultimo_sello' => $fechaHoy,
        ]);

        $response = $this->postJson('/api/clientes/pedido', [
            'pedido_uuid' => 'order-today-2',
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sellos_actuales', 1);

        $this->assertEquals(1, Cliente::where('telefono', '3764123456')->first()->sellos_actuales);
    }

    public function test_grants_stamp_if_last_stamp_was_yesterday()
    {
        $fechaAyer = now()->subDay()->toDateString();
        Cliente::create([
            'nombre' => 'Diego',
            'telefono' => '3764123456',
            'sellos_actuales' => 1,
            'fecha_ultimo_sello' => $fechaAyer,
        ]);

        $response = $this->postJson('/api/clientes/pedido', [
            'pedido_uuid' => 'order-today',
            'nombre' => 'Diego',
            'telefono' => '3764123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sellos_actuales', 2);

        $cliente = Cliente::where('telefono', '3764123456')->first();
        $this->assertEquals(2, $cliente->sellos_actuales);
        $this->assertEquals(now()->toDateString(), $cliente->fecha_ultimo_sello->toDateString());
    }
}

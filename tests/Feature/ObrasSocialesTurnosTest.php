<?php

namespace Tests\Feature;

use App\Models\Disponibilidad;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObrasSocialesTurnosTest extends TestCase
{
    use RefreshDatabase;

    private User $usuarioPaciente;
    private Paciente $paciente;
    private Especialidad $especialidad;
    private Medico $conCobertura;
    private Medico $particular;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-25 09:00:00');

        $this->usuarioPaciente = User::create([
            'name' => 'Paciente Prueba',
            'email' => 'paciente.cobertura@example.test',
            'password' => 'password',
            'rol' => 'paciente',
        ]);
        $this->paciente = Paciente::create([
            'user_id' => $this->usuarioPaciente->id,
            'dni' => '12345678',
            'telefono' => '2994000000',
            'fecha_nacimiento' => '2002-01-01',
            'obra_social' => 'OSDE',
        ]);
        $this->especialidad = Especialidad::create([
            'nombre' => 'Cardiologia prueba',
            'duracion_turno' => 30,
        ]);
        $this->conCobertura = $this->crearMedico('1', ['Swiss Medical', 'OSDE']);
        $this->particular = $this->crearMedico('2', ['Swiss Medical']);
        $this->conCobertura->especialidades()->attach($this->especialidad->id);
        $this->particular->especialidades()->attach($this->especialidad->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function crearMedico(string $numero, array $coberturas): Medico
    {
        $usuario = User::create([
            'name' => 'Medico | Prueba ' . $numero,
            'email' => 'medico' . $numero . '@example.test',
            'password' => 'password',
            'rol' => 'medico',
        ]);

        return Medico::create([
            'user_id' => $usuario->id,
            'matricula' => 'TEST-COBERTURA-' . $numero,
            'obra_social' => $coberturas[0],
            'obras_sociales' => $coberturas,
        ]);
    }

    public function test_muestra_medicos_con_y_sin_cobertura_de_la_especialidad(): void
    {
        $this->crearMedico('3', ['OSDE']);

        $this->actingAs($this->usuarioPaciente)
            ->getJson(route('turnos.ajax.medicos', $this->especialidad->id))
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $this->conCobertura->id])
            ->assertJsonFragment(['id' => $this->particular->id]);
    }

    public function test_identifica_la_cobertura_de_cada_medico(): void
    {
        $respuesta = $this->actingAs($this->usuarioPaciente)
            ->getJson(route('turnos.ajax.medicos', $this->especialidad->id))
            ->assertOk();

        $medicos = collect($respuesta->json())->keyBy('id');
        $this->assertTrue($medicos[$this->conCobertura->id]['acepta_obra_social']);
        $this->assertFalse($medicos[$this->particular->id]['acepta_obra_social']);
        $this->assertTrue($medicos[$this->conCobertura->id]['mostrar_cobertura']);
        $this->assertTrue($medicos[$this->particular->id]['mostrar_cobertura']);
        $this->assertSame('Medico Prueba 1', $medicos[$this->conCobertura->id]['nombre']);
    }

    public function test_paciente_puede_reservar_como_particular(): void
    {
        Disponibilidad::create([
            'medico_id' => $this->particular->id,
            'especialidad_id' => $this->especialidad->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fin' => '11:00',
        ]);

        $this->actingAs($this->usuarioPaciente)
            ->post(route('store.turno'), [
                'medico' => $this->particular->id,
                'especialidad' => $this->especialidad->id,
                'fecha_hora' => '2026-12-07 09:00:00',
            ])
            ->assertRedirect(route('usuarios.paciente.panel'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('turnos', [
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->particular->id,
            'especialidad_id' => $this->especialidad->id,
            'fecha' => '2026-12-07 00:00:00',
            'estado' => 'pendiente',
        ]);
    }
    private function habilitarMedicos(): void
    {
        foreach ([$this->conCobertura, $this->particular] as $medico) {
            Disponibilidad::create([
                'medico_id' => $medico->id,
                'especialidad_id' => $this->especialidad->id,
                'dia_semana' => 1,
                'hora_inicio' => '08:00',
                'hora_fin' => '11:00',
            ]);
        }
    }

    public function test_paciente_no_reserva_turnos_simultaneos_con_distintos_medicos(): void
    {
        $this->habilitarMedicos();
        $this->actingAs($this->usuarioPaciente)->post(route('store.turno'), [
            'medico' => $this->conCobertura->id,
            'especialidad' => $this->especialidad->id,
            'fecha_hora' => '2026-12-07 09:00:00',
        ])->assertSessionHasNoErrors();
        $ruta = route('turnos.ajax.disponibilidad', [
            'medicoId' => $this->particular->id, 'especialidad' => $this->especialidad->id,
        ]);
        $horarios = $this->getJson($ruta)->assertOk()->json();
        $this->assertNotContains('09:00', $horarios['2026-12-07']);
        $this->assertContains('09:30', $horarios['2026-12-07']);
        $datos = ['medico' => $this->particular->id, 'especialidad' => $this->especialidad->id];
        $this->post(route('store.turno'), $datos + ['fecha_hora' => '2026-12-07 09:00:00'])
            ->assertSessionHasErrors('fecha_hora');
        $this->assertDatabaseCount('turnos', 1);
        $this->post(route('store.turno'), $datos + ['fecha_hora' => '2026-12-07 09:30:00'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('turnos', 2);
    }
}

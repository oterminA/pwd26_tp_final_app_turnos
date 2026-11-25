<?php

namespace Tests\Feature;

use App\Models\Disponibilidad;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguridadRolesTest extends TestCase
{
    use RefreshDatabase;

    private function usuario($rol, $numero = 1)
    {
        return User::create([
            'name' => 'Usuario prueba',
            'email' => $rol . $numero . '@example.test',
            'password' => 'password',
            'rol' => $rol,
        ]);
    }

    private function paciente($numero = 1)
    {
        return Paciente::create([
            'user_id' => $this->usuario('paciente', $numero)->id,
            'dni' => '1234567' . $numero,
            'telefono' => '2994000000',
            'fecha_nacimiento' => '2002-01-01',
            'obra_social' => 'OSDE',
        ]);
    }

    private function medico($numero = 1)
    {
        return Medico::create([
            'user_id' => $this->usuario('medico', $numero)->id,
            'matricula' => 'TEST-SEG-' . $numero,
            'obra_social' => 'OSDE',
            'obras_sociales' => ['OSDE'],
        ]);
    }

    public function test_visitante_no_accede_a_paneles_ni_reservas(): void
    {
        $this->get(route('usuarios.medico.panel'))->assertRedirect(route('login.medico'));
        $this->get(route('usuarios.paciente.panel'))->assertRedirect(route('login.paciente'));
        $this->get(route('usuarios.admin.panel'))->assertRedirect(route('login.admin'));
        $this->get(route('create.turno'))->assertRedirect(route('login.paciente'));
        $this->post(route('store.turno'))->assertRedirect(route('login.paciente'));
        $this->delete(route('turnos.delete', 1))->assertRedirect('/');
    }

    public function test_paciente_no_accede_a_rutas_de_medico_o_admin(): void
    {
        $this->actingAs($this->paciente()->user);
        foreach (['usuarios.medico.panel', 'medico.edit', 'medico.disponibilidades.index', 'admin.edit', 'admin.medicos.index', 'admin.especialidades.index'] as $ruta) {
            $this->get(route($ruta))->assertForbidden();
        }
        $this->put(route('medico.update'))->assertForbidden();
        $this->put(route('admin.update'))->assertForbidden();
        $this->post(route('medico.disponibilidades.store'))->assertForbidden();
        $this->get(route('create.turno'))->assertOk();
    }

    public function test_medico_y_admin_no_pueden_reservar_ni_acceder_a_perfil_paciente(): void
    {
        $paciente = $this->paciente();
        foreach ([$this->medico()->user, $this->usuario('admin')] as $usuario) {
            $this->actingAs($usuario);
            $this->get(route('usuarios.paciente.panel'))->assertForbidden();
            $this->get(route('paciente.edit'))->assertForbidden();
            $this->put(route('paciente.update'))->assertForbidden();
            $this->get(route('create.turno'))->assertForbidden();
            $this->post(route('store.turno'), ['paciente_id' => $paciente->id])->assertForbidden();
            $this->getJson(route('turnos.ajax.medicos', 1))->assertForbidden();
            $this->getJson(route('turnos.ajax.disponibilidad', 1))->assertForbidden();
            $this->getJson(route('turnos.ajax.especialidad', 1))->assertForbidden();
        }
        $this->assertDatabaseCount('turnos', 0);
    }

    public function test_admin_no_accede_a_perfil_medico_y_medico_no_accede_a_perfil_admin(): void
    {
        $this->actingAs($this->usuario('admin'))->get(route('medico.edit'))->assertForbidden();
        $this->put(route('medico.update'))->assertForbidden();
        $this->actingAs($this->medico()->user)->get(route('admin.edit'))->assertForbidden();
        $this->put(route('admin.update'))->assertForbidden();
    }

    public function test_medico_no_puede_modificar_disponibilidad_ajena(): void
    {
        $medico = $this->medico();
        $especialidad = Especialidad::create(['nombre' => 'Cardiologia', 'duracion_turno' => 30]);
        $medico->especialidades()->attach($especialidad->id);
        $franja = $medico->disponibilidades()->create([
            'especialidad_id' => $especialidad->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fin' => '11:00',
        ]);
        $this->actingAs($this->medico(2)->user);
        $this->get(route('medico.disponibilidades.edit', $franja->id))->assertForbidden();
        $this->put(route('medico.disponibilidades.update', $franja->id))->assertForbidden();
        $this->delete(route('medico.disponibilidades.delete', $franja->id))->assertForbidden();
        $this->assertDatabaseHas('disponibilidades', ['id' => $franja->id]);
    }

    public function test_reserva_usa_paciente_logueado_y_no_id_enviado(): void
    {
        Carbon::setTestNow('2026-11-25 09:00:00');
        try {
            $paciente = $this->paciente();
            $otro = $this->paciente(2);
            $medico = $this->medico();
            $especialidad = Especialidad::create(['nombre' => 'Cardiologia', 'duracion_turno' => 30]);
            $medico->especialidades()->attach($especialidad->id);
            $medico->disponibilidades()->create([
                'especialidad_id' => $especialidad->id,
                'dia_semana' => 1,
                'hora_inicio' => '08:00',
                'hora_fin' => '11:00',
            ]);
            $this->actingAs($paciente->user)->post(route('store.turno'), [
                'medico' => $medico->id,
                'especialidad' => $especialidad->id,
                'fecha_hora' => '2026-12-07 09:00:00',
                'paciente_id' => $otro->id,
            ])->assertRedirect(route('usuarios.paciente.panel'));
            $this->assertDatabaseHas('turnos', ['paciente_id' => $paciente->id]);
            $this->assertDatabaseMissing('turnos', ['paciente_id' => $otro->id]);
            $turno = Turno::firstOrFail();
            $this->actingAs($otro->user)->get(route('turno.comprobante', $turno->id))->assertForbidden();
        } finally {
            Carbon::setTestNow();
        }
    }
}

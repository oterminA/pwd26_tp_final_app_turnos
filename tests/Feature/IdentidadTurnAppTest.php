<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentidadTurnAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_nombre_compuesto_se_muestra_sin_separador(): void
    {
        $user = new User(['name' => 'Maria Elena | Perez Gomez']);

        $this->assertSame('Maria Elena Perez Gomez', $user->nombre_visible);
        $this->assertSame('Maria Elena', $user->nombre);
        $this->assertSame('Perez Gomez', $user->apellido);
    }

    public function test_admin_edita_nombre_y_apellido_por_separado(): void
    {
        $admin = User::create([
            'name' => 'Juan Pablo | Ruiz Gomez',
            'email' => 'admin@identidad.test',
            'password' => 'password',
            'rol' => 'admin',
        ]);

        $this->actingAs($admin)->get(route('admin.edit'))
            ->assertOk()->assertSee('name="nombre"', false)
            ->assertSee('name="apellido"', false)
            ->assertSee('Juan Pablo')->assertSee('Ruiz Gomez')
            ->assertDontSee('Juan Pablo | Ruiz Gomez');

        $this->from(route('admin.edit'))->put(route('admin.update'), [
            'nombre' => 'Juan Carlos',
            'apellido' => 'Ruiz Gomez',
            'email' => $admin->email,
        ])->assertRedirect(route('admin.edit'))->assertSessionHasNoErrors();

        $this->assertSame('Juan Carlos | Ruiz Gomez', $admin->fresh()->name);
        $this->assertSame('Juan Carlos Ruiz Gomez', $admin->fresh()->nombre_visible);
    }

    public function test_nombre_de_app_en_pagina_publica_y_login(): void
    {
        config(['app.name' => 'TurnApp']);

        $this->get('/')->assertOk()->assertSee('TurnApp');
        $this->get(route('login.paciente'))->assertOk()->assertSee('TurnApp');
        $this->get(route('login.medico'))->assertOk()->assertSee('TurnApp');
        $this->get(route('login.admin'))->assertOk()->assertSee('TurnApp');
    }
    public function test_admin_cambia_password_y_valida_confirmacion(): void
    {
        $admin = User::create([
            'name' => 'Admin | Prueba',
            'email' => 'admin.password@example.test',
            'password' => 'password',
            'rol' => 'admin',
        ]);
        $datos = ['nombre' => 'Admin', 'apellido' => 'Prueba', 'email' => $admin->email];
        $this->actingAs($admin)->from(route('admin.edit'))->put(route('admin.update'), $datos + [
            'password' => 'nuevaClave123456', 'password_confirmation' => 'distinta',
        ])->assertSessionHasErrors('password');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $admin->fresh()->password));

        $this->put(route('admin.update'), $datos + [
            'password' => 'nuevaClave123456', 'password_confirmation' => 'nuevaClave123456',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('nuevaClave123456', $admin->fresh()->password));
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('password', $admin->fresh()->password));
    }

    public function test_admin_conserva_password_si_no_la_cambia(): void
    {
        $admin = User::create([
            'name' => 'Admin | Prueba', 'email' => 'admin.sin.cambio@example.test',
            'password' => 'password', 'rol' => 'admin',
        ]);
        $hash = $admin->password;
        $this->actingAs($admin)->put(route('admin.update'), [
            'nombre' => 'Admin', 'apellido' => 'Prueba', 'email' => $admin->email,
            'password' => '', 'password_confirmation' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame($hash, $admin->fresh()->password);
    }
}

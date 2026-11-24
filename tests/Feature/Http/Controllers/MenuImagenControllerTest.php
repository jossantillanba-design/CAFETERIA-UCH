<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\MenuComponente;
use App\Models\MenuDia;
use App\Models\MenuDiaOpcion;
use App\Models\MenuImagen;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuImagenControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function menuConPlatos(int $cantidad): array
    {
        $menu = MenuDia::factory()->create();
        $platos = MenuComponente::factory()->fondo()->count($cantidad)->create();

        foreach ($platos as $plato) {
            MenuDiaOpcion::factory()->create(['menu_id' => $menu->id, 'componente_id' => $plato->id]);
        }

        return [$menu, $platos];
    }

    public function test_employee_cannot_upload_menu_images(): void
    {
        Storage::fake('public');
        [$menu, $platos] = $this->menuConPlatos(1);

        $this->actingAs(User::factory()->empleado()->create())
            ->post('/admin/menus/'.$menu->id.'/imagenes', [
                'componente_id' => $platos[0]->id,
                'imagen' => UploadedFile::fake()->image('plato.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_admin_uploads_one_image_per_main_dish_without_a_limit(): void
    {
        Storage::fake('public');
        [$menu, $platos] = $this->menuConPlatos(6);
        $admin = User::factory()->administrador()->create();

        foreach ($platos as $plato) {
            $this->actingAs($admin)
                ->postJson('/admin/menus/'.$menu->id.'/imagenes', [
                    'componente_id' => $plato->id,
                    'imagen' => UploadedFile::fake()->image('plato.png'),
                ])
                ->assertCreated();
        }

        $this->assertSame(6, $menu->imagenes()->count());

        foreach ($menu->imagenes as $imagen) {
            Storage::disk('public')->assertExists($imagen->ruta);
        }
    }

    public function test_uploading_again_replaces_the_previous_file(): void
    {
        Storage::fake('public');
        [$menu, $platos] = $this->menuConPlatos(1);
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->postJson('/admin/menus/'.$menu->id.'/imagenes', [
            'componente_id' => $platos[0]->id,
            'imagen' => UploadedFile::fake()->image('uno.jpg'),
        ])->assertCreated();

        $anterior = $menu->imagenes()->first()->ruta;

        $this->actingAs($admin)->postJson('/admin/menus/'.$menu->id.'/imagenes', [
            'componente_id' => $platos[0]->id,
            'imagen' => UploadedFile::fake()->image('dos.jpg'),
        ])->assertCreated();

        $this->assertSame(1, $menu->imagenes()->count());
        Storage::disk('public')->assertMissing($anterior);
    }

    public function test_image_type_and_size_are_validated(): void
    {
        Storage::fake('public');
        [$menu, $platos] = $this->menuConPlatos(1);
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->postJson('/admin/menus/'.$menu->id.'/imagenes', [
            'componente_id' => $platos[0]->id,
            'imagen' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        ])->assertJsonValidationErrors('imagen');

        $this->actingAs($admin)->postJson('/admin/menus/'.$menu->id.'/imagenes', [
            'componente_id' => $platos[0]->id,
            'imagen' => UploadedFile::fake()->image('enorme.jpg')->size(3000),
        ])->assertJsonValidationErrors('imagen');

        $this->assertSame(0, $menu->imagenes()->count());
    }

    public function test_image_requires_a_dish_enabled_in_that_menu(): void
    {
        Storage::fake('public');
        [$menu] = $this->menuConPlatos(1);
        $ajeno = MenuComponente::factory()->fondo()->create();

        $this->actingAs(User::factory()->administrador()->create())
            ->postJson('/admin/menus/'.$menu->id.'/imagenes', [
                'componente_id' => $ajeno->id,
                'imagen' => UploadedFile::fake()->image('plato.jpg'),
            ])
            ->assertJsonValidationErrors('componente_id');
    }

    public function test_admin_reorders_and_deletes_images(): void
    {
        Storage::fake('public');
        [$menu, $platos] = $this->menuConPlatos(3);
        $admin = User::factory()->administrador()->create();

        foreach ($platos as $plato) {
            $this->actingAs($admin)->postJson('/admin/menus/'.$menu->id.'/imagenes', [
                'componente_id' => $plato->id,
                'imagen' => UploadedFile::fake()->image('plato.jpg'),
            ])->assertCreated();
        }

        $nuevoOrden = [$platos[2]->id, $platos[0]->id, $platos[1]->id];

        $this->actingAs($admin)
            ->postJson('/admin/menus/'.$menu->id.'/imagenes/orden', ['orden' => $nuevoOrden])
            ->assertOk();

        $this->assertSame($nuevoOrden, $menu->imagenes()->pluck('componente_id')->all());

        $imagen = $menu->imagenes()->first();
        $ruta = $imagen->ruta;

        $this->actingAs($admin)->deleteJson('/admin/menus/imagenes/'.$imagen->id)->assertOk();

        $this->assertNull(MenuImagen::find($imagen->id));
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_admin_creates_a_new_main_dish_from_the_menu_screen(): void
    {
        $menu = MenuDia::factory()->create();

        $this->actingAs(User::factory()->administrador()->create())
            ->postJson('/admin/menus/'.$menu->id.'/componentes', ['tiempo' => 'fondo', 'nombre' => 'Tallarín saltado'])
            ->assertCreated();

        $componente = MenuComponente::where('nombre', 'Tallarín saltado')->firstOrFail();

        $this->assertDatabaseHas('menu_dia_opciones', [
            'menu_id' => $menu->id,
            'componente_id' => $componente->id,
            'habilitado' => true,
            'stock_raciones' => 0,
        ]);
    }

    public function test_public_menu_exposes_the_carousel_only_for_enabled_main_dishes(): void
    {
        Storage::fake('public');
        [$menu, $platos] = $this->menuConPlatos(2);
        $menu->update(['activo' => true]);
        $admin = User::factory()->administrador()->create();

        foreach ($platos as $plato) {
            $this->actingAs($admin)->postJson('/admin/menus/'.$menu->id.'/imagenes', [
                'componente_id' => $plato->id,
                'imagen' => UploadedFile::fake()->image('plato.jpg'),
            ])->assertCreated();
        }

        $menu->opciones()->where('componente_id', $platos[1]->id)->update(['habilitado' => false]);

        $response = $this->get('/menu');

        $carrusel = collect($response->viewData('menusMenu'))->firstWhere('id', $menu->id)['carrusel'];

        $this->assertCount(1, $carrusel);
        $this->assertSame($platos[0]->nombre, $carrusel[0]['nombre']);
    }
}

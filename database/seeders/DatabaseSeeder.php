<?php
namespace Database\Seeders;
use App\Models\{Configuracion, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // 1. Configuracion global
        $configs = [
            ['clave'=>'nombre_empresa',          'valor'=>'Gas Zeta, S.A.'],
            ['clave'=>'departamento_sistemas',    'valor'=>'Direccion de Sistemas'],
            ['clave'=>'ciclo_renovacion_meses',   'valor'=>'18'],
            ['clave'=>'alerta_anticipacion_meses','valor'=>'2'],
        ];
        foreach ($configs as $c) Configuracion::firstOrCreate(['clave'=>$c['clave']],['valor'=>$c['valor']]);
        $this->command->info('Configuracion: lista.');

        // 2. Usuario admin
        User::firstOrCreate(['email'=>'admin@gesticell.com'],[
            'name'     => 'Administrador Principal',
            'password' => Hash::make('Admin1234!'),
            'rol'      => 'admin',
        ]);
        $this->command->info('Usuario admin: listo.');

        // 3. Departamentos
        $this->call(DepartamentoSeeder::class);

        // 4. Bloques (obligatorio antes de dispositivos)
        $this->call(BloqueSeeder::class);

        // 5. Dispositivos con bloque
        $this->call(DispositivoSeeder::class);
    }
}
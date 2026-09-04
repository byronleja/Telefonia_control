<?php
namespace Database\Seeders;
use App\Models\Bloque;
use Illuminate\Database\Seeder;

class BloqueSeeder extends Seeder {
    public function run(): void {
        $bloques = [
            // Alta gama
            ['nombre'=>'Bloque Gerencial','descripcion'=>'Dispositivos para gerentes y directores','gama'=>'alta','costo_mensual_linea'=>450.00,'operadora'=>'Claro','color_etiqueta'=>'#7c3aed','activo'=>true],
            ['nombre'=>'Bloque Ejecutivo','descripcion'=>'Dispositivos para jefes y coordinadores','gama'=>'alta','costo_mensual_linea'=>350.00,'operadora'=>'Claro','color_etiqueta'=>'#2563eb','activo'=>true],
            // Media gama
            ['nombre'=>'Bloque Profesional','descripcion'=>'Dispositivos para profesionales y tecnicos','gama'=>'media','costo_mensual_linea'=>250.00,'operadora'=>'Tigo','color_etiqueta'=>'#059669','activo'=>true],
            ['nombre'=>'Bloque Comercial','descripcion'=>'Dispositivos para ventas y atencion al cliente','gama'=>'media','costo_mensual_linea'=>200.00,'operadora'=>'Tigo','color_etiqueta'=>'#d97706','activo'=>true],
            // Basica
            ['nombre'=>'Bloque Operativo','descripcion'=>'Dispositivos para operaciones y logistica','gama'=>'basica','costo_mensual_linea'=>120.00,'operadora'=>'Movistar','color_etiqueta'=>'#64748b','activo'=>true],
        ];

        foreach ($bloques as $data) {
            Bloque::firstOrCreate(['nombre' => $data['nombre']], $data);
        }
        $this->command->info('Bloques: '.count($bloques).' creados.');
    }
}
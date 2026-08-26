<?php
namespace Database\Seeders;
use App\Models\Departamento;
use Illuminate\Database\Seeder;
class DepartamentoSeeder extends Seeder {
    public function run(): void {
        $data=[['nombre'=>'Tecnologia e Informatica','descripcion'=>'Gestion de sistemas y redes'],['nombre'=>'Recursos Humanos','descripcion'=>'Gestion del talento humano'],['nombre'=>'Ventas','descripcion'=>'Equipo comercial y ventas'],['nombre'=>'Finanzas y Contabilidad','descripcion'=>'Gestion financiera y contable'],['nombre'=>'Operaciones','descripcion'=>'Operaciones y logistica'],['nombre'=>'Gerencia General','descripcion'=>'Alta direccion'],['nombre'=>'Atencion al Cliente','descripcion'=>'Servicio y soporte a clientes'],['nombre'=>'Marketing','descripcion'=>'Mercadeo y comunicacion']];
        foreach($data as $d) Departamento::firstOrCreate(['nombre'=>$d['nombre']],array_merge($d,['activo'=>true]));
    }
}
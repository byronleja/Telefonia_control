<?php
namespace Database\Seeders;
use App\Models\{Bloque, Dispositivo};
use Illuminate\Database\Seeder;

class DispositivoSeeder extends Seeder {
    public function run(): void {
        $gerencial   = Bloque::where('nombre','Bloque Gerencial')->first();
        $ejecutivo   = Bloque::where('nombre','Bloque Ejecutivo')->first();
        $profesional = Bloque::where('nombre','Bloque Profesional')->first();
        $comercial   = Bloque::where('nombre','Bloque Comercial')->first();
        $operativo   = Bloque::where('nombre','Bloque Operativo')->first();

        $dispositivos = [
            // Alta gama - Gerencial
            ['numero_serie'=>'SN-GER-001','marca'=>'Apple','modelo'=>'iPhone 15 Pro Max','imei'=>'352000001111111','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Claro','numero_telefonico'=>'5555-0001','color'=>'Negro','fecha_compra'=>'2025-01-10','costo'=>9500.00,'meses_renovacion'=>24,'bloque_id'=>$gerencial?->id],
            ['numero_serie'=>'SN-GER-002','marca'=>'Apple','modelo'=>'iPhone 15 Pro','imei'=>'352000001111112','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Claro','numero_telefonico'=>'5555-0002','color'=>'Titanio','fecha_compra'=>'2025-01-10','costo'=>8500.00,'meses_renovacion'=>24,'bloque_id'=>$gerencial?->id],
            ['numero_serie'=>'SN-GER-003','marca'=>'Samsung','modelo'=>'Galaxy S24 Ultra','imei'=>'352000001111113','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Claro','numero_telefonico'=>'5555-0003','color'=>'Gris','fecha_compra'=>'2025-02-01','costo'=>8800.00,'meses_renovacion'=>24,'bloque_id'=>$gerencial?->id],

            // Alta gama - Ejecutivo
            ['numero_serie'=>'SN-EJE-001','marca'=>'Apple','modelo'=>'iPhone 15','imei'=>'352000002222221','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Claro','numero_telefonico'=>'5555-0011','color'=>'Azul','fecha_compra'=>'2025-01-15','costo'=>7200.00,'meses_renovacion'=>24,'bloque_id'=>$ejecutivo?->id],
            ['numero_serie'=>'SN-EJE-002','marca'=>'Samsung','modelo'=>'Galaxy S24+','imei'=>'352000002222222','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Claro','numero_telefonico'=>'5555-0012','color'=>'Negro','fecha_compra'=>'2025-01-15','costo'=>6800.00,'meses_renovacion'=>24,'bloque_id'=>$ejecutivo?->id],
            ['numero_serie'=>'SN-EJE-003','marca'=>'Samsung','modelo'=>'Galaxy S24','imei'=>'352000002222223','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Claro','numero_telefonico'=>'5555-0013','color'=>'Violeta','fecha_compra'=>'2025-03-01','costo'=>5500.00,'meses_renovacion'=>24,'bloque_id'=>$ejecutivo?->id],

            // Media gama - Profesional
            ['numero_serie'=>'SN-PRO-001','marca'=>'Samsung','modelo'=>'Galaxy A55','imei'=>'352000003333331','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0021','color'=>'Azul marino','fecha_compra'=>'2024-06-01','costo'=>2800.00,'meses_renovacion'=>18,'bloque_id'=>$profesional?->id],
            ['numero_serie'=>'SN-PRO-002','marca'=>'Samsung','modelo'=>'Galaxy A54','imei'=>'352000003333332','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0022','color'=>'Negro','fecha_compra'=>'2024-06-01','costo'=>2600.00,'meses_renovacion'=>18,'bloque_id'=>$profesional?->id],
            ['numero_serie'=>'SN-PRO-003','marca'=>'Motorola','modelo'=>'Edge 40','imei'=>'352000003333333','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0023','color'=>'Gris','fecha_compra'=>'2024-07-15','costo'=>2400.00,'meses_renovacion'=>18,'bloque_id'=>$profesional?->id],
            ['numero_serie'=>'SN-PRO-004','marca'=>'Xiaomi','modelo'=>'Redmi Note 13 Pro','imei'=>'352000003333334','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0024','color'=>'Blanco','fecha_compra'=>'2024-08-01','costo'=>2200.00,'meses_renovacion'=>18,'bloque_id'=>$profesional?->id],

            // Media gama - Comercial
            ['numero_serie'=>'SN-COM-001','marca'=>'Samsung','modelo'=>'Galaxy A35','imei'=>'352000004444441','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0031','color'=>'Azul','fecha_compra'=>'2024-05-01','costo'=>2000.00,'meses_renovacion'=>18,'bloque_id'=>$comercial?->id],
            ['numero_serie'=>'SN-COM-002','marca'=>'Samsung','modelo'=>'Galaxy A34','imei'=>'352000004444442','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0032','color'=>'Negro','fecha_compra'=>'2024-05-01','costo'=>1900.00,'meses_renovacion'=>18,'bloque_id'=>$comercial?->id],
            ['numero_serie'=>'SN-COM-003','marca'=>'Motorola','modelo'=>'Moto G84','imei'=>'352000004444443','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0033','color'=>'Grafito','fecha_compra'=>'2024-06-15','costo'=>1800.00,'meses_renovacion'=>18,'bloque_id'=>$comercial?->id],
            ['numero_serie'=>'SN-COM-004','marca'=>'Xiaomi','modelo'=>'Redmi Note 13','imei'=>'352000004444444','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Tigo','numero_telefonico'=>'5555-0034','color'=>'Negro','fecha_compra'=>'2024-07-01','costo'=>1700.00,'meses_renovacion'=>18,'bloque_id'=>$comercial?->id],

            // Basica - Operativo
            ['numero_serie'=>'SN-OPE-001','marca'=>'Nokia','modelo'=>'C32','imei'=>'352000005555551','tipo'=>'basico','estado'=>'disponible','operadora'=>'Movistar','numero_telefonico'=>'5555-0041','color'=>'Negro','fecha_compra'=>'2024-03-01','costo'=>700.00,'meses_renovacion'=>12,'bloque_id'=>$operativo?->id],
            ['numero_serie'=>'SN-OPE-002','marca'=>'Nokia','modelo'=>'C22','imei'=>'352000005555552','tipo'=>'basico','estado'=>'disponible','operadora'=>'Movistar','numero_telefonico'=>'5555-0042','color'=>'Gris','fecha_compra'=>'2024-03-01','costo'=>600.00,'meses_renovacion'=>12,'bloque_id'=>$operativo?->id],
            ['numero_serie'=>'SN-OPE-003','marca'=>'Samsung','modelo'=>'Galaxy A05','imei'=>'352000005555553','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Movistar','numero_telefonico'=>'5555-0043','color'=>'Negro','fecha_compra'=>'2024-04-01','costo'=>800.00,'meses_renovacion'=>12,'bloque_id'=>$operativo?->id],
            ['numero_serie'=>'SN-OPE-004','marca'=>'Alcatel','modelo'=>'1B','imei'=>'352000005555554','tipo'=>'basico','estado'=>'disponible','operadora'=>'Movistar','numero_telefonico'=>'5555-0044','color'=>'Azul','fecha_compra'=>'2024-04-01','costo'=>500.00,'meses_renovacion'=>12,'bloque_id'=>$operativo?->id],
            ['numero_serie'=>'SN-OPE-005','marca'=>'Alcatel','modelo'=>'3L','imei'=>'352000005555555','tipo'=>'smartphone','estado'=>'disponible','operadora'=>'Movistar','numero_telefonico'=>'5555-0045','color'=>'Negro','fecha_compra'=>'2024-05-01','costo'=>650.00,'meses_renovacion'=>12,'bloque_id'=>$operativo?->id],
        ];

        $creados = 0;
        foreach ($dispositivos as $data) {
            if (!$data['bloque_id']) continue; // Omitir si no se encontró el bloque
            Dispositivo::firstOrCreate(['numero_serie' => $data['numero_serie']], $data);
            $creados++;
        }
        $this->command->info("Dispositivos: {$creados} creados (".(count($dispositivos)-$creados)." omitidos sin bloque).");
    }
}
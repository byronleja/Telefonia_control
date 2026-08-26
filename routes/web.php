<?php
use App\Http\Controllers\{AsignacionController,AuthController,BusquedaController,CartaCustodiaController,ConfiguracionController,DepartamentoController,DispositivoController,EmpleadoController,EmpleadoImportController,ExportController,ImportController,RenovacionController,RenovacionEntregaController,ReporteController,SolicitudEntregaController,UsuarioController};
use Illuminate\Support\Facades\Route;

Route::get('login',[AuthController::class,'showLogin'])->name('login');
Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout'])->name('logout');

Route::middleware('auth')->group(function(){
    Route::get('/',fn()=>redirect()->route('reportes.index'));
    Route::get('buscar',[BusquedaController::class,'index'])->name('busqueda.index');

    Route::resource('empleados',EmpleadoController::class)->parameters(['empleados'=>'empleado']);

    // Rutas especificas de dispositivos ANTES del resource
    Route::get('dispositivos/{dispositivo}/carta',[CartaCustodiaController::class,'desde_dispositivo'])->name('dispositivos.carta');
    Route::get('dispositivos/{dispositivo}/historial',[AsignacionController::class,'historial'])->name('dispositivos.historial');
    Route::get('dispositivos/{dispositivo}/baja',[DispositivoController::class,'confirmarBaja'])->name('dispositivos.confirmar_baja');
    Route::post('dispositivos/{dispositivo}/baja',[DispositivoController::class,'procesarBaja'])->name('dispositivos.procesar_baja');
    Route::post('dispositivos/{dispositivo}/restaurar',[DispositivoController::class,'restaurar'])->name('dispositivos.restaurar');
    Route::resource('dispositivos',DispositivoController::class)->parameters(['dispositivos'=>'dispositivo']);

    Route::get('asignaciones',[AsignacionController::class,'index'])->name('asignaciones.index');
    Route::get('asignaciones/crear',[AsignacionController::class,'create'])->name('asignaciones.create');
    Route::post('asignaciones',[AsignacionController::class,'store'])->name('asignaciones.store');
    Route::get('dispositivos/{dispositivo}/devolver',[AsignacionController::class,'devolver'])->name('asignaciones.devolver');
    Route::post('dispositivos/{dispositivo}/devolver',[AsignacionController::class,'procesarDevolucion'])->name('asignaciones.procesar_devolucion');

    // Entrega por Renovacion ANTES del resource de renovaciones
    Route::get('renovaciones/entrega/crear',[RenovacionEntregaController::class,'create'])->name('renovaciones.entregar')->middleware('solo_admin');
    Route::post('renovaciones/entrega',[RenovacionEntregaController::class,'store'])->name('renovaciones.entregar.store')->middleware('solo_admin');
    Route::get('renovaciones/{renovacion}/carta',[CartaCustodiaController::class,'desde_renovacion'])->name('renovaciones.carta');
    Route::delete('adjuntos/{adjunto}',[RenovacionController::class,'eliminarAdjunto'])->name('adjuntos.destroy');
    Route::resource('renovaciones',RenovacionController::class)->parameters(['renovaciones'=>'renovacion']);

    Route::get('importar',[ImportController::class,'index'])->name('importar.index');
    Route::post('importar',[ImportController::class,'store'])->name('importar.store');
    Route::get('importar/plantilla',[ImportController::class,'plantilla'])->name('importar.plantilla');
    Route::get('importar/empleados',[EmpleadoImportController::class,'index'])->name('importar.empleados');
    Route::post('importar/empleados',[EmpleadoImportController::class,'store'])->name('importar.empleados.store');
    Route::get('importar/empleados/plantilla',[EmpleadoImportController::class,'plantilla'])->name('importar.empleados.plantilla');

    Route::get('exportar/empleados',[ExportController::class,'empleados'])->name('exportar.empleados');
    Route::get('exportar/dispositivos',[ExportController::class,'dispositivos'])->name('exportar.dispositivos');
    Route::get('exportar/renovaciones',[ExportController::class,'renovaciones'])->name('exportar.renovaciones');
    Route::get('exportar/alertas-renovacion',[ExportController::class,'alertasRenovacion'])->name('exportar.alertas');

    Route::prefix('reportes')->name('reportes.')->group(function(){
        Route::get('/',[ReporteController::class,'index'])->name('index');
        Route::get('renovaciones',[ReporteController::class,'renovaciones'])->name('renovaciones');
        Route::get('dispositivos',[ReporteController::class,'dispositivos'])->name('dispositivos');
        Route::get('alertas',[ReporteController::class,'alertas'])->name('alertas');
        Route::get('equipos-danados',[ReporteController::class,'equiposDanados'])->name('equipos_danados');
        Route::get('comprados',[ReporteController::class,'comprados'])->name('comprados');
    });

    // Solicitudes de Entrega
    Route::get('solicitudes',[SolicitudEntregaController::class,'index'])->name('solicitudes.index');
    Route::get('solicitudes/crear',[SolicitudEntregaController::class,'create'])->name('solicitudes.create')->middleware('solo_contabilidad');
    Route::post('solicitudes',[SolicitudEntregaController::class,'store'])->name('solicitudes.store')->middleware('solo_contabilidad');
    Route::get('solicitudes/{solicitude}',[SolicitudEntregaController::class,'show'])->name('solicitudes.show');
    Route::post('solicitudes/{solicitude}/entregar',[SolicitudEntregaController::class,'entregar'])->name('solicitudes.entregar')->middleware('solo_admin');
    Route::post('solicitudes/{solicitude}/rechazar',[SolicitudEntregaController::class,'rechazar'])->name('solicitudes.rechazar')->middleware('solo_admin');

    Route::middleware('solo_admin')->group(function(){
        Route::resource('departamentos',DepartamentoController::class)->parameters(['departamentos'=>'departamento']);
        Route::resource('usuarios',UsuarioController::class)->parameters(['usuarios'=>'usuario']);
        Route::get('configuracion',[ConfiguracionController::class,'index'])->name('configuracion.index');
        Route::put('configuracion',[ConfiguracionController::class,'update'])->name('configuracion.update');
    });
});

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,Hash,DB};
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users','rol')) {
            Schema::table('users', function(Blueprint $t) {
                $t->enum('rol',['admin','visualizador'])->default('visualizador')->after('email');
            });
        }
        if (!DB::table('users')->where('email','admin@gesticell.com')->exists()) {
            DB::table('users')->insert(['name'=>'Administrador','email'=>'admin@gesticell.com',
                'password'=>Hash::make('Admin1234!'),'rol'=>'admin',
                'email_verified_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
        }
    }
    public function down(): void {
        Schema::table('users', function(Blueprint $t) { $t->dropColumn('rol'); });
    }
};
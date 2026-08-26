<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = ['name','email','password','rol'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed'];
    public function esAdmin(): bool        { return $this->rol === 'admin'; }
    public function esVisualizador(): bool { return $this->rol === 'visualizador'; }
    public function esContabilidad(): bool { return $this->rol === 'contabilidad'; }
    public function getRolLabelAttribute(): string {
        return match($this->rol) {
            'admin'        => 'Administrador',
            'visualizador' => 'Visualizador',
            'contabilidad' => 'Contabilidad',
            default        => ucfirst($this->rol),
        };
    }
}
<?php
namespace App\Models;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Members_permission_group extends Model implements Authenticatable
{
    use HasFactory, AuthenticatableTrait, Notifiable;
    protected $fillable = [
        'member_id',
        'client_permission_group',
        'nutriflow_permission_group',
        'created_by',
        'updated_by'
    ];

}

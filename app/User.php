<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
    ];

    protected $appends = [
        'cpf'
     ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function person()
    {
        return $this->belongsTo('App\Person');
    }
    
    public function profile()
    {
        return $this->hasOne('App\Profile');
    }
    
    public function hasRole($id)
    {
        return $this->profile->role->id == (int)$id;
    }

    public function getCpfAttribute()
    {
        return $this->person->getAttribute('cpf');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
    */
    public function sendPasswordResetNotification($token)
    {
        $name = $this->person->name;
        $this->notify(new Notifications\MailResetPasswordNotification($token, $name));
    }
}

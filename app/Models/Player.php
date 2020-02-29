<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Player extends Eloquent
{

   
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'players';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /**
    * The primary key used by the model.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
 

    protected $guarded = ['created_at' , 'updated_at' , 'id' ];


    public function teama()
    {
        return $this->hasOne('App\Models\TeamASquad', 'player_id', 'pid') ;
    }
    public function teamb()
    {
       return $this->hasOne('App\Models\TeamBSquad', 'player_id', 'pid') ;
    }
}

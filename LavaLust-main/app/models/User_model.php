<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class User_model extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $fillable = ['username', 'email', 'password', 'role'];
}
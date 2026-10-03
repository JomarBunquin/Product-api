<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Product_model extends Model
{
    protected $table = 'products';
    protected $primary_key = 'id';
    protected $fillable = ['product_name', 'description', 'price', 'quantity'];

    public function get_all_products()
    {
        return $this->db->table($this->table)->order_by('id', 'DESC')->get_all();
    }
}
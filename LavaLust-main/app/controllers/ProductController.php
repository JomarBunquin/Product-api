<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
        $this->call->model('Product_model');

        // Every product endpoint requires a valid JWT. No token = 401 and stop.
        $this->api->require_jwt();
    }

    private function json_body(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    private function validated(array $in, bool $partial = false): array
    {
        $clean = [];
        $errors = [];

        if (!$partial || array_key_exists('product_name', $in)) {
            $name = trim((string)($in['product_name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 100) {
                $errors[] = 'product_name is required (max 100 characters)';
            } else {
                $clean['product_name'] = $name;
            }
        }
        if (!$partial || array_key_exists('description', $in)) {
            $clean['description'] = trim((string)($in['description'] ?? ''));
        }
        if (!$partial || array_key_exists('price', $in)) {
            $price = $in['price'] ?? null;
            if (!is_numeric($price) || $price < 0 || $price > 99999999.99) {
                $errors[] = 'price must be a number from 0 to 99999999.99';
            } else {
                $clean['price'] = round((float)$price, 2);
            }
        }
        if (!$partial || array_key_exists('quantity', $in)) {
            $qty = filter_var($in['quantity'] ?? null, FILTER_VALIDATE_INT);
            if ($qty === false || $qty < 0) {
                $errors[] = 'quantity must be a whole number, 0 or more';
            } else {
                $clean['quantity'] = $qty;
            }
        }

        if ($errors) {
            $this->api->respond_error(implode('; ', $errors), 422);
        }
        return $clean;
    }

    // GET /api/products
    public function index()
    {
        $this->api->require_method('GET');
        $this->api->respond(['data' => $this->Product_model->get_all_products()]);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = $this->Product_model->find($id);
        if (!$product) {
            $this->api->respond_error('Product not found', 404);
        }
        $this->api->respond(['data' => $product]);
    }

    // POST /api/products
    public function store()
    {
        $this->api->require_method('POST');
        $data = $this->validated($this->json_body());

        $id = $this->Product_model->insert($data);
        $this->api->respond([
            'message' => 'Product created',
            'data'    => $this->Product_model->find($id),
        ], 201);
    }

    // PUT/PATCH /api/products/{id}
    public function update($id)
    {
        if (!$this->Product_model->find($id)) {
            $this->api->respond_error('Product not found', 404);
        }
        $data = $this->validated($this->json_body(), true);
        if (!$data) {
            $this->api->respond_error('Nothing to update', 422);
        }

        $this->Product_model->update($id, $data);
        $this->api->respond([
            'message' => 'Product updated',
            'data'    => $this->Product_model->find($id),
        ]);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        if (!$this->Product_model->find($id)) {
            $this->api->respond_error('Product not found', 404);
        }
        $this->Product_model->delete($id);
        $this->api->respond(['message' => 'Product deleted']);
    }
}
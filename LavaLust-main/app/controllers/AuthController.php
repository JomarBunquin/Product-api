<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
        $this->call->model('User_model');
    }

    // Raw JSON (the API library's body() HTML-escapes passwords and text)
    private function json_body(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    public function register()
    {
        $this->api->require_method('POST');
        $in = $this->json_body();

        $username = trim((string)($in['username'] ?? ''));
        $email    = trim((string)($in['email'] ?? ''));
        $password = (string)($in['password'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            $this->api->respond_error('Username must be 3-50 letters, numbers, . _ -', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->api->respond_error('A valid email is required', 422);
        }
        if (strlen($password) < 6) {
            $this->api->respond_error('Password must be at least 6 characters', 422);
        }
        if ($this->User_model->find_by('username', $username)) {
            $this->api->respond_error('Username already taken', 409);
        }
        if ($this->User_model->find_by('email', $email)) {
            $this->api->respond_error('Email already registered', 409);
        }

        $id = $this->User_model->insert([
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role'     => 'user',
        ]);

        $this->api->respond(['message' => 'Account created', 'id' => $id], 201);
    }

    public function login()
    {
        $this->api->require_method('POST');
        $in = $this->json_body();

        $login    = trim((string)($in['login'] ?? $in['username'] ?? ''));
        $password = (string)($in['password'] ?? '');

        if ($login === '' || $password === '') {
            $this->api->respond_error('Username/email and password are required', 422);
        }

        $user = $this->User_model->find_by('username', $login)
             ?: $this->User_model->find_by('email', $login);

        if (!$user || !(int)$user['is_active'] || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Invalid credentials', 401);
        }

        $tokens = $this->api->issue_tokens([
            'id'     => $user['id'],
            'role'   => $user['role'],
            'scopes' => ['read', 'write', 'delete'],
        ]);

        $this->api->respond([
            'message' => 'Login successful',
            'user'    => ['id' => $user['id'], 'username' => $user['username'], 'email' => $user['email']],
            'tokens'  => $tokens,
        ]);
    }

    public function logout()
    {
        $this->api->require_method('POST');
        $this->api->require_jwt();
        $in = $this->json_body();

        if (!empty($in['refresh_token'])) {
            $this->api->revoke_refresh_token($in['refresh_token']);
        }
        $this->api->respond(['message' => 'Logged out']);
    }
}
<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/*
| Enable the API library (required, otherwise it shows "Api Helper is disabled")
*/
$config['api_helper_enabled'] = TRUE;

/*
| Access token lifetime in seconds (1 hour)
*/
$config['payload_token_expiration'] = 3600;

/*
| Refresh token lifetime in seconds (7 days)
*/
$config['refresh_token_expiration'] = 604800;

/*
| Secrets are read from .env (never hardcode them)
*/
$config['jwt_secret']        = getenv('JWT_SECRET') ?: '';
$config['refresh_token_key'] = getenv('REFRESH_TOKEN_KEY') ?: '';

/*
| CORS: "*" for local testing, your frontend URL once deployed
*/
$config['allow_origin'] = getenv('ALLOW_ORIGIN') ?: '*';

/*
| Table that stores refresh tokens (created by migration 002)
*/
$config['refresh_token_table'] = 'refresh_tokens';

/*
| JWT claims
*/
$config['jwt_issuer']   = 'product-api';
$config['jwt_audience'] = 'product-api-clients';

/*
| Rate limiting: 60 requests per 60 seconds per IP
*/
$config['rate_limit_enabled']  = true;
$config['rate_limit_requests'] = 60;
$config['rate_limit_seconds']  = 60;
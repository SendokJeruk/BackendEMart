# Authentication API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

## Endpoints

### Login
- **Endpoint:** `/auth/login`
- **Method:** `POST`
- **Description:** Authenticate user and return an access token.
- **Request:** `email`, `password`
- **Response:** Authentication token and user details

### Register
- **Endpoint:** `/auth/register`
- **Method:** `POST`
- **Description:** Register a new user and return an access token.
- **Request:** `name`, `email`, `password`, `no_telp`
- **Response:** Authentication token and user details

## Notes
- Use the token for authenticated requests.
- Include the token in the `Authorization` header.


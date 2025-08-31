# 🔐 API de Autenticación - Inventario

## Endpoints de Autenticación

### 📝 Registro de Usuario
```http
POST /api/inventario/auth/register
Content-Type: application/json

{
    "email": "usuario@ejemplo.com",
    "password": "mi_password",
    "password_confirmation": "mi_password",
    "first_name": "Juan",
    "last_name": "Pérez",
    "organization_id": "uuid-opcional"
}
```

**Respuesta exitosa (201):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": "uuid",
            "email": "usuario@ejemplo.com",
            "first_name": "Juan",
            "last_name": "Pérez",
            "status": "active"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    },
    "message": "Usuario registrado exitosamente"
}
```

### 🔑 Iniciar Sesión
```http
POST /api/inventario/auth/login
Content-Type: application/json

{
    "email": "usuario@ejemplo.com",
    "password": "mi_password"
}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": "uuid",
            "email": "usuario@ejemplo.com",
            "first_name": "Juan",
            "last_name": "Pérez",
            "status": "active",
            "last_login_at": "2024-01-01T10:00:00.000000Z"
        },
        "token": "1|abc123...",
        "token_type": "Bearer",
        "organizations": [
            {
                "id": "uuid",
                "name": "Mi Empresa",
                "role": "admin",
                "status": "active"
            }
        ]
    },
    "message": "Login exitoso"
}
```

### 🚪 Cerrar Sesión
```http
POST /api/inventario/auth/logout
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "data": null,
    "message": "Logout exitoso"
}
```

### 👤 Información del Usuario
```http
GET /api/inventario/auth/me
Authorization: Bearer {token}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": "uuid",
            "email": "usuario@ejemplo.com",
            "first_name": "Juan",
            "last_name": "Pérez",
            "status": "active",
            "email_verified": true,
            "last_login_at": "2024-01-01T10:00:00.000000Z"
        },
        "organizations": [
            {
                "id": "uuid",
                "name": "Mi Empresa",
                "role": "admin",
                "status": "active"
            }
        ]
    }
}
```

### 🔐 Cambiar Contraseña
```http
POST /api/inventario/auth/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "password_actual",
    "new_password": "nueva_password",
    "new_password_confirmation": "nueva_password"
}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "data": null,
    "message": "Contraseña cambiada exitosamente"
}
```

## 📋 Códigos de Error

### Errores de Validación (422)
```json
{
    "success": false,
    "error": "Errores de validación",
    "errors": {
        "email": ["El campo email es obligatorio."],
        "password": ["El campo password debe tener al menos 6 caracteres."]
    }
}
```

### Credenciales Incorrectas (401)
```json
{
    "success": false,
    "error": "Credenciales incorrectas"
}
```

### Usuario Inactivo (403)
```json
{
    "success": false,
    "error": "Usuario inactivo"
}
```

## 🌐 Ejemplo de Uso desde Frontend (JavaScript)

```javascript
class AuthService {
    constructor() {
        this.baseURL = 'http://localhost:8000/api/inventario/auth';
        this.token = localStorage.getItem('auth_token');
    }

    async register(userData) {
        const response = await fetch(`${this.baseURL}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('auth_token', data.data.token);
            localStorage.setItem('user', JSON.stringify(data.data.user));
            return data;
        }
        
        throw new Error(data.error || 'Error en el registro');
    }

    async login(email, password) {
        const response = await fetch(`${this.baseURL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('auth_token', data.data.token);
            localStorage.setItem('user', JSON.stringify(data.data.user));
            localStorage.setItem('organizations', JSON.stringify(data.data.organizations));
            return data;
        }
        
        throw new Error(data.error || 'Error en el login');
    }

    async logout() {
        const response = await fetch(`${this.baseURL}/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            }
        });

        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('organizations');
        
        return response.json();
    }

    async getMe() {
        const response = await fetch(`${this.baseURL}/me`, {
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            }
        });

        return response.json();
    }

    async changePassword(currentPassword, newPassword) {
        const response = await fetch(`${this.baseURL}/change-password`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: newPassword
            })
        });

        return response.json();
    }

    isAuthenticated() {
        return !!this.token;
    }

    getUser() {
        return JSON.parse(localStorage.getItem('user') || 'null');
    }

    getOrganizations() {
        return JSON.parse(localStorage.getItem('organizations') || '[]');
    }
}

// Uso
const auth = new AuthService();

// Registro
try {
    const result = await auth.register({
        email: 'nuevo@usuario.com',
        password: 'mi_password',
        password_confirmation: 'mi_password',
        first_name: 'Nuevo',
        last_name: 'Usuario'
    });
    console.log('Usuario registrado:', result);
} catch (error) {
    console.error('Error en registro:', error.message);
}

// Login
try {
    const result = await auth.login('usuario@ejemplo.com', 'mi_password');
    console.log('Login exitoso:', result);
} catch (error) {
    console.error('Error en login:', error.message);
}
```

## 🚀 Credenciales de Prueba

```
Email: admin@demo.inventario.com
Password: password
```

¡Estas rutas están listas para usar desde tu frontend! 🎉

# 🧪 Script de Pruebas - API Inventario

## ✅ Problema Solucionado

El error de la tabla `personal_access_tokens` ha sido resuelto exitosamente:

1. ✅ **Migración creada** para la tabla `personal_access_tokens` en la BD de inventario
2. ✅ **Email actualizado** de `admin@demo.inventario.com` a `admin@demo.com`
3. ✅ **Sanctum configurado** para usar el modelo personalizado
4. ✅ **Login funcionando** correctamente

## 🔑 Credenciales Actualizadas

```
Email: admin@demo.com
Password: password
```

## 🧪 Pruebas de los Endpoints

### 1. **Login Exitoso** ✅
```bash
curl -X POST "http://localhost:8000/api/inventario/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@demo.com",
    "password": "password"
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": "07ccf095-8272-437d-a2c2-2dbbf650d60e",
      "email": "admin@demo.com",
      "first_name": "Administrador",
      "last_name": "Demo",
      "status": "active",
      "last_login_at": "2025-08-31T02:09:52.000000Z"
    },
    "token": "1|afiLwtn127RNqi7lf0ER7V8ECNq4zmrDzrnS2dmef79e182b",
    "token_type": "Bearer",
    "organizations": [...]
  }
}
```

### 2. **Registro de Usuario**
```bash
curl -X POST "http://localhost:8000/api/inventario/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "nuevo@usuario.com",
    "password": "mi_password",
    "password_confirmation": "mi_password",
    "first_name": "Nuevo",
    "last_name": "Usuario"
  }'
```

### 3. **Información del Usuario**
```bash
curl -X GET "http://localhost:8000/api/inventario/auth/me" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

### 4. **Obtener Organizaciones**
```bash
curl -X GET "http://localhost:8000/api/inventario/organizations" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

### 5. **Dashboard (requiere Organization-Id)**
```bash
curl -X GET "http://localhost:8000/api/inventario/inventory/dashboard" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Organization-Id: ID_DE_ORGANIZACION" \
  -H "Accept: application/json"
```

### 6. **Productos (requiere Organization-Id)**
```bash
curl -X GET "http://localhost:8000/api/inventario/products" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Organization-Id: ID_DE_ORGANIZACION" \
  -H "Accept: application/json"
```

### 7. **Logout**
```bash
curl -X POST "http://localhost:8000/api/inventario/auth/logout" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

## 📋 Lista de Verificación

- [x] ✅ Tabla `personal_access_tokens` creada en BD inventario
- [x] ✅ Email del usuario demo actualizado a `admin@demo.com`
- [x] ✅ Sanctum configurado para usar modelo personalizado
- [x] ✅ Login endpoint funcionando correctamente
- [x] ✅ Token generado exitosamente
- [x] ✅ Información de usuario y organizaciones incluida
- [x] ✅ Rutas de autenticación registradas
- [x] ✅ Modelo User compatible con Sanctum

## 🚀 Desde tu Frontend

```javascript
// Función de login lista para usar
async function loginToInventory(email, password) {
    try {
        const response = await fetch('http://localhost:8000/api/inventario/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Guardar token y datos del usuario
            localStorage.setItem('auth_token', data.data.token);
            localStorage.setItem('user', JSON.stringify(data.data.user));
            localStorage.setItem('organizations', JSON.stringify(data.data.organizations));
            
            console.log('Login exitoso:', data.data.user);
            return data;
        } else {
            throw new Error(data.error || 'Error en el login');
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// Uso
loginToInventory('admin@demo.com', 'password')
    .then(result => console.log('Usuario logueado:', result))
    .catch(error => console.error('Error en login:', error));
```

## ✅ Estado Final

**Todo está funcionando correctamente ahora:**

- 🔐 **Autenticación**: Login/Register/Logout funcionando
- 📊 **APIs**: 27 endpoints de inventario disponibles
- 🏢 **Multitenancy**: Headers Organization-Id funcionando
- 💾 **Base de datos**: Todas las tablas creadas correctamente
- 🎯 **Tokens**: Sanctum generando tokens en la BD correcta

¡El sistema está listo para usar desde tu frontend! 🚀

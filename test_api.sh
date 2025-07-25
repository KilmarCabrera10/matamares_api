#!/bin/bash

echo "🚀 Iniciando pruebas de la API Matamares..."
echo "=============================================="

BASE_URL="https://api.matamares.com/api"

echo ""
echo "1. 🧪 Probando ruta de prueba básica..."
curl -s "$BASE_URL/test" | jq .

echo ""
echo "2. 📦 Probando ruta de productos (pública)..."
curl -s "$BASE_URL/products" | jq .

echo ""
echo "3. 🔐 Probando estado CSRF..."
curl -s "$BASE_URL/csrf-status" | jq .

echo ""
echo "4. 📝 Probando registro de usuario (POST)..."
curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Usuario Prueba",
    "email": "prueba@matamares.com",
    "password": "password123",
    "password_confirmation": "password123"
  }' | jq .

echo ""
echo "5. 🔑 Probando login..."
curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "prueba@matamares.com",
    "password": "password123"
  }' | jq .

echo ""
echo "✅ Pruebas completadas!"

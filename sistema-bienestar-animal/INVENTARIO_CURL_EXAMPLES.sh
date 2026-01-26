#!/bin/bash

# ============================================
# Scripts CURL para Testing API de Inventario
# ============================================
# Este archivo contiene ejemplos de llamadas
# a la API de inventario usando curl
#
# Prerequisitos:
# - curl instalado
# - Backend corriendo en http://localhost:8000
# - Token de autenticación válido
#
# Uso: Copiar comandos y ejecutar en terminal

# ============================================
# VARIABLES GLOBALES
# ============================================

API_URL="http://localhost:8000/api/v1"
TOKEN="tu_token_aqui"  # Reemplazar con token real

# Para obtener token:
# curl -X POST http://localhost:8000/api/v1/auth/login \
#   -H "Content-Type: application/json" \
#   -d '{"email":"admin@example.com","password":"password"}'

# ============================================
# 1. LISTAR INVENTARIO
# ============================================

# Listar todos los items
curl -X GET "$API_URL/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  | json_pp

# Listar con paginación
curl -X GET "$API_URL/inventario?per_page=50" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Listar con filtro de categoría
curl -X GET "$API_URL/inventario?categoria=Medicamento" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Listar solo items con stock bajo
curl -X GET "$API_URL/inventario?cantidad_baja=1" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Buscar por nombre
curl -X GET "$API_URL/inventario?busqueda=Amoxicilina" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# ============================================
# 2. LISTAR INSUMOS ESPECÍFICOS
# ============================================

curl -X GET "$API_URL/inventario/insumos?per_page=100" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Listar insumos con stock bajo
curl -X GET "$API_URL/inventario/insumos?stock_bajo=1" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Listar insumos próximos a vencer
curl -X GET "$API_URL/inventario/insumos?proximos_vencer=1&dias_vencimiento=30" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# ============================================
# 3. CREAR NUEVO MEDICAMENTO
# ============================================

curl -X POST "$API_URL/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "MED-001",
    "nombre": "Amoxicilina 500mg",
    "categoria": "Medicamento",
    "tipo": "Antibiótico",
    "unidad_medida": "comprimidos",
    "cantidad_actual": 100,
    "cantidad_minima": 20,
    "ubicacion": "Armario A - Estante 1",
    "fecha_vencimiento": "2025-12-31",
    "proveedor": "Farmacéutica XYZ"
  }' \
  | json_pp

# ============================================
# 4. CREAR MEDICAMENTO VACUNA
# ============================================

curl -X POST "$API_URL/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "VAC-001",
    "nombre": "Vacuna Antirrábica",
    "categoria": "Vacuna",
    "tipo": "Vacuna",
    "unidad_medida": "dosis",
    "cantidad_actual": 50,
    "cantidad_minima": 10,
    "fecha_vencimiento": "2026-06-30",
    "proveedor": "Laboratorio Veterinario"
  }' \
  | json_pp

# ============================================
# 5. ACTUALIZAR MEDICAMENTO
# ============================================

# Reemplazar ITEM_ID con el ID real
curl -X PUT "$API_URL/inventario/ITEM_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Amoxicilina 500mg - Actualizado",
    "cantidad_minima": 30,
    "ubicacion": "Armario B"
  }' \
  | json_pp

# ============================================
# 6. REGISTRAR ENTRADA (INGRESO)
# ============================================

# Registrar entrada de 50 unidades
curl -X POST "$API_URL/inventario/ITEM_ID/entrada" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "insumo",
    "cantidad": 50,
    "motivo": "Reposición de stock - Compra a proveedor"
  }' \
  | json_pp

# Entrada con cantidad decimal
curl -X POST "$API_URL/inventario/ITEM_ID/entrada" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "medicamento",
    "cantidad": 25.50,
    "motivo": "Ingreso parcial"
  }' \
  | json_pp

# ============================================
# 7. REGISTRAR SALIDA (CONSUMO)
# ============================================

# Registrar salida de 10 unidades
curl -X POST "$API_URL/inventario/ITEM_ID/salida" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "insumo",
    "cantidad": 10,
    "motivo": "Uso en consulta veterinaria"
  }' \
  | json_pp

# Salida para procedimiento quirúrgico
curl -X POST "$API_URL/inventario/ITEM_ID/salida" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "medicamento",
    "cantidad": 5,
    "motivo": "Procedimiento quirúrgico - Código: CIRUGIA-123"
  }' \
  | json_pp

# ============================================
# 8. VERIFICAR DISPONIBILIDAD DE STOCK
# ============================================

# Verificar si hay 50 unidades disponibles
curl -X GET "$API_URL/inventario/verificar-stock?tipo=insumo&id=ITEM_ID&cantidad=50" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# ============================================
# 9. OBTENER ALERTAS
# ============================================

# Items con stock bajo
curl -X GET "$API_URL/inventario/stock-bajo" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Items próximos a vencer (30 días)
curl -X GET "$API_URL/inventario/proximos-vencer?dias=30" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Items próximos a vencer (15 días)
curl -X GET "$API_URL/inventario/proximos-vencer?dias=15" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# Items ya vencidos
curl -X GET "$API_URL/inventario/vencidos" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# ============================================
# 10. OBTENER ESTADÍSTICAS
# ============================================

curl -X GET "$API_URL/inventario/estadisticas" \
  -H "Authorization: Bearer $TOKEN" \
  | json_pp

# ============================================
# 11. CASOS DE PRUEBA COMPLEJOS
# ============================================

# --- Caso 1: Flujo completo de medicamento ---
echo "=== CASO 1: Crear medicamento, registrar entrada y verificar ==="

# 1. Crear medicamento
RESPONSE=$(curl -s -X POST "$API_URL/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "TEST-'$(date +%s)'",
    "nombre": "Medicamento Test",
    "categoria": "Medicamento",
    "tipo": "Test",
    "unidad_medida": "unidades",
    "cantidad_actual": 10,
    "cantidad_minima": 5,
    "fecha_vencimiento": "2026-12-31"
  }')

echo "$RESPONSE" | json_pp
ITEM_ID=$(echo "$RESPONSE" | grep -o '"id":"[^"]*"' | cut -d'"' -f4 | head -1)
echo "Item ID: $ITEM_ID"

# 2. Registrar entrada
curl -s -X POST "$API_URL/inventario/$ITEM_ID/entrada" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "insumo",
    "cantidad": 50,
    "motivo": "Ingreso inicial"
  }' | json_pp

# 3. Verificar stock
curl -s -X GET "$API_URL/inventario/verificar-stock?tipo=insumo&id=$ITEM_ID&cantidad=30" \
  -H "Authorization: Bearer $TOKEN" | json_pp

# --- Caso 2: Alertas en tiempo real ---
echo "=== CASO 2: Obtener todas las alertas ==="

# Stock bajo
echo "--- Items con stock bajo ---"
curl -s -X GET "$API_URL/inventario/stock-bajo" \
  -H "Authorization: Bearer $TOKEN" | json_pp

# Próximos a vencer
echo "--- Items próximos a vencer ---"
curl -s -X GET "$API_URL/inventario/proximos-vencer?dias=30" \
  -H "Authorization: Bearer $TOKEN" | json_pp

# Vencidos
echo "--- Items vencidos ---"
curl -s -X GET "$API_URL/inventario/vencidos" \
  -H "Authorization: Bearer $TOKEN" | json_pp

# ============================================
# 12. MANEJO DE ERRORES
# ============================================

echo "=== PRUEBAS DE ERRORES ==="

# Error: Código duplicado
echo "--- Error: Código duplicado ---"
curl -X POST "$API_URL/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "MED-001",
    "nombre": "Duplicado",
    "categoria": "Medicamento",
    "tipo": "Test",
    "unidad_medida": "unidades",
    "cantidad_actual": 10,
    "cantidad_minima": 5
  }' \
  | json_pp

# Error: Campos requeridos faltantes
echo "--- Error: Campo requerido faltante ---"
curl -X POST "$API_URL/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "TEST-123",
    "categoria": "Medicamento"
  }' \
  | json_pp

# Error: Stock insuficiente
echo "--- Error: Stock insuficiente ---"
curl -X POST "$API_URL/inventario/ITEM_ID/salida" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "insumo",
    "cantidad": 10000,
    "motivo": "Prueba de error"
  }' \
  | json_pp

# ============================================
# NOTAS IMPORTANTES
# ============================================

# 1. Reemplazar TOKEN con token real
# 2. Reemplazar ITEM_ID con ID válido del item
# 3. Para obtener IDs: ver respuesta de creación
# 4. Para depuración: agregar -v al curl
# 5. Para guardar en archivo: > output.json
# 6. Para enviar archivo: -d @data.json

# ============================================
# UTILIDADES
# ============================================

# Obtener token (necesario para todas las operaciones)
# LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
#   -H "Content-Type: application/json" \
#   -d '{
#     "email": "admin@example.com",
#     "password": "password"
#   }')
#
# TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
# echo "Token obtenido: $TOKEN"

# Hacer request con debugging
# curl -v -X GET "$API_URL/inventario/estadisticas" \
#   -H "Authorization: Bearer $TOKEN"

# Ver solo headers de respuesta
# curl -i -X GET "$API_URL/inventario/estadisticas" \
#   -H "Authorization: Bearer $TOKEN"

# ============================================
# FIN DE EJEMPLOS
# ============================================

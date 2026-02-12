# Informe de Integracion SCI - Bienestar Animal
**Fecha:** 2026-02-12
**Autor:** Claude Code

## Resumen
Se integro el Sistema Central de Interoperabilidad (SCI) en la aplicacion de Bienestar Animal. Se crearon 2 endpoints protegidos por token que el SCI consultara periodicamente para obtener KPIs y datos estadisticos en tiempo real. La implementacion sigue los patrones existentes del proyecto (Service Layer, controladores bajo Api/V1, queries basadas en modelos Eloquent con scopes).

## Archivos Creados
| Archivo | Descripcion |
|---------|-------------|
| `backend/config/sci.php` | Archivo de configuracion con app_id y api_token |
| `backend/app/Http/Middleware/VerifySciToken.php` | Middleware de autenticacion por Bearer Token para endpoints SCI |
| `backend/app/Services/SciService.php` | Service con logica de negocio para KPIs, resumen, tendencias y detalle |
| `backend/app/Http/Controllers/Api/V1/SciController.php` | Controlador con metodos `kpis()` y `data()` |
| `backend/tests/Feature/SciIntegrationTest.php` | Tests de integracion (auth 401, KPIs, data, filtros de fecha, tipo detalle) |

## Archivos Modificados
| Archivo | Cambio realizado |
|---------|-----------------|
| `backend/bootstrap/app.php` | Se agrego el alias `auth.sci` para el middleware `VerifySciToken` |
| `backend/routes/api.php` | Se agrego import de `SciController` y grupo de rutas `/sci` con middleware `auth.sci` |
| `backend/.env.example` | Se agregaron variables `SCI_APP_ID` y `SCI_API_TOKEN` |

## Variables de Entorno Agregadas
| Variable | Descripcion | Valor por defecto |
|----------|-------------|-------------------|
| `SCI_APP_ID` | Identificador de la aplicacion en el SCI | `bienestar-animal` |
| `SCI_API_TOKEN` | Token de autenticacion compartido con el SCI | (vacio) |

## Modelos Utilizados
| Modelo | Usado para |
|--------|-----------|
| `Animal\Animal` | KPI `total_animales`, resumen de animales en refugio, detalle por especie/estado |
| `Adopcion\Adopcion` | KPI `adopciones_mes`, resumen de adopciones completadas, tendencias vs mes anterior |
| `Denuncia\Denuncia` | KPI `denuncias_activas`, resumen de denuncias resueltas, detalle por tipo/prioridad |
| `Veterinaria\Consulta` | KPI `consultas_hoy`, detalle de consultas por periodo |

## Scopes Reutilizados
| Modelo | Scope | Uso |
|--------|-------|-----|
| `Animal` | `enRefugio()` | Animales actualmente en refugio |
| `Animal` | `disponiblesAdopcion()` | Animales disponibles para adopcion |
| `Animal` | `adoptados()` | Animales adoptados |
| `Adopcion` | `completadas()` | Adopciones con estado 'completada' |
| `Adopcion` | `delMes()` | Adopciones del mes actual |
| `Adopcion` | `pendientes()` | Adopciones con estado 'solicitada' |
| `Adopcion` | `enEvaluacion()` | Adopciones en evaluacion |
| `Denuncia` | `pendientes()` | Denuncias no resueltas/cerradas/desestimadas |
| `Denuncia` | `resueltas()` | Denuncias resueltas |
| `Consulta` | `deHoy()` | Consultas veterinarias del dia |

## Modelos Creados
Ninguno. Todos los modelos necesarios ya existian en el proyecto.

## Endpoints Disponibles
| Metodo | URL | Descripcion |
|--------|-----|-------------|
| GET | `/api/sci/kpis` | KPIs en tiempo real (4 indicadores obligatorios) |
| GET | `/api/sci/data` | Datos y estadisticas (resumen + tendencias) |
| GET | `/api/sci/data?tipo=detalle` | Datos detallados por modulo |
| GET | `/api/sci/data?tipo=resumen` | Solo resumen (default) |
| GET | `/api/sci/data?fecha_inicio=YYYY-MM-DD&fecha_fin=YYYY-MM-DD` | Filtrado por rango de fechas |

## Como Probar

### 1. Configurar token en `.env`
```env
SCI_API_TOKEN=mi-token-secreto-compartido
```

### 2. Limpiar cache de configuracion
```bash
php artisan config:clear
```

### 3. Verificar rutas registradas
```bash
php artisan route:list --path=sci
```

### 4. Probar con cURL
```bash
# KPIs
curl -H "Authorization: Bearer mi-token-secreto-compartido" \
     http://localhost:8000/api/sci/kpis

# Data (resumen)
curl -H "Authorization: Bearer mi-token-secreto-compartido" \
     http://localhost:8000/api/sci/data

# Data (detalle)
curl -H "Authorization: Bearer mi-token-secreto-compartido" \
     "http://localhost:8000/api/sci/data?tipo=detalle"

# Data con filtro de fechas
curl -H "Authorization: Bearer mi-token-secreto-compartido" \
     "http://localhost:8000/api/sci/data?fecha_inicio=2026-01-01&fecha_fin=2026-01-31"

# Sin token (debe retornar 401)
curl http://localhost:8000/api/sci/kpis
```

### 5. Ejecutar tests
```bash
php artisan test --filter=SciIntegrationTest
```

## Notas y Observaciones

1. **Rutas fuera de /v1**: Los endpoints SCI se registraron en `/api/sci/` (no en `/api/v1/sci/`) para cumplir con la especificacion del SCI que espera `/api/sci/kpis` y `/api/sci/data`.

2. **Formato de respuesta propio**: Los endpoints SCI NO usan el `ApiResponse` helper del proyecto porque el SCI espera un formato especifico con `app_id`, `timestamp` y estructura propia. El formato de error tambien es especifico del SCI.

3. **Service Pattern**: Se creo `SciService` siguiendo el patron existente del proyecto (similar a `ReporteService`). Las queries reutilizan los scopes ya definidos en los modelos.

4. **Sin cache**: No se implemento cache de KPIs dado que el proyecto usa SQLite y las queries son ligeras. Si se migra a MySQL con volumen de datos alto, considerar agregar `Cache::remember()` con TTL de 5 minutos.

5. **Middleware independiente**: `VerifySciToken` es independiente de Sanctum. Usa un token fijo compartido via variable de entorno, no tokens de usuario.

6. **KPI `adopciones_mes`**: Cuenta adopciones con estado `completada` del mes actual usando los scopes `completadas()->delMes()`. Si el SCI prefiere contar aprobadas + completadas, ajustar en `SciService::getKpis()`.

7. **KPI `denuncias_activas`**: Usa el scope `pendientes()` que excluye estados `resuelta`, `cerrada` y `desestimada`, lo cual representa correctamente las denuncias activas/en curso.

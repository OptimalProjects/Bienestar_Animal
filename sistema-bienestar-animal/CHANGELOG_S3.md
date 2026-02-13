# Informe de Integración S3/MinIO - Bienestar Animal

**Fecha:** 2026-02-12
**Autor:** Claude Code
**Rama:** s3int

## Resumen

Se integró almacenamiento S3 compatible al proyecto Sistema Bienestar Animal utilizando MinIO como emulador local de AWS S3. Todos los archivos (fotos de animales, certificados veterinarios, contratos de adopción, documentos de identidad, firmas, PDFs de visitas y devoluciones, reportes diarios) ahora se almacenan en un bucket S3 en lugar del disco local.

La migración fue diseñada para que al recibir credenciales reales de AWS S3, solo se cambien las variables de entorno sin modificar código.

---

## Archivos Creados

| Archivo | Descripción |
|---------|-------------|
| `backend/app/Services/FileService.php` | Servicio centralizado para manejo de archivos en S3 (upload, getUrl, delete, exists, put, download, etc.) |

## Archivos Modificados

### Docker

| Archivo | Cambio realizado |
|---------|-----------------|
| `docker/docker-compose.yml` | Agregados servicios `minio` (MinIO server) y `minio-init` (creador de bucket). Agregado volumen `minio_data`. Usa la red `bienestar_network` existente. |

### Configuración / Entorno

| Archivo | Cambio realizado |
|---------|-----------------|
| `backend/.env` | Actualizado: `FILESYSTEM_DISK=s3`, credenciales MinIO (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`), agregadas `AWS_ENDPOINT`, `AWS_URL`, `AWS_USE_PATH_STYLE_ENDPOINT=true` |
| `backend/.env.example` | Mismos cambios que .env para documentar las variables requeridas |
| `backend/composer.json` | Agregada dependencia `league/flysystem-aws-s3-v3` (^3.31) |

### Servicios (app/Services/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `AnimalService.php` | `guardarFoto()`, `guardarGaleria()`, `actualizar()`: Migrados de `disk('public')` a `disk('s3')`. Paths cambiados a `public/animales/fotos` y `public/animales/galeria` |
| `AdopcionService.php` | `guardarDocumento()`: Migrado de `disk('public')` a `disk('s3')`. Path cambiado a `documentos/adoptantes/{cedulas,domicilios}` |
| `ContratoAdopcionService.php` | Todos los `Storage::disk('public')` migrados a `disk('s3')`. Paths cambiados: contratos a `documentos/contratos/`, firmas a `documentos/firmas/` |
| `VisitaSeguimientoService.php` | `generarPdfResumen()`: Migrado a `disk('s3')`. Path cambiado a `documentos/visitas/pdf/` |
| `DevolucionService.php` | `generarPdfResumen()`: Migrado a `disk('s3')`. Path cambiado a `documentos/devoluciones/pdf/` |

### Controladores (app/Http/Controllers/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `Animal/AnimalController.php` | `adjuntarCertificadoEsterilizacion()`, `obtenerCertificadoEsterilizacion()`, `descargarCertificadoEsterilizacion()`: Todos migrados de `disk('public')` a `disk('s3')`. Path de certificados a `documentos/certificados/esterilizacion` |
| `Animal/AnimalCertificateController.php` | `attachSterilizationCertificate()`, `downloadCertificate()`, `deleteCertificate()`: Migrados a `disk('s3')`. Path a `documentos/certificados/esterilizacion` |
| `Veterinary/CertificadoVeterinarioController.php` | `adjuntar()`, `obtenerPorAnimal()`, `eliminarCertificado()`, `descargar()`: Migrados a `disk('s3')`. Paths a `documentos/certificados/{esterilizacion,cirugias,vacunas}` |
| `Adoptions/AdopcionController.php` | Todas las referencias `Storage::disk('public')` migradas a `disk('s3')` (contrato URLs, existencia, descarga) |
| `Adoptions/VisitaSeguimientoController.php` | Upload de fotos de respaldo y URLs de contratos: Migrados a `disk('s3')`. Path de fotos a `documentos/visitas/fotos` |

### Modelos (app/Models/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `Animal/Animal.php` | `getFotoUrlAttribute()`, `getGaleriaUrlsAttribute()`: Cambiados de `url('storage/' . $path)` a `Storage::disk('s3')->url($path)`. Agregado import de Storage facade. |
| `Adopcion/Adoptante.php` | `getCopiaCedulaUrlAttribute()`, `getComprobanteDomicilioUrlAttribute()`: Cambiados de `url('storage/' . $path)` a `Storage::disk('s3')->url($path)`. Agregado import de Storage facade. |

### Mail (app/Mail/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `ContratoFirmadoMail.php` | Cambiado de `attach()` con path local a `attachData()` leyendo contenido desde S3 |
| `DevolucionRegistradaMail.php` | Cambiado de `attach()` con path local a `attachData()` leyendo contenido desde S3 |
| `VisitaRealizadaMail.php` | Cambiado de `attach()` con path local a `attachData()` leyendo contenido desde S3 |

### Jobs (app/Jobs/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `GenerateDailyReport.php` | `Storage::put()` (disco default) cambiado a `Storage::disk('s3')->put()`. Path a `documentos/reportes/` |

---

## Variables de Entorno Agregadas/Modificadas

| Variable | Descripción | Valor local (MinIO) | Valor producción (AWS) |
|----------|-------------|---------------------|------------------------|
| `FILESYSTEM_DISK` | Disco de almacenamiento por defecto | `s3` | `s3` |
| `AWS_ACCESS_KEY_ID` | Clave de acceso S3 | `minioadmin` | *(clave real de AWS)* |
| `AWS_SECRET_ACCESS_KEY` | Secreto de acceso S3 | `minioadmin` | *(secreto real de AWS)* |
| `AWS_DEFAULT_REGION` | Región AWS | `us-east-1` | *(región del bucket)* |
| `AWS_BUCKET` | Nombre del bucket | `bienestar-animal` | *(nombre del bucket real)* |
| `AWS_ENDPOINT` | Endpoint S3 (solo MinIO) | `http://minio:9000` | *(eliminar o dejar vacío)* |
| `AWS_URL` | URL base para URLs públicas | `http://localhost:9000/bienestar-animal` | *(URL de CloudFront o S3)* |
| `AWS_USE_PATH_STYLE_ENDPOINT` | Estilo de path (requerido por MinIO) | `true` | `false` |

---

## Modelos Afectados

| Modelo | Campo(s) de archivo | Cambio |
|--------|---------------------|--------|
| `Animal` | `foto_principal`, `galeria_fotos`, `certificado_esterilizacion` | Accessors `getFotoUrlAttribute` y `getGaleriaUrlsAttribute` ahora usan `Storage::disk('s3')->url()` |
| `Adoptante` | `copia_cedula`, `comprobante_domicilio` | Accessors `getCopiaCedulaUrlAttribute` y `getComprobanteDomicilioUrlAttribute` ahora usan `Storage::disk('s3')->url()` |
| `Adopcion` | `contrato_url` | URLs generadas con `Storage::disk('s3')->url()` en controladores/servicios |
| `VisitaDomiciliaria` | `fotos_respaldo` (json) | Fotos se almacenan en S3, URLs generadas con `Storage::disk('s3')->url()` |
| `Cirugia` | `certificados` (json array) | Certificados se almacenan en S3, URLs generadas con `Storage::disk('s3')->url()` |
| `Vacuna` | `certificados` (json array) | Certificados se almacenan en S3, URLs generadas con `Storage::disk('s3')->url()` |

---

## Estructura de Carpetas en S3

```
bienestar-animal/                          (bucket)
├── public/
│   ├── animales/
│   │   ├── fotos/                         # Fotos principales de animales
│   │   └── galeria/                       # Galerías de fotos de animales
├── documentos/
│   ├── adoptantes/
│   │   ├── cedulas/                       # Copias de cédulas de adoptantes
│   │   └── domicilios/                    # Comprobantes de domicilio
│   ├── certificados/
│   │   ├── esterilizacion/                # Certificados de esterilización
│   │   ├── cirugias/                      # Certificados de cirugías
│   │   └── vacunas/                       # Certificados de vacunación
│   ├── contratos/                         # Contratos de adopción (PDF)
│   ├── firmas/                            # Imágenes de firmas digitales
│   ├── visitas/
│   │   ├── pdf/                           # PDFs de resumen de visitas
│   │   └── fotos/                         # Fotos de respaldo de visitas
│   ├── devoluciones/
│   │   └── pdf/                           # PDFs de resumen de devoluciones
│   └── reportes/                          # Reportes diarios JSON
```

---

## Cómo Cambiar a AWS S3 en Producción

1. **Obtener credenciales de AWS:**
   - Access Key ID
   - Secret Access Key
   - Nombre del bucket
   - Región del bucket

2. **Actualizar `.env` en producción:**
   ```env
   AWS_ACCESS_KEY_ID=AKIA_TU_CLAVE_REAL
   AWS_SECRET_ACCESS_KEY=tu_secreto_real
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=nombre-bucket-produccion
   AWS_ENDPOINT=
   AWS_URL=https://nombre-bucket-produccion.s3.us-east-1.amazonaws.com
   AWS_USE_PATH_STYLE_ENDPOINT=false
   ```

3. **Eliminar las variables de MinIO:**
   - `AWS_ENDPOINT` debe quedar **vacío** o eliminarse
   - `AWS_USE_PATH_STYLE_ENDPOINT` debe ser `false`

4. **Si se usa CloudFront CDN:**
   ```env
   AWS_URL=https://d1234567890.cloudfront.net
   ```

5. **Crear el bucket en AWS S3** con las mismas carpetas descritas arriba.

6. **Configurar permisos del bucket:**
   - Carpeta `public/` debe tener policy de lectura pública
   - Carpeta `documentos/` debe ser privada (acceso solo por URLs firmadas si es necesario)

7. **No se requiere cambiar código** - Solo variables de entorno.

---

## Dependencia Instalada

```
league/flysystem-aws-s3-v3: ^3.31
└── aws/aws-sdk-php: 3.369.33
```

---

## Servicios Docker Agregados

### MinIO Server
- **Imagen:** `minio/minio:latest`
- **Puertos:** `9000` (API S3), `9001` (Consola web)
- **Credenciales:** `minioadmin` / `minioadmin`
- **Volumen:** `minio_data` (persistente)
- **Consola web:** http://localhost:9001

### MinIO Init
- **Imagen:** `minio/mc` (MinIO Client)
- **Función:** Crea el bucket `bienestar-animal` automáticamente al iniciar
- **Configura:** Policy de descarga pública para la carpeta `public/`

---

## Notas y Observaciones

1. **Compatibilidad total:** MinIO implementa la API de S3 al 100%, por lo que el código funciona idénticamente con MinIO local y AWS S3 real.

2. **URLs de archivos:**
   - `AWS_ENDPOINT=http://minio:9000` es la URL interna entre contenedores Docker
   - `AWS_URL=http://localhost:9000/bienestar-animal` es la URL para el navegador del usuario

3. **Adjuntos en correos:** Los Mailables se actualizaron para usar `attachData()` en lugar de `attach()` con path local, ya que con S3 los archivos no están en el filesystem local.

4. **FileService creado** en `app/Services/FileService.php` como servicio centralizado. Aunque la migración actual usa `Storage::disk('s3')` directamente (para minimizar cambios), el FileService está disponible para uso futuro o refactoring gradual.

5. **config/filesystems.php** ya tenía el disco `s3` configurado correctamente por defecto de Laravel, incluyendo `endpoint` y `use_path_style_endpoint`. No requirió cambios.

6. **Seeders y SCI:** No se tocaron seeders ni la integración SCI según las instrucciones.

7. **Archivos existentes en disco local:** Los archivos previamente almacenados en `storage/app/public/` no se migran automáticamente. Si existen datos en producción, se necesitará un script de migración que copie los archivos al bucket S3.

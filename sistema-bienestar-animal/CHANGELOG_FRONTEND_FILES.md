# Informe de Conexión Frontend ↔ Backend para Archivos S3

**Fecha:** 2026-02-12
**Autor:** Claude Code
**Rama:** s3int

## Resumen

Se conectó el frontend (Vue.js 3) con el backend (Laravel) para el manejo de archivos almacenados en S3/MinIO. Los cambios principales fueron:

1. Actualización de resolución de URLs de imágenes para soportar URLs absolutas de S3
2. Implementación del composable `useFileUpload.js`
3. Corrección de la resolución de URLs de galería en componentes de visualización
4. Preparación del formulario de denuncias para envío de archivos de evidencia

---

## Archivos Creados

| Archivo | Descripción |
|---------|-------------|
| `frontend/src/composables/useFileUpload.js` | Composable reutilizable para manejo de carga de archivos con validación, previews, progreso y gestión de estado |

## Archivos Modificados

### Utilidades (src/utils/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `animalImages.js` | `resolveAnimalImageUrl()`: Eliminado fallback a `/storage/` para paths relativos. Los archivos están en S3 y el backend retorna URLs absolutas vía accessors. Paths relativos ahora muestran placeholder por especie. |

### Componentes de Animales (src/components/animals/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `AnimalDetail.vue` | `galeriaFotos` computed: Simplificado para usar `resolveAnimalImageUrl()` directamente. Eliminada lógica manual de `/storage/` que era incompatible con S3. Usa `galeria_urls` (accessor del backend con URLs S3 completas). |

### Componentes de Adopciones (src/components/adoptions/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `AdoptionList.vue` | Eliminada función `resolveGalleryUrl()` con lógica `/storage/` obsoleta. `getGalleryPhotos()` ahora usa `resolveAnimalImageUrl()` directamente. Removido import no utilizado de `getSpeciesPlaceholder`. |

### Componentes de Denuncias (src/components/complaints/)

| Archivo | Cambio realizado |
|---------|-----------------|
| `ComplaintForm.vue` | `onSubmit()`: Los archivos de evidencia ahora se incluyen en el envío. Si hay evidencias, se construye FormData con campos de texto + archivos (`evidencias[]`). Si no hay evidencias, se envía JSON como antes. |

---

## Composable `useFileUpload.js` - API

| Propiedad/Método | Tipo | Descripción |
|-------------------|------|-------------|
| `files` | `Ref<File[]>` | Array reactivo de archivos seleccionados |
| `previews` | `Ref<string[]>` | URLs de preview (ObjectURL para imágenes) |
| `uploading` | `Ref<boolean>` | Estado de carga |
| `progress` | `Ref<number>` | Progreso de upload (0-100) |
| `error` | `Ref<string>` | Mensaje de error |
| `hasFiles` | `ComputedRef<boolean>` | Si hay archivos seleccionados |
| `fileCount` | `ComputedRef<number>` | Cantidad de archivos |
| `addFiles(files)` | `Function` | Agrega archivos con validación |
| `setFile(file)` | `Function` | Reemplaza con un solo archivo |
| `removeFile(index)` | `Function` | Elimina archivo por índice |
| `clear()` | `Function` | Limpia todos los archivos |
| `appendToFormData(fd, name, multiple)` | `Function` | Agrega archivos a FormData existente |
| `startUpload()` | `Function` | Marca inicio de upload |
| `finishUpload()` | `Function` | Marca fin exitoso |
| `failUpload(msg)` | `Function` | Marca error en upload |

### Ejemplo de uso:

```javascript
import { useFileUpload } from '@/composables/useFileUpload';

const photos = useFileUpload({
  maxSizeMB: 2,
  maxFiles: 5,
  allowedTypes: ['image/jpeg', 'image/png'],
});

// Agregar archivos
photos.addFiles(event.target.files);

// Agregar a FormData
const fd = new FormData();
photos.appendToFormData(fd, 'fotos', true); // fotos[]
```

---

## Flujo de URLs de Imágenes (Post-S3)

```
Backend (Laravel)                          Frontend (Vue.js)
─────────────────                          ──────────────────
Animal model                               AnimalCard.vue / AnimalDetail.vue
  ├── foto_principal: "public/animales/fotos/abc.jpg"
  ├── getFotoUrlAttribute()                  foto_url → resolveAnimalImageUrl()
  │   └── Storage::disk('s3')->url(...)        ├── URL absoluta (http://...) → usar directo
  │       → "http://localhost:9000/           ├── null/vacío → placeholder por especie
  │          bienestar-animal/public/..."     └── path relativo → placeholder (S3 no accesible vía /storage/)
  │
  ├── galeria_fotos: ["public/animales/galeria/1.jpg", ...]
  └── getGaleriaUrlsAttribute()              galeria_urls → resolveAnimalImageUrl() por cada URL
      └── array_map(Storage::disk('s3')->url)
          → ["http://localhost:9000/...", ...]
```

---

## Componentes que YA funcionaban correctamente

| Componente | Funcionalidad | Por qué funciona |
|-----------|---------------|------------------|
| `AnimalForm.vue` | Upload foto_principal + galeria_fotos[] | Ya usa FormData correctamente con `api.post('/animals', fd)` |
| `AnimalCard.vue` | Mostrar foto de animal | Usa `foto_url` (accessor S3 → URL absoluta) que pasa por `resolveAnimalImageUrl()` |
| `AdoptionForm.vue` | Upload copia_cedula + comprobante_domicilio | Ya usa FormData vía `submitAdoptionRequest()` en adoptionService.js |
| `AttachCertificateForm.vue` | Upload certificado veterinario | Ya usa FormData con `api.post('/certificados-veterinarios', fd)` |
| `FileUploader.vue` | Componente genérico de selección de archivos | Componente UI que emite archivos seleccionados, no hace upload |

---

## Nota sobre Denuncias - Evidencias

El backend `DenunciaController@store` actualmente valida `evidencias.*` como `string` (no como `file`). Esto significa que:

- **Estado actual**: El controlador espera un array de strings, no archivos binarios
- **Cambio necesario en backend**: Para subir evidencias a S3, se debe cambiar la validación a:
  ```php
  'evidencias' => 'nullable|array',
  'evidencias.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
  ```
  Y agregar lógica de upload a S3 en `DenunciaService::registrar()`
- **Frontend preparado**: El `ComplaintForm.vue` ya envía los archivos como FormData cuando hay evidencias. Solo falta que el backend los acepte como archivos.

---

## Migración de `/storage/` a S3 - Resumen

| Patrón anterior (disco local) | Patrón nuevo (S3) |
|-------------------------------|-------------------|
| `url('storage/' . $path)` | `Storage::disk('s3')->url($path)` |
| Frontend: `/storage/animales/fotos/x.jpg` | Frontend: `http://localhost:9000/bienestar-animal/public/animales/fotos/x.jpg` |
| `resolveAnimalImageUrl()` → prepend `/storage/` | `resolveAnimalImageUrl()` → pass-through URL absoluta |
| Galería: manual `/storage/` concat | Galería: `galeria_urls` accessor retorna URLs completas |

---

## Dependencias

No se agregaron dependencias nuevas al frontend. Todos los cambios usan APIs nativas de Vue 3 y el cliente Axios existente.

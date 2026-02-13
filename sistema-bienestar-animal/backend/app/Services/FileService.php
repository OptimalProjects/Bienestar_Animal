<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class FileService
{
    protected string $disk = 's3';

    /**
     * Subir archivo a S3.
     *
     * @param UploadedFile $file      Archivo a subir
     * @param string       $folder    Carpeta destino (ej: 'public/animales/fotos')
     * @param string|null  $filename  Nombre personalizado (opcional)
     * @return string Path del archivo guardado
     */
    public function upload(UploadedFile $file, string $folder, ?string $filename = null): string
    {
        if ($filename) {
            $path = $file->storeAs($folder, $filename, $this->disk);
        } else {
            $path = $file->store($folder, $this->disk);
        }

        Log::debug('Archivo subido a S3', [
            'path' => $path,
            'folder' => $folder,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        return $path;
    }

    /**
     * Guardar contenido raw (ej: PDF generado, firma base64 decodificada).
     *
     * @param string $path     Path destino en el bucket
     * @param string $content  Contenido del archivo
     * @param string $visibility  'public' o 'private'
     * @return bool
     */
    public function put(string $path, string $content, string $visibility = 'private'): bool
    {
        $result = Storage::disk($this->disk)->put($path, $content, $visibility);

        Log::debug('Contenido guardado en S3', [
            'path' => $path,
            'visibility' => $visibility,
        ]);

        return $result;
    }

    /**
     * Obtener URL del archivo.
     * Si $minutes > 0, genera URL temporal firmada.
     * Si $minutes = 0, genera URL pública.
     *
     * @param string $path     Path del archivo en S3
     * @param int    $minutes  Minutos de validez para URL firmada (0 = pública)
     * @return string|null
     */
    public function getUrl(string $path, int $minutes = 0): ?string
    {
        if (!$path) {
            return null;
        }

        // Archivos privados se sirven a través del proxy del backend
        if ($minutes > 0) {
            return self::privateUrl($path);
        }

        // Archivos públicos usan URL directa de S3
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Generar URL firmada del proxy backend para archivos privados.
     *
     * Usa rutas firmadas de Laravel (APP_KEY) en vez de presigned URLs de S3.
     * El navegador puede acceder directamente sin token Bearer.
     * La URL expira en 30 minutos.
     *
     * @param string $path Path del archivo en S3
     * @return string URL firmada del endpoint proxy
     */
    public static function privateUrl(string $path): string
    {
        return URL::signedRoute('files.private', ['path' => $path], now()->addMinutes(30));
    }

    /**
     * Obtener el contenido de un archivo.
     *
     * @param string $path Path del archivo en S3
     * @return string|null
     */
    public function get(string $path): ?string
    {
        if (!Storage::disk($this->disk)->exists($path)) {
            return null;
        }

        return Storage::disk($this->disk)->get($path);
    }

    /**
     * Eliminar archivo de S3.
     *
     * @param string|null $path Path del archivo
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (!Storage::disk($this->disk)->exists($path)) {
            return false;
        }

        $result = Storage::disk($this->disk)->delete($path);

        Log::debug('Archivo eliminado de S3', ['path' => $path]);

        return $result;
    }

    /**
     * Verificar si un archivo existe.
     *
     * @param string|null $path
     * @return bool
     */
    public function exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Listar archivos en una carpeta.
     *
     * @param string $directory
     * @return array
     */
    public function files(string $directory): array
    {
        return Storage::disk($this->disk)->files($directory);
    }

    /**
     * Obtener respuesta de descarga.
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $path)
    {
        return Storage::disk($this->disk)->download($path);
    }

    /**
     * Obtener el disco de Storage subyacente.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk()
    {
        return Storage::disk($this->disk);
    }
}

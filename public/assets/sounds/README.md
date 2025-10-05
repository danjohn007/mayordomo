# Archivos de Sonido para Notificaciones

## Archivo Requerido

**notification.mp3** - Sonido que se reproduce cuando llega una nueva notificación

### Opciones para obtener el archivo de sonido:

1. **Descargar un sonido libre de derechos:**
   - Visita: https://freesound.org/ o https://mixkit.co/free-sound-effects/
   - Busca: "notification sound" o "alert sound"
   - Descarga un archivo MP3 corto (1-2 segundos)
   - Renombra el archivo a: `notification.mp3`
   - Coloca el archivo en este directorio

2. **Usar un generador de tonos:**
   - Visita: https://onlinetonegenerator.com/
   - Genera un tono simple (por ejemplo: 800 Hz, 0.5 segundos)
   - Exporta como MP3
   - Guarda como `notification.mp3` en este directorio

3. **Convertir desde otro formato:**
   - Si tienes un archivo WAV u otro formato
   - Usa https://cloudconvert.com/ para convertir a MP3
   - Guarda como `notification.mp3`

## Especificaciones Recomendadas

- Formato: MP3
- Duración: 0.5 - 2 segundos
- Tamaño: Menos de 50 KB
- Frecuencia: 44.1 kHz
- Bitrate: 128 kbps

## Archivo Temporal (para desarrollo)

Mientras tanto, el sistema funcionará sin el archivo de sonido. Las notificaciones visuales 
seguirán apareciendo, pero el sonido no se reproducirá hasta que se agregue el archivo.

## Permisos del Navegador

Nota: Los navegadores modernos requieren interacción del usuario antes de reproducir audio 
automáticamente. El primer sonido puede no reproducirse hasta que el usuario interactúe con la página.

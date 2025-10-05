#!/bin/bash
# Script to create a simple notification sound using sox (if available)
# If sox is not installed: sudo apt-get install sox

# Check if sox is installed
if ! command -v sox &> /dev/null; then
    echo "SOX no está instalado. Instalando..."
    echo "Ejecuta: sudo apt-get install sox"
    echo ""
    echo "O descarga manualmente desde: https://freesound.org/"
    exit 1
fi

# Generate a simple notification beep (800Hz, 0.3 seconds)
sox -n -r 44100 -c 1 notification.mp3 synth 0.3 sine 800 fade 0.05 0.3 0.05

echo "Archivo notification.mp3 creado exitosamente!"
echo "Tamaño: $(du -h notification.mp3 | cut -f1)"

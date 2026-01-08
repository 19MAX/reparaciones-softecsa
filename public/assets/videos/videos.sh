#!/bin/bash

# Optimizar video de laptops
ffmpeg -i laptops.mp4 \
  -c:v libx264 \
  -preset slow \
  -crf 22 \
  -pix_fmt yuv420p \
  -movflags +faststart \
  -g 1 \
  -keyint_min 1 \
  -vf "scale=1280:720" \
  -r 30 \
  -an \
  laptops_optimizado.mp4

# Optimizar video de celulares
ffmpeg -i celulares.mp4 \
  -c:v libx264 \
  -preset slow \
  -crf 22 \
  -pix_fmt yuv420p \
  -movflags +faststart \
  -g 1 \
  -keyint_min 1 \
  -vf "scale=1280:720" \
  -r 30 \
  -an \
  celulares_optimizado.mp4

echo "¡Videos optimizados!"
# Expense OCR

Aplicación web para la carga, procesamiento y gestión de documentos mediante OCR.

El sistema permite cargar documentos en formato JPG, PNG y PDF, extraer información relevante mediante OCR, estructurar los datos detectados y permitir su revisión y corrección manual antes de confirmar la información.

## Tecnologías

### Backend

- Laravel
- PHP
- PostgreSQL
- Tesseract OCR

### Frontend

- React
- Vite
- JavaScript

## Funcionalidades

- Carga de documentos JPG, PNG y PDF.
- Almacenamiento del documento original.
- Procesamiento mediante OCR.
- Extracción de información de facturas y documentos.
- Detección de:
  - Proveedor o establecimiento.
  - Número de documento.
  - Fecha.
  - Subtotal.
  - Impuestos.
  - Total.
  - Moneda.
- Clasificación del gasto.
- Niveles de confianza para la información extraída.
- Validación de datos detectados.
- Revisión y corrección manual.
- Consulta de documentos procesados.
- Edición y eliminación de documentos.
- Filtros por fecha y categoría.

## Categorías

Las categorías iniciales disponibles son:

- Alimentación
- Transporte
- Tecnología
- Servicios
- Otros

## Arquitectura

```text
expense-ocr/
│
├── backend/          # API REST - Laravel
│
├── frontend/         # Aplicación web - React
│
├── README.md
├── .gitignore
└── .env.example
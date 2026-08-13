# Expense OCR

Aplicación web para la carga, procesamiento y gestión de documentos de gastos mediante OCR.

El sistema permite cargar facturas o documentos en formato PDF, JPG y PNG, extraer automáticamente información relevante mediante OCR, revisar y corregir los datos detectados y finalmente almacenarlos como registros de gastos.

---

## Tecnologías

### Backend

- PHP 8.2+
- Laravel
- PostgreSQL
- Tesseract OCR
- Leptonica

### Frontend

- React
- Vite
- JavaScript

### OCR

Se utiliza **Tesseract OCR** como solución local para la extracción de texto de los documentos.

El procesamiento OCR se realiza en el backend y posteriormente se ejecutan reglas de extracción para identificar información estructurada.

---

## Características

- Carga de documentos PDF, JPG y PNG.
- Almacenamiento del documento original.
- Procesamiento mediante OCR.
- Extracción automática de:
  - Proveedor / establecimiento.
  - Número de documento.
  - Fecha.
  - Subtotal.
  - Impuestos.
  - Total.
  - Moneda.
- Clasificación automática del gasto.
- Categorías:
  - Alimentación
  - Transporte
  - Tecnología
  - Servicios
  - Otros
- Estado de revisión del documento.
- Formulario para revisar y corregir la información detectada.
- Visualización del documento original durante la revisión.
- Edición de documentos.
- Eliminación de documentos.
- Filtro por categoría.
- Filtro por rango de fechas.
- API REST.
- Persistencia en PostgreSQL.
- Separación entre carga, OCR y extracción de información.

---

# Arquitectura

La aplicación está dividida en dos proyectos:

```text
expense-ocr/
│
├── Backend/
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── .env
│   └── ...
│
├── Frontend/
│   ├── src/
│   ├── public/
│   ├── package.json
│   └── ...
│
├── .gitignore
└── README.md


Usuario
   │
   ▼
React
   │
   │ POST /api/documents
   ▼
Laravel
   │
   ├── Guarda documento
   │
   ├── Ejecuta OCR
   │
   ├── Extrae información
   │
   └── Clasifica categoría
   │
   ▼
Documento en estado "review"
   │
   ▼
React muestra:
   ├── Documento original
   └── Información detectada
   │
   ▼
Usuario revisa / corrige
   │
   ▼
PUT /api/documents/{id}
   │
   ▼
Documento confirmado


Requisitos

Antes de instalar el proyecto es necesario tener instalado:

Git
PHP 8.2 o superior
Composer
Node.js
npm
PostgreSQL
Tesseract OCR


sudo pacman -S tesseract tesseract-data-spa

tesseract --version
import { useState } from 'react'
import './App.css'

const categories = [
  { value: 'food', label: 'Alimentación' },
  { value: 'transport', label: 'Transporte' },
  { value: 'technology', label: 'Tecnología' },
  { value: 'services', label: 'Servicios' },
  { value: 'other', label: 'Otros' },
]

const emptyDocument = {
  provider: '',
  document_number: '',
  document_date: '',
  subtotal: '',
  tax: '',
  total: '',
  currency: 'COP',
  category: 'other',
}

function App() {
  const [view, setView] = useState('upload')
  const [document, setDocument] = useState(null)
  const [documents, setDocuments] = useState([])
  const [file, setFile] = useState(null)

  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [isEditing, setIsEditing] = useState(false)

  const [filters, setFilters] = useState({
    category: '',
    date_from: '',
    date_to: '',
  })

  const uploadDocument = async () => {
    if (!file) {
      setError('Selecciona un documento.')
      return
    }

    setLoading(true)
    setError('')
    setMessage('Procesando documento...')

    try {
      const formData = new FormData()
      formData.append('document', file)

      const response = await fetch('/api/documents', {
        method: 'POST',
        body: formData,
      })

      const result = await response.json()

      if (!response.ok) {
        throw new Error(result.message || 'Error procesando documento.')
      }

      setDocument(result.data)
      setView('review')
      setMessage('')
    } catch (err) {
      setError(err.message)
      setMessage('')
    } finally {
      setLoading(false)
    }
  }

  const updateDocument = (field, value) => {
    setDocument(prev => ({
      ...prev,
      [field]: value,
    }))
  }

  const confirmDocument = async () => {
    setLoading(true)
    setError('')

    try {
      const response = await fetch(
        `/api/documents/${document.id}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            provider: document.provider,
            document_number: document.document_number || null,
            document_date: document.document_date || null,
            subtotal: Number(document.subtotal || 0),
            tax: Number(document.tax || 0),
            total: Number(document.total || 0),
            currency: document.currency,
            category: document.category,
          }),
        }
      )

      const result = await response.json()

      if (!response.ok) {
        throw new Error(result.message || 'Error confirmando documento.')
      }

      setDocument(result.data)
      setMessage('Documento confirmado correctamente.')
      setView('documents')

      loadDocuments()
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const loadDocuments = async () => {
    try {
      const params = new URLSearchParams()

      if (filters.category) {
        params.append('category', filters.category)
      }

      if (filters.date_from) {
        params.append('date_from', filters.date_from)
      }

      if (filters.date_to) {
        params.append('date_to', filters.date_to)
      }

      const response = await fetch(`/api/documents?${params}`)

      const result = await response.json()

      if (!response.ok) {
        throw new Error(result.message || 'Error cargando documentos.')
      }

      setDocuments(result.data)
    } catch (err) {
      setError(err.message)
    }
  }

  const deleteDocument = async (id) => {
    if (!confirm('¿Eliminar este documento?')) {
      return
    }

    try {
      const response = await fetch(`/api/documents/${id}`, {
        method: 'DELETE',
      })

      if (!response.ok) {
        throw new Error('No se pudo eliminar el documento.')
      }

      loadDocuments()
    } catch (err) {
      setError(err.message)
    }
  }

  const openDocuments = () => {
    setView('documents')
    loadDocuments()
  }

  const editDocument = async (id) => {
    setLoading(true)
    setError('')

    try {
      const response = await fetch(`/api/documents/${id}`)
      const result = await response.json()

      if (!response.ok) {
        throw new Error(
          result.message || 'Error cargando documento.'
        )
      }

      setDocument(result.data)
      setView('review')
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="app">
      <header className="header">
        <div>
          <h1>Expense OCR</h1>
          <p>Gestión y extracción de documentos</p>
        </div>

        <nav>
          <button
            className={view === 'upload' || view === 'review' ? 'active' : ''}
            onClick={() => {
              setView('upload')
              setDocument(null)
              setFile(null)
              setError('')
            }}
          >
            Subir documento
          </button>

          <button
            className={view === 'documents' ? 'active' : ''}
            onClick={openDocuments}
          >
            Documentos
          </button>
        </nav>
      </header>

      <main>
        {message && (
          <div className="alert success">
            {message}
          </div>
        )}

        {error && (
          <div className="alert error">
            {error}
          </div>
        )}

        {view === 'upload' && (
          <UploadView
            file={file}
            setFile={setFile}
            uploadDocument={uploadDocument}
            loading={loading}
          />
        )}

        {view === 'review' && document && (
          <ReviewView
            document={document}
            updateDocument={updateDocument}
            confirmDocument={confirmDocument}
            loading={loading}
            isEditing={isEditing}
          />
        )}

        {view === 'documents' && (
          <DocumentsView
            documents={documents}
            filters={filters}
            setFilters={setFilters}
            loadDocuments={loadDocuments}
            deleteDocument={deleteDocument}
            editDocument={editDocument}
          />
        )}
      </main>
    </div>
  )
}

function UploadView({ file, setFile, uploadDocument, loading }) {
  return (
    <section className="card upload">
      <h2>Subir documento</h2>

      <p className="muted">
        Carga una factura o documento para iniciar el procesamiento OCR.
      </p>

      <label className="dropzone">
        <input
          type="file"
          accept=".jpg,.jpeg,.png,.pdf"
          onChange={e => setFile(e.target.files[0])}
        />

        <strong>
          {file
            ? file.name
            : 'Selecciona o arrastra un documento'}
        </strong>

        <span>
          JPG, PNG o PDF
        </span>
      </label>

      <button
        className="primary"
        onClick={uploadDocument}
        disabled={!file || loading}
      >
        {loading ? 'Procesando...' : 'Procesar documento'}
      </button>
    </section>
  )
}

function ReviewView({ document, updateDocument, confirmDocument, loading, isEditing, }) {
  const fileUrl = `/api/documents/${document.id}/file`;
  return (
    <section className="card">
      <div className="section-header">
        <div>
          <h2>{isEditing ? 'Editar documento' : 'Revisar documento'}</h2>
          <p className="muted">
            Verifica y corrige la información detectada por OCR.
          </p>
        </div>

        <span className="status review">
          Revisión
        </span>
      </div>

      <section className="review-document">
        <div className="section-header">
            <h2>Documento original</h2>
        </div>

        <div className="document-preview-container">

            {document.mime_type === 'application/pdf' ? (
                <iframe
                    src={fileUrl}
                    title="Documento original"
                    className="document-preview"
                />
            ) : (
                <img
                    src={fileUrl}
                    alt="Documento original"
                    className="document-preview"
                />
            )}

        </div>
    </section>

      <div className="form-grid">
        <Field
          label="Proveedor"
          value={document.provider}
          onChange={value => updateDocument('provider', value)}
        />

        <Field
          label="Número de documento"
          value={document.document_number}
          onChange={value => updateDocument('document_number', value)}
        />

        <Field
          label="Fecha"
          type="date"
          value={document.document_date?.substring(0, 10) || ''}
          onChange={value => updateDocument('document_date', value)}
        />

        <Field
          label="Subtotal"
          type="number"
          value={document.subtotal ?? ''}
          onChange={value => updateDocument('subtotal', value)}
        />

        <Field
          label="Impuestos"
          type="number"
          value={document.tax ?? ''}
          onChange={value => updateDocument('tax', value)}
        />

        <Field
          label="Total"
          type="number"
          value={document.total ?? ''}
          onChange={value => updateDocument('total', value)}
        />

        <Field
          label="Moneda"
          value={document.currency}
          onChange={value => updateDocument('currency', value)}
        />

        <div className="field">
          <label>Categoría</label>

          <select
            value={document.category}
            onChange={e =>
              updateDocument('category', e.target.value)
            }
          >
            {categories.map(category => (
              <option
                key={category.value}
                value={category.value}
              >
                {category.label}
              </option>
            ))}
          </select>
        </div>
      </div>

      <div className="review-actions">
        <button
          className="secondary"
          onClick={() => window.location.reload()}
        >
          Cancelar
        </button>

        <button
          className="primary"
          onClick={confirmDocument}
          disabled={loading}
        >
          {loading
            ? 'Guardando...'
            : isEditing
              ? 'Guardar cambios'
              : 'Confirmar documento'}
        </button>
      </div>
    </section>
  )
}

function Field({ label, type = 'text', value, onChange }) {
  return (
    <div className="field">
      <label>{label}</label>

      <input
        type={type}
        value={value ?? ''}
        onChange={e => onChange(e.target.value)}
      />
    </div>
  )
}

function DocumentsView({ documents, filters, setFilters, loadDocuments, deleteDocument, editDocument, }) {
  return (
    <section>
      <div className="section-header">
        <div>
          <h2>Documentos</h2>
          <p className="muted">
            Facturas y documentos registrados.
          </p>
        </div>
      </div>

      <div className="card filters">
        <div className="field">
          <label>Categoría</label>

          <select
            value={filters.category}
            onChange={e =>
              setFilters({
                ...filters,
                category: e.target.value,
              })
            }
          >
            <option value="">Todas</option>

            {categories.map(category => (
              <option
                key={category.value}
                value={category.value}
              >
                {category.label}
              </option>
            ))}
          </select>
        </div>

        <div className="field">
          <label>Desde</label>

          <input
            type="date"
            value={filters.date_from}
            onChange={e =>
              setFilters({
                ...filters,
                date_from: e.target.value,
              })
            }
          />
        </div>

        <div className="field">
          <label>Hasta</label>

          <input
            type="date"
            value={filters.date_to}
            onChange={e =>
              setFilters({
                ...filters,
                date_to: e.target.value,
              })
            }
          />
        </div>

        <button className="primary filter-button" onClick={loadDocuments}>
          Filtrar
        </button>
      </div>

      <div className="card table-container">
        <table>
          <thead>
            <tr>
              <th>Proveedor</th>
              <th>Fecha</th>
              <th>Categoría</th>
              <th>Total</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            {documents.map(document => (
              <tr key={document.id}>
                <td>{document.provider || 'Sin proveedor'}</td>

                <td>
                  {document.document_date
                    ? new Date(document.document_date).toLocaleDateString(
                      'es-CO'
                    )
                    : '-'}
                </td>

                <td>
                  {categories.find(
                    c => c.value === document.category
                  )?.label || 'Otros'}
                </td>

                <td>
                  {document.currency} {document.total ?? '0.00'}
                </td>

                <td>
                  <span className={`status ${document.status}`}>
                    {document.status}
                  </span>
                </td>

                <td>
                  <div className="actions">
                    <button
                      className="edit-button"
                      onClick={() =>
                        editDocument(document.id)
                      }
                    >
                      Editar
                    </button>

                    <button
                      className="danger-button"
                      onClick={() =>
                        deleteDocument(document.id)
                      }
                    >
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            ))}

            {documents.length === 0 && (
              <tr>
                <td colSpan="6" className="empty">
                  No hay documentos registrados.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </section>
  )
}

export default App
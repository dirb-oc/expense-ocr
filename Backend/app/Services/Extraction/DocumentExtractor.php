<?php

namespace App\Services\Extraction;

use Carbon\Carbon;

class DocumentExtractor
{
    public function extract(string $text): array
    {
        return [
            'provider' => $this->field(
                $this->extractProvider($text),
                $this->confidenceProvider($text)
            ),
    
            'document_number' => $this->field(
                $this->extractDocumentNumber($text),
                $this->confidenceDocumentNumber($text)
            ),
    
            'document_date' => $this->field(
                $this->extractDate($text),
                $this->confidenceDate($text)
            ),
    
            'subtotal' => $this->field(
                $this->extractMoney($text, 'subtotal'),
                $this->confidenceMoney($text, 'subtotal')
            ),
    
            'tax' => $this->field(
                $this->extractMoney($text, 'tax'),
                $this->confidenceMoney($text, 'tax')
            ),
    
            'total' => $this->field(
                $this->extractMoney($text, 'total'),
                $this->confidenceMoney($text, 'total')
            ),
    
            'currency' => $this->field(
                $this->detectCurrency($text),
                0.95
            ),
    
            'category' => $this->field(
                $this->detectCategory($text),
                0.85
            ),
        ];
    }
    
    private function field(mixed $value, float $confidence): array
    {
        return [
            'value' => $value,
            'confidence' => $confidence,
            'source' => 'regex',
        ];
    }

    private function extractProvider(string $text): ?string
    {
        $lines = preg_split('/\R/', $text);

        foreach ($lines as $index => $line) {
            if (preg_match('/\bNIT\.?\s*[\d.\-]+/iu', $line)) {
                // Revisar las líneas cercanas al NIT
                $candidates = [
                    $index - 1,
                    $index + 1,
                    $index + 2,
                ];

                foreach ($candidates as $candidateIndex) {
                    if (!isset($lines[$candidateIndex])) {
                        continue;
                    }

                    $candidate = trim($lines[$candidateIndex]);

                    if ($this->isValidProviderCandidate($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    private function isValidProviderCandidate(string $value): bool
    {
        if (mb_strlen($value) < 3 || mb_strlen($value) > 100) {
            return false;
        }

        if (preg_match('/\b(NIT|RÉGIMEN|REGIMEN|FECHA|CLIENTE|DIRECCIÓN|TELEFONO|TELÉFONO)\b/iu', $value)) {
            return false;
        }

        return preg_match('/[A-Za-zÁÉÍÓÚÑáéíóúñ]{3,}/u', $value) === 1;
    }

    private function extractDocumentNumber(string $text): ?string
    {
        $patterns = [
            '/factura\s*(?:n[°ºo.]*)?\s*[:#-]?\s*([A-Z0-9][A-Z0-9\-]{2,})/iu',
            '/factura\s*([A-Z0-9][A-Z0-9\-]{2,})/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $value = trim($matches[1]);

                // Evitar falsos positivos muy cortos
                if (strlen($value) >= 3) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function extractDate(string $text): ?string
    {
        if (!preg_match(
            '/(?:fecha)?\s*:?\s*(\d{4}[\/-]\d{1,2}[\/-]\d{1,2})/iu',
            $text,
            $matches
        )) {
            return null;
        }

        try {
            return Carbon::parse(
                str_replace('/', '-', $matches[1])
            )->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractMoney(string $text, string $field): ?float
    {
        $labels = match ($field) {
            'subtotal' => 'subtotal',
            'tax' => '(?:iva|impuesto|tax)',
            'total' => 'total',
            default => $field,
        };

        $pattern = '/'
            . $labels
            . '\s*:?\s*'
            . '[\$]?\s*'
            . '([0-9][0-9.,\s-]{1,})'
            . '/iu';

        if (!preg_match($pattern, $text, $matches)) {
            return null;
        }

        return $this->normalizeMoney($matches[1]);
    }

    private function normalizeMoney(string $value): ?float
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', '', $value);

        if (!$value) {
            return null;
        }

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }

    private function detectCurrency(string $text): string
    {
        if (preg_match('/\b(USD|EUR|COP)\b/i', $text, $matches)) {
            return strtoupper($matches[1]);
        }

        if (str_contains($text, '$')) {
            return 'COP';
        }

        return 'COP';
    }

    private function detectCategory(string $text): string
    {
        $text = mb_strtolower($text);

        if (preg_match('/comida|restaurante|supermercado|alimento|café|pizza/i', $text)) {
            return 'food';
        }

        if (preg_match('/taxi|uber|transporte|gasolina|combustible|parqueadero/i', $text)) {
            return 'transport';
        }

        if (preg_match('/computador|computadora|portátil|tecnología|software|hardware|accesorio/i', $text)) {
            return 'technology';
        }

        if (preg_match('/internet|energía|agua|teléfono|telefonía|servicio/i', $text)) {
            return 'services';
        }

        return 'other';
    }

    private function confidenceProvider(string $text): float
    {
        if (preg_match('/\bCOMPUKAR\b/i', $text)) {
            return 0.95;
        }
    
        return 0.40;
    }
    
    private function confidenceDocumentNumber(string $text): float
    {
        if (preg_match('/FACTURA.*[A-Z0-9]{3,}/iu', $text)) {
            return 0.75;
        }
    
        return 0.10;
    }
    
    private function confidenceDate(string $text): float
    {
        return preg_match(
            '/\b\d{4}[\/-]\d{1,2}[\/-]\d{1,2}\b/',
            $text
        ) ? 0.98 : 0.0;
    }
    
    private function confidenceMoney(string $text, string $field): float
    {
        $label = match ($field) {
            'subtotal' => 'subtotal',
            'tax' => '(?:iva|impuesto|tax)',
            'total' => 'total',
            default => $field,
        };
    
        if (!preg_match('/' . $label . '\s*:/iu', $text)) {
            return 0.0;
        }
    
        return 0.70;
    }
}
<?php

namespace App\Support;

/**
 * Issuer details for customer account statements (web, PDF, print).
 * Defaults match Jawad Al Lail General Contracting Establishment; override via COMPANY_* env (non-empty only).
 */
final class StatementCompany
{
    private const DEFAULT_NAME = 'Jawad Al Lail General Contracting Establishment';

    /** @var list<string> */
    private const DEFAULT_ADDRESS_LINES = [
        'Building No 8147, Office No 02, King Saman -22.',
        'Al Khobar Al Shumaliya, Al Khobar 34426.',
    ];

    /** Lines shown under "ACCOUNT STATEMENT" on the PDF header. */
    private const DEFAULT_PDF_HEADER_NAME_LINES = [
        'Jawad Al Lail General Contracting',
        'Establishment',
    ];

    /** Company name lines inside the PDF issuer card (above address). */
    private const DEFAULT_PDF_BLOCK_NAME_LINES = [
        'Jawad Al Lail General',
        'Contracting Establishment',
    ];

    /** Path relative to the `public/` directory (PNG/SVG/JPEG recommended for PDFs). */
    private const DEFAULT_LOGO = 'images/company/laiba-logo.png';

    /**
     * @return array{name: string, address_lines: list<string>, registration: string, vat_number: string, phone_label: string, phone: string, email: string, logo: string, pdf_header_name_lines: list<string>, pdf_block_name_lines: list<string>}
     */
    public static function defaults(): array
    {
        return [
            'name' => self::DEFAULT_NAME,
            'address_lines' => [...self::DEFAULT_ADDRESS_LINES],
            'registration' => '2062028424',
            'vat_number' => '300660064800003',
            'phone_label' => 'Mobile & WhatsApp',
            'phone' => '+966-564520210',
            'email' => 'info@jawadallail.com',
            'logo' => self::DEFAULT_LOGO,
            'pdf_header_name_lines' => [...self::DEFAULT_PDF_HEADER_NAME_LINES],
            'pdf_block_name_lines' => [...self::DEFAULT_PDF_BLOCK_NAME_LINES],
        ];
    }

    private static function envNonEmptyString(string $key, string $default): string
    {
        $v = env($key);

        return (is_string($v) && trim($v) !== '') ? trim($v) : $default;
    }

    /**
     * Build company array from environment (used by config/company.php).
     *
     * @return array{name: string, address_lines: list<string>, registration: string, vat_number: string, phone_label: string, phone: string, email: string, logo: string, pdf_header_name_lines: list<string>, pdf_block_name_lines: list<string>}
     */
    public static function resolvedFromEnvironment(): array
    {
        $addressLines = [];
        foreach ([1, 2, 3] as $i) {
            $line = env('COMPANY_ADDRESS_LINE'.$i);
            if (is_string($line) && trim($line) !== '') {
                $addressLines[] = trim($line);
            }
        }
        if ($addressLines === []) {
            $addressLines = [...self::DEFAULT_ADDRESS_LINES];
        }

        return [
            'name' => self::envNonEmptyString('COMPANY_NAME', self::DEFAULT_NAME),
            'address_lines' => $addressLines,
            'registration' => self::envNonEmptyString('COMPANY_REGISTRATION', '2062028424'),
            'vat_number' => self::envNonEmptyString('COMPANY_VAT_NUMBER', '300660064800003'),
            'phone_label' => self::envNonEmptyString('COMPANY_PHONE_LABEL', 'Mobile & WhatsApp'),
            'phone' => self::envNonEmptyString('COMPANY_PHONE', '+966-564520210'),
            'email' => self::envNonEmptyString('COMPANY_EMAIL', 'info@jawadallail.com'),
            'logo' => self::envNonEmptyString('COMPANY_LOGO', self::DEFAULT_LOGO),
            'pdf_header_name_lines' => [...self::DEFAULT_PDF_HEADER_NAME_LINES],
            'pdf_block_name_lines' => [...self::DEFAULT_PDF_BLOCK_NAME_LINES],
        ];
    }

    /**
     * Merge config('company') with defaults so missing keys, null, or blank strings never hide issuer details.
     *
     * @return array{name: string, address_lines: list<string>, registration: string, vat_number: string, phone_label: string, phone: string, email: string, logo: string, pdf_header_name_lines: list<string>, pdf_block_name_lines: list<string>}
     */
    public static function normalize(mixed $config): array
    {
        $out = self::defaults();
        if (! is_array($config)) {
            return $out;
        }

        foreach (['name', 'registration', 'vat_number', 'phone_label', 'phone', 'email', 'logo'] as $key) {
            if (! array_key_exists($key, $config)) {
                continue;
            }
            $v = $config[$key];
            if (is_string($v) && trim($v) !== '') {
                $out[$key] = trim($v);
            }
        }

        if (isset($config['address_lines']) && is_array($config['address_lines'])) {
            $lines = [];
            foreach ($config['address_lines'] as $line) {
                if (is_string($line) && trim($line) !== '') {
                    $lines[] = trim($line);
                }
            }
            if ($lines !== []) {
                $out['address_lines'] = $lines;
            }
        }

        foreach (['pdf_header_name_lines', 'pdf_block_name_lines'] as $arrKey) {
            if (! isset($config[$arrKey]) || ! is_array($config[$arrKey])) {
                continue;
            }
            $lines = [];
            foreach ($config[$arrKey] as $line) {
                if (is_string($line) && trim($line) !== '') {
                    $lines[] = trim($line);
                }
            }
            if ($lines !== []) {
                $out[$arrKey] = $lines;
            }
        }

        return $out;
    }
}

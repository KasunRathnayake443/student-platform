<?php

namespace App\Services;

use App\Models\School;
use InvalidArgumentException;

class SchoolMailTransport
{
    public function isConfigured(School $school): bool
    {
        return filter_var($school->email, FILTER_VALIDATE_EMAIL) !== false
            && is_string($school->smtp_password)
            && $school->smtp_password !== ''
            && filled($school->smtp_host)
            && (int) $school->smtp_port >= 1
            && (int) $school->smtp_port <= 65535
            && in_array($school->smtp_encryption, ['tls', 'ssl', 'none'], true);
    }

    public function configure(School $school): string
    {
        if (! $this->isConfigured($school)) {
            throw new InvalidArgumentException('The school SMTP configuration is incomplete.');
        }

        $encryption = $school->smtp_encryption;
        $fingerprint = substr(hash('sha256', implode('|', [
            $school->email,
            $school->smtp_password,
            $school->smtp_host,
            (string) $school->smtp_port,
            $encryption,
        ])), 0, 12);
        $mailer = "school-{$school->getKey()}-{$fingerprint}";

        config()->set("mail.mailers.{$mailer}", [
            'transport' => 'smtp',
            'scheme' => match ($encryption) {
                'ssl' => 'smtps',
                default => 'smtp',
            },
            'host' => $school->smtp_host,
            'port' => (int) $school->smtp_port,
            'username' => $school->email,
            'password' => $school->smtp_password,
            'timeout' => 30,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost',
            'auto_tls' => $encryption === 'tls',
            'require_tls' => $encryption === 'tls',
        ]);

        return $mailer;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    protected $fillable = [
        'user_id',
        'domain',
        'verification_token',
        'verified',
        'verified_via',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * The meta tag snippet the user must paste into their <head>.
     */
    public function metaTagHtml(): string
    {
        return '<meta name="widget-verification" content="' . $this->verification_token . '">';
    }

    /**
     * The DNS TXT record value the user must add.
     */
    public function dnsTxtValue(): string
    {
        return 'widget-verification=' . $this->verification_token;
    }

    /**
     * Sanitise and normalise a raw domain string.
     * Strips scheme, www prefix, path, port, and whitespace.
     * Returns lowercase bare hostname or empty string if invalid.
     */
    public static function normaliseDomain(string $raw): string
    {
        $raw = trim($raw);

        // Add a scheme so parse_url works reliably
        if (!preg_match('#^https?://#i', $raw)) {
            $raw = 'https://' . $raw;
        }

        $host = parse_url($raw, PHP_URL_HOST) ?? '';
        $host = strtolower(trim($host));

        // Strip leading www.
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    /**
     * Returns true if the string looks like a valid public hostname.
     * Rejects IP addresses, localhost, and *.local / *.test dev hostnames.
     */
    public static function isValidPublicDomain(string $domain): bool
    {
        if ($domain === '') return false;

        // Reject IP addresses
        if (filter_var($domain, FILTER_VALIDATE_IP)) return false;

        // Reject local / dev hostnames
        $local = ['localhost', '127.0.0.1', '::1'];
        if (in_array($domain, $local, true)) return false;
        if (str_ends_with($domain, '.local')) return false;
        if (str_ends_with($domain, '.test'))  return false;

        // Must match valid hostname pattern and have at least one dot
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)+$/', $domain)) {
            return false;
        }

        return true;
    }
}

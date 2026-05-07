<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    // =========================================================================
    // LIST
    // =========================================================================

    public function index()
    {
        $domains = Domain::where('user_id', 1)
            ->orderByDesc('created_at')
            ->get();

        return view('domains.index', compact('domains'));
    }

    // =========================================================================
    // ADD DOMAIN
    // =========================================================================

    public function store(Request $request)
    {
        $raw = (string) ($request->input('domain') ?? '');

        if (preg_match('/[\s,;|]/', $raw)) {
            return back()
                ->withInput()
                ->with('error', 'Please enter a single domain only (no spaces, commas, or separators).');
        }

        $domain = Domain::normaliseDomain($raw);

        if (!Domain::isValidPublicDomain($domain)) {
            return back()
                ->withInput()
                ->with('error', "'{$raw}' is not a valid public domain. Use a format like example.com");
        }

        $alreadyOwned = Domain::where('user_id', 1)
            ->where('domain', $domain)
            ->exists();

        if ($alreadyOwned) {
            return back()
                ->withInput()
                ->with('error', "You have already added '{$domain}'.");
        }

        $claimedByOther = Domain::where('domain', $domain)
            ->where('user_id', '!=', 1)
            ->where('verified', true)
            ->exists();

        if ($claimedByOther) {
            return back()
                ->withInput()
                ->with('error', "'{$domain}' is already verified by another account. If this is your domain, please contact support.");
        }

        $token = bin2hex(random_bytes(32));

        Domain::create([
            'user_id'            => 1,
            'domain'             => $domain,
            'verification_token' => $token,
            'verified'           => false,
            'verified_via'       => null,
        ]);

        return back()->with('success', "Domain '{$domain}' added. Please verify ownership using one of the methods below.");
    }

    // =========================================================================
    // VERIFY — META TAG
    // =========================================================================

    /**
     * POST /domains/{domain}/verify/meta
     *
     * Checks for the meta tag on the domain.
     *
     * URL resolution priority:
     *   1. verify_url from request  — user pasted the exact page URL (e.g. https://epos.com/customer-dashboard)
     *   2. https://{domain}         — homepage fallback
     *
     * If the user has the widget on a specific page (not the homepage),
     * they can enter that page URL in the optional field and we check there.
     * We ALSO check the homepage as a secondary fallback so either placement works.
     */
    public function verifyMeta(Domain $domain, Request $request)
    {
        $this->authoriseDomain($domain);

        if ($domain->verified) {
            return back()->with('success', "'{$domain->domain}' is already verified.");
        }

        // ── Resolve which URLs to check ───────────────────────────────────────
        $customUrl  = trim((string) ($request->input('verify_url') ?? ''));
        $homepage   = 'https://' . $domain->domain;

        // Build the list of URLs to try, in priority order — no duplicates
        $urlsToCheck = [];

        if ($customUrl !== '') {
            // Validate it belongs to the same domain
            $customHost = parse_url($customUrl, PHP_URL_HOST) ?? '';
            $cleanCustomHost = Domain::normaliseDomain($customHost);

            if ($cleanCustomHost !== $domain->domain) {
                return back()->with('error',
                    "The URL '{$customUrl}' does not belong to '{$domain->domain}'. " .
                    "Please enter a URL on the same domain."
                );
            }
            // Ensure it has a scheme
            if (!str_starts_with($customUrl, 'http')) {
                $customUrl = 'https://' . ltrim($customUrl, '/');
            }
            $urlsToCheck[] = $customUrl;
        }

        // Always also check homepage (unless it's the same as customUrl)
        if (!in_array($homepage, $urlsToCheck, true)) {
            $urlsToCheck[] = $homepage;
        }

        // ── Try each URL until we find the tag ───────────────────────────────
        $token       = $domain->verification_token;
        $lastError   = '';
        $checkedUrls = [];

        foreach ($urlsToCheck as $url) {
            $checkedUrls[] = $url;
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'WidgetVerifier/1.0'])
                    ->get($url);

                if (!$response->successful()) {
                    $lastError = "Could not reach {$url} (HTTP {$response->status()}).";
                    continue; // try next URL
                }

                if ($this->htmlContainsVerificationMeta($response->body(), $token)) {
                    // ── Found! Mark as verified ───────────────────────────
                    $domain->update(['verified' => true, 'verified_via' => 'meta']);

                    $foundOn = $url === $homepage ? 'homepage' : $url;
                    return back()->with('success', "✓ '{$domain->domain}' verified successfully via meta tag! (Found on: {$foundOn})");
                }

                $lastError = "Meta tag not found on {$url}.";

            } catch (\Throwable $e) {
                Log::error("Domain meta verify error: domain={$domain->domain} url={$url} | {$e->getMessage()}");
                $lastError = "Could not connect to {$url}.";
                continue;
            }
        }

        // ── Not found on any checked URL ──────────────────────────────────────
        $checkedList = implode(' and ', $checkedUrls);
        return back()->with('error',
            "Meta tag not found on {$checkedList}. " .
            "Possible reasons: (1) The tag has not been added yet. " .
            "(2) The page requires login — our verifier cannot access protected pages. " .
            "Solution: place the tag on your public homepage, or use DNS TXT verification instead."
        );
    }

    // =========================================================================
    // VERIFY — DNS TXT
    // =========================================================================

    public function verifyDns(Domain $domain)
    {
        $this->authoriseDomain($domain);

        if ($domain->verified) {
            return back()->with('success', "'{$domain->domain}' is already verified.");
        }

        try {
            $records = dns_get_record($domain->domain, DNS_TXT);

            if ($records === false || empty($records)) {
                return back()->with('error',
                    "No TXT records found for '{$domain->domain}'. " .
                    "DNS changes can take up to 24–48 hours to propagate. Please try again later."
                );
            }

            $expectedValue = $domain->dnsTxtValue();
            $found         = false;

            foreach ($records as $record) {
                $txt = trim($record['txt'] ?? $record['entries'][0] ?? '');
                if ($txt === $expectedValue) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return back()->with('error',
                    "TXT record not found for '{$domain->domain}'. " .
                    "Expected: {$expectedValue} — DNS propagation can take up to 48 hours."
                );
            }

            $domain->update(['verified' => true, 'verified_via' => 'dns']);

            return back()->with('success', "✓ '{$domain->domain}' verified successfully via DNS TXT record!");

        } catch (\Throwable $e) {
            Log::error("Domain DNS verify error: domain={$domain->domain} | {$e->getMessage()}");
            return back()->with('error', "DNS lookup failed for '{$domain->domain}'. Please try again.");
        }
    }



    public function forceVerify(Domain $domain)
    {
        $this->authoriseDomain($domain);
        $domain->update(['verified' => true, 'verified_via' => 'manual']);
        return back()->with('success', "✓ '{$domain->domain}' marked as verified manually.");
    }

    
    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy(Domain $domain)
    {
        $this->authoriseDomain($domain);

        $name = $domain->domain;
        $domain->delete();

        return back()->with('success', "Domain '{$name}' removed.");
    }

    // =========================================================================
    // API — used by WidgetController::initWidget()
    // =========================================================================

    public static function findVerifiedDomain(string $hostname): ?Domain
    {
        $clean = Domain::normaliseDomain($hostname);
        if ($clean === '') return null;

        return Domain::where('domain', $clean)
            ->where('verified', true)
            ->first();
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function authoriseDomain(Domain $domain): void
    {
        abort_if($domain->user_id !== 1, 403, 'Forbidden.');
    }

    private function htmlContainsVerificationMeta(string $html, string $token): bool
    {
        // Fast path
        if (str_contains($html, 'name="widget-verification"') &&
            str_contains($html, 'content="' . $token . '"')) {
            return true;
        }

        // Flexible attribute order, single or double quotes
        $pattern = '/<meta\s[^>]*name=["\']widget-verification["\'][^>]*content=["\']'
            . preg_quote($token, '/') . '["\'][^>]*\/?>/i';
        if (preg_match($pattern, $html)) return true;

        $pattern2 = '/<meta\s[^>]*content=["\']' . preg_quote($token, '/') . '["\'][^>]*name=["\']widget-verification["\'][^>]*\/?>/i';
        if (preg_match($pattern2, $html)) return true;

        return false;
    }
}
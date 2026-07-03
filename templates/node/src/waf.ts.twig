/**
 * Appwrite WAF proof-of-work challenge handler (Node / server).
 *
 * When a request is met with `waf_challenge_required`, the client solves the
 * proof-of-work advertised by the `X-Appwrite-WAF-*` headers, exchanges the
 * solution for a short-lived clearance token, and retries the request with it —
 * transparently, so application code never sees the challenge.
 *
 * On the server we use Node's native `crypto` SHA-256 (fast, hardware-accelerated)
 * and a straight synchronous solve loop; solves are single-flight (concurrent
 * 403s share one solve) and the clearance token is cached until shortly before it
 * expires.
 *
 * NOTE: this file is emitted by sdk-generator (templates/node/src/waf.ts.twig).
 * Edit it there, not in the generated SDK, or a regen will overwrite your change.
 */

import { createHash } from 'crypto';
// Solve over the same transport library the client uses, so proxy/keep-alive
// behaviour is consistent with the rest of the SDK.
import { fetch } from 'undici';

export const WAF_CHALLENGE_ERROR = 'waf_challenge_required';

function leadingZeroBits(digest: Buffer): number {
    let bits = 0;
    for (let i = 0; i < digest.length; i++) {
        const byte = digest[i];
        if (byte === 0) { bits += 8; continue; }
        for (let mask = 0x80; mask > 0; mask >>= 1) {
            if (byte & mask) return bits;
            bits++;
        }
    }
    return bits;
}

function meets(nonce: string, solution: string, difficulty: number): boolean {
    const digest = createHash('sha256').update(nonce + '.' + solution).digest();
    return leadingZeroBits(digest) >= difficulty;
}

/** Synchronous native solve — server CPUs clear typical difficulties in well under a second. */
export function solvePow(nonce: string, difficulty: number): string {
    for (let n = 0; ; n++) {
        const solution = String(n);
        if (meets(nonce, solution, difficulty)) return solution;
    }
}

const DEADLINE_SKEW_MS = 30_000;

/**
 * Stateful per-client challenge handler: single-flight solve + token cache.
 */
export class WafChallenge {
    private clearance: { token: string; deadline: number } | null = null;
    private inflight: Promise<string> | null = null;

    constructor(
        private readonly getEndpoint: () => string,
        private readonly getProject: () => string,
    ) {}

    /** Currently-valid clearance token, or null. */
    token(): string | null {
        return this.clearance && Date.now() < this.clearance.deadline ? this.clearance.token : null;
    }

    /** Drop the cached clearance (e.g. it was rejected — stale / IP changed). */
    reset(): void {
        this.clearance = null;
    }

    /**
     * Solve the challenge described by the response headers and mint a clearance.
     * Concurrent callers share one in-flight solve.
     */
    solve(headers: Record<string, string>): Promise<string> {
        if (this.inflight) return this.inflight;

        const nonce = headers['x-appwrite-waf-nonce'] || '';
        const difficulty = parseInt(headers['x-appwrite-waf-difficulty'] || '0', 10) || 0;
        const expiresIn = parseInt(headers['x-appwrite-waf-expires-in'] || '0', 10) || 0;

        this.inflight = (async () => {
            const solution = solvePow(nonce, difficulty);
            // Solve UNAUTHENTICATED — only the project header, never the API key.
            const endpoint = this.getEndpoint().replace(/\/+$/, '');
            const res = await fetch(endpoint + '/waf/challenge', {
                method: 'POST',
                headers: { 'content-type': 'application/json', 'x-appwrite-project': this.getProject() },
                body: JSON.stringify({ nonce, solution }),
            });
            if (!res.ok) {
                throw new Error('WAF challenge solve rejected: ' + res.status);
            }
            const body: any = await res.json();
            const ttl = (body.expiresIn || expiresIn) as number;
            this.clearance = { token: body.token, deadline: Date.now() + Math.max(0, ttl * 1000 - DEADLINE_SKEW_MS) };
            return body.token as string;
        })().finally(() => { this.inflight = null; });

        return this.inflight;
    }
}

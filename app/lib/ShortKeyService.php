<?php

namespace App\lib;

use Illuminate\Support\Str;
use Illuminate\Database\ConnectionInterface;

final class ShortKeyService
{
    public function __construct(
        public ConnectionInterface $db
    ) {}

    /**
     * Generates a unique 8-digit short key for a given tenant election.
     */
    public function generateUniqueShortKey(int $electionId): string
    {
        // 8-digit space: 100,000,000 codes.
        // Use cryptographically secure random bytes with rejection sampling to avoid modulo bias.
        $max = 100_000_000;
        $maxUint32 = 0xFFFFFFFF;
        $limit = intdiv($maxUint32 + 1, $max) * $max; // highest acceptable value (exclusive) aligned to $max

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $bytes = random_bytes(4);
            $value = unpack('N', $bytes)[1]; // big-endian unsigned 32-bit

            if ($value >= $limit) {
                continue; // reject to avoid bias
            }

            $num = $value % $max;
            $key = str_pad((string) $num, 8, '0', STR_PAD_LEFT);

            $exists = $this->db->table('ballots')
                ->where('election_id', $electionId)
                ->where('short_key', $key)
                ->exists();

            if (! $exists) {
                return $key;
            }
        }

        // Extremely unlikely: fallback - derive a numeric 8-digit value from a ULID hash.
        $ulid = Str::ulid()->toBase32();
        $hash = hash('sha256', $ulid, true);
        $num = (unpack('N', substr($hash, 0, 4))[1] ?? random_int(0, $max - 1)) % $max;

        return str_pad((string) $num, 8, '0', STR_PAD_LEFT);
    }
}

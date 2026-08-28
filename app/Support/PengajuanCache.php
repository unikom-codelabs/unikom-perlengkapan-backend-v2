<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class PengajuanCache
{
    private const VERSION_KEY = 'pengajuan:version';

    private const TTL = 300;

    public static function remember(string $nama, array $konteks, Closure $callback)
    {
        return Cache::remember(
            self::kunci($nama, $konteks),
            self::TTL,
            $callback
        );
    }

    public static function flush(): void
    {
        if (Cache::has(self::VERSION_KEY)) {
            Cache::increment(self::VERSION_KEY);

            return;
        }

        Cache::forever(self::VERSION_KEY, 2);
    }

    private static function versi(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    private static function kunci(string $nama, array $konteks): string
    {
        $konteks = array_filter($konteks, fn ($v) => $v !== null && $v !== '');

        ksort($konteks);

        return sprintf(
            'pengajuan:v%d:%s:%s',
            self::versi(),
            $nama,
            md5(json_encode($konteks))
        );
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Throwable;
use __PHP_Incomplete_Class;

class CacheGuard
{
    public const BATAS_BYTE = 1048576;

    /**
     * Nilai dari cache bisa kembali sebagai objek cacat kalau payload-nya
     * pernah terpotong saat disimpan. unserialize() tidak melempar apa pun
     * untuk kasus itu, jadi kerusakannya baru meledak saat objeknya dipakai.
     */
    public static function utuh($nilai): bool
    {
        if ($nilai instanceof __PHP_Incomplete_Class) {
            return false;
        }

        if ($nilai instanceof Collection) {
            foreach ($nilai as $item) {
                if ($item instanceof __PHP_Incomplete_Class) {
                    return false;
                }

                break;
            }
        }

        return true;
    }

    /**
     * Payload besar berisiko terpotong oleh kolom cache atau batas paket
     * database, dan hasilnya justru rusak saat dibaca kembali. Lebih baik
     * tidak disimpan sama sekali.
     */
    public static function layakSimpan($nilai): bool
    {
        try {
            return strlen(serialize($nilai)) <= self::BATAS_BYTE;
        } catch (Throwable $e) {
            return false;
        }
    }
}

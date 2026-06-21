<?php

namespace App\Support;

class StaffEmailDomains
{
    public static function allows(string $email): bool
    {
        $domain = self::domain($email);

        return $domain !== '' && in_array($domain, self::allowedDomains(), true);
    }

    /**
     * @return list<string>
     */
    public static function allowedDomains(): array
    {
        return array_values(array_unique(array_filter(
            array_map(
                static fn (mixed $domain): string => strtolower(trim((string) $domain)),
                (array) config('security.staff_email_domains', [])
            ),
            static fn (string $domain): bool => $domain !== ''
        )));
    }

    private static function domain(string $email): string
    {
        $position = strrpos($email, '@');

        if ($position === false) {
            return '';
        }

        return strtolower(substr($email, $position + 1));
    }
}

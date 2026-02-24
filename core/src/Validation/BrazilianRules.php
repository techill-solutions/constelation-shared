<?php

declare(strict_types=1);

namespace Constelation\Shared\Validation;

final class BrazilianRules
{
    public static function isCpf(string $value): bool
    {
        $cpf = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        for ($digit = 9; $digit < 11; $digit++) {
            $sum = 0;
            for ($index = 0; $index < $digit; $index++) {
                $sum += (int) $cpf[$index] * (($digit + 1) - $index);
            }

            $rest = ($sum * 10) % 11;
            if ($rest === 10) {
                $rest = 0;
            }

            if ($rest !== (int) $cpf[$digit]) {
                return false;
            }
        }

        return true;
    }

    public static function isCnpj(string $value): bool
    {
        $cnpj = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights1[$i];
        }
        $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights2[$i];
        }
        $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        return $digit1 === (int) $cnpj[12] && $digit2 === (int) $cnpj[13];
    }

    public static function isCpfOrCnpj(string $value): bool
    {
        return self::isCpf($value) || self::isCnpj($value);
    }

    public static function isCep(string $value): bool
    {
        $cep = preg_replace('/\D+/', '', $value) ?? '';
        return strlen($cep) === 8;
    }

    public static function isPhone(string $value): bool
    {
        $phone = preg_replace('/\D+/', '', $value) ?? '';
        return strlen($phone) >= 10 && strlen($phone) <= 11;
    }
}

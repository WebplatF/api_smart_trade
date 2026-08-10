<?php

namespace App\Helper;

use Exception;
use Illuminate\Database\QueryException;

class DatabaseErrorHelper {
     public static function handle(QueryException $e): Exception
    {
        $sqlState = $e->errorInfo[0] ?? null; // SQLSTATE
        $errorCode = $e->errorInfo[1] ?? null; // Database-specific code
        $dbMessage = $e->errorInfo[2] ?? $e->getMessage();

        $message = match ($errorCode) {

            // Duplicate Entry
            1062 => self::duplicateMessage($dbMessage),

            // Foreign Key
            1451 => "Cannot delete or update because this record is referenced by another record.",
            1452 => "Referenced record does not exist.",

            // Data too long
            1406 => "The entered value is too long.",

            // Cannot be null
            1048 => self::nullMessage($dbMessage),

            // Default
            default => "Database operation failed."
        };

        return new Exception($message, 0, $e);
    }

    private static function duplicateMessage(string $message): string
    {
        if (preg_match("/for key '([^']+)'/", $message, $matches)) {

            $field = preg_replace('/_unique$/', '', $matches[1]);
            $field = preg_replace('/^[a-zA-Z0-9]+_/', '', $field);
            $field = ucwords(str_replace('_', ' ', $field));

            return "$field already exists.";
        }

        return "Duplicate record already exists.";
    }

    private static function nullMessage(string $message): string
    {
        if (preg_match("/Column '([^']+)'/", $message, $matches)) {

            $field = ucwords(str_replace('_', ' ', $matches[1]));

            return "$field cannot be empty.";
        }

        return "Required field is missing.";
    }
}

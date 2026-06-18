<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = ['key', 'locale', 'content'];

    public static function get(string $key, ?string $locale = null, ?string $default = null): string
    {
        $locale = $locale === 'en' ? 'en' : 'vi';

        $record = static::where('key', $key)->where('locale', $locale)->first();

        if ($record && $record->content !== null) {
            return $record->content;
        }

        // fallback to other locale
        $other = static::where('key', $key)->where('locale', $locale === 'en' ? 'vi' : 'en')->first();
        if ($other && $other->content !== null) {
            return $other->content;
        }

        return (string) $default;
    }
}

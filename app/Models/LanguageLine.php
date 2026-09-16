<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class LanguageLine extends \Spatie\TranslationLoader\LanguageLine
{
    public function getTranslation(string $locale): ?string
    {
        // if (! isset($this->text[$locale])) {
        //     $fallback = config('app.fallback_locale');
        //
        //     return $this->text[$fallback] ?? null;
        // }

        return $this->text[$locale] ?? null;
    }

    public static function getTranslationsForGroup(string $locale, string $group): array
    {
        return Cache::store('file')->rememberForever(static::getCacheKey($group, $locale), function () use ($group, $locale) {
            return static::query()
                    ->where('group', $group)
                    ->get()
                    ->reduce(function ($lines, \Spatie\TranslationLoader\LanguageLine $languageLine) use ($locale) {
                        $translation = $languageLine->getTranslation($locale);
                        if ($translation !== null) {
                            array_set($lines, $languageLine->key, $translation);
                        }

                        return $lines;
                    }) ?? [];
        });
    }

    public function flushGroupCache()
    {
        foreach ($this->getTranslatedLocales() as $locale) {
            Cache::store('file')->forget(static::getCacheKey($this->group, $locale));
        }
    }
}

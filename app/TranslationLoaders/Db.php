<?php

namespace App\TranslationLoaders;

use Spatie\TranslationLoader\LanguageLine;
use Spatie\TranslationLoader\Exceptions\InvalidConfiguration;
use Spatie\TranslationLoader\TranslationLoaders\TranslationLoader;

class Db implements TranslationLoader
{
    public function loadTranslations(string $locale, string $group): array
    {
        $model = $this->getConfiguredModelClass();
        $trans = [];

        if (defined('ETO_LANGUAGE_LINES_TABLE_EXISTS')) {
            try {
                $trans = $model::getTranslationsForGroup($locale, $group);
            }
            catch (\Exception $e) {
                // \Log::warning('Language lines table do not exists: '. $e->getMessage());
            }
        }

        return $trans;
    }

    protected function getConfiguredModelClass(): string
    {
        $modelClass = config('translation-loader.model');

        if (!is_a(new $modelClass, LanguageLine::class)) {
            throw InvalidConfiguration::invalidModel($modelClass);
        }

        return $modelClass;
    }
}

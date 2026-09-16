<?php

namespace App\Traits\Roles;

use Illuminate\Support\Str;

trait Slugable
{
    /**
     * Set slug attribute.
     *
     * @param string $value
     *
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $this->slug($value, config('roles.separator'));
    }

    public static function slug($title, $separator = '-')
    {
        $title = Str::ascii($title);
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $title = preg_replace('#['.preg_quote($flip).']+#u', $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('#[^!_|'.preg_quote($separator).'\pL\pN\s]+#u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('#['.preg_quote($separator).'\s]+#u', $separator, $title);

        return trim($title, $separator);
    }
}

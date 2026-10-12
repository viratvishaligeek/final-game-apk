<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToSite
{
    protected static function bootBelongsToSite(): void
    {
        static::addGlobalScope('site', function (Builder $builder): void {
            $siteKey = (string) config('sites.current_key', config('sites.default', 'play-online-khaiwal'));
            $builder->where($builder->getModel()->qualifyColumn('site_key'), $siteKey);
        });

        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('site_key'))) {
                $model->setAttribute(
                    'site_key',
                    (string) config('sites.current_key', config('sites.default', 'play-online-khaiwal'))
                );
            }
        });
    }
}

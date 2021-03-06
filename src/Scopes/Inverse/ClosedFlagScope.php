<?php

/*
 * This file is part of Laravel Eloquent Flag.
 *
 * (c) Anton Komarev <anton@komarev.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cog\Flag\Scopes\Inverse;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class ClosedFlagScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = [
        'UndoClose',
        'Close',
        'WithClosed',
        'WithoutClosed',
        'OnlyClosed',
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (method_exists($model, 'shouldApplyClosedFlagScope') && $model->shouldApplyClosedFlagScope()) {
            $builder->where('is_closed', 0);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the `undoClose` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addUndoClose(Builder $builder): void
    {
        $builder->macro('undoClose', function (Builder $builder) {
            $builder->withClosed();

            return $builder->update(['is_closed' => 0]);
        });
    }

    /**
     * Add the `close` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addClose(Builder $builder): void
    {
        $builder->macro('close', function (Builder $builder) {
            return $builder->update(['is_closed' => 1]);
        });
    }

    /**
     * Add the `withClosed` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithClosed(Builder $builder): void
    {
        $builder->macro('withClosed', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the `withoutClosed` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithoutClosed(Builder $builder): void
    {
        $builder->macro('withoutClosed', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)->where('is_closed', 0);
        });
    }

    /**
     * Add the `onlyClosed` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addOnlyClosed(Builder $builder): void
    {
        $builder->macro('onlyClosed', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)->where('is_closed', 1);
        });
    }
}

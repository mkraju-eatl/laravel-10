<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheInvalidation
{
    protected static function bootCacheInvalidation()
    {
        // Add to cache when a record is created
        static::created(function ($model) {
            $cacheKey = $model->getCacheKey($model->id);
            Cache::put($cacheKey, $model, now()->addHours(24)); // Store in cache for 24 hours
        });

        // Update cache when a record is updated
        static::updated(function ($model) {
            $cacheKey = $model->getCacheKey($model->id);
            Cache::put($cacheKey, $model, now()->addHours(24)); // Update in cache
        });

        // Remove from cache when a record is deleted
        static::deleted(function ($model) {
            $cacheKey = $model->getCacheKey($model->id);
            Cache::forget($cacheKey); // Remove from cache
        });
    }

    /**
     * Get the cache key for a specific record using the model's table and primary key.
     *
     * @param mixed $id
     * @return string
     */
    public function getCacheKey($id)
    {
        return $this->getTable() . '_' . $id;
    }

    /**
     * Get all cached records (optional: you can cache the entire list of records if needed).
     *
     * @return mixed
     */
    public static function getCachedRecords()
    {
        $cacheKey = (new static)->getTable() . '_list';

        // Check if cache exists, if not store all records
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            return static::all();
        });
    }

    /**
     * Retrieve a cached record by ID or fetch from database if not found in cache.
     *
     * @param mixed $id
     * @return mixed
     */
    public static function getCachedById($id)
    {
        $cacheKey = (new static)->getCacheKey($id);

        // Check if cache exists, if not store it
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($id) {
            return static::find($id);
        });
    }
}


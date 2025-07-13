<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_id'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function oglasi()
    {
        return $this->hasMany(Oglas::class, 'kategorija_id');
    }

    public function ads()
    {
        return $this->hasMany(Ad::class, 'kategorija_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function getFullPathAttribute()
    {
        $path = collect([$this]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $path;
    }

    public function getFullSlugAttribute()
    {
        return $this->fullPath->pluck('slug')->implode('/');
    }
}

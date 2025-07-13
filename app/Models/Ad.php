<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'naslov',
        'slug',
        'opis', 
        'cena',
        'stanje',
        'slika',
        'kontakt_telefon',
        'lokacija',
        'status',
        'kategorija_id',
        'user_id'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ad) {
            if (empty($ad->slug)) {
                $ad->slug = \Str::slug($ad->naslov);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kategorija()
    {
        return $this->belongsTo(Category::class, 'kategorija_id');
    }

    public function getFullUrlAttribute()
    {
        $categoryPath = $this->kategorija->full_slug;
        return "/oglasi/{$categoryPath}/{$this->slug}";
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}

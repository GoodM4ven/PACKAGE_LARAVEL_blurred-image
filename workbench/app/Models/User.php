<?php

declare(strict_types=1);

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use GoodMaven\BlurredImage\Concerns\HasBlurredImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
    use HasBlurredImages;

    /** @use HasFactory<\Workbench\Database\Factories\UserFactory> */
    use HasFactory;

    use InteractsWithMedia;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('profile')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addBlurredThumbnailConversion();
                $this
                    ->addMediaConversion('profile')
                    ->width(100)
                    ->height(100);
            });
    }
}

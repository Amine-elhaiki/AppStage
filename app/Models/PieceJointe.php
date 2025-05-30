<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PieceJointe extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'pieces_jointes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom_fichier',
        'type_fichier',
        'taille',
        'chemin',
        'id_rapport',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_creation' => 'datetime',
        'taille' => 'integer',
    ];

    /**
     * Boot method for the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->date_creation = now();
        });
    }

    /**
     * Relationship: Report this attachment belongs to
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'id_rapport');
    }

    /**
     * Get file size in human readable format
     */
    public function getFormattedSizeAttribute()
    {
        $size = $this->taille;

        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        } else {
            return round($size / 1073741824, 2) . ' GB';
        }
    }

    /**
     * Get file extension
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->nom_fichier, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($this->type_fichier, $imageTypes);
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf()
    {
        return $this->type_fichier === 'application/pdf';
    }

    /**
     * Check if file is a document
     */
    public function isDocument()
    {
        $docTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        return in_array($this->type_fichier, $docTypes);
    }

    /**
     * Get file icon class for UI
     */
    public function getIconClassAttribute()
    {
        if ($this->isImage()) {
            return 'fa-image';
        } elseif ($this->isPdf()) {
            return 'fa-file-pdf';
        } elseif (strpos($this->type_fichier, 'word') !== false) {
            return 'fa-file-word';
        } elseif (strpos($this->type_fichier, 'excel') !== false || strpos($this->type_fichier, 'sheet') !== false) {
            return 'fa-file-excel';
        } else {
            return 'fa-file';
        }
    }

    /**
     * Get file color class for UI
     */
    public function getColorClassAttribute()
    {
        if ($this->isImage()) {
            return 'text-success';
        } elseif ($this->isPdf()) {
            return 'text-danger';
        } elseif (strpos($this->type_fichier, 'word') !== false) {
            return 'text-primary';
        } elseif (strpos($this->type_fichier, 'excel') !== false) {
            return 'text-success';
        } else {
            return 'text-secondary';
        }
    }
}

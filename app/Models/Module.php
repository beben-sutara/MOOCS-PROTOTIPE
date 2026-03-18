<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    const TYPE_TEXT      = 'text';
    const TYPE_YOUTUBE   = 'youtube';
    const TYPE_IFRAME    = 'iframe';
    const TYPE_VIDEO_DRM = 'video_drm';
    const TYPE_QUIZ      = 'quiz';
    const TYPE_COACHING  = 'coaching';
    const TYPE_FILE      = 'file';
    const TYPE_AUDIO     = 'audio';
    const TYPE_TAG       = 'tag';

    public static function allTypes(): array
    {
        return [
            self::TYPE_TEXT, self::TYPE_YOUTUBE, self::TYPE_IFRAME,
            self::TYPE_VIDEO_DRM, self::TYPE_QUIZ, self::TYPE_COACHING,
            self::TYPE_FILE, self::TYPE_AUDIO, self::TYPE_TAG,
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_TEXT      => 'Teks',
            self::TYPE_YOUTUBE   => 'Embed YouTube',
            self::TYPE_IFRAME    => 'Embed iframe',
            self::TYPE_VIDEO_DRM => 'Video DRM',
            self::TYPE_QUIZ      => 'Kuis',
            self::TYPE_COACHING  => 'Coaching',
            self::TYPE_FILE      => 'File',
            self::TYPE_AUDIO     => 'Audio',
            self::TYPE_TAG       => 'Tag',
        ];
    }

    public static function typeIcons(): array
    {
        return [
            self::TYPE_TEXT      => 'bi-file-text',
            self::TYPE_YOUTUBE   => 'bi-youtube',
            self::TYPE_IFRAME    => 'bi-code-slash',
            self::TYPE_VIDEO_DRM => 'bi-shield-lock',
            self::TYPE_QUIZ      => 'bi-patch-question',
            self::TYPE_COACHING  => 'bi-camera-video',
            self::TYPE_FILE      => 'bi-file-earmark-arrow-up',
            self::TYPE_AUDIO     => 'bi-music-note-beamed',
            self::TYPE_TAG       => 'bi-tag',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? ucfirst($this->type);
    }

    public function typeIcon(): string
    {
        return self::typeIcons()[$this->type] ?? 'bi-journal';
    }

    protected $fillable = [
        'course_id',
        'section_id',
        'type',
        'title',
        'content',
        'order',
        'is_locked',
        'available_from',
        'available_until',
        'is_preview',
        'is_member_access',
        'prerequisite_module_id',
        'quiz_duration',
        'quiz_one_attempt',
        'quiz_required_for_next',
    ];

    protected $casts = [
        'is_locked'              => 'boolean',
        'is_preview'             => 'boolean',
        'is_member_access'       => 'boolean',
        'available_from'         => 'datetime',
        'available_until'        => 'datetime',
        'quiz_one_attempt'       => 'boolean',
        'quiz_required_for_next' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(Module::class, 'prerequisite_module_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Return a plain-text excerpt from the module content.
     * Handles both Editor.js JSON (new) and raw HTML (legacy TinyMCE).
     */
    public function excerpt(int $limit = 120): string
    {
        $content = $this->content ?? '';
        if ($content === '') {
            return '';
        }

        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // EditorJS format
            if (isset($decoded['blocks'])) {
                foreach ($decoded['blocks'] as $block) {
                    $type = $block['type'] ?? '';
                    $data = $block['data'] ?? [];

                    $text = match ($type) {
                        'paragraph', 'header', 'quote' => strip_tags($data['text'] ?? ''),
                        'list'    => strip_tags(
                            is_array($data['items'][0] ?? null)
                                ? ($data['items'][0]['content'] ?? '')
                                : ($data['items'][0] ?? '')
                        ),
                        'code'    => $data['code'] ?? '',
                        'warning' => strip_tags($data['title'] ?? $data['message'] ?? ''),
                        default   => '',
                    };

                    if ($text !== '') {
                        return \Illuminate\Support\Str::limit($text, $limit);
                    }
                }
                return '';
            }
            // Structured JSON (file, audio, youtube, etc.) — no text excerpt
            return '';
        }

        // Legacy HTML content (TinyMCE)
        return \Illuminate\Support\Str::limit(strip_tags($content), $limit);
    }
}

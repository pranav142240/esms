<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoticeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->when($request->get('detailed'), $this->content),
            'excerpt' => $this->excerpt,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'priority' => $this->priority,
            'priority_color' => $this->priority_color,
            'target_audience' => $this->target_audience,
            'audience_label' => $this->audience_label,
            'class_ids' => $this->when(
                $this->target_audience === 'specific_classes',
                $this->class_ids
            ),
            'status' => $this->status,
            'status_color' => $this->status_color,
            'is_published' => $this->is_published,
            'is_urgent' => $this->is_urgent,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'view_count' => $this->view_count,
            'reading_time' => $this->reading_time,
            'has_attachments' => $this->hasAttachments(),
            'attachment' => $this->when($this->attachment_path, [
                'name' => $this->attachment_name,
                'path' => $this->attachment_path,
                'url' => $this->attachment_path ? asset('storage/' . $this->attachment_path) : null,
            ]),
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'path' => $attachment->path,
                        'url' => asset('storage/' . $attachment->path),
                        'size' => $attachment->size,
                        'type' => $attachment->type,
                    ];
                });
            }),
            'comments_count' => $this->whenCounted('comments'),
            'comments' => $this->whenLoaded('comments', function () {
                return $this->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                        'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            'dates' => [
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $this->created_at->diffForHumans(),
                'updated_at_human' => $this->updated_at->diffForHumans(),
                'published_at_human' => $this->published_at?->diffForHumans(),
                'expires_at_human' => $this->expires_at?->diffForHumans(),
            ],
            'flags' => [
                'is_active' => $this->isActive(),
                'is_expired' => $this->isExpired(),
                'is_scheduled' => $this->isScheduled(),
                'can_edit' => $this->created_by === auth()->id() || auth()->user()?->hasRole('admin'),
                'can_delete' => $this->created_by === auth()->id() || auth()->user()?->hasRole('admin'),
            ],
            'meta' => [
                'word_count' => str_word_count(strip_tags($this->content)),
                'character_count' => strlen(strip_tags($this->content)),
                'days_until_expiry' => $this->expires_at ? 
                    max(0, $this->expires_at->diffInDays(now(), false)) : null,
                'days_since_published' => $this->published_at ? 
                    $this->published_at->diffInDays(now()) : null,
            ],
        ];
    }
}

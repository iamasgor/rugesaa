<?php

namespace App\Http\Middleware;

use App\Enums\SitePage;
use App\Models\PageContent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lays contact details and social links saved in the admin panel over `config('rugesaa')`,
 * so every view and the contact form read one source.
 */
class ApplyContactSettings
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $saved = PageContent::query()->where('page', SitePage::Contact)->first();

        if ($saved !== null) {
            $content = SitePage::Contact->mergeWithDefaults($saved->content);

            config([
                'rugesaa.email' => $content['association']['email'] ?? null,
                'rugesaa.phone' => $content['association']['phone'] ?? null,
                'rugesaa.office_hours' => $content['association']['office_hours'] ?? null,
                'rugesaa.social' => self::socialLinks($content['social']['links'] ?? []),
                'rugesaa.department' => array_merge((array) config('rugesaa.department'), $content['department']),
            ]);
        }

        return $next($request);
    }

    /**
     * Saved `[{label, url}]` items as the `label => url` map the views expect.
     *
     * @return array<string, string>
     */
    private static function socialLinks(mixed $links): array
    {
        $map = [];

        foreach (is_array($links) ? $links : [] as $link) {
            if (is_array($link) && is_string($link['label'] ?? null) && is_string($link['url'] ?? null) && $link['label'] !== '' && $link['url'] !== '') {
                $map[$link['label']] = $link['url'];
            }
        }

        return $map;
    }
}

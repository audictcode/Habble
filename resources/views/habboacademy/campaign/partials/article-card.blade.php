@php
    $viewerName = auth()->check() ? (auth()->user()->habbo_name ?: auth()->user()->username) : ($campaign->author_name ?: 'Usuario');
    $isInfoCampaign = ($campaign->target_page ?? '') === 'informacion-campana';
    $titleText = str_replace('{usuario}', $viewerName, (string) $campaign->title);
    $excerptSource = (string) ($campaign->excerpt ?? '');
    if (trim($excerptSource) === '' && ($campaign->target_page ?? '') === 'informacion-campana' && filled($campaign->month_label)) {
        $excerptSource = 'Novedades y contenido de ' . $campaign->month_label . '.';
    }
    $excerptText = str_replace('{usuario}', $viewerName, $excerptSource);

    $bodyHtmlSource = str_replace('{usuario}', $viewerName, (string) ($campaign->body_html ?? ''));
    $trimmedBodyHtmlSource = trim($bodyHtmlSource);
    if ($trimmedBodyHtmlSource !== '' && preg_match('/^<pre\\b[^>]*>/i', $trimmedBodyHtmlSource)) {
        $bodyHtmlSource = preg_replace('/^<pre\\b[^>]*>|<\\/pre>$/i', '', $trimmedBodyHtmlSource) ?? $bodyHtmlSource;
    }
    if (str_contains($bodyHtmlSource, '&lt;') && str_contains($bodyHtmlSource, '&gt;')) {
        $bodyHtmlSource = html_entity_decode($bodyHtmlSource, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    // Remove imported article header blocks (thumbnail + duplicated title/meta from external source).
    $bodyHtmlSource = preg_replace('/<header\\b[^>]*class="[^"]*post-header[^"]*"[^>]*>.*?<\\/header>/is', '', $bodyHtmlSource) ?? $bodyHtmlSource;
    $bodyHtmlSource = preg_replace('/<header\\b[^>]*class="[^"]*campaign-header[^"]*"[^>]*>.*?<\\/header>/is', '', $bodyHtmlSource) ?? $bodyHtmlSource;

    // Strip embedded "comments / leave a reply" sections from imported external HTML.
    $commentMarkers = [
        'id="comments"',
        "id='comments'",
        'class="comment-respond"',
        "class='comment-respond'",
        'id="respond"',
        "id='respond'",
        'deja tu comentario',
        'Deja tu comentario',
    ];
    $firstCommentMarkerPos = null;
    foreach ($commentMarkers as $marker) {
        $markerPos = stripos($bodyHtmlSource, $marker);
        if ($markerPos !== false && ($firstCommentMarkerPos === null || $markerPos < $firstCommentMarkerPos)) {
            $firstCommentMarkerPos = $markerPos;
        }
    }
    if ($firstCommentMarkerPos !== null) {
        $bodyHtmlSource = substr($bodyHtmlSource, 0, $firstCommentMarkerPos);
    }
    if ($isInfoCampaign && str_contains($bodyHtmlSource, 'href=')) {
        $bodyHtmlSource = preg_replace_callback('/href\\s*=\\s*([\'"])(.*?)\\1/i', function (array $matches) {
            $quote = $matches[1];
            $url = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $parts = @parse_url($url);
            if (!is_array($parts)) {
                return 'href=' . $quote . $matches[2] . $quote;
            }

            $query = [];
            if (isset($parts['query']) && $parts['query'] !== '') {
                parse_str($parts['query'], $query);
            }

            $forbiddenKeys = [
                'ref',
                'referral',
                'referral_code',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_id',
                'utm_term',
                'utm_content',
            ];
            foreach ($forbiddenKeys as $key) {
                unset($query[$key]);
            }

            $rebuilt = '';
            if (isset($parts['scheme'])) {
                $rebuilt .= $parts['scheme'] . '://';
            }
            if (isset($parts['user'])) {
                $rebuilt .= $parts['user'];
                if (isset($parts['pass'])) {
                    $rebuilt .= ':' . $parts['pass'];
                }
                $rebuilt .= '@';
            }
            if (isset($parts['host'])) {
                $rebuilt .= $parts['host'];
            }
            if (isset($parts['port'])) {
                $rebuilt .= ':' . $parts['port'];
            }
            if (isset($parts['path'])) {
                $rebuilt .= $parts['path'];
            }
            if (!empty($query)) {
                $rebuilt .= '?' . http_build_query($query);
            }
            if (isset($parts['fragment']) && $parts['fragment'] !== '') {
                $rebuilt .= '#' . $parts['fragment'];
            }

            return 'href=' . $quote . e($rebuilt !== '' ? $rebuilt : $url) . $quote;
        }, $bodyHtmlSource) ?? $bodyHtmlSource;
    }
    $bodyHtml = $bodyHtmlSource;

    $bannerValue = academyMediaUrl((string) ($campaign->banner_image_path ?? ''));

    $authorName = $campaign->author_name ?: 'Reportero';
    $authorAvatar = $campaign->author_avatar_url ?: ('https://www.habbo.es/habbo-imaging/avatarimage?user=' . urlencode($authorName) . '&direction=2&head_direction=2&headonly=1&size=l');
    $publishedAtText = optional($campaign->published_at ?: $campaign->created_at)->format('d/m/Y H:i');
    $filteredInfoCells = [];
    if (is_array($campaign->info_cells)) {
        $filteredInfoCells = collect($campaign->info_cells)
            ->filter(function ($cell) use ($isInfoCampaign) {
                $title = strtolower(trim((string) ($cell['title'] ?? '')));
                if (!$isInfoCampaign) {
                    return true;
                }

                return !in_array($title, ['raros', 'lotes', 'lotes de sala'], true);
            })
            ->values()
            ->all();
    }
@endphp

<article class="campaign-article">
    @if (filled($bannerValue))
        <div class="campaign-banner-wrap">
            <img class="campaign-banner" src="{{ $bannerValue }}" alt="{{ $titleText }} banner">
            <div class="campaign-banner-overlay">
                <div class="campaign-banner-top">
                    <h1 class="campaign-title">{{ $titleText }}</h1>
                    @if(filled($excerptText))
                        <p class="campaign-banner-excerpt">{{ $excerptText }}</p>
                    @endif
                </div>
                <div class="campaign-banner-bottom">
                    <p class="campaign-banner-author">
                        <img src="{{ $authorAvatar }}" alt="{{ $authorName }} avatar">
                        <span class="campaign-banner-author-meta">
                            <span class="campaign-banner-author-name">{{ $authorName }}</span>
                            <span class="campaign-banner-author-date">{{ $publishedAtText }}</span>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    @else
        <h1 class="campaign-title">{{ $titleText }}</h1>
    @endif

    @if(!filled($bannerValue) && filled($excerptText))
        <p class="campaign-excerpt">{{ $excerptText }}</p>
    @endif

    @if(count($filteredInfoCells))
        <section class="campaign-cells-grid">
            @foreach($filteredInfoCells as $cell)
                <div class="campaign-cell">
                    <h4>
                        @if(!empty($cell['icon']))
                            <i class="{{ $cell['icon'] }}"></i>
                        @endif
                        {{ $cell['title'] ?? 'Celda' }}
                    </h4>
                    <p>{{ $cell['value'] ?? '' }}</p>
                </div>
            @endforeach
        </section>
    @endif

    @if(filled($bodyHtml))
        <section class="campaign-body">{!! $bodyHtml !!}</section>
    @endif

    @unless($isInfoCampaign)
        <footer class="campaign-actions">
            @if(filled($campaign->primary_button_text) && filled($campaign->primary_button_url))
                <a class="campaign-button" style="--btn-color: {{ $campaign->primary_button_color ?: '#0095ff' }};" href="{{ url($campaign->primary_button_url) }}">
                    {{ $campaign->primary_button_text }}
                </a>
            @endif
            @if(filled($campaign->secondary_button_text) && filled($campaign->secondary_button_url))
                <a class="campaign-button secondary" style="--btn-color: {{ $campaign->secondary_button_color ?: '#1f2937' }};" href="{{ url($campaign->secondary_button_url) }}">
                    {{ $campaign->secondary_button_text }}
                </a>
            @endif
        </footer>
    @endunless

    <footer class="campaign-post-meta">
        <div class="campaign-meta-row">
            @if(filled($campaign->month_label))
                <span class="campaign-meta-badge top"><i class="fas fa-fire-alt"></i> {{ $campaign->month_label }}</span>
            @endif
        </div>
    </footer>
</article>

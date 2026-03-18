@php
    $decoded = null;
    $isJson = false;
    if (!empty($content)) {
        $trimmed = trim($content);
        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['blocks'])) {
                $isJson = true;
            }
        }
    }
@endphp

@if($isJson && !empty($decoded['blocks']))
    <div class="editorjs-content">
        @foreach($decoded['blocks'] as $block)
            @php $type = $block['type'] ?? ''; $data = $block['data'] ?? []; @endphp

            @switch($type)

                @case('paragraph')
                    <p>{!! $data['text'] ?? '' !!}</p>
                    @break

                @case('header')
                    @switch($data['level'] ?? 2)
                        @case(3) <h3>{!! $data['text'] ?? '' !!}</h3> @break
                        @case(4) <h4>{!! $data['text'] ?? '' !!}</h4> @break
                        @default  <h2>{!! $data['text'] ?? '' !!}</h2>
                    @endswitch
                    @break

                @case('list')
                    @php
                        $ordered = ($data['style'] ?? 'unordered') === 'ordered';
                        $items   = $data['items'] ?? [];
                    @endphp
                    @if($ordered)<ol>@else<ul>@endif
                    @foreach($items as $item)
                        @php $text = is_array($item) ? ($item['content'] ?? '') : $item; @endphp
                        <li>{!! $text !!}</li>
                    @endforeach
                    @if($ordered)</ol>@else</ul>@endif
                    @break

                @case('code')
                    <pre><code>{{ $data['code'] ?? '' }}</code></pre>
                    @break

                @case('quote')
                    <blockquote class="editorjs-quote">
                        <p>{!! $data['text'] ?? '' !!}</p>
                        @if(!empty($data['caption']))
                            <cite>— {!! $data['caption'] !!}</cite>
                        @endif
                    </blockquote>
                    @break

                @case('table')
                    @php
                        $rows        = $data['content'] ?? [];
                        $withHeading = $data['withHeadings'] ?? false;
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            @if($withHeading && !empty($rows[0]))
                                <thead><tr>@foreach($rows[0] as $cell)<th>{!! $cell !!}</th>@endforeach</tr></thead>
                                <tbody>
                                @foreach(array_slice($rows, 1) as $row)
                                    <tr>@foreach($row as $cell)<td>{!! $cell !!}</td>@endforeach</tr>
                                @endforeach
                                </tbody>
                            @else
                                <tbody>
                                @foreach($rows as $row)
                                    <tr>@foreach($row as $cell)<td>{!! $cell !!}</td>@endforeach</tr>
                                @endforeach
                                </tbody>
                            @endif
                        </table>
                    </div>
                    @break

                @case('delimiter')
                    <hr class="editorjs-delimiter">
                    @break

                @case('embed')
                    @if(!empty($data['embed']))
                        <figure class="editorjs-embed">
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ $data['embed'] }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy"></iframe>
                            </div>
                            @if(!empty($data['caption']))
                                <figcaption>{!! $data['caption'] !!}</figcaption>
                            @endif
                        </figure>
                    @endif
                    @break

                @case('checklist')
                    <ul class="editorjs-checklist list-unstyled">
                        @foreach($data['items'] ?? [] as $item)
                            <li class="editorjs-checklist-item">
                                @if($item['checked'] ?? false)
                                    <i class="bi bi-check-square-fill text-success me-2"></i>
                                @else
                                    <i class="bi bi-square text-muted me-2"></i>
                                @endif
                                <span>{!! $item['text'] ?? '' !!}</span>
                            </li>
                        @endforeach
                    </ul>
                    @break

                @case('warning')
                    <div class="alert alert-warning editorjs-warning d-flex gap-2 align-items-start">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            @if(!empty($data['title']))<strong>{{ $data['title'] }}</strong><br>@endif
                            @if(!empty($data['message'])){!! $data['message'] !!}@endif
                        </div>
                    </div>
                    @break

                @case('image')
                    @php $imgUrl = $data['file']['url'] ?? ($data['url'] ?? ''); @endphp
                    @if($imgUrl)
                        <figure class="editorjs-image{{ ($data['withBorder'] ?? false) ? ' with-border' : '' }}{{ ($data['withBackground'] ?? false) ? ' with-background' : '' }}{{ ($data['stretched'] ?? false) ? ' stretched' : '' }}">
                            <img src="{{ $imgUrl }}" alt="{{ $data['caption'] ?? '' }}" loading="lazy">
                            @if(!empty($data['caption']))
                                <figcaption>{!! $data['caption'] !!}</figcaption>
                            @endif
                        </figure>
                    @endif
                    @break

                @case('raw')
                    {!! $data['html'] ?? '' !!}
                    @break

            @endswitch
        @endforeach
    </div>
@elseif(!empty($content))
    {{-- Backward compatibility: render legacy HTML content (TinyMCE output) --}}
    <div class="editorjs-content module-content-legacy">
        {!! $content !!}
    </div>
@endif

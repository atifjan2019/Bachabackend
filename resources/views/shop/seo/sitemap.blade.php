<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($entries as $e)
    <url>
        <loc>{{ $e['loc'] }}</loc>
        <lastmod>{{ $e['lastmod'] }}</lastmod>
        <changefreq>{{ $e['changefreq'] }}</changefreq>
        <priority>{{ $e['priority'] }}</priority>
    </url>
@endforeach
</urlset>

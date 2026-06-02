<?php
echo "<h2>PHP OK - version " . PHP_VERSION . "</h2>";

// Test file_get_contents vers Supabase
$url = 'https://unwrghiiocaztnecmpeh.supabase.co/rest/v1/businesses?limit=1&select=id,name,slug';
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", [
            "apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVud3JnaGlpb2NhenRuZWNtcGVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ2Mjc4NTUsImV4cCI6MjA4MDIwMzg1NX0.m9s85OKGVTQbItxB8bHaCpfpvICRf5tWSztUyLvOeZw",
            "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVud3JnaGlpb2NhenRuZWNtcGVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ2Mjc4NTUsImV4cCI6MjA4MDIwMzg1NX0.m9s85OKGVTQbItxB8bHaCpfpvICRf5tWSztUyLvOeZw",
        ])
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);

if ($result === false) {
    $error = error_get_last();
    echo "<h3 style='color:red'>ERREUR file_get_contents</h3>";
    echo "<pre>" . print_r($error, true) . "</pre>";
    
    // Test si allow_url_fopen est activé
    echo "<p>allow_url_fopen : " . (ini_get('allow_url_fopen') ? 'OUI' : 'NON') . "</p>";
    echo "<p>curl disponible : " . (function_exists('curl_init') ? 'OUI' : 'NON') . "</p>";
} else {
    echo "<h3 style='color:green'>Supabase OK !</h3>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
}
?>

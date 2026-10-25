$ErrorActionPreference = 'Stop'
$projectRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$releaseDirectory = Join-Path $projectRoot 'dist'
[IO.Directory]::CreateDirectory($releaseDirectory) | Out-Null
$archivePath = Join-Path $releaseDirectory ('dbt-web-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.zip')
$files = [Collections.Generic.List[string]]::new()
foreach ($relativePath in @('.htaccess', '.user.ini', '.env.example', 'index.php', 'error.php', 'favicon.ico', 'uploads/.htaccess', 'scratch/.gitkeep', 'tools/check-production.php', 'tools/create-admin.php', 'tools/install-schema.php', 'tools/setup-production.php', 'tools/package-release.php', 'docs/production.md', 'deploy/php-production.ini', 'deploy/apache-production.conf', 'database/migrations/2026_10_class_timetable_images.php')) {
    $full = Join-Path $projectRoot $relativePath
    if (Test-Path -LiteralPath $full) {
        $files.Add($full)
    }
}
foreach ($directory in @('admin', 'api', 'assets', 'includes', 'pages', 'database/schema')) {
    foreach ($file in Get-ChildItem -LiteralPath (Join-Path $projectRoot $directory) -Recurse -File) {
        if (($file.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) { throw 'Release inputs must not contain symlinks' }
        $files.Add($file.FullName)
    }
}
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.IO.Compression
$stream = [IO.File]::Open($archivePath, [IO.FileMode]::CreateNew)
$archive = [IO.Compression.ZipArchive]::new($stream, [IO.Compression.ZipArchiveMode]::Create)
try {
    foreach ($file in $files) {
        $resolved = [IO.Path]::GetFullPath($file)
        if (!$resolved.StartsWith($projectRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Release input escaped the project' }
        $entryName = $resolved.Substring($projectRoot.Length + 1).Replace('\', '/')
        [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $resolved, $entryName, [IO.Compression.CompressionLevel]::Optimal) | Out-Null
    }
} finally {
    $archive.Dispose()
    $stream.Dispose()
}
$hash = (Get-FileHash -LiteralPath $archivePath -Algorithm SHA256).Hash
[IO.File]::WriteAllText($archivePath + '.sha256', $hash + '  ' + [IO.Path]::GetFileName($archivePath) + [Environment]::NewLine)
Write-Output $archivePath
Write-Output "SHA256: $hash"

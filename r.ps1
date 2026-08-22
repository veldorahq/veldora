$token = "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"

$headers = @{
    Authorization = "Bearer $token"
    "Content-Type" = "application/json"
    Accept = "application/vnd.github+json"
}

$releaseNotes = @"
## Veldora Language Support v0.5.0

### 🎉 What's New

- Official Visual Studio Code Marketplace release
- Added Marketplace icon
- Added branded Marketplace banner
- Improved extension metadata and discoverability
- Added framework-related keywords
- Updated README with Marketplace installation
- General documentation improvements

### Features

- Syntax Highlighting
- IntelliSense Snippets
- Language Configuration
- Bracket Matching
- PHP Embedding
- Blade-inspired Directives

### Install

```bash
code --install-extension veldora.veldora-vscode
```

Or install directly from the Visual Studio Code Marketplace.
"@

$body = ConvertTo-Json -Depth 5 @{
    tag_name         = "v0.5.0"
    target_commitish = "main"
    name             = "v0.5.0 - Marketplace Release"
    body             = $releaseNotes
    draft            = $false
    prerelease       = $false
}

$release = Invoke-RestMethod `
    -Method POST `
    -Uri "https://api.github.com/repos/veldorahq/veldora-vscode/releases" `
    -Headers $headers `
    -Body $body

Write-Host "Release ID: $($release.id)"
Write-Host "URL: $($release.html_url)"

$assetHeaders = @{
    Authorization = "Bearer $token"
    "Content-Type" = "application/octet-stream"
    Accept         = "application/vnd.github+json"
}

$bytes = [System.IO.File]::ReadAllBytes("D:\Veldora\veldora-vscode\download\v0.5.0\veldora-vscode-0.5.0.vsix")

$asset = Invoke-RestMethod `
    -Method POST `
    -Uri "https://uploads.github.com/repos/veldorahq/veldora-vscode/releases/$($release.id)/assets?name=veldora-vscode-0.5.0.vsix" `
    -Headers $assetHeaders `
    -Body $bytes

Write-Host "Asset URL: $($asset.browser_download_url)"
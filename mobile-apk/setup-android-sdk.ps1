# setup-android-sdk.ps1
# Script otomatis setup Android SDK + build APK Presensi PKBM Pikat
# Jalankan sekali saja dari folder root project Laravel

$ANDROID_HOME = "C:\Android\Sdk"
$CMDLINE_TOOLS = "$ANDROID_HOME\cmdline-tools\latest"

Write-Host "`n=== STEP 1: Extract Command Line Tools ===" -ForegroundColor Cyan

# Extract zip ke folder sementara
$zipFile = "C:\Android\cmdline-tools.zip"
$extractTo = "C:\Android\_extract"

if (-not (Test-Path $zipFile)) {
    Write-Host "ERROR: File $zipFile tidak ditemukan. Download dulu!" -ForegroundColor Red
    exit 1
}

Write-Host "Extracting..."
Expand-Archive -Path $zipFile -DestinationPath $extractTo -Force

# Capacitor perlu struktur: cmdline-tools/latest/bin/sdkmanager
# Zip menghasilkan: cmdline-tools/bin/sdkmanager → perlu dipindah ke latest/
$extracted = "$extractTo\cmdline-tools"
$target = "$ANDROID_HOME\cmdline-tools\latest"
New-Item -ItemType Directory -Force -Path $target | Out-Null
Copy-Item -Path "$extracted\*" -Destination $target -Recurse -Force
Remove-Item -Path $extractTo -Recurse -Force
Write-Host "Extract selesai di: $target" -ForegroundColor Green

Write-Host "`n=== STEP 2: Set Environment Variables ===" -ForegroundColor Cyan

[System.Environment]::SetEnvironmentVariable("ANDROID_HOME", $ANDROID_HOME, "User")
[System.Environment]::SetEnvironmentVariable("ANDROID_SDK_ROOT", $ANDROID_HOME, "User")

# Tambahkan ke PATH
$currentPath = [System.Environment]::GetEnvironmentVariable("PATH", "User")
$additions = @(
    "$ANDROID_HOME\cmdline-tools\latest\bin",
    "$ANDROID_HOME\platform-tools"
)
foreach ($add in $additions) {
    if ($currentPath -notlike "*$add*") {
        $currentPath = "$add;$currentPath"
    }
}
[System.Environment]::SetEnvironmentVariable("PATH", $currentPath, "User")

# Set juga untuk session ini
$env:ANDROID_HOME = $ANDROID_HOME
$env:ANDROID_SDK_ROOT = $ANDROID_HOME
$env:PATH = "$ANDROID_HOME\cmdline-tools\latest\bin;$ANDROID_HOME\platform-tools;$env:PATH"

Write-Host "ANDROID_HOME = $ANDROID_HOME" -ForegroundColor Green

Write-Host "`n=== STEP 3: Install Build Tools via sdkmanager ===" -ForegroundColor Cyan

$sdkmanager = "$CMDLINE_TOOLS\bin\sdkmanager.bat"
if (-not (Test-Path $sdkmanager)) {
    Write-Host "ERROR: sdkmanager tidak ditemukan di $sdkmanager" -ForegroundColor Red
    exit 1
}

Write-Host "Menerima lisensi..."
echo "y" | & $sdkmanager --licenses 2>&1 | Where-Object { $_ -match "license" -or $_ -match "accepted" }

Write-Host "Install platform-tools, build-tools, platform Android 34..."
& $sdkmanager "platform-tools" "build-tools;34.0.0" "platforms;android-34" 2>&1 | Where-Object { $_ -notmatch "^\[=" }

Write-Host "SDK packages installed!" -ForegroundColor Green

Write-Host "`n=== STEP 4: Build APK Debug ===" -ForegroundColor Cyan

$androidDir = "mobile-apk\android"
if (-not (Test-Path $androidDir)) {
    Write-Host "ERROR: Folder $androidDir tidak ditemukan. Jalankan dari root project Laravel." -ForegroundColor Red
    exit 1
}

Set-Location $androidDir
Write-Host "Building APK (proses ini 2-5 menit pertama kali)..."
.\gradlew.bat assembleDebug 2>&1

$apkPath = "app\build\outputs\apk\debug\app-debug.apk"
if (Test-Path $apkPath) {
    $size = (Get-Item $apkPath).Length / 1MB
    Write-Host "`n✅ APK BERHASIL DIBUILD!" -ForegroundColor Green
    Write-Host "Lokasi: $(Resolve-Path $apkPath)" -ForegroundColor Yellow
    Write-Host "Ukuran: $([math]::Round($size, 1)) MB" -ForegroundColor Yellow
} else {
    Write-Host "`n❌ Build gagal. Lihat output di atas untuk detail error." -ForegroundColor Red
}

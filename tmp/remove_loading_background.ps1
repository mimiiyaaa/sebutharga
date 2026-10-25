Add-Type -AssemblyName System.Drawing

$sourcePath = 'C:\laragon\www\sebutharga\public\images\loading-mascot.png'
$outputPath = 'C:\laragon\www\sebutharga\public\images\loading-mascot-transparent.png'
$bitmap = [System.Drawing.Bitmap]::new($sourcePath)
$width = $bitmap.Width
$height = $bitmap.Height
$background = $bitmap.GetPixel(0, 0)
$threshold = 48
$visited = New-Object 'bool[,]' $width, $height
$queue = New-Object System.Collections.Generic.Queue[System.Drawing.Point]

for ($x = 0; $x -lt $width; $x++) {
    $queue.Enqueue([System.Drawing.Point]::new($x, 0))
    $queue.Enqueue([System.Drawing.Point]::new($x, $height - 1))
}
for ($y = 1; $y -lt ($height - 1); $y++) {
    $queue.Enqueue([System.Drawing.Point]::new(0, $y))
    $queue.Enqueue([System.Drawing.Point]::new($width - 1, $y))
}

while ($queue.Count -gt 0) {
    $point = $queue.Dequeue()
    $x = $point.X
    $y = $point.Y
    if ($x -lt 0 -or $x -ge $width -or $y -lt 0 -or $y -ge $height -or $visited[$x, $y]) { continue }
    $visited[$x, $y] = $true
    $pixel = $bitmap.GetPixel($x, $y)
    $distance = [Math]::Sqrt([Math]::Pow($pixel.R - $background.R, 2) + [Math]::Pow($pixel.G - $background.G, 2) + [Math]::Pow($pixel.B - $background.B, 2))
    if ($distance -le $threshold) {
        $bitmap.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(0, $pixel.R, $pixel.G, $pixel.B))
        $queue.Enqueue([System.Drawing.Point]::new($x + 1, $y))
        $queue.Enqueue([System.Drawing.Point]::new($x - 1, $y))
        $queue.Enqueue([System.Drawing.Point]::new($x, $y + 1))
        $queue.Enqueue([System.Drawing.Point]::new($x, $y - 1))
    }
}

$bitmap.Save($outputPath, [System.Drawing.Imaging.ImageFormat]::Png)
$bitmap.Dispose()
Write-Output $outputPath

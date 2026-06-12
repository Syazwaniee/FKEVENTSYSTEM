<?php

include '../INCLUDE/db.php';

function pdfEscape(string $text): string
{
    return str_replace(
        ['\\', '(', ')'],
        ['\\\\', '\\(', '\\)'],
        $text
    );
}

function buildPdfPage(array $lines): string
{
    $stream = "BT /F1 10 Tf 40 572 Td ";
    $first = true;

    foreach ($lines as $line) {
        $escaped = pdfEscape($line);

        if ($first) {
            $stream .= "(" . $escaped . ") Tj ";
            $first = false;
        } else {
            $stream .= "0 -14 Td (" . $escaped . ") Tj ";
        }
    }

    $stream .= "ET";
    $length = strlen($stream);

    return "<< /Length $length >>\nstream\n$stream\nendstream";
}

function createPdf(array $pages): string
{
    $objects = [];

    // Catalog
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";

    // Pages container
    $kids = [];
    $pageCount = count($pages);
    for ($i = 0; $i < $pageCount; $i++) {
        $kids[] = ($i + 3) . " 0 R";
    }
    $objects[] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $pageCount >>";

    // Page objects
    $fontObjectId = 3 + $pageCount;
    $firstContentObjectId = $fontObjectId + 1;

    for ($i = 0; $i < $pageCount; $i++) {
        $contentId = $firstContentObjectId + $i;
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 792 612] /Resources << /ProcSet [/PDF /Text] /Font << /F1 $fontObjectId 0 R >> >> /Contents $contentId 0 R >>";
    }

    // Font object
    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";

    // Content streams
    foreach ($pages as $page) {
        $objects[] = buildPdfPage($page);
    }

    $pdf = "%PDF-1.3\n%âãÏÓ\n";
    $offsets = [];

    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= sprintf("%010d 65535 f \n", 0);

    foreach ($offsets as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }

    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

    return $pdf;
}

$query = mysqli_query($conn, "SELECT * FROM attendance ORDER BY id DESC");
$rows = [];

while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

$header = sprintf(
    "%-20s %-12s %-15s %-18s %-10s %-8s %-10s %-10s %-6s",
    'Student Name',
    'Student ID',
    'Club Name',
    'Event Name',
    'Date',
    'Time',
    'Status',
    'Volunteer',
    'Points'
);

$lines = [
    'Attendance Report',
    '',
    $header,
    str_repeat('-', 115),
];

foreach ($rows as $row) {
    $lines[] = sprintf(
        "%-20.20s %-12.12s %-15.15s %-18.18s %-10.10s %-8.8s %-10.10s %-10.10s %-6.6s",
        $row['student_name'] ?? '',
        $row['student_id'] ?? '',
        $row['club_name'] ?? '',
        $row['event_name'] ?? '',
        $row['attendance_date'] ?? '',
        $row['attendance_time'] ?? '',
        $row['attendance_status'] ?? '',
        $row['volunteer_status'] ?? '',
        $row['points'] ?? ''
    );
}

$maxLinesPerPage = 35;
$pages = array_chunk($lines, $maxLinesPerPage);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="attendance-report.pdf"');

echo createPdf($pages);
exit;

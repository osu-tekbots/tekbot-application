<?php
// NEW DIR = \dept_www\eecs_www\education\tekbotSuite\tekbot\uploads\tickets
// OLD DIR \dept_www\eecs_www\education\labs\image\uploads
function BulkMoveFiles($oldDir, $newDir) {
    $files = scandir($oldDir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $oldPath = $oldDir . '/' . $file;
        $newPath = $newDir . '/' . $file;
        copy($oldPath, $newPath);
    }
}
?>
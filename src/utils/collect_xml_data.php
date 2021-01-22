#!/usr/share/tuleap/src/utils/php-launcher.sh
<?php
/**
 * Take a project archive generated by export_project_xml.php on a remote server
 * and look for files that are missing in the archive.
 *
 * This will replaces data/stuff that contains only path to files by actual file
 * content into the zip archive.
 */

if (! file_exists($argv[1])) {
    fwrite(STDERR, "Usage: collect_xml_data.php archive.zip" . PHP_EOL);
    exit(1);
}

$content = getArchiveFileReferences($argv[1]);
replaceReferencesByActualFiles($argv[1], $content);

function getArchiveFileReferences($archive_path)
{
    $content = new TuleapArchiveContent();

    $src_archive = new ZipArchive();

    if ($src_archive->open($archive_path) !== true) {
        fwrite(STDERR, "*** ERROR: unable to open $archive_path for read" . PHP_EOL);
        exit(1);
    }

    for ($i = 0; $i < $src_archive->numFiles; $i++) {
        $filename = $src_archive->getNameIndex($i);
        if (strpos($filename, 'data/') !== false) {
            $target_file = getFileContentFromArchive($src_archive, $filename);
            if ($target_file) {
                $content->addFileReference($filename, $target_file);
            }
        } else {
            $target_content = getFileContentFromArchive($src_archive, $filename);
            $content->addFileContent($filename, $target_content);
        }
    }

    $src_archive->close();

    return $content;
}

function getFileContentFromArchive(ZipArchive $archive, $filename)
{
    $fp       = $archive->getStream($filename);
    $contents = '';
    while (! feof($fp)) {
        $contents .= fread($fp, 4096);
    }
    fclose($fp);
    return $contents;
}

function replaceReferencesByActualFiles($archive_path, TuleapArchiveContent $content)
{
    $src_archive = new ZipArchive();

    if ($src_archive->open($archive_path, ZipArchive::OVERWRITE) !== true) {
        fwrite(STDERR, "*** ERROR: unable to open $archive_path for write" . PHP_EOL);
        exit(1);
    }

    foreach ($content->getFileReferences() as $archive_filename => $local_filename) {
        $src_archive->addFile($local_filename, $archive_filename);
    }

    foreach ($content->getFileContents() as $archive_filename => $contents) {
        $src_archive->addFromString($archive_filename, $contents);
    }

    $src_archive->close();
}

class TuleapArchiveContent
{
    private $reference_files = [];

    private $content_files = [];

    public function addFileReference($reference, $target_file_path)
    {
        if (file_exists($target_file_path)) {
            $this->reference_files[$reference] = $target_file_path;
        } else {
            fwrite(STDERR, "*** ERROR: unable to find data for $reference" . PHP_EOL);
        }
    }

    public function addFileContent($reference, $content)
    {
        $this->content_files[$reference] = $content;
    }

    public function getFileReferences()
    {
        return $this->reference_files;
    }

    public function getFileContents()
    {
        return $this->content_files;
    }
}

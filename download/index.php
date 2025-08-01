<?php

// avoid getting messed up by poorly configured webservers
error_reporting(0);

function isValidUUIDv4(string $uuid): bool {
    return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        ) === 1;
}

function isValidMD5($str): bool
{
    // Must be a string of exactly 32 characters
    if (!is_string($str) || strlen($str) !== 32) {
        return false;
    }

    // All characters must be hexadecimal (0–9, a–f, A–F)
    return ctype_xdigit($str);
}

if(count($_GET) != 1)
{
    print '
    <blockquote><br /><span style="color:#000000; font-size:14pt;">
            <b>Ein Fehler ist aufgetreten</b><br />
            &nbsp;<br>
            Unvollst&auml;ndiger Link.<br />
            Der Link zur angeforderten Datei ist nicht vollst&auml;ndig. Bitte kontaktieren Sie den Support.<br />
           &nbsp;<br>
    </blockquote>';
    exit;
}

$file_uuid = array_key_first($_GET);
$file_name = $_GET[$file_uuid];

// Check if link format is correct (first array key must be a uuid4)
if(!isValidUUIDv4($file_uuid) && !isValidMD5($file_uuid))
{
    print '
    <blockquote><br /><span style="color:#000000; font-size:14pt;">
            <b>Ein Fehler ist aufgetreten</b><br />
            &nbsp;<br>
            Ung&uuml;ltiger Link.<br />
            Der Link zur angeforderten Datei ist ung&uuml;ltig. Bitte kontaktieren Sie den Support.<br />
           &nbsp;<br>
    </blockquote>';
    exit;
}


// Check if file is still available in filesystem
$file_path = "$file_uuid";
if(!is_file($file_path))
{
    print '
    <blockquote><br /><span style="color:#000000; font-size:14pt;">
            <b>Ein Fehler ist aufgetreten</b><br />
            &nbsp;<br>
            Die angeforderte Datei konnte nicht gefunden werden.<br />
            Links zu Exportdateien sind nur einmal gültig, danach muss der Export erneut durchgef&uuml;hrt werden.<br />
           &nbsp;<br>
    </blockquote>';
    exit;
}

// Okay, we passed every check, let's provide the file

if($file_name)  $filename = $file_name; else $filename = 'download';
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $filename);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

ob_clean();
flush();

readfile($file_path);

// we obviously made it 'til the end of this download,
// so lets remove the file

unlink($file_path);

// and gracefully let go ...
exit;
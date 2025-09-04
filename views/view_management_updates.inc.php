<?php

include_once('view.inc.php');
class VIEW_MANAGEMENT_UPDATES extends VIEW
{
    function view_render_update_codebase()
    {
        ?>

        <header id="header">
            <h1>R:EEG:ISTRY | UPDATE | Codebase</h1>
        </header>

        <?php

        print "<br />";

        $latest_manifest = file_get_contents("https://resystance.io/software/r-eeg-istry/manifest.json");
        $manifest_data = json_decode($latest_manifest, true);

        $manifest_files = $manifest_data['files'];
        $manifest_version = $manifest_data['version'];
        $update_map = [];

        foreach ($manifest_files as $manifest_file_path => $manifest_file_data)
        {
            if (!is_file($manifest_file_path))
            {
                //print "<b>Path:</b> " . $manifest_file_path . "<br />";
                $update_map[$manifest_file_path] = $manifest_file_path;
                //print "<span style=\"color:red;font-weight:bold;\">File does not exist:</span> Download scheduled<br />";
            }
            else
            {
                $manifest_file_hash = hash_file('sha256', $manifest_file_path);
                if ($manifest_file_hash != $manifest_file_data['hash'])
                {
                    //print "<b>Path:</b> " . $manifest_file_path . "<br />";
                    $update_map[$manifest_file_path] = $manifest_file_path;
                    //print "<span style=\"color:orange;font-weight:bold;\">File should be updated!</span> Download scheduled<br />";
                }
            }
        }

        if($_REQUEST['update'] == 'now')
        {
            print "Updating to version $manifest_version<br />";

            // URL of the zip containing the latest version
            $ZIP_URL = 'https://resystance.io/software/r-eeg-istry/' . $manifest_version . '.zip';

            // Where your project lives (destination base directory)
            $DEST_BASE = str_replace('/views', '', __DIR__); // e.g. '/var/www/your-project'

            $this->runUpdater($ZIP_URL, $DEST_BASE, $update_map);
        }
        else
        {
            if(count($update_map) > 0)
            {
                print "<br />Es ist eine aktuellere Version verf&uuml;gbar:<br /><b>$manifest_version</b><br />&nbsp;<br />";
                print "<a href=\"?update=now\"><b>&gt;&nbsp;Klicke hier, um die Aktualisierung zu starten&nbsp;&lt;</a>";
            }
            else
            {
                print "Keine Updates verf&uuml;gbar.<br />Du nutzt die aktuellste Version ($manifest_version)<br />";
            }
        }
    }

    function view_render_update_database()
    {
        ?>

        <header id="header">
            <h1>R:EEG:ISTRY | UPDATE | Database Scheme</h1>
        </header>

        <?php

        print "<br />";

        include(dirname(__FILE__) . '/../configs/migrations.php');
        $db_config = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TEMPORARY'], 'feature', 'database_version');
        if(isset($db_config[0]))
        {
            $installed_database_version = $db_config[0]['value1'];
        }
        else
        {
            $installed_database_version = 0;
        }

        /* @var $database_migrations array */
        if(count($database_migrations) > $installed_database_version)
        {
            if (isset($_REQUEST['update_now']))
            {
                print "<b>Aktualisierung wird initialisiert...</b><br />&nbsp;<br /><hr>";

                print "<b>Installiertes Datenbankschema:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
                print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";

                $migration_start = $installed_database_version + 1;
                for ($i = $migration_start; $i <= count($database_migrations); $i++)
                {
                    print "<b>Durchf&uuml;hrung von Migration " . hash('crc32', $i) . "...</b><br />";
                    print "<blockquote>";
                    foreach($database_migrations[$i] as $migration_index => $migration_step)
                    {
                        if($migration_index > 0)    print "<br />";
                        print "Adaption " . $migration_index + 1 . " von " . count($database_migrations[$i]) . "<br />\n";
                        print "&bull; " . hash('sha256', $migration_step) . "... ";
                        $this->db->execute_query($migration_step);
                        print "[confirmed]<br />&nbsp;<br />";
                    }

                    print "</blockquote>";
                }

                $db_config = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TEMPORARY'], 'feature', 'database_version');
                if(isset($db_config[0]))
                {
                    $installed_database_version = $db_config[0]['value1'];
                }
                else
                {
                    $installed_database_version = 0;
                }

                print "<b>Installiertes Datenbankschema:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
                print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";
                if($installed_database_version < count($database_migrations))
                {
                    print "<br /><br /><br /><center><b>Es konnte nicht alle Aktualisierungen durchgeführt werden.</b><br />Bitte konsultiere die Logs f&uuml;r weitere Informationen.&nbsp;<br />&nbsp;<br />";
                }
                else
                {
                    print "<br /><br /><br /><center>Aktualisierung erfolgreich.<br />&nbsp;<br />";
                }

                print '
                    <div class="button_container">
                        <button type="button" class="mainbtn" style="" id="btn_back" onClick="location.href=' . "'" . "/" . "'" . '"><img src="images/noun_manage.png" alt="Zur&uuml;ck zu den Einstellungen" id="back" style="height: 60px; margin-left: 30px;"><br />Zur&uuml;ck zu den Einstellungen</button>
                    </div>
                    </center>
                ';
            }
            else
            {
                print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
                print "<b>Installiertes Datenbankschema:</b><br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";
                print "<br /><br /><br /><center>Die Datenbank muss aktualisiert werden, um die Kompatibilität zur aktuell installierten Version von R-EEG-ISTRY zu gew&auml;hrleisten.<br />&nbsp;<br />";
                print '
                    <div class="button_container">
                        <button type="button" class="mainbtn" style="" id="btn_upgrade" onClick="location.href=' . "'" . "?update_now" . "'" . '"><img src="images/noun_dbupdate.png" alt="Aktualisieren" id="upgrade" style="height: 60px; margin-left: 30px;"><br />Aktualisierung starten</button>
                    </div>
                    </center>
                ';
            }
        }
        else
        {
            print "<b>Datenbankschema der Codebase:</b><br />" . hash('crc32', count($database_migrations)) . '.' . count($database_migrations) . "<br />&nbsp;<br />";
            print "<b></b>Installiertes Datenbankschema:<br />" . hash('crc32', $installed_database_version) . ".$installed_database_version<br /><hr>";
            print "<br /><br /><br /><center>Es ist keine Aktualisierung erforderlich.<br />&nbsp;<br />";
            print '
                    <div class="button_container">
                        <button type="button" class="mainbtn" style="" id="btn_back" onClick="location.href=' . "'" . "/" . "'" . '"><img src="images/noun_manage.png" alt="Zur&uuml;ck zu den Einstellungen" id="back" style="height: 60px; margin-left: 30px;"><br />Zur&uuml;ck zu den Einstellungen</button>
                    </div>
                    </center>
                ';
        }
    }


    // CODEBASE UPDATE HELPERS
    private function downloadFile(string $url, string $targetPath): void {
        // Prefer cURL for better control/timeouts
        if (function_exists('curl_init')) {
            $fp = fopen($targetPath, 'wb');
            if (!$fp) {
                throw new RuntimeException("Cannot open $targetPath for writing.");
            }

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_FAILONERROR => true,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_USERAGENT => 'PHP Updater/1.0',
            ]);
            $ok = curl_exec($ch);
            $err = curl_error($ch);
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if (!$ok || $http >= 400) {
                @unlink($targetPath);
                throw new RuntimeException("Download failed (HTTP $http): $err");
            }
        } else {
            // Fallback
            $data = @file_get_contents($url);
            if ($data === false) {
                throw new RuntimeException("Download failed using file_get_contents().");
            }
            if (@file_put_contents($targetPath, $data) === false) {
                throw new RuntimeException("Cannot write downloaded data to $targetPath.");
            }
        }
    }

    private function makeTempDir(string $prefix = 'updater_'): string {
        $base = str_replace('/views', '',__DIR__);
        $dir = $base . DIRECTORY_SEPARATOR . $prefix . bin2hex(random_bytes(4));
        if (!mkdir($dir, 0775) && !is_dir($dir)) {
            throw new RuntimeException("Failed to create temp dir at $dir");
        }
        return $dir;
    }

    private function unzipTo(string $zipPath, string $targetDir): void {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Failed to open ZIP: $zipPath");
        }
        // Security: prevent ZipSlip
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $normalized = str_replace('\\', '/', $name);
            if (strpos($normalized, '../') !== false) {
                $zip->close();
                throw new RuntimeException("ZIP contains unsafe path: $name");
            }
        }
        if (!$zip->extractTo($targetDir)) {
            $zip->close();
            throw new RuntimeException("Failed to extract ZIP to $targetDir");
        }
        $zip->close();
    }

    private function detectZipRootPrefix(string $extractedDir): string {
        // If the ZIP created a single top-level folder, use that as prefix
        $entries = array_values(array_filter(scandir($extractedDir) ?: [], fn($v) => $v !== '.' && $v !== '..'));
        if (count($entries) === 1) {
            $only = $entries[0];
            if (is_dir($extractedDir . DIRECTORY_SEPARATOR . $only)) {
                return $only;
            }
        }
        return '';
    }

    private function ensureDir(string $path): void {
        if (is_dir($path)) return;
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException("Failed to create directory: $path");
        }
    }

    private function rrmdir(string $dir): void {
        if (!is_dir($dir)) return;
        $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @chmod($file->getPathname(), 0664);
                @unlink($file->getPathname());
            }
        }
        @rmdir($dir);
    }

    private function rcopy(string $src, string $dst): void {
        if (is_dir($src)) {
            $this->ensureDir($dst);
            $dir = opendir($src);
            if (!$dir) {
                throw new RuntimeException("Cannot open directory: $src");
            }
            while (($file = readdir($dir)) !== false) {
                if ($file === '.' || $file === '..') continue;
                $this->rcopy("$src/$file", "$dst/$file");
            }
            closedir($dir);
        } else {
            $this->ensureDir(dirname($dst));
            if (!copy($src, $dst)) {
                throw new RuntimeException("Failed to copy $src to $dst");
            }
        }
    }

    /* ===================== ORCHESTRATION ===================== */

    private function runUpdater(string $zipUrl, string $destBase, array $fileMap, string $zipRootPrefix = ''): void {
        $temp = $this->makeTempDir();
        $zipFile = $temp . DIRECTORY_SEPARATOR . 'package.zip';
        $extractDir = $temp . DIRECTORY_SEPARATOR . 'extracted';

        try {
            echo "<br />";
            echo "[1/4] R-EEG-ISTRY <b>stable</b> wird heruntergeladen...<br />";
            $this->downloadFile($zipUrl, $zipFile);

            echo "[2/4] Dateien werden extrahiert...<br />";
            $this->ensureDir($extractDir);
            $this->unzipTo($zipFile, $extractDir);
            @unlink($zipFile);

            // Determine root_prefix if needed
            if ($zipRootPrefix === '') {
                $zipRootPrefix = $this->detectZipRootPrefix($extractDir);
            }
            $prefixPath = rtrim($zipRootPrefix, "/\\");
            $sourceBase = $prefixPath ? ($extractDir . DIRECTORY_SEPARATOR . $prefixPath) : $extractDir;

            echo "[3/4] &Auml;nderungen werden &uuml;bernommen...<br />";
            foreach ($fileMap as $srcRel => $dstRel) {
                $srcPath = $sourceBase . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $srcRel);
                $dstPath = rtrim($destBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dstRel);

                if (!file_exists($srcPath)) {
                    print "$srcPath not found in extracted ZIP<br />";
                    print "<b>Warnung:</b> $srcRel im Manifest, aber nicht im Update.";
                }

                // If destination is a directory mapping, copy recursively
                if (is_dir($srcPath)) {
                    $this->rcopy($srcPath, $dstPath);
                    //echo "  - [DIR] $srcRel -> $dstRel<br />";
                } else {
                    $this->ensureDir(dirname($dstPath));
                    if (!@copy($srcPath, $dstPath)) {
                        print "<b>Warnung:</b> $srcRel konnte nicht &uuml;bertragen werden.";
                    }
                    //echo "  - [FILE] $srcRel -> $dstRel<br />";
                }
            }

            echo "[4/4] *knock knock* ... Housekeeping!<br />";
            $this->rrmdir($temp);

            echo "&nbsp;<br />";
            echo "<b>Update abgeschlossen.</b><br />";
        } catch (Throwable $e) {
            echo "Update failed: " . $e->getMessage() . "\n";

            // Try to clean up temp artifacts
            if (is_file($zipFile)) @unlink($zipFile);
            if (is_dir($extractDir)) $this->rrmdir($extractDir);
            if (is_dir($temp)) $this->rrmdir($temp);

            // Re-throw if you want a non-zero exit code in CLI
            if (PHP_SAPI === 'cli') {
                exit(1);
            }
        }
    }

}
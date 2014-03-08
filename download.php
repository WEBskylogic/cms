<?
if(isset($_GET['file']) && $_GET['file']!='' && isset($_GET['type']) && $_GET['type']!='')
{
    $filePath = 'files/'.$_GET['type'].'/'.$_GET['file'];

    if (file_exists($filePath))
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filePath));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // читаем файл и отправляем его пользователю

        if ($fd = fopen($filePath, 'rb')) {
            while (!feof($fd)) {
                print fread($fd, 1024);
            }
            fclose($fd);
        }
        exit;
    }
    else {
        die("Файл не найден.");
    }
}
<?php

namespace QCubed\Plugin;

/**
 * Class FileHandler
 *
 * Note: the "upload" folder must already exist in /project/assets/ and this folder has 777 permissions.
 *
 * @property string $RootPath Default root path APP_UPLOADS_DIR. You may change the location of the file repository
 *                             at your own risk.*
 * Note: If you want to change TempPath, TempUrl and StoragePath, you have to rewrite the setup() function in the FileUpload class.
 * This class is located in the /project/includes/plugins folder.
 * @property string $TempPath = Default temp path APP_UPLOADS_TEMP_DIR. If necessary, the temp dir must be specified.
 * @property string $StoragePath Default dir named _files. This dir is generated together with the dirs
 *                               /thumbnail,  /medium,  /large when the corresponding page is opened for the first time.
 * @property string $FullStoragePath Please see the setup() function! Can only be changed in this function.
 *
 *
 * @property integer $ThumbnailResizeDimensions Default resized image dimensions. Default 320 is a good balance between
 *                                              visible quality and file size.
 * @property integer $MediumResizeDimensions Default 480. Resize image dimensions for high-density (retina) screens.
 *                                           This allows you to serve higher quality images for HiDPI screens, at the
 *                                           cost of slightly larger file size. For example, generated for site preview.
 * @property integer $LargeResizeDimensions Default 1500. Resize image dimensions for high-density (retina) screens.
 *                                          This allows you to serve higher quality images for HiDPI (e.g 27- and 30-inch
 *                                          monitors) screens, at the cost of slightly larger file size.*
 *
 *
 * @property integer $ImageResizeQuality Default 85. JPG compression level for resized images.
 * @property string $ImageResizeFunction Default 'imagecopyresampled'. Choose between 'imagecopyresampled' (smoother)
 *                                       and 'imagecopyresized' (faster). Difference is minimal, but you could use
 *                                       imagecopyresized for example if you want faster resizing when not using image
 *                                       resize cache.
 * @property boolean $ImageResizeSharpen Default true. Creates sharper (less blurry) preview images.
 * @property array $TempFolders Default '['thumbnail', 'medium', 'large']'. If you want to change the names of
 *                              the temporary folders, you need to override the setup() function of the FileUpload class
 *                              ($strCreateDirs = ['/thumbnail', '/medium', '/large'];).
 *
 * @property array $ResizeDimensions Default '[320, 480, 1500]'. Note: Here you need to set the ResizeDimensions
 *                                   [320, 480, 1500] in the order of TempFolders ['thumbnail', 'medium', 'large'].
 *
 *                                   Default 320 is a good balance between visible quality and file size.
 *
 *                                   Default 480. Resize image dimensions for high-density (retina) screens. This allows
 *                                   you to serve higher quality images for HiDPI screens, at the cost of slightly
 *                                   larger file size. For example, generated for site preview.
 *
 *                                   Default 1500. Resize image dimensions for high-density (retina) screens. This allows
 *                                   you to serve higher quality images for HiDPI (e.g 27- and 30-inch monitors) screens,
 *                                   at the cost of slightly larger file size.
 *
 * @property array $AcceptFileTypes Default null. The output form of the array looks like this:
 *                                  '['gif', 'jpg', 'jpeg', 'png', 'pdf']'. If necessary, specify the allowed file types.
 *                                  When empty (default), all file types are allowed.
 * @property integer $MaxFileSize Default null. Sets the maximum file size (bytes) allowed for uploads. Default value null
 *                                means no limit, but maximum file size will always be limited by your server's
 *                                PHP upload_max_filesize value.
 * @property string $UploadExists Default 'increment'. Decides what to do if uploaded filename already exists in upload
 *                                target folder. Default 'increment' will rename uploaded files by appending a number,
 *                                'overwrite' will overwrite existing files.
 *                                Usage:
 *                                $this->UploadExists = 'increment'; // increment filename, for example filename.jpg => filename-2.jpg
 *                                $this->UploadExists = 'overwrite', // overwrite existing file if filename exists
 *
 * @property-read string $FileName is the name of the file that the user uploads
 * @property-read string $FileType is the MIME type of the file
 * @property-read integer $FileSize is the size in bytes of the file
 * @property string $DestinationPath Default null. This is a prepared option. If there is a need to create new subfolders
 *                                   and save images there. Then you need to make your own function to create new folders.
 *                                   For example:
 *                                   [folder1]
 *                                   |___ [folder2]
 *                                        |___ [folder3]
 *                                   Then write $this->DestinationPath = 'folder1/folder2/folder3' etc...
 *
 * @package QCubed\Plugin
 */

class FileHandler
{
    protected $options;
    // PHP File Upload error message codes:
    // https://www.php.net/manual/en/features.file-upload.errors.php
    protected $uploadErrors;
    protected $index;
    protected $chunk;
    protected $count;
    protected $counter = 0;

    public function __construct($options = null)
    {
        $this->options = array(
            'RootPath' => APP_UPLOADS_DIR,
            'TempPath' => APP_UPLOADS_TEMP_DIR,
            'StoragePath' => '_files',
            'FullStoragePath' => null,
            'ChunkPath' => null,

            'ImageResizeQuality' => 85,
            'ImageResizeFunction' => 'imagecopyresampled', // imagecopyresampled || imagecopyresized
            'ImageResizeSharpen' => true,

            'TempFolders' =>  ['thumbnail', 'medium', 'large'],
            'ResizeDimensions' => [320, 480, 1500],
            'DestinationPath' => null,
            'AcceptFileTypes' => null,
            'MaxFileSize' => null,
            'MinFileSize' => 1,
            'UploadExists' => 'increment', // increment || overwrite

            'File' => null,
            'FileName' => null,
            'FileType' => null,
            'FileSize' => null,
            'FileError' => null,
        );

        $this->uploadErrors = array(
            1 => t('Uploaded file exceeds upload_max_filesize directive in php.ini'),
            2 => t('Uploaded file exceeds MAX_FILE_SIZE directive specified in the HTML form'),
            3 => t('The uploaded file was only partially uploaded'),
            4 => t('Failed to move uploaded file'),
            6 => t('Missing a temporary folder'),
            7 => t('Failed to write file to disk'),
            8 => t('A PHP extension stopped the file upload'),
            'post_max_size' => t('The uploaded file exceeds the post_max_size directive in php.ini'),
            'max_file_size' => 'File is too big',
            'min_file_size' => 'File is too small',
            'accept_file_types' => t('Filetype not allowed'),
            'invalid_image_type' => t('Invalid image type'),
            'invalid_file_size' => t('Invalid file size'),
            'post_max_size' => t('File size exceeds max_filesize %s'),
            'overwritten' => t('This file has been overwritten'),
            'invalid_image' => t('Invalid image / failed getimagesize()'),
            'failed_to_resize_image' => t('Failed to resize image'),
            'resizeimage_failed_to_create_and_resize_the_image' => t('The resizeImage() function failed to create and resize the image'),
            'invalid_chunk_size' => t('Invalid chunk size'),
            'failed_to_open_stream' => t('Failed to open stream: No such directory to put into'),
            'could-not_write_output' => t('Failed to open output stream'),
            'could_not_read_input' => t('Failed to open input stream'),
            'failed_to_move_uploaded_file' => t('Failed to move uploaded file'),
            'file_not_found' => t('File not found')
        );

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }

        $this->options['FullStoragePath'] = $this->options['TempPath'] . '/' . $this->options['StoragePath'];
        $this->options['ChunkPath'] = $this->options['FullStoragePath'] . '/' . 'temp';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->header();
            $this->handleFileUpload();
        }
    }

    /**
     * @return void
     */
    protected function header()
    {
        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/json');
    }

    public function handleFileUpload()
    {
        $json = array();

        $chunkEnabled = isset($_REQUEST['chunkEnabled']) ? $_REQUEST['chunkEnabled'] : "false";
        $this->index = isset($_REQUEST['index']) ? intval($_REQUEST['index']) : 0;
        $this->chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
        $this->count = isset($_REQUEST['count']) ? intval($_REQUEST['count']) : 0;

        $this->options['FileName'] = $this->options['RootPath'] . '/' . $_FILES["files"]["name"];
        $this->options['File'] = $_FILES["files"]["tmp_name"];
        $this->options['FileType'] = $_FILES["files"]["type"];
        $this->options['FileSize'] = $_FILES["files"]["size"];
        $this->options['FileError'] = $_FILES["files"]["error"];

        // If DestinationPath is set
        if ($this->options['DestinationPath'] !== null) {
            $this->options['FileName'] = $this->options['RootPath'] . $this->options['DestinationPath'] . '/' . basename($this->options['FileName']);
        }

        if ($chunkEnabled === "false") {
            // Check for duplicate filenames and increment if necessary
            $newFileName = $this->checkDuplicateFile($this->options['FileName']);

            // Make sure the new file name is received and then validate the file
            if ($newFileName !== null) {
                $this->options['FileName'] = $newFileName;
            }

            // Validate the file with the updated filename
            if ($this->regularValidate($this->options['File'], $this->options['FileName'], $this->options['FileError'])) {
                // Upload file with new name
                $this->handleRegularUpload($this->options['File'], $this->options['FileName']);
            }
        } else {
            $this->handleChunkUpload($this->options['File'], $this->options['FileName']);
        }
    }

    public function regularValidate($uploadedFile, $fileName, $error)
    {
        if ($error) {
            $file->error = $this->handleError($this->getErrorMessage($error), $fileName);
            return false;
        }

        // Get the value of post_max_size in bytes
        $postMaxSize = $this->getConfigBytes(ini_get('post_max_size'));

        // Check if the file size exceeds the post_max_size limit
        if ($postMaxSize && ($_SERVER['CONTENT_LENGTH'] > $postMaxSize)) {
            $file->error = $this->handleError($this->getErrorMessage('post_max_size'), $fileName);
            return false;
        }

        // Check file size
        $fileSize = $this->getFileSize($uploadedFile);

        if ($this->options['MaxFileSize'] && $fileSize > $this->options['MaxFileSize']) {
            $file->error = $this->handleError($this->getErrorMessage('max_file_size'), $fileName);
            return false;
        }

        if ($this->options['MinFileSize'] && $fileSize < $this->options['MinFileSize']) {
            $file->error = $this->handleError($this->getErrorMessage('min_file_size'), $fileName);
            return false;
        }

        if ($this->options['AcceptFileTypes']) {
            // Check the file type
            if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->options['AcceptFileTypes'])) {
                $file->error = $this->handleError($this->getErrorMessage('accept_file_types'), $fileName);
                return false;
            }
        }

        return true;
    }

    public function chunkValidate($uploadedFile, $fileName, $error, $isLastChunk)
    {
        if ($error) {
            $file->error = $this->handleError($this->getErrorMessage($error), $fileName);
            return false;
        }

        // Calculate chunk size
        $fileSize = 0;
        if (is_resource($uploadedFile)) {
            fseek($uploadedFile, 0, SEEK_END);
            $fileSize = ftell($uploadedFile);
            fseek($uploadedFile, 0, SEEK_SET);
        }

        // We only check if all chunks are merged
        if ($isLastChunk) {
            $fileSize = $this->getFileSize($fileName);

            if ($this->options['MaxFileSize'] && $fileSize > $this->options['MaxFileSize']) {
                $file->error = $this->handleError($this->getErrorMessage('max_file_size'), $fileName);
                return false;
            }

            if ($this->options['MinFileSize'] && $fileSize < $this->options['MinFileSize']) {
                $file->error = $this->handleError($this->getErrorMessage('min_file_size'), $fileName);
                return false;
            }

            if ($this->options['AcceptFileTypes']) {
                // We check the file type after merging the chunks
                if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->options['AcceptFileTypes'])) {
                    $file->error = $this->handleError($this->getErrorMessage('accept_file_types'), $fileName);
                    return false;
                }
            }
        }

        return true;
    }

    protected function handleRegularUpload($uploadedFile, $file)
    {
        move_uploaded_file($uploadedFile, $file);

        clearstatcache();

        $this->resizeImageProcess($file);
        $this->uploadInfo();
    }

    protected function handleChunkUpload($uploadedFile, $file)
    {
        $chunkFile = $this->options['ChunkPath'] . '/' . basename($file);

        // Move the file to a temporary location
        if (!move_uploaded_file($uploadedFile, $chunkFile . '.part' . $this->chunk)) {
            $this->handleError($this->getErrorMessage('failed_to_move_uploaded_file'), $file);
            return;
        }

        clearstatcache();

        $filePath = $chunkFile . '.part*';
        $fileParts = glob($filePath);
        sort($fileParts, SORT_NATURAL);
        $_SESSION['parts'] = $fileParts; // We keep the parts in the session

        // Merge chunks
        $finalFile = fopen($chunkFile, 'wb');

        foreach ($fileParts as $filePart) {
            $chunk = file_get_contents($filePart);
            fwrite($finalFile, $chunk);
            $this->counter++;
        }

        fclose($finalFile);

        // When all parts are received
        if ($this->count == $this->counter) {

            $this->partFilesToDelete($_SESSION['parts']);

            // Final validation after merging files
            if ($this->chunkValidate($finalFile, $chunkFile , null, true)) {

                // If filename already exists, check for duplicates
                if (!file_exists($file)) {
                    rename($chunkFile, $file);
                    $this->options['FileSize'] = filesize($file);
                } else {
                    $newFileName = $this->checkDuplicateFile($file);
                    rename($chunkFile, $newFileName);
                    $this->options['FileName'] = $newFileName;
                    $this->options['FileSize'] = filesize($newFileName);
                }

                // Further processing
                $this->resizeImageProcess($this->options['FileName']);
                $this->uploadInfo();
            }
        }
    }

    /**
     * Deletes all temporary files
     * @param $tempFiles
     * @return void
     */
    protected function partFilesToDelete($tempFiles)
    {
        foreach ($tempFiles as $tempFile) {
            unlink($tempFile);
        }

        unset($_SESSION['parts']);
    }

    /**
     * Checks if the file name to be sent exists elsewhere, if so, the file name is incremented
     * @param $fileName
     * @return mixed|string|void|null
     */
    protected function checkDuplicateFile($fileName) {
        // Set dirname, name and ext
        $dirname = $this->removeFileName($fileName);
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        $isExists = false;
        $files = glob($dirname . '/*', GLOB_NOSORT);

        if (in_array($fileName, $files)) {
            $isExists = true;
        }

        if ($isExists === true) {
            if (file_exists($dirname . '/' . $name . '.' . $ext)) {

                // If 'overwrite' is selected, the file will be overwritten
                if ($this->options['UploadExists'] == 'overwrite') {
                    $this->handleError($this->getErrorMessage('overwritten'), $name);
                    return null;
                }

                // If 'increment' is selected, the filename is incremented
                if ($this->options['UploadExists'] == 'increment') {
                    $inc = 1;
                    while (file_exists($dirname . '/' . $name . '-' . $inc . '.' . $ext)) {
                        $inc++;
                    }
                    return $dirname . '/' . $name . '-' . $inc . '.' . $ext;
                }
            }

            return $fileName;
        }
    }

    protected function getErrorMessage($error)
    {
        return isset($this->uploadErrors[$error]) ? $this->uploadErrors[$error] : $error;
    }

    /**
     * Checks the file type and if the file type corresponds to the 'image type' and sends the images on for processing
     * @param string $fileName
     * @return void
     */
    protected function resizeImageProcess($fileName)
    {
        $associatedParameters = array_combine($this->options['TempFolders'], $this->options['ResizeDimensions']);

        if (is_file($fileName) && in_array($this->getExtension($fileName), $this->getImageExtensions())) {

            $size = getimagesize($fileName);

            foreach ($associatedParameters as $tempFolder => $resizeDimension) {

                if ($this->options['DestinationPath'] == null) {
                    $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . basename($fileName);
                } else {
                    $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . $this->options['DestinationPath'] . '/' . basename($fileName);
                }

                if (self::getMimeType($fileName) == 'image/svg+xml' || ($resizeDimension > $size[0]) && $size[0] !== 0) {
                    copy($fileName, $newPath);
                } else {
                    $this->resizeImage($fileName, $newPath, $resizeDimension);
                }
            }
        }
    }

    /**
     * @param $path
     * @param $type
     * @return false|\GdImage|resource|void
     */
    protected function imageCreateFrom($path, $type)
    {
        if (!$path || !$type) return;
        if ($type === IMAGETYPE_JPEG) {
            return imagecreatefromjpeg($path);
        } else if ($type === IMAGETYPE_PNG) {
            return imagecreatefrompng($path);
        } else if ($type === IMAGETYPE_GIF) {
            return imagecreatefromgif($path);
        } else if ($type === 18/*IMAGETYPE_WEBP*/) {
            if (version_compare(PHP_VERSION, '5.4.0') >= 0) return imagecreatefromwebp($path);
        } else if ($type === IMAGETYPE_BMP) {
            if (version_compare(PHP_VERSION, '7.2.0') >= 0) return imagecreatefrombmp($path);
        }
    }

    /**
     * This function can sharpen a resized image, potentially within a limited range.
     * @param $image
     * @return void
     */
    protected function sharpenImage($image)
    {
        $matrix = array(
            array(-1, -1, -1),
            array(-1, 20, -1),
            array(-1, -1, -1),
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        $offset = 0;
        imageconvolution($image, $matrix, $divisor, $offset);
    }

    /**
     * This function handles image processing
     * @param $path
     * @param $newPath
     * @param $resizeDimensions
     * @return void
     */
    protected function resizeImage($path, $newPath, $resizeDimensions)
    {

        if (function_exists('exif_imagetype') && exif_imagetype($path) !== false) {
            // file size
            $fileSize = filesize($path);
            // imagesize
            $size = getimagesize($path);

            if (empty($size) || !is_array($size)) {
                return $this->handleError($this->getErrorMessage('invalid_image'), $path);
            }

            $resizeRatio = max($size[0], $size[1]) / $resizeDimensions;

            // Calculate new image dimensions.
            $resizeWidth = round($size[0] / $resizeRatio);
            $resizeHeight = round($size[1] / $resizeRatio);

            // Create final image with new dimensions.
            $newImage = imagecreatetruecolor($resizeWidth, $resizeHeight);

            // create new $image
            $image = $this->imageCreateFrom($path, $size[2]);

            imageAlphaBlending($newImage, false);
            imageSaveAlpha($newImage, true);

            if (!call_user_func($this->options['ImageResizeFunction'], $newImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $size[0], $size[1])) {
                return $this->handleError($this->getErrorMessage('failed_to_resize_image'), $path);
            }

            // destroy original $image resource
            imagedestroy($image);

            // sharpen resized image
            if ($this->options['ImageResizeSharpen']) {
                $this->sharpenImage($newImage);
            }

            if ($this->options['ImageResizeQuality']) {
                switch ($size[2]) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($newImage, $newPath, $this->options['ImageResizeQuality']);
                        break;
                    case IMAGETYPE_GIF:
                        imagegif($newImage, $newPath, $this->options['ImageResizeQuality']);
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($newImage, $newPath, floatval($this->options['ImageResizeQuality'] / 100));
                        break;
                    default:
                        throw new Exception(t("Unable to deal with image type"));
                }
            } else {
                return $this->handleError($this->getErrorMessage('resizeimage_failed_to_create_and_resize_the_image'), $path);
            }

            // destroy image
            imagedestroy($newImage);
        }
    }

    /**
     * Send file data
     * @return void
     */
    protected function uploadInfo()
    {
        print json_encode(array(
            'filename' =>  basename($this->options['FileName']),
            'path' => $this->getRelativePath($this->options['FileName']),
            'extension' => $this->getExtension($this->options['FileName']),
            'type' => $this->options['FileType'],
            'error' => $this->options['FileError'],
            'size' => $this->options['FileSize'],
            'mtime' => filemtime($this->options['FileName']),
            'dimensions' => $this->getDimensions($this->options['FileName'])
        ));
    }

    /**
     * Send details of the problem file
     * @param $errorMessage
     * @param $file
     * @return void
     */
    protected function handleError($errorMessage, $file = null) {
        $json['filename'] = basename($file);
        $json['size'] = $this->options['FileSize'];
        $json['type'] = $this->options['FileType'];
        $json['error'] = $errorMessage;
        print json_encode($json);

        // Check and remove temporary files
        $chunkFile = $this->options['ChunkPath'] . '/' . basename($file);

        // If there is an error, delete the final file and all temporary parts
        if ($errorMessage) {
            if (file_exists($chunkFile)) {
                // Remove final file
                unlink($chunkFile);
            }
        }

        exit;
    }

    /**
     * Fix for overflowing signed 32 bit integers, works for sizes up to 2^32-1 bytes (4 GiB - 1):
     * @param $size
     * @return float
     */
    protected function fixIntegerOverflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    /**
     * @param $filePath
     * @param $clearStatCache
     * @return float
     */
    protected function getFileSize($filePath, $clearStatCache = false) {
        if ($clearStatCache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $filePath);
            } else {
                clearstatcache();
            }
        }
        return $this->fixIntegerOverflow(filesize($filePath));
    }

    /**
     * @param $val
     * @return int
     */
    public function getConfigBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        if (is_numeric($val)) {
            $val = (int)$val;
        } else {
            $val = (int)substr($val, 0, -1);
        }
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    /**
     * This removes the filename from the entire path
     * @param $path
     * @return string
     */
    protected function removeFileName($path)
    {
        return substr($path, 0, (int) strrpos($path, '/'));
    }

    /**
     * Get file path without RootPath
     * @param $path
     * @return string
     */
    public function getRelativePath($path)
    {
        return substr($path, strlen($this->options['RootPath']));
    }

    /**
     * Get file extension
     * @param string $path
     * @return mixed|string
     */
    public static function getExtension($path)
    {
        if(!is_dir($path) && is_file($path)){
            return strtolower(substr(strrchr($path, '.'), 1));
        }
    }

    /**
     * Get mime type
     * @param string $path
     * @return mixed|string
     */
    public static function getMimeType($path)
    {
        if(function_exists('mime_content_type')) {
            return mime_content_type($path);
        } else {
            return function_exists('finfo_file') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : false;
        }
    }

    /**
     * Get the dimensions of the image
     * @param string $path
     * @return mixed|string
     */
    public static function getDimensions($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $ImageSize = getimagesize($path);

        if (in_array($ext, self::getImageExtensions())) {
            $width = (isset($ImageSize[0]) ? $ImageSize[0] : '0');
            $height = (isset($ImageSize[1]) ? $ImageSize[1] : '0');
            $dimensions = $width . ' x ' . $height;
            return $dimensions;
        }
    }

    /**
     * Get image files extensions
     * @return array
     */
    public static function getImageExtensions()
    {
        return array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif');
    }

    /**
     * Show bytes in a human-friendly and understandable format
     * @param $bytes
     * @return string|void
     */
    protected function readableBytes($bytes)
    {
        $i = floor(log($bytes) / log(1024));
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }

    /**
     * Clean path
     * @param string $path
     * @return string
     */
    public static function cleanPath($path)
    {
        $path = trim($path);
        $path = trim($path, '\\/');
        $path = str_replace(array('../', '..\\'), '', $path);
        if ($path == '..') {
            $path = '';
        }
        return str_replace('\\', '/', $path);
    }

    /**
     * @param $old
     * @param $new
     * @return bool|null
     */
    protected function rename($old, $new)
    {
        return (!file_exists($new) && file_exists($old)) ? rename($old, $new) : null;
    }
}
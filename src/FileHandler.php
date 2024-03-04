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
    protected $chunk;
    protected $chunks;

    public function __construct($options = null)
    {
        $this->options = array(
            'RootPath' => APP_UPLOADS_DIR,
            'TempPath' => APP_UPLOADS_TEMP_DIR,
            'StoragePath' => '_files',
            'FullStoragePath' => null,

            'ImageResizeQuality' => 85,
            'ImageResizeFunction' => 'imagecopyresampled', // imagecopyresampled || imagecopyresized
            'ImageResizeSharpen' => true,

            'TempFolders' =>  ['thumbnail', 'medium', 'large'],
            'ResizeDimensions' => [320, 480, 1500],
            'DestinationPath' => null,
            'AcceptFileTypes' => null,
            'MaxFileSize' => null,
            'UploadExists' => 'increment', // increment || overwrite

            'File' => null,
            'FileName' => null,
            'FileType' => null,
            'FileError' => null,
            'FileSize' => null,
        );

        $this->uploadErrors = array(
            1 => t('Uploaded file exceeds upload_max_filesize directive in php.ini'),
            2 => t('Uploaded file exceeds MAX_FILE_SIZE directive specified in the HTML form'),
            3 => t('The uploaded file was only partially uploaded'),
            4 => t('Failed to move uploaded file'),
            6 => t('Missing a temporary folder'),
            7 => t('Failed to write file to disk'),
            8 => t('A PHP extension stopped the file upload'),
            'accept_file_types' => t('Filetype not allowed'),
            'invalid_image_type' => t('Invalid image type'),
            'invalid_file_size' => t('Invalid file size'),
            'post_max_size' => t('File size exceeds max_filesize %s'),
            'already_exists' => t('%s already exists'),
            'invalid_image' => t('Invalid image / failed getimagesize()'),
            'failed_to_resize_image' => t('Failed to resize image'),
            'resizeimage_failed_to_create_and_resize_the_image' => t('The resizeImage() function failed to create and resize the image'),
            'failed_to_open_stream' => t('Failed to open stream: No such directory to put into'),
            'could-not_write_output' => t('Failed to open output stream'),
            'could_not_read_input' => t('Failed to open input stream')
        );

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }

        $this->options['FullStoragePath'] = '/' . $this->options['TempPath'] . '/' . $this->options['StoragePath'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->header();
            $this->upload();
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

    public function upload()
    {
        $json = array();
        $json['check'] = 'false';

        // Handle chunked uploads
        $chunkEnabled = isset($_REQUEST['chunkEnabled']) ? $_REQUEST['chunkEnabled'] : "false";

        // Get chunk number, along with chunk total number (chunks)
        $this->chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
        $this->chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;

        // get $_FILES["files"]
        $file = isset($_FILES) && isset($_FILES["files"]) && is_array($_FILES["files"]) ? $_FILES["files"] : false;

        if ($file['error'] !== 0) {
            $this->errorValidity('true', $file['error'], null);
        }

        $this->options['FileName'] = $this->options['RootPath'] . '/' . $_FILES["files"]["name"];
        $this->options['File'] = $_FILES["files"]["tmp_name"]; //getting temp_name of file
        $this->options['FileType'] = $_FILES["files"]["type"];
        $this->options['FileError'] = $_FILES["files"]["error"];
        $this->options['FileSize'] = $_FILES["files"]["size"];

        if ($this->options['DestinationPath'] == null) {
            $this->options['FileName'] = $this->options['RootPath'] . '/' . basename($this->options['FileName']);
        } else {
            $this->options['FileName'] = $this->options['RootPath'] . $this->options['DestinationPath'] . '/' . basename($this->options['FileName']);
        }

        // invalid $_FILES["files"]["type"]
        $allowedFileTypes = !empty($this->options['AcceptFileTypes']) ? $this->options['AcceptFileTypes'] : false;

        if (!empty($allowedFileTypes)) {
            $ext = pathinfo(strtolower($this->options['FileName']), PATHINFO_EXTENSION);
            if (!in_array($ext, $allowedFileTypes)) {
                $this->errorValidity('true', 'accept_file_types', null);
            }
        }

        // invalid $_FILES["files"]["size"]
        if (!isset($this->options['FileSize']) || empty($this->options['FileSize'])) {
            $this->errorValidity('true', 'invalid_file_size', null);
        }

        // $_FILES["files"]["size"] must not exceed $this->options['MaxFileSize']
        if ($this->options['MaxFileSize'] && $_FILES["files"]["size"] > $this->options['MaxFileSize']) {
            $this->errorValidity('true', 'post_max_size', $this->readableBytes($this->options['MaxFileSize']));
        }

        $this->dirname = $this->removeFileName($this->options['FileName']);
        $this->name = pathinfo($this->options['FileName'], PATHINFO_FILENAME);
        $this->ext = pathinfo($this->options['FileName'], PATHINFO_EXTENSION);

        if (file_exists($this->dirname . '/' . basename($this->name) . '.' . $this->ext)) {

            // File naming if overwrite and file exists, then it will be overwritten
            if ($this->options['UploadExists'] == 'overwrite') {
                $this->errorValidity('false', 'already_exists', basename($this->options['FileName']));
            }
            // Increment filename / $this->trUploadExists => 'increment'
            if ($this->options['UploadExists'] == 'increment') {
                $inc = 1;
                while (file_exists($this->dirname . '/' . $this->name . '-' . $inc . '.' . $this->ext)) $inc++;
                $this->options['FileName'] = $this->dirname . '/' . $this->name . '-' . $inc . '.' . $this->ext;
            }
        }

        if ($chunkEnabled === "false") {
            $this->handleRegularUpload();
        } else {
            $this->handleChunkUpload();
        }
    }

    protected function getErrorMessage($error)
    {
        return array_key_exists($error, $this->uploadErrors) ? $this->uploadErrors[$error] : $error;
    }

    protected function removeFileName($path)
    {
        return substr($path, 0, (int) strrpos($path, '/'));
    }

    protected function handleRegularUpload()
    {
        if (is_dir($this->removeFileName($this->options['FileName']))) {
            $file = $this->getErrorMessage($this->options['FileError']) ? $this->getErrorMessage('failed_to_open_stream') : false;
            move_uploaded_file($this->options['File'], $this->options['FileName']);
        } else {
            $this->errorValidity('true', $file, null);
        }

        clearstatcache();

        $this->resizeImageProcess($this->options['FileName']);
        $this->uploadInfo();
    }

    protected function handleChunkUpload()
    {
        if (is_dir($this->removeFileName($this->options['FileName']))) {
            $file =$this->getErrorMessage('could_not_write_output') ? $this->getErrorMessage($this->options['FileError']) : false;
            $outPut = fopen($this->options['FileName'] . ".part", $this->chunks ? "ab" : "wb");
        } else {
            $this->errorValidity('true', $file, null);
        }

        if (is_dir($this->removeFileName($this->options['FileName']))) {
            $file = $this->getErrorMessage('could_not_read_input') ?  $this->getErrorMessage($this->options['FileError']) : false;
            $input  = fopen($this->options['File'], "rb");
        } else {
            $this->errorValidity('true', $file, null);
        }

        clearstatcache();

        while ($buffer = fread($input, 2048)) {
            fwrite($outPut, $buffer);
        }

        fclose($outPut);
        fclose($input);

        if ($this->chunk == $this->chunks) {
            rename($this->options['FileName'] . ".part", $this->options['FileName']);
            $this->resizeImageProcess($this->options['FileName']);
            $this->uploadInfo();
        }
    }

    /**
     * This function indicates the validity of the error
     * @param string $check
     * @param string $errorText
     * @param mixed $sprintf
     * @return void
     */
    protected function errorValidity($check, $errorText, $sprintf = null)
    {
        $json['check'] = $check;

        if ($sprintf == null) {
            $json['msg'] = $this->getErrorMessage($errorText);
        } else {
            $json['msg'] = sprintf($this->getErrorMessage($errorText), $sprintf);
        }

        $json['filename'] = basename($this->options['FileName']);
        $json['type'] = $this->options['FileType'];
        $json['error'] = $this->options['FileError'];
        print json_encode($json);
        die();
    }

    /**
     * Received image resizing handler
     * @param string $fileName
     * @return void
     */
    protected function resizeImageProcess($fileName)
    {
        if (is_file($fileName)) {
            $associatedParameters = array_combine($this->options['TempFolders'], $this->options['ResizeDimensions']);
            $size = getimagesize($fileName);

            foreach ($associatedParameters as $tempFolder => $resizeDimension) {

                if ($this->options['DestinationPath'] == null) {
                    $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . basename($fileName);
                } else {
                    $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . $this->options['DestinationPath'] . '/' . basename($fileName);
                }

                if ($resizeDimension < $size[0]) {
                    $this->resizeImage($fileName, $newPath, $resizeDimension);
                } else {
                    copy($fileName, $newPath);
                }
            }
        }
    }

    /**
     * Send file data
     * @return void
     */
    protected function uploadInfo()
    {
        print json_encode(array(
            'check' => 'false',
            'msg' => null,
            'filename' => basename($this->options['FileName']),
            'path' => $this->getRelativePath($this->options['FileName']),
            'extension' => $this->getExtension($this->options['FileName']),
            'type' => $this->getMimeType($this->options['FileName']),
            'error' => $this->options['FileError'],
            'size' => $this->options['FileSize'],
            'mtime' => filemtime($this->options['FileName']),
            'dimensions' => $this->getDimensions($this->options['FileName'])
        ));
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
        if(function_exists('mime_content_type')){
            return mime_content_type($path);
        } else {
            return function_exists('finfo_file') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : false;
        }
    }

    /**
     * Get size of an image
     * @param string $path
     * @return mixed|string
     */
    public static function getDimensions($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $ImageSize = getimagesize($path);

        if (in_array($ext, self::getImageExtensions()))
        {
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
     * @param $path
     * @param $newPath
     * @param $resizeDimensions
     * @return void
     */
    protected function resizeImage($path, $newPath, $resizeDimensions)
    {
        $json = array();
        $json['check'] = 'false';

        if (function_exists('exif_imagetype') && exif_imagetype($path) !== false) {
            // file size
            $fileSize = filesize($path);
            // imagesize
            $size = getimagesize($path);

            if (empty($size) || !is_array($size)) {
                $this->errorValidity('true', 'invalid_image', null);
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
                $this->errorValidity('true', 'failed_to_resize_image', null);
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
                $this->errorValidity('true', 'resizeimage_failed_to_create_and_resize_the_image', null);
            }

            // destroy image
            imagedestroy($newImage);
        }
    }
}
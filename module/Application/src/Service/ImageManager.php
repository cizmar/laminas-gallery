<?php
namespace Application\Service;

class ImageManager
{
    private $saveToDir = './data/upload/';

    private $thumbString = '_thumbnail';
        
    public function getSaveToDir()
    {
        return $this->saveToDir;
    }

    public function getThumbString()
    {
        return $this->thumbString;
    }

    // Returns the array of uploaded file names.
    public function getSavedFiles()
    {
        if (!is_dir($this->saveToDir)) {
            if (!mkdir($this->saveToDir)) {
                throw new \Exception('Could not create directory for uploads: ' . error_get_last());
            }
        }
         
        // Scan the directory and create the list of uploaded files.
        $files = [];
        $handle  = opendir($this->saveToDir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry=='.' || $entry=='..') {
                continue;
            } // Skip current dir and parent dir.
            if (str_contains($entry, $this->thumbString)) {
                continue;
            }
            $filename = pathinfo($entry, PATHINFO_FILENAME);
            $fileExt = pathinfo($entry, PATHINFO_EXTENSION);

            $data = \json_decode(\base64_decode($filename));
            $files[] = array(
                'filename' => $entry,
                'thumbnail' => $filename.$this->thumbString.'.'.$fileExt,
                'title' => $data->title,
                'description' => $data->description,
            );
        }
        return $files;
    }

    // Returns the path to the saved image file.
    public function getImagePathByName($fileName)
    {
        // Take some precautions to make file name secure.
        $fileName = str_replace("/", "", $fileName);  // Remove slashes.
        $fileName = str_replace("\\", "", $fileName); // Remove back-slashes.
                
        // Return concatenated directory name and file name.
        return $this->saveToDir . $fileName;
    }

    // Returns the image file content. On error, returns boolean false.
    public function getImageFileContent($filePath)
    {
        return file_get_contents($filePath);
    }

    // Retrieves the file information (size, MIME type) by image path.
    public function getImageFileInfo($filePath)
    {
        // Try to open file
        if (!is_readable($filePath)) {
            return false;
        }
            
        // Get file size in bytes.
        $fileSize = filesize($filePath);

        // Get MIME type of the file.
        $finfo = \finfo_open(FILEINFO_MIME);
        $mimeType = \finfo_file($finfo, $filePath);
        if ($mimeType===false) {
            $mimeType = 'application/octet-stream';
        }
    
        return [
            'size' => $fileSize,
            'type' => $mimeType
        ];
    }
    

    // Resizes the image, keeping its aspect ratio.
    public function resizeImage($filePath, $resizedFilePath, $desiredWidth = 240)
    {
        // Get original image dimensions.
        list($originalWidth, $originalHeight) = getimagesize($filePath);

        // Calculate aspect ratio
        $aspectRatio = $originalWidth/$originalHeight;
        // Calculate the resulting height
        $desiredHeight = $desiredWidth/$aspectRatio;

        // Get image info
        $fileInfo = $this->getImageFileInfo($filePath);
        
        // Resize the image
        $resultingImage = imagecreatetruecolor($desiredWidth, $desiredHeight);
        if (substr($fileInfo['type'], 0, 9) =='image/png') {
            $originalImage = imagecreatefrompng($filePath);
        } else {
            $originalImage = imagecreatefromjpeg($filePath);
        }
        imagecopyresampled($resultingImage, $originalImage, 0, 0, 0, 0, $desiredWidth, $desiredHeight, $originalWidth, $originalHeight);

        // Save the resized image to resizedFilePath
        imagejpeg($resultingImage, $resizedFilePath, 80);
        
        // Return the path to resulting image.
        return $resizedFilePath;
    }

    public function removeImageAndThumbnail($originFileName)
    {
        $filename = pathinfo($originFileName, PATHINFO_FILENAME);
        $fileExt = pathinfo($originFileName, PATHINFO_EXTENSION);

        unlink($this->getImagePathByName($originFileName));
        unlink($this->getImagePathByName($filename.$this->thumbString.'.'.$fileExt));
    }
}

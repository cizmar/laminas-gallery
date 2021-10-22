<?php
namespace Gallery\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

use Gallery\Form\ImageForm;
use Application\Service\ImageManager;

use Laminas\Filter\File\RenameUpload;

class ShowroomController extends AbstractActionController
{
    private $form;
    private $imageManager;

    public function __construct(ImageForm $form, ImageManager $imageManager)
    {
        $this->form = $form;
        $this->imageManager = $imageManager;
    }
    
    public function indexAction()
    {
        $files = $this->imageManager->getSavedFiles();
        
        return new ViewModel([
            'files' => $files
        ]);
    }

    public function addAction()
    {
        $request = $this->getRequest();
        $viewModel = new ViewModel(['form' => $this->form]);

        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = array_merge_recursive(
            $request->getPost()->toArray(),
            $request->getFiles()->toArray()
        );

        $this->form->setData($data);
        if (!$this->form->isValid()) {
            return $viewModel;
        }

        $data = $this->form->getData();
        $fileName = base64_encode(
            json_encode(
                array('title' => $data['title'], 'description' => $data['description'])
            )
        );
        $fileExt = pathinfo($data['file']['name'], PATHINFO_EXTENSION);
      
        $filter = new \Laminas\Filter\File\RenameUpload($this->imageManager->getSaveToDir().$fileName.'.'.$fileExt);
        $data = $filter->filter($data['file']);

        $this->imageManager->resizeImage($data['tmp_name'], $this->imageManager->getSaveToDir().$fileName.$this->imageManager->getThumbString().'.'.$fileExt);
      
        return $this->redirect()->toRoute('gallery');
    }


    public function fileAction()
    {
        // Get the file name from GET variable.
        $fileName = $this->params()->fromQuery('name', '');

        // Get path to image file.
        $fileName = $this->imageManager->getImagePathByName($fileName);
           
        // Get image file info (size and MIME type).
        $fileInfo = $this->imageManager->getImageFileInfo($fileName);
        if ($fileInfo===false) {
            // Set 404 Not Found status code
            $this->getResponse()->setStatusCode(404);
            return;
        }
                
        // Write HTTP headers.
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine("Content-type: " . $fileInfo['type']);
        $headers->addHeaderLine("Content-length: " . $fileInfo['size']);
            
        // Write file content.
        $fileContent = $this->imageManager->getImageFileContent($fileName);
        if ($fileContent!==false) {
            $response->setContent($fileContent);
        } else {
            // Set 500 Server Error status code.
            $this->getResponse()->setStatusCode(500);
            return;
        }
     
        // Return Response to avoid default view rendering.
        return $this->getResponse();
    }

    public function removeAction()
    {
        $fileName = $this->params()->fromQuery('name', '');
        $this->imageManager->removeImageAndThumbnail($fileName);
        return $this->redirect()->toRoute('gallery');
    }
}

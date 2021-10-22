<?php
namespace Gallery\Form;

use Laminas\Form\Form;

use Laminas\Validator;
use Laminas\Filter;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\FileInput;

// use Laminas\InputFilter\FileInput;

class ImageForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('album');
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        $this->add([
            'type' => 'text',
            'name' => 'title',
            'options' => [
                'label' => 'Title',
            ],
        ]);


        $this->add([
            'type' => 'file',
            'name' => 'file',
            'attributes' => [
                'id' => 'file'
            ],
            'options' => [
                'label' => 'Image file',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'description',
            'options' => [
                'label' => 'Description',
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Add Image',
            ],
        ]);
    }

    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter();

        $title = new Input('title');
        $title->getFilterChain()->attach(new Filter\StringTrim());
        $title->getValidatorChain()->attach(new Validator\StringLength(['max' => 35]));

        $inputFilter->add($title);

        $desc = new Input('description');
        $desc->getFilterChain()->attach(new Filter\StringTrim());
        $desc->getValidatorChain()->attach(new Validator\StringLength(['max' => 145]));
        $inputFilter->add($desc);

        $file = new FileInput('file');
        $file->getValidatorChain()->attach(new Validator\File\UploadFile());
        $this->setInputFilter($inputFilter);
    }
}

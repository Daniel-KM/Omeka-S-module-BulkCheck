<?php
namespace BulkCheck\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'process_mode',
            'type' => Element\Radio::class,
            'options' => [
                'label' => 'Check to process', // @translate
                'value_options' => [
                    'files_excess' => 'List files that are present in "/files/", but not in database', // @translate
                    'files_excess_move' => 'Move files that are present in "/files/", but not in database, into /files/check/', // @translate
                    'files_missing' => 'List files that are present in database, not in "/files/"', // @translate
                    'dirs_excess' => 'Remove empty directories in "/files/" (for module Archive Repertory)', // @translate
                    'filesize_check' => 'Check missing file sizes in database (not managed during upgrade to Omeka 1.2.0)', // @translate
                    'filesize_fix' => 'Fix all file sizes in database (for example after hard import)', // @translate
                    'filehash_check' => 'Check sha256 hashes of files', // @translate
                    'filehash_fix' => 'Fix wrong sha256 of files', // @translate
                    'db_session_check' => 'Check the size of of the database table of sessions (see logs)', // @translate
                    'db_session_clean' => 'Remove old sessions (more than 100 days)', // @translate
                ],
            ],
            'attributes' => [
                'id' => 'process_mode',
            ],
        ]);

        // Fix the formatting issue of the label in Omeka.
        $this->get('process_mode')->setLabelAttributes(['style' => 'display: inline-block']);

        $this->add([
            'name' => 'process',
            'type' => Element\Submit::class,
            'options' => [
                'label' => 'Run in background', // @translate
            ],
            'attributes' => [
                'id' => 'process',
                'value' => 'Process', // @translate
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'process_mode',
            'required' => false,
        ]);
    }
}
